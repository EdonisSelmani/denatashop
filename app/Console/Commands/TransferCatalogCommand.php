<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class TransferCatalogCommand extends Command
{
    protected $signature = 'catalog:transfer
        {--dry-run : Print counts and planned changes without writing}
        {--chunk=200 : Number of products to process per upsert batch}
        {--source=source : Source MySQL connection name}
        {--destination= : Destination connection name; defaults to database.default}';

    protected $description = 'Safely upsert categories, subcategories, and products from a source MySQL catalog into the default PostgreSQL database.';

    private int $processedProducts = 0;

    public function handle(): int
    {
        $sourceName = (string) $this->option('source');
        $destinationName = $this->option('destination') ?: config('database.default');
        $dryRun = (bool) $this->option('dry-run');
        $chunkSize = max(1, (int) $this->option('chunk'));

        if (! $this->validateSourceConfiguration($sourceName)) {
            return self::FAILURE;
        }

        $source = DB::connection($sourceName);
        $destination = DB::connection($destinationName);

        $this->printConnectionSummary('Source', $sourceName, $source);
        $this->printConnectionSummary('Destination', $destinationName, $destination);

        if (! in_array($source->getDriverName(), ['mysql', 'mariadb'], true)) {
            $this->error('The source connection must be mysql or mariadb.');

            return self::FAILURE;
        }

        if ($destination->getDriverName() !== 'pgsql') {
            $message = 'The destination connection is not pgsql. Apply mode is refused because this command is intended for Neon/PostgreSQL.';

            if (! $dryRun) {
                $this->error($message);

                return self::FAILURE;
            }

            $this->warn($message.' Continuing because this is a dry-run.');
        }

        if (! $this->validateTables($sourceName, $destinationName)) {
            return self::FAILURE;
        }

        $sourceCounts = $this->catalogCounts($sourceName);
        $destinationCounts = $this->catalogCounts($destinationName);
        $this->printCounts('Source totals', $sourceCounts);
        $this->printCounts('Destination totals before transfer', $destinationCounts);
        $this->printSourceImageSummary($sourceName);

        $orphans = $this->orphanCounts($sourceName);
        $this->line('Source subcategories without source category: '.$orphans['subcategories']);
        $this->line('Source products without source subcategory: '.$orphans['products']);

        if ($orphans['subcategories'] > 0 || $orphans['products'] > 0) {
            $this->error('Source catalog has broken relationships. Transfer stopped.');

            return self::FAILURE;
        }

        if ($dryRun) {
            $this->printMatchCounts($sourceName, $destinationName);
            $this->info('Dry-run complete. No destination rows were written.');

            return self::SUCCESS;
        }

        try {
            $destination->transaction(function () use ($sourceName, $destinationName, $chunkSize): void {
                $categoryIdBySourceId = $this->upsertCategories($sourceName, $destinationName);
                $subcategoryIdBySourceId = $this->upsertSubcategories($sourceName, $destinationName, $categoryIdBySourceId);
                $this->upsertProducts($sourceName, $destinationName, $subcategoryIdBySourceId, $chunkSize);
            });
        } catch (Throwable $exception) {
            $this->error('Transfer failed and destination transaction was rolled back: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->line('Products processed: '.$this->processedProducts);
        $this->printCounts('Destination totals after transfer', $this->catalogCounts($destinationName));

        if (! $this->validateDestinationMatchesSource($sourceName, $destinationName)) {
            return self::FAILURE;
        }

        $this->info('Catalog transfer complete. No destination rows were deleted.');

        return self::SUCCESS;
    }

    private function validateSourceConfiguration(string $sourceName): bool
    {
        $config = config("database.connections.{$sourceName}");

        if (! is_array($config)) {
            $this->error("Source connection [{$sourceName}] is not configured.");

            return false;
        }

        $missing = collect(['host', 'database', 'username'])
            ->filter(fn (string $key) => blank($config[$key] ?? null))
            ->values();

        if ($missing->isNotEmpty()) {
            $this->error('Source database connection is incomplete. Missing SOURCE_DB_ values for: '.$missing->implode(', '));

            return false;
        }

        return true;
    }

    private function printConnectionSummary(string $label, string $name, ConnectionInterface $connection): void
    {
        $this->line("{$label} connection: {$name}");
        $this->line("{$label} driver: ".$connection->getDriverName());
        $this->line("{$label} database: ".$connection->getDatabaseName());
    }

    private function validateTables(string $sourceName, string $destinationName): bool
    {
        foreach (['categories', 'subcategories', 'products'] as $table) {
            if (! Schema::connection($sourceName)->hasTable($table)) {
                $this->error("Source table [{$table}] is missing.");

                return false;
            }

            if (! Schema::connection($destinationName)->hasTable($table)) {
                $this->error("Destination table [{$table}] is missing. Run migrations before transfer.");

                return false;
            }
        }

        return true;
    }

    /**
     * @return array{categories:int, subcategories:int, products:int}
     */
    private function catalogCounts(string $connectionName): array
    {
        return [
            'categories' => DB::connection($connectionName)->table('categories')->count(),
            'subcategories' => DB::connection($connectionName)->table('subcategories')->count(),
            'products' => DB::connection($connectionName)->table('products')->count(),
        ];
    }

    /**
     * @param  array<string, int>  $counts
     */
    private function printCounts(string $label, array $counts): void
    {
        $this->line($label.': categories='.$counts['categories'].', subcategories='.$counts['subcategories'].', products='.$counts['products']);
    }

    /**
     * @return array{subcategories:int, products:int}
     */
    private function orphanCounts(string $sourceName): array
    {
        $source = DB::connection($sourceName);

        return [
            'subcategories' => $source->table('subcategories')
                ->leftJoin('categories', 'subcategories.category_id', '=', 'categories.id')
                ->whereNull('categories.id')
                ->count(),
            'products' => $source->table('products')
                ->leftJoin('subcategories', 'products.subcategory_id', '=', 'subcategories.id')
                ->whereNull('subcategories.id')
                ->count(),
        ];
    }

    private function printSourceImageSummary(string $sourceName): void
    {
        $source = DB::connection($sourceName);
        $imageReferences = $source->table('products')->whereNotNull('image')->count();
        $galleryReferences = Schema::connection($sourceName)->hasColumn('products', 'gallery')
            ? $source->table('products')->whereNotNull('gallery')->count()
            : 0;

        $this->line('Source product image references: '.$imageReferences);
        $this->line('Source product gallery references: '.$galleryReferences);
        $this->warn('Image files are not copied by this command; only safe database path references are preserved.');
    }

    private function printMatchCounts(string $sourceName, string $destinationName): void
    {
        $this->line('Destination rows matching source category slugs: '.$this->matchingSlugCount($sourceName, $destinationName, 'categories'));
        $this->line('Destination rows matching source subcategory slugs: '.$this->matchingSlugCount($sourceName, $destinationName, 'subcategories'));
        $this->line('Destination rows matching source product SKUs: '.$this->matchingProductSkuCount($sourceName, $destinationName));
    }

    private function matchingSlugCount(string $sourceName, string $destinationName, string $table): int
    {
        $slugs = $this->normalizedSourceRows($sourceName, $table)
            ->pluck('slug')
            ->filter()
            ->unique()
            ->values();

        if ($slugs->isEmpty()) {
            return 0;
        }

        return DB::connection($destinationName)->table($table)->whereIn('slug', $slugs)->count();
    }

    private function matchingProductSkuCount(string $sourceName, string $destinationName): int
    {
        $skus = DB::connection($sourceName)
            ->table('products')
            ->pluck('sku')
            ->map(fn ($sku) => $this->stringOrNull($sku))
            ->filter()
            ->unique()
            ->values();

        if ($skus->isEmpty()) {
            return 0;
        }

        return DB::connection($destinationName)->table('products')->whereIn('sku', $skus)->count();
    }

    /**
     * @return array<int, int>
     */
    private function upsertCategories(string $sourceName, string $destinationName): array
    {
        $rows = $this->normalizedSourceRows($sourceName, 'categories')
            ->map(fn (object $category) => [
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'image' => $category->image,
                'is_active' => $category->is_active,
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at,
            ])
            ->values()
            ->all();

        if ($rows !== []) {
            DB::connection($destinationName)->table('categories')->upsert($rows, ['slug'], [
                'name',
                'description',
                'image',
                'is_active',
                'updated_at',
            ]);
        }

        $slugs = collect($rows)->pluck('slug');
        $destinationIdsBySlug = DB::connection($destinationName)
            ->table('categories')
            ->whereIn('slug', $slugs)
            ->pluck('id', 'slug');

        return $this->normalizedSourceRows($sourceName, 'categories')
            ->mapWithKeys(fn (object $category) => [$category->id => $destinationIdsBySlug[$category->slug]])
            ->all();
    }

    /**
     * @param  array<int, int>  $categoryIdBySourceId
     * @return array<int, int>
     */
    private function upsertSubcategories(string $sourceName, string $destinationName, array $categoryIdBySourceId): array
    {
        $sourceRows = $this->normalizedSourceRows($sourceName, 'subcategories');
        $rows = $sourceRows
            ->map(function (object $subcategory) use ($categoryIdBySourceId) {
                if (! isset($categoryIdBySourceId[$subcategory->category_id])) {
                    throw new \RuntimeException("Missing destination category for source category id {$subcategory->category_id}.");
                }

                return [
                    'category_id' => $categoryIdBySourceId[$subcategory->category_id],
                    'name' => $subcategory->name,
                    'slug' => $subcategory->slug,
                    'description' => $subcategory->description,
                    'is_active' => $subcategory->is_active,
                    'created_at' => $subcategory->created_at,
                    'updated_at' => $subcategory->updated_at,
                ];
            })
            ->values()
            ->all();

        if ($rows !== []) {
            DB::connection($destinationName)->table('subcategories')->upsert($rows, ['slug'], [
                'category_id',
                'name',
                'description',
                'is_active',
                'updated_at',
            ]);
        }

        $slugs = collect($rows)->pluck('slug');
        $destinationIdsBySlug = DB::connection($destinationName)
            ->table('subcategories')
            ->whereIn('slug', $slugs)
            ->pluck('id', 'slug');

        return $sourceRows
            ->mapWithKeys(fn (object $subcategory) => [$subcategory->id => $destinationIdsBySlug[$subcategory->slug]])
            ->all();
    }

    /**
     * @param  array<int, int>  $subcategoryIdBySourceId
     */
    private function upsertProducts(string $sourceName, string $destinationName, array $subcategoryIdBySourceId, int $chunkSize): void
    {
        DB::connection($sourceName)
            ->table('products')
            ->orderBy('id')
            ->chunk($chunkSize, function (Collection $products) use ($destinationName, $subcategoryIdBySourceId): bool {
                $rows = $products->map(function (object $product) use ($subcategoryIdBySourceId) {
                    if (! isset($subcategoryIdBySourceId[$product->subcategory_id])) {
                        throw new \RuntimeException("Missing destination subcategory for source subcategory id {$product->subcategory_id}.");
                    }

                    return [
                        'subcategory_id' => $subcategoryIdBySourceId[$product->subcategory_id],
                        'name' => $this->stringOrNull($product->name) ?: 'Unnamed product',
                        'slug' => $this->stableSlug($product, 'product'),
                        'description' => $this->stringOrNull($product->description) ?: '',
                        'price' => $this->decimalString($product->price ?? 0),
                        'compare_price' => $this->nullableDecimalString($product->compare_price ?? null),
                        'stock' => (int) ($product->stock ?? 0),
                        'sku' => $this->stableSku($product),
                        'image' => $this->stringOrNull($product->image ?? null),
                        'gallery' => $this->normalizeJson($product->gallery ?? null),
                        'attributes' => $this->normalizeJson($product->attributes ?? null),
                        'is_active' => $this->toBoolean($product->is_active ?? true),
                        'is_featured' => $this->toBoolean($product->is_featured ?? false),
                        'created_at' => $this->timestampOrNow($product->created_at ?? null),
                        'updated_at' => $this->timestampOrNow($product->updated_at ?? null),
                    ];
                })->values()->all();

                if ($rows !== []) {
                    DB::connection($destinationName)->table('products')->upsert($rows, ['sku'], [
                        'subcategory_id',
                        'name',
                        'slug',
                        'description',
                        'price',
                        'compare_price',
                        'stock',
                        'image',
                        'gallery',
                        'attributes',
                        'is_active',
                        'is_featured',
                        'updated_at',
                    ]);
                }

                $this->processedProducts += count($rows);

                return true;
            });
    }

    private function validateDestinationMatchesSource(string $sourceName, string $destinationName): bool
    {
        $sourceCounts = $this->catalogCounts($sourceName);
        $categoryMatches = $this->matchingSlugCount($sourceName, $destinationName, 'categories');
        $subcategoryMatches = $this->matchingSlugCount($sourceName, $destinationName, 'subcategories');
        $productMatches = $this->matchingProductSkuCount($sourceName, $destinationName);

        $this->line('Validated destination category matches: '.$categoryMatches.'/'.$sourceCounts['categories']);
        $this->line('Validated destination subcategory matches: '.$subcategoryMatches.'/'.$sourceCounts['subcategories']);
        $this->line('Validated destination product matches: '.$productMatches.'/'.$sourceCounts['products']);

        if ($categoryMatches !== $sourceCounts['categories'] ||
            $subcategoryMatches !== $sourceCounts['subcategories'] ||
            $productMatches !== $sourceCounts['products']) {
            $this->error('Destination match counts do not equal source counts.');

            return false;
        }

        return true;
    }

    private function normalizedSourceRows(string $sourceName, string $table): Collection
    {
        return DB::connection($sourceName)
            ->table($table)
            ->orderBy('id')
            ->get()
            ->map(function (object $row) use ($table) {
                $row->name = $this->stringOrNull($row->name ?? null) ?: Str::headline(Str::singular($table)).' '.$row->id;
                $row->slug = $this->stableSlug($row, Str::singular($table));
                $row->description = $this->stringOrNull($row->description ?? null);
                $row->image = $this->stringOrNull($row->image ?? null);
                $row->is_active = $this->toBoolean($row->is_active ?? true);
                $row->created_at = $this->timestampOrNow($row->created_at ?? null);
                $row->updated_at = $this->timestampOrNow($row->updated_at ?? null);

                return $row;
            });
    }

    private function stableSlug(object $row, string $prefix): string
    {
        $slug = $this->stringOrNull($row->slug ?? null);

        if ($slug) {
            return $slug;
        }

        $base = Str::slug($this->stringOrNull($row->name ?? null) ?: "{$prefix}-{$row->id}");

        return $base ?: "{$prefix}-{$row->id}";
    }

    private function stableSku(object $row): string
    {
        $sku = $this->stringOrNull($row->sku ?? null);

        if ($sku) {
            return $sku;
        }

        return Str::upper($this->stableSlug($row, 'product'));
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
        return number_format((float) ($value ?? 0), 2, '.', '');
    }

    private function nullableDecimalString(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $this->decimalString($value);
    }

    private function normalizeJson(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        if (is_string($value)) {
            json_decode($value);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $value;
            }
        }

        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function toBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }

    private function timestampOrNow(mixed $value): string
    {
        return $this->stringOrNull($value) ?: now()->toDateTimeString();
    }
}
