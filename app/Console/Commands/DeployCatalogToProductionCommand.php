<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class DeployCatalogToProductionCommand extends Command
{
    protected $signature = 'catalog:deploy-to-production
        {--dry-run : Validate and report planned changes without writing}
        {--allow-testing-override : Allow local/testing dry-runs outside production PostgreSQL}
        {--catalog=storage/app/import/recovery/denatashop-final-catalog-candidate.csv : Final catalog CSV path}
        {--bateria-prices=storage/app/import/recovery/bateria-price-audit.csv : Approved Bateria price audit CSV path}';

    protected $description = 'Safely import the approved catalog CSV into production PostgreSQL and apply Bateria price overrides.';

    /** @var array<int, string> */
    private array $validationErrors = [];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $allowTestingOverride = (bool) $this->option('allow-testing-override');
        $connection = DB::connection();

        if (! $this->guardEnvironment($connection, $dryRun, $allowTestingOverride)) {
            return self::FAILURE;
        }

        if (! $this->validateTables($connection->getName())) {
            return self::FAILURE;
        }

        $catalogPath = $this->resolvePath((string) $this->option('catalog'));
        $bateriaPath = $this->resolvePath((string) $this->option('bateria-prices'));

        $catalogRows = $this->readCsv($catalogPath, [
            'category',
            'subcategory',
            'name',
            'description',
            'price',
            'compare_price',
            'stock',
            'sku',
            'image',
            'is_active',
            'is_featured',
        ]);
        $bateriaRows = $this->readCsv($bateriaPath, [
            'sku',
            'proposed_price',
        ]);

        if ($this->validationErrors !== []) {
            $this->printValidationErrors();

            return self::FAILURE;
        }

        $catalog = $this->buildCatalog($catalogRows);
        $bateriaOverrides = $this->applyBateriaPrices($catalog, $bateriaRows);

        if ($this->validationErrors !== []) {
            $this->printValidationErrors();

            return self::FAILURE;
        }

        $plan = $this->buildPlan($catalog, $bateriaOverrides, $connection);
        $this->printPlan($plan, $catalogPath, $bateriaPath, $dryRun, $connection);

        if ($this->validationErrors !== []) {
            $this->printValidationErrors();

            return self::FAILURE;
        }

        if ($dryRun) {
            $this->info('Dry-run complete. No database rows were written.');

            return self::SUCCESS;
        }

        try {
            $connection->transaction(function () use ($catalog, $plan, $connection): void {
                $this->applyCatalog($catalog, $plan, $connection);
            });
        } catch (Throwable $exception) {
            $this->error('Catalog deployment failed and the transaction was rolled back: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Production catalog deployment completed.');
        $this->line('Final product count: '.$connection->table('products')->count());

        return self::SUCCESS;
    }

    private function guardEnvironment(ConnectionInterface $connection, bool $dryRun, bool $allowTestingOverride): bool
    {
        $defaultConnection = (string) config('database.default');
        $driver = $connection->getDriverName();
        $environment = (string) app()->environment();
        $overrideAllowed = $dryRun && $allowTestingOverride;

        $this->line('Application environment: '.$environment);
        $this->line('Database connection: '.$defaultConnection);
        $this->line('Database driver: '.$driver);
        $this->line('Database name: '.$connection->getDatabaseName());

        if ($defaultConnection !== 'pgsql' || $driver !== 'pgsql') {
            $message = 'Refusing to run because DB_CONNECTION is not pgsql.';

            if (! $overrideAllowed) {
                $this->error($message);

                return false;
            }

            $this->warn($message.' Continuing only because --dry-run and --allow-testing-override were provided.');
        }

        if ($environment !== 'production') {
            $message = 'Refusing to run because APP_ENV is not production.';

            if (! $overrideAllowed) {
                $this->error($message);

                return false;
            }

            $this->warn($message.' Continuing only because --dry-run and --allow-testing-override were provided.');
        }

        return true;
    }

    private function validateTables(string $connectionName): bool
    {
        foreach (['categories', 'subcategories', 'products'] as $table) {
            if (! Schema::connection($connectionName)->hasTable($table)) {
                $this->error("Required table [{$table}] is missing. Run migrations before catalog deployment.");

                return false;
            }
        }

        return true;
    }

    private function resolvePath(string $path): string
    {
        if (preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) === 1 || str_starts_with($path, DIRECTORY_SEPARATOR)) {
            return $path;
        }

        return base_path($path);
    }

    /**
     * @param  array<int, string>  $requiredHeaders
     * @return array<int, array<string, string>>
     */
    private function readCsv(string $path, array $requiredHeaders): array
    {
        if (! is_file($path)) {
            $this->validationErrors[] = "CSV file not found: {$path}";

            return [];
        }

        $handle = fopen($path, 'rb');

        if ($handle === false) {
            $this->validationErrors[] = "Unable to open CSV file: {$path}";

            return [];
        }

        $headers = fgetcsv($handle);

        if ($headers === false) {
            fclose($handle);
            $this->validationErrors[] = "CSV file has no header row: {$path}";

            return [];
        }

        $headers = array_map(fn (string $header): string => $this->normalizeHeader($header), $headers);
        $missing = array_diff($requiredHeaders, $headers);

        if ($missing !== []) {
            fclose($handle);
            $this->validationErrors[] = "CSV file {$path} is missing required columns: ".implode(', ', $missing);

            return [];
        }

        $rows = [];
        $line = 1;

        while (($record = fgetcsv($handle)) !== false) {
            $line++;

            if (count($record) !== count($headers)) {
                $this->validationErrors[] = "CSV file {$path} line {$line} has ".count($record).' columns; expected '.count($headers).'.';
                continue;
            }

            $row = array_combine($headers, $record);

            if ($row === false) {
                $this->validationErrors[] = "CSV file {$path} line {$line} could not be read.";
                continue;
            }

            $rows[] = array_map(static fn (mixed $value): string => trim((string) $value), $row);
        }

        fclose($handle);

        return $rows;
    }

    private function normalizeHeader(string $header): string
    {
        $header = str_replace("\xEF\xBB\xBF", '', $header);
        $header = preg_replace('/^\x{FEFF}/u', '', $header) ?? $header;

        return trim($header, "\" \t\n\r\0\x0B");
    }

    /**
     * @param  array<int, array<string, string>>  $rows
     * @return array{
     *     categories: array<string, array<string, mixed>>,
     *     subcategories: array<string, array<string, mixed>>,
     *     products: array<string, array<string, mixed>>,
     *     base_price_adjustments: array<int, array<string, string>>,
     *     missing_images: array<int, array<string, string>>
     * }
     */
    private function buildCatalog(array $rows): array
    {
        $categories = [];
        $subcategories = [];
        $products = [];
        $basePriceAdjustments = [];
        $missingImages = [];
        $skuCounts = [];

        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $sku = $this->stringOrNull($row['sku'] ?? null);

            if ($sku === null) {
                $this->validationErrors[] = "Catalog CSV line {$line} has an empty SKU.";
                continue;
            }

            $skuCounts[$sku] = ($skuCounts[$sku] ?? 0) + 1;

            $categoryName = $this->stringOrNull($row['category'] ?? null);
            $subcategoryName = $this->stringOrNull($row['subcategory'] ?? null);
            $name = $this->stringOrNull($row['name'] ?? null);

            if ($categoryName === null || $subcategoryName === null || $name === null) {
                $this->validationErrors[] = "Catalog CSV line {$line} for SKU {$sku} is missing category, subcategory, or name.";
                continue;
            }

            if (! is_numeric($row['price'] ?? null)) {
                $this->validationErrors[] = "Catalog CSV line {$line} for SKU {$sku} has an invalid price.";
                continue;
            }

            $categorySlug = $this->slugFor($categoryName, 'category');
            $subcategorySlug = $this->slugFor($subcategoryName, 'subcategory');

            $categories[$categorySlug] ??= [
                'name' => $categoryName,
                'slug' => $categorySlug,
                'description' => "Produkte {$categoryName}",
                'image' => null,
                'is_active' => true,
            ];

            if (($categories[$categorySlug]['name'] ?? null) !== $categoryName) {
                $this->validationErrors[] = "Category slug collision [{$categorySlug}] between [{$categories[$categorySlug]['name']}] and [{$categoryName}].";
            }

            $subcategories[$subcategorySlug] ??= [
                'category_slug' => $categorySlug,
                'name' => $subcategoryName,
                'slug' => $subcategorySlug,
                'description' => "Produkte {$subcategoryName}",
                'is_active' => true,
            ];

            if (($subcategories[$subcategorySlug]['category_slug'] ?? null) !== $categorySlug ||
                ($subcategories[$subcategorySlug]['name'] ?? null) !== $subcategoryName) {
                $this->validationErrors[] = "Subcategory slug collision [{$subcategorySlug}] has conflicting category or name values.";
            }

            $rawPrice = (float) $row['price'];
            $price = $this->boundedPrice($rawPrice);

            if (abs($rawPrice - $price) >= 0.005) {
                $basePriceAdjustments[] = [
                    'sku' => $sku,
                    'source_price' => $this->decimalString($rawPrice),
                    'bounded_price' => $this->decimalString($price),
                ];
            }

            $image = $this->stringOrNull($row['image'] ?? null);

            if ($image !== null && ! is_file(storage_path('app/public/'.$image))) {
                $missingImages[] = [
                    'sku' => $sku,
                    'image' => $image,
                ];
            }

            $products[$sku] = [
                'category_slug' => $categorySlug,
                'subcategory_slug' => $subcategorySlug,
                'name' => $name,
                'description' => $this->stringOrNull($row['description'] ?? null) ?? '',
                'price' => $this->decimalString($price),
                'compare_price' => $this->nullableDecimalString($row['compare_price'] ?? null),
                'stock' => (int) ($row['stock'] ?? 0),
                'sku' => $sku,
                'image' => $image,
                'is_active' => $this->toBoolean($row['is_active'] ?? true),
                'is_featured' => $this->toBoolean($row['is_featured'] ?? false),
            ];
        }

        foreach ($skuCounts as $sku => $count) {
            if ($count > 1) {
                $this->validationErrors[] = "Duplicate SKU in catalog CSV: {$sku}";
            }
        }

        return [
            'categories' => $categories,
            'subcategories' => $subcategories,
            'products' => $products,
            'base_price_adjustments' => $basePriceAdjustments,
            'missing_images' => $missingImages,
        ];
    }

    /**
     * @param  array<string, mixed>  $catalog
     * @param  array<int, array<string, string>>  $rows
     * @return array<string, array{old_price:string, new_price:string}>
     */
    private function applyBateriaPrices(array &$catalog, array $rows): array
    {
        $skuCounts = [];
        $overrides = [];

        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $sku = $this->stringOrNull($row['sku'] ?? null);

            if ($sku === null) {
                $this->validationErrors[] = "Bateria price CSV line {$line} has an empty SKU.";
                continue;
            }

            $skuCounts[$sku] = ($skuCounts[$sku] ?? 0) + 1;

            if (! str_starts_with($sku, 'BAT')) {
                $this->validationErrors[] = "Bateria price CSV line {$line} has non-Bateria SKU {$sku}.";
                continue;
            }

            if (! isset($catalog['products'][$sku])) {
                $this->validationErrors[] = "Bateria price CSV SKU {$sku} is missing from the final catalog CSV.";
                continue;
            }

            if (! is_numeric($row['proposed_price'] ?? null)) {
                $this->validationErrors[] = "Bateria price CSV SKU {$sku} has an invalid proposed_price.";
                continue;
            }

            $price = round((float) $row['proposed_price'], 2);

            if ($price < 1.50 || $price > 100.00) {
                $this->validationErrors[] = "Bateria price CSV SKU {$sku} has proposed_price outside 1.50-100.00.";
                continue;
            }

            $oldPrice = (string) $catalog['products'][$sku]['price'];
            $newPrice = $this->decimalString($price);
            $catalog['products'][$sku]['price'] = $newPrice;
            $overrides[$sku] = [
                'old_price' => $oldPrice,
                'new_price' => $newPrice,
            ];
        }

        foreach ($skuCounts as $sku => $count) {
            if ($count > 1) {
                $this->validationErrors[] = "Duplicate SKU in Bateria price CSV: {$sku}";
            }
        }

        return $overrides;
    }

    /**
     * @param  array<string, mixed>  $catalog
     * @param  array<string, array{old_price:string, new_price:string}>  $bateriaOverrides
     * @return array<string, mixed>
     */
    private function buildPlan(array $catalog, array $bateriaOverrides, ConnectionInterface $connection): array
    {
        $categorySlugs = array_keys($catalog['categories']);
        $subcategorySlugs = array_keys($catalog['subcategories']);
        $productSkus = array_keys($catalog['products']);

        $existingCategories = $connection->table('categories')
            ->whereIn('slug', $categorySlugs)
            ->get()
            ->keyBy('slug');
        $existingSubcategories = $connection->table('subcategories')
            ->whereIn('slug', $subcategorySlugs)
            ->get()
            ->keyBy('slug');
        $existingProducts = $connection->table('products')
            ->whereIn('sku', $productSkus)
            ->get()
            ->keyBy('sku');
        $existingSubcategoriesById = $connection->table('subcategories')
            ->get(['id', 'slug'])
            ->keyBy('id');

        $allProductSlugs = $connection->table('products')
            ->pluck('sku', 'slug')
            ->mapWithKeys(fn ($sku, $slug) => [(string) $slug => (string) $sku])
            ->all();

        $productsToInsert = [];
        $productsToUpdate = [];
        $unchangedProducts = [];
        $plannedProducts = [];
        $reservedSlugs = array_keys($allProductSlugs);

        foreach ($catalog['products'] as $sku => $product) {
            $existing = $existingProducts->get($sku);
            $product['slug'] = $existing?->slug ?: $this->uniqueProductSlug((string) $product['name'], $sku, $reservedSlugs);
            $plannedProducts[$sku] = $product;

            if ($existing === null) {
                $productsToInsert[] = $sku;
                continue;
            }

            $currentSubcategorySlug = null;

            if ($existing->subcategory_id !== null && $existingSubcategoriesById->has($existing->subcategory_id)) {
                $currentSubcategorySlug = (string) $existingSubcategoriesById->get($existing->subcategory_id)->slug;
            }

            if ($this->productChanged($product, $existing, $currentSubcategorySlug)) {
                $productsToUpdate[] = $sku;
                continue;
            }

            $unchangedProducts[] = $sku;
        }

        $finalProductCount = $connection->table('products')->count() + count($productsToInsert);
        $bateriaProductsToUpdate = collect($bateriaOverrides)
            ->filter(function (array $override, string $sku) use ($existingProducts): bool {
                $existing = $existingProducts->get($sku);

                if ($existing === null) {
                    return true;
                }

                return abs(((float) $existing->price) - ((float) $override['new_price'])) >= 0.005;
            })
            ->count();
        $prices = array_map(static fn (array $product): float => (float) $product['price'], $plannedProducts);

        return [
            'source_counts' => [
                'categories' => count($catalog['categories']),
                'subcategories' => count($catalog['subcategories']),
                'products' => count($catalog['products']),
            ],
            'missing_categories' => array_values(array_diff($categorySlugs, $existingCategories->keys()->all())),
            'missing_subcategories' => array_values(array_diff($subcategorySlugs, $existingSubcategories->keys()->all())),
            'products_to_insert' => $productsToInsert,
            'products_to_update' => $productsToUpdate,
            'unchanged_products' => $unchangedProducts,
            'planned_products' => $plannedProducts,
            'final_product_count' => $finalProductCount,
            'bateria_overrides' => $bateriaOverrides,
            'bateria_products_to_update' => $bateriaProductsToUpdate,
            'price_range' => [
                'minimum' => $this->decimalString(min($prices)),
                'maximum' => $this->decimalString(max($prices)),
                'average' => $this->decimalString(array_sum($prices) / count($prices)),
            ],
            'base_price_adjustments' => $catalog['base_price_adjustments'],
            'missing_images' => $catalog['missing_images'],
            'duplicate_skus' => [],
        ];
    }

    private function productChanged(array $planned, object $existing, ?string $currentSubcategorySlug): bool
    {
        $comparisons = [
            'subcategory_slug' => [$currentSubcategorySlug, $planned['subcategory_slug']],
            'name' => [(string) $existing->name, $planned['name']],
            'description' => [(string) $existing->description, $planned['description']],
            'price' => [$this->decimalString($existing->price), $planned['price']],
            'compare_price' => [$this->nullableDecimalString($existing->compare_price), $planned['compare_price']],
            'stock' => [(int) $existing->stock, $planned['stock']],
            'image' => [$this->stringOrNull($existing->image), $planned['image']],
            'is_active' => [$this->toBoolean($existing->is_active), $planned['is_active']],
            'is_featured' => [$this->toBoolean($existing->is_featured), $planned['is_featured']],
        ];

        foreach ($comparisons as [$current, $intended]) {
            if ($current !== $intended) {
                return true;
            }
        }

        return false;
    }

    private function printPlan(array $plan, string $catalogPath, string $bateriaPath, bool $dryRun, ConnectionInterface $connection): void
    {
        $this->info($dryRun ? 'DRY RUN: no database rows will be written.' : 'Applying production catalog import.');
        $this->line('Catalog CSV: '.$catalogPath);
        $this->line('Bateria price CSV: '.$bateriaPath);
        $this->line('Catalog source products: '.$plan['source_counts']['products']);
        $this->line('Catalog source categories: '.$plan['source_counts']['categories']);
        $this->line('Catalog source subcategories: '.$plan['source_counts']['subcategories']);
        $this->line('Products to insert: '.count($plan['products_to_insert']));
        $this->line('Products to update: '.count($plan['products_to_update']));
        $this->line('Unchanged products: '.count($plan['unchanged_products']));
        $this->line('Missing categories to create: '.count($plan['missing_categories']));
        $this->line('Missing subcategories to create: '.count($plan['missing_subcategories']));
        $this->line('Missing images: '.count($plan['missing_images']));
        $this->line('Duplicate SKUs: '.count($plan['duplicate_skus']));
        $this->line('Final product count after import: '.$plan['final_product_count']);
        $this->line('Bateria price overrides in CSV: '.count($plan['bateria_overrides']));
        $this->line('Bateria products to update/insert: '.$plan['bateria_products_to_update']);
        $this->line('Price range: min='.$plan['price_range']['minimum'].', max='.$plan['price_range']['maximum'].', avg='.$plan['price_range']['average']);
        $this->line('Base catalog prices normalized to allowed range: '.count($plan['base_price_adjustments']));
        $this->line('Destination current product count: '.$connection->table('products')->count());

        $this->printSample('Missing category slugs', $plan['missing_categories']);
        $this->printSample('Missing subcategory slugs', $plan['missing_subcategories']);
        $this->printSample('Products to insert', $plan['products_to_insert']);
        $this->printSample('Products to update', $plan['products_to_update']);
        $this->printMissingImages($plan['missing_images']);
    }

    /**
     * @param  array<int, string>  $values
     */
    private function printSample(string $label, array $values): void
    {
        if ($values === []) {
            return;
        }

        $sample = array_slice($values, 0, 20);
        $suffix = count($values) > 20 ? ' ...' : '';
        $this->line($label.': '.implode(', ', $sample).$suffix);
    }

    /**
     * @param  array<int, array<string, string>>  $missingImages
     */
    private function printMissingImages(array $missingImages): void
    {
        if ($missingImages === []) {
            return;
        }

        $sample = array_slice($missingImages, 0, 10);
        $this->warn('Missing image sample:');

        foreach ($sample as $item) {
            $this->line($item['sku'].' -> '.$item['image']);
        }

        if (count($missingImages) > 10) {
            $this->line('...');
        }
    }

    /**
     * @param  array<string, mixed>  $catalog
     * @param  array<string, mixed>  $plan
     */
    private function applyCatalog(array $catalog, array $plan, ConnectionInterface $connection): void
    {
        $now = now()->toDateTimeString();

        foreach ($catalog['categories'] as $category) {
            $this->insertOrUpdateChanged($connection, 'categories', ['slug' => $category['slug']], [
                'name' => $category['name'],
                'description' => $category['description'],
                'image' => $category['image'],
                'is_active' => $category['is_active'],
            ], [
                'name' => $category['name'],
                'is_active' => $category['is_active'],
            ], $now);
        }

        $categoryIdsBySlug = $connection->table('categories')
            ->whereIn('slug', array_keys($catalog['categories']))
            ->pluck('id', 'slug')
            ->all();

        foreach ($catalog['subcategories'] as $subcategory) {
            $categoryId = $categoryIdsBySlug[$subcategory['category_slug']] ?? null;

            if ($categoryId === null) {
                throw new \RuntimeException("Missing category id for subcategory {$subcategory['slug']}.");
            }

            $this->insertOrUpdateChanged($connection, 'subcategories', ['slug' => $subcategory['slug']], [
                'category_id' => $categoryId,
                'name' => $subcategory['name'],
                'description' => $subcategory['description'],
                'is_active' => $subcategory['is_active'],
            ], [
                'category_id' => $categoryId,
                'name' => $subcategory['name'],
                'is_active' => $subcategory['is_active'],
            ], $now);
        }

        $subcategoryIdsBySlug = $connection->table('subcategories')
            ->whereIn('slug', array_keys($catalog['subcategories']))
            ->pluck('id', 'slug')
            ->all();

        foreach ($plan['planned_products'] as $sku => $product) {
            $subcategoryId = $subcategoryIdsBySlug[$product['subcategory_slug']] ?? null;

            if ($subcategoryId === null) {
                throw new \RuntimeException("Missing subcategory id for product {$sku}.");
            }

            $values = [
                'subcategory_id' => $subcategoryId,
                'name' => $product['name'],
                'description' => $product['description'],
                'price' => $product['price'],
                'compare_price' => $product['compare_price'],
                'stock' => $product['stock'],
                'image' => $product['image'],
                'is_active' => $product['is_active'],
                'is_featured' => $product['is_featured'],
            ];

            if (in_array($sku, $plan['products_to_insert'], true)) {
                $values['slug'] = $product['slug'];
            }

            $this->updateOrInsertChanged($connection, 'products', ['sku' => $sku], $values, $now);
        }
    }

    /**
     * @param  array<string, mixed>  $keys
     * @param  array<string, mixed>  $values
     */
    private function insertOrUpdateChanged(
        ConnectionInterface $connection,
        string $table,
        array $keys,
        array $insertValues,
        array $updateValues,
        string $now
    ): void {
        $query = $connection->table($table);

        foreach ($keys as $key => $value) {
            $query->where($key, $value);
        }

        $existing = $query->first();

        if ($existing === null) {
            $connection->table($table)->insert(array_merge($keys, $insertValues, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));

            return;
        }

        foreach ($updateValues as $field => $value) {
            $current = $existing->{$field} ?? null;

            if (is_bool($value)) {
                $current = $this->toBoolean($current);
            }

            if ($current !== $value) {
                $connection->table($table)
                    ->where('id', $existing->id)
                    ->update(array_merge($updateValues, ['updated_at' => $now]));

                return;
            }
        }
    }

    /**
     * @param  array<string, mixed>  $keys
     * @param  array<string, mixed>  $values
     */
    private function updateOrInsertChanged(ConnectionInterface $connection, string $table, array $keys, array $values, string $now): void
    {
        $query = $connection->table($table);

        foreach ($keys as $key => $value) {
            $query->where($key, $value);
        }

        $existing = $query->first();

        if ($existing === null) {
            $connection->table($table)->insert(array_merge($keys, $values, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));

            return;
        }

        foreach ($values as $field => $value) {
            $current = $existing->{$field} ?? null;

            if ($field === 'price' || $field === 'compare_price') {
                $current = $this->nullableDecimalString($current);
            }

            if (is_bool($value)) {
                $current = $this->toBoolean($current);
            }

            if ($current !== $value) {
                $connection->table($table)
                    ->where('id', $existing->id)
                    ->update(array_merge($values, ['updated_at' => $now]));

                return;
            }
        }
    }

    /**
     * @param  array<int, string>  $reservedSlugs
     */
    private function uniqueProductSlug(string $name, string $sku, array &$reservedSlugs): string
    {
        $base = $this->slugFor($name, 'product');
        $slug = $base;

        if (in_array($slug, $reservedSlugs, true)) {
            $skuSlug = Str::slug($sku) ?: Str::lower(preg_replace('/[^A-Za-z0-9]+/', '-', $sku) ?: $sku);
            $slug = "{$base}-{$skuSlug}";
        }

        $counter = 2;

        while (in_array($slug, $reservedSlugs, true)) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        $reservedSlugs[] = $slug;

        return $slug;
    }

    private function slugFor(string $value, string $fallbackPrefix): string
    {
        $slug = Str::slug($value);

        return $slug !== '' ? $slug : $fallbackPrefix.'-'.substr(sha1($value), 0, 10);
    }

    private function stringOrNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    private function decimalString(mixed $value): string
    {
        return number_format(round((float) $value, 2), 2, '.', '');
    }

    private function nullableDecimalString(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $this->decimalString($value);
    }

    private function boundedPrice(float $price): float
    {
        if ($price < 1.50) {
            return 1.50;
        }

        if ($price > 100.00) {
            return 100.00;
        }

        return round($price, 2);
    }

    private function toBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(Str::lower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }

    private function printValidationErrors(): void
    {
        foreach ($this->validationErrors as $error) {
            $this->error($error);
        }
    }
}
