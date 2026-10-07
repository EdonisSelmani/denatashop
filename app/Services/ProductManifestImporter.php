<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Imports the reviewed product manifest (FINAL-product-import-review.csv).
 *
 * plan() only reads: the CSV, the source images on disk and the current database.
 * execute() is the only method that writes, and it only writes the rows plan() marked valid.
 */
class ProductManifestImporter
{
    public const ELIGIBLE_STATUS = 'NEW';

    public const REQUIRED_HEADERS = [
        'row_no', 'status', 'supplier_code', 'proposed_sku', 'supplier_card_name', 'proposed_display_name',
        'cost_price', 'proposed_selling_price', 'stock', 'top_level_category', 'proposed_subcategory',
        'source_product_image', 'source_image_sha1', 'source_supplier_card', 'notes',
    ];

    public const IMAGE_DIRECTORY = 'products/vs';

    public const MAX_IMAGE_EDGE = 1600;

    private const ALLOWED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    public function __construct(
        private readonly ProductImageService $images,
        private readonly PriceNormalizer $prices,
    ) {}

    /**
     * Builds the full import plan without writing anything.
     *
     * @return array<string, mixed>
     */
    public function plan(string $csvPath, int $expectedStock, array $sharedSupplierCodes = []): array
    {
        if ($expectedStock < 0) {
            throw new RuntimeException('Expected stock must be zero or more.');
        }

        // Supplier codes explicitly approved to cover several distinct products (e.g. 1434 -> VS-MJE-1434-A / -B).
        $sharedSupplierCodes = array_fill_keys(array_map('strval', $sharedSupplierCodes), true);
        [$rows, $headerErrors] = $this->readCsv($csvPath);

        $plan = [
            'manifest' => $csvPath,
            'manifest_sha256' => is_file($csvPath) ? hash_file('sha256', $csvPath) : null,
            'shared_supplier_codes' => array_keys($sharedSupplierCodes),
            'expected_stock' => $expectedStock,
            'is_active_on_import' => false,
            'header_errors' => $headerErrors,
            'total_rows' => count($rows),
            'status_counts' => [],
            'excluded_counts' => [],
            'eligible' => 0,
            'valid' => [],
            'invalid' => [],
            'existing_sku_conflicts' => [],
            'supplier_code_conflicts' => [],
            'missing_images' => [],
            'unresolved_fields' => [],
            'categories_existing' => [],
            'categories_to_create' => [],
            'subcategories_existing' => [],
            'subcategories_to_create' => [],
            'images_to_copy' => [],
            'images_already_present' => [],
        ];

        if ($headerErrors !== []) {
            return $plan;
        }

        foreach ($rows as $row) {
            $plan['status_counts'][$row['status']] = ($plan['status_counts'][$row['status']] ?? 0) + 1;
        }

        $eligible = array_values(array_filter($rows, fn (array $row) => $row['status'] === self::ELIGIBLE_STATUS));
        $plan['eligible'] = count($eligible);
        $plan['excluded_counts'] = array_diff_key($plan['status_counts'], [self::ELIGIBLE_STATUS => true]);

        $existingSkus = Product::query()->pluck('sku')->mapWithKeys(fn ($sku) => [Str::upper((string) $sku) => (string) $sku])->all();
        $existingCodes = $this->existingSupplierCodes();
        $reservedSlugs = Product::query()->pluck('slug')->all();

        $skuCounts = array_count_values(array_map(fn (array $row) => Str::upper(trim($row['proposed_sku'])), $eligible));
        $codeCounts = array_count_values(array_map(fn (array $row) => trim($row['supplier_code']), $eligible));

        $categoryCache = [];
        $destinations = [];

        foreach ($eligible as $row) {
            $errors = [];
            $sku = trim($row['proposed_sku']);
            $code = trim($row['supplier_code']);
            $label = "row {$row['row_no']} ({$sku})";

            foreach (['proposed_sku', 'supplier_code', 'proposed_display_name', 'supplier_card_name', 'cost_price', 'proposed_selling_price', 'top_level_category', 'proposed_subcategory', 'source_product_image'] as $field) {
                $value = trim($row[$field]);
                if ($value === '' || Str::upper($value) === 'UNRESOLVED') {
                    $errors[] = "{$field} is unresolved";
                    $plan['unresolved_fields'][] = "{$label}: {$field}";
                }
            }

            if ($sku !== '' && ($skuCounts[Str::upper($sku)] ?? 0) > 1) {
                $errors[] = 'proposed_sku is duplicated in the manifest';
            }

            if ($code !== '' && ($codeCounts[$code] ?? 0) > 1 && ! isset($sharedSupplierCodes[$code])) {
                $errors[] = 'supplier_code is duplicated among eligible rows';
            }

            $cost = $this->cents($row['cost_price']);
            $selling = $this->cents($row['proposed_selling_price']);

            if (trim($row['cost_price']) !== 'UNRESOLVED' && $cost === null) {
                $errors[] = "cost_price '{$row['cost_price']}' is not a valid 2-decimal amount";
            }

            if (trim($row['proposed_selling_price']) !== 'UNRESOLVED' && $selling === null) {
                $errors[] = "proposed_selling_price '{$row['proposed_selling_price']}' is not a valid 2-decimal amount";
            }

            if ($cost !== null && $selling !== null && $this->sellingCents($cost) !== $selling) {
                $errors[] = sprintf('proposed_selling_price %s does not equal cost %s x 1.45 (expected %s)', $row['proposed_selling_price'], $row['cost_price'], $this->formatCents($this->sellingCents($cost)));
            }

            if ($selling !== null && $selling <= 0) {
                $errors[] = 'proposed_selling_price must be greater than zero';
            }

            // Stock comes only from the reviewed manifest and must equal the quantity confirmed on the command line.
            $stock = trim($row['stock']);
            if (! preg_match('/^\d+$/', $stock)) {
                $errors[] = "stock '{$row['stock']}' is not a whole number";
                $plan['unresolved_fields'][] = "{$label}: stock";
            } elseif ((int) $stock !== $expectedStock) {
                $errors[] = "stock {$stock} does not equal the confirmed stock {$expectedStock}";
            }

            $source = $this->localPath($row['source_product_image']);
            if (! is_file($source)) {
                $errors[] = 'source image not found';
                $plan['missing_images'][] = "{$label}: {$row['source_product_image']}";
            } elseif (! in_array(Str::lower(pathinfo($source, PATHINFO_EXTENSION)), self::ALLOWED_IMAGE_EXTENSIONS, true)) {
                $errors[] = 'source image type is not jpg, png or webp';
            }

            $category = null;
            $subcategory = null;
            if (trim($row['top_level_category']) !== '' && Str::upper(trim($row['top_level_category'])) !== 'UNRESOLVED'
                && trim($row['proposed_subcategory']) !== '' && Str::upper(trim($row['proposed_subcategory'])) !== 'UNRESOLVED') {
                [$category, $subcategory, $taxonomyError] = $this->resolveTaxonomy($row['top_level_category'], $row['proposed_subcategory'], $categoryCache);
                if ($taxonomyError) {
                    $errors[] = $taxonomyError;
                }
            }

            $isConflict = false;
            if ($sku !== '' && isset($existingSkus[Str::upper($sku)])) {
                $plan['existing_sku_conflicts'][] = "{$label}: SKU already exists as {$existingSkus[Str::upper($sku)]}; skipped, existing product not touched";
                $isConflict = true;
            }

            if ($code !== '' && isset($existingCodes[$code])) {
                $plan['supplier_code_conflicts'][] = "{$label}: supplier code {$code} already used by existing product {$existingCodes[$code]}; skipped";
                $isConflict = true;
            }

            if ($errors !== [] || $isConflict) {
                if ($errors !== []) {
                    $plan['invalid'][] = ['row_no' => $row['row_no'], 'sku' => $sku, 'errors' => $errors];
                }

                continue;
            }

            $destination = $this->destinationPath($row['source_product_image']);
            if (! isset($destinations[$destination])) {
                $destinations[$destination] = true;
                if (Storage::disk('public')->exists($destination)) {
                    $plan['images_already_present'][] = $destination;
                } else {
                    $plan['images_to_copy'][] = ['source' => $row['source_product_image'], 'destination' => $destination];
                }
            }

            $name = trim($row['proposed_display_name']);
            $plan['valid'][] = [
                'row_no' => (int) $row['row_no'],
                'sku' => $sku,
                'supplier_code' => $code,
                'name' => $name,
                'slug' => $this->uniqueSlug($name, $sku, $reservedSlugs),
                'price' => $this->formatCents($selling),
                'cost_price' => $this->formatCents($cost),
                'stock' => (int) $stock,
                'is_active' => false,
                'category' => $category,
                'subcategory' => $subcategory,
                'image' => $destination,
                'source_image' => $row['source_product_image'],
                'source_image_sha1' => trim($row['source_image_sha1']),
                'supplier_card' => trim($row['source_supplier_card']),
                'supplier_card_name' => trim($row['supplier_card_name']),
                'notes' => trim($row['notes']),
            ];
        }

        foreach ($categoryCache as $entry) {
            if ($entry['type'] === 'category') {
                $plan[$entry['exists'] ? 'categories_existing' : 'categories_to_create'][] = $entry['name'];
            } else {
                $plan[$entry['exists'] ? 'subcategories_existing' : 'subcategories_to_create'][] = $entry['category'].' / '.$entry['name'];
            }
        }

        return $plan;
    }

    /**
     * Writes the valid rows of a plan. Never updates or deletes existing rows.
     *
     * @param  array<string, mixed>  $plan
     * @return array<string, int>
     */
    public function execute(array $plan, bool $resizeImages = true): array
    {
        foreach ($plan['valid'] as $product) {
            if (! is_int($product['stock'] ?? null) || $product['stock'] !== $plan['expected_stock']) {
                throw new RuntimeException("Row {$product['row_no']} has stock that differs from the confirmed stock; nothing was imported.");
            }
        }

        if ($plan['header_errors'] !== []) {
            throw new RuntimeException('The manifest has header errors; nothing was imported.');
        }

        if ($resizeImages && ! extension_loaded('gd')) {
            throw new RuntimeException('The GD extension is required to resize images. Run PHP with GD enabled.');
        }

        $written = [];
        $created = ['categories' => 0, 'subcategories' => 0, 'products' => 0, 'skipped_existing' => 0, 'images_copied' => 0];

        try {
            foreach ($plan['images_to_copy'] as $image) {
                $disk = Storage::disk('public');
                if ($disk->exists($image['destination'])) {
                    continue;
                }

                $contents = $resizeImages
                    ? $this->resizedJpeg($this->localPath($image['source']))
                    : file_get_contents($this->localPath($image['source']));

                if ($contents === false || $contents === '') {
                    throw new RuntimeException("Could not read image {$image['source']}");
                }

                $disk->put($image['destination'], $contents);
                $written[] = $image['destination'];
                $created['images_copied']++;
            }

            DB::transaction(function () use ($plan, &$created) {
                $categoryIds = [];
                $subcategoryIds = [];
                $now = now();

                foreach ($plan['valid'] as $product) {
                    $categorySlug = Str::slug($product['category']);
                    if ($categorySlug === '' || Str::slug($product['subcategory']) === '' || $product['price'] === null || $product['image'] === '') {
                        throw new RuntimeException("Row {$product['row_no']} is incomplete; plan was modified after validation.");
                    }
                    if (! isset($categoryIds[$categorySlug])) {
                        $category = Category::query()->where('slug', $categorySlug)->first();
                        if (! $category) {
                            $category = Category::create(['name' => $product['category'], 'slug' => $categorySlug, 'is_active' => true]);
                            $created['categories']++;
                        }
                        $categoryIds[$categorySlug] = $category->id;
                    }

                    $subcategorySlug = Str::slug($product['subcategory']);
                    if (! isset($subcategoryIds[$subcategorySlug])) {
                        $subcategory = Subcategory::query()->where('slug', $subcategorySlug)->first();
                        if ($subcategory && $subcategory->category_id !== $categoryIds[$categorySlug]) {
                            throw new RuntimeException("Subcategory slug {$subcategorySlug} belongs to another category.");
                        }
                        if (! $subcategory) {
                            $subcategory = Subcategory::create([
                                'category_id' => $categoryIds[$categorySlug],
                                'name' => $product['subcategory'],
                                'slug' => $subcategorySlug,
                                'is_active' => true,
                            ]);
                            $created['subcategories']++;
                        }
                        $subcategoryIds[$subcategorySlug] = $subcategory->id;
                    }

                    // Re-checked inside the transaction so a second run, or a product added meanwhile, is skipped.
                    if (Product::query()->whereRaw('UPPER(sku) = ?', [Str::upper($product['sku'])])->exists()) {
                        $created['skipped_existing']++;

                        continue;
                    }

                    $slug = $product['slug'];
                    if (Product::query()->where('slug', $slug)->exists()) {
                        $slug = $slug.'-'.Str::slug($product['sku']);
                    }

                    Product::create([
                        'subcategory_id' => $subcategoryIds[$subcategorySlug],
                        'name' => $product['name'],
                        'slug' => $slug,
                        'description' => '',
                        'price' => $product['price'],
                        'compare_price' => null,
                        'sku' => $product['sku'],
                        'stock' => $product['stock'],
                        'image' => $product['image'],
                        'gallery' => null,
                        'attributes' => [
                            'supplier_code' => $product['supplier_code'],
                            'cost_price' => $product['cost_price'],
                            'import' => [
                                'source' => basename($plan['manifest']),
                                'manifest_sha256' => $plan['manifest_sha256'],
                                'manifest_row' => $product['row_no'],
                                'source_image' => $product['source_image'],
                                'source_image_sha1' => $product['source_image_sha1'],
                                'supplier_card' => $product['supplier_card'],
                                'supplier_card_name' => $product['supplier_card_name'],
                                'notes' => $product['notes'],
                                'imported_at' => $now->toIso8601String(),
                            ],
                        ],
                        // New products stay hidden until they are reviewed in the admin.
                        'is_active' => false,
                        'is_featured' => false,
                    ]);
                    $created['products']++;
                }
            });
        } catch (Throwable $exception) {
            foreach ($written as $path) {
                Storage::disk('public')->delete($path);
            }

            throw $exception;
        }

        if ($resizeImages) {
            foreach ($plan['valid'] as $product) {
                $this->images->makeThumbnail($product['image']);
            }
        }

        Cache::forget(PublicCatalogCache::NAVIGATION_CATEGORIES_KEY);
        Cache::forget(PublicCatalogCache::HOMEPAGE_SECTIONS_KEY);

        return $created;
    }

    /**
     * @return array{0: array<int, array<string, string>>, 1: array<int, string>}
     */
    private function readCsv(string $path): array
    {
        if (! is_file($path)) {
            return [[], ["Manifest not found: {$path}"]];
        }

        $handle = fopen($path, 'rb');
        $headers = fgetcsv($handle);

        if ($headers === false) {
            fclose($handle);

            return [[], ['Manifest is empty']];
        }

        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
        $headers = array_map('trim', $headers);
        $missing = array_diff(self::REQUIRED_HEADERS, $headers);

        if ($missing !== []) {
            fclose($handle);

            return [[], ['Manifest is missing columns: '.implode(', ', $missing)]];
        }

        $rows = [];
        $errors = [];
        $line = 1;
        while (($values = fgetcsv($handle)) !== false) {
            $line++;
            if ($values === [null]) {
                continue;
            }
            if (count($values) !== count($headers)) {
                $errors[] = "Manifest line {$line} has ".count($values).' columns, expected '.count($headers);

                continue;
            }
            $rows[] = array_combine($headers, $values);
        }
        fclose($handle);

        return [$rows, $errors];
    }

    /**
     * Supplier codes already used by existing products: from import metadata, and from VS-XXX-<code> SKUs.
     *
     * @return array<string, string>
     */
    private function existingSupplierCodes(): array
    {
        $codes = [];

        Product::query()->select(['sku', 'attributes'])->orderBy('id')->each(function (Product $product) use (&$codes) {
            $attributes = $product->getAttribute('attributes');
            if (is_array($attributes) && ! empty($attributes['supplier_code'])) {
                $codes[(string) $attributes['supplier_code']] ??= $product->sku;
            }
            if (preg_match('/^VS-[A-Z]+-(\d+)$/i', (string) $product->sku, $match)) {
                $codes[$match[1]] ??= $product->sku;
            }
        });

        return $codes;
    }

    /**
     * @param  array<string, array<string, mixed>>  $cache
     * @return array{0: string, 1: string, 2: ?string}
     */
    private function resolveTaxonomy(string $categoryName, string $subcategoryName, array &$cache): array
    {
        $categoryName = $this->cleanTaxonomyName($categoryName);
        $subcategoryName = $this->cleanTaxonomyName($subcategoryName);
        $categorySlug = Str::slug($categoryName);
        $subcategorySlug = Str::slug($subcategoryName);

        if ($categorySlug === '' || $subcategorySlug === '') {
            return [$categoryName, $subcategoryName, 'category or subcategory name produces an empty slug'];
        }

        $categoryKey = 'c:'.$categorySlug;
        if (! isset($cache[$categoryKey])) {
            $category = Category::query()->where('slug', $categorySlug)->first();
            $cache[$categoryKey] = ['type' => 'category', 'name' => $category->name ?? $categoryName, 'exists' => (bool) $category, 'id' => $category->id ?? null];
        }

        $subKey = 's:'.$subcategorySlug;
        if (! isset($cache[$subKey])) {
            $subcategory = Subcategory::query()->where('slug', $subcategorySlug)->first();
            $cache[$subKey] = [
                'type' => 'subcategory', 'name' => $subcategory->name ?? $subcategoryName, 'category' => $cache[$categoryKey]['name'],
                'exists' => (bool) $subcategory, 'category_id' => $subcategory->category_id ?? null, 'category_slug' => $categorySlug,
            ];
        }

        $sub = $cache[$subKey];
        if ($sub['category_slug'] !== $categorySlug) {
            return [$cache[$categoryKey]['name'], $sub['name'], "subcategory '{$subcategoryName}' is mapped to two different categories in the manifest"];
        }
        if ($sub['exists'] && $cache[$categoryKey]['exists'] && $sub['category_id'] !== $cache[$categoryKey]['id']) {
            return [$cache[$categoryKey]['name'], $sub['name'], "subcategory '{$subcategoryName}' already exists under a different category"];
        }
        if ($sub['exists'] && ! $cache[$categoryKey]['exists']) {
            return [$cache[$categoryKey]['name'], $sub['name'], "subcategory '{$subcategoryName}' already exists under a different category"];
        }

        return [$cache[$categoryKey]['name'], $sub['name'], null];
    }

    private function cleanTaxonomyName(string $name): string
    {
        return trim(preg_replace('/\s*\(NEW\)\s*$/i', '', trim($name)));
    }

    /**
     * Destination on the public disk, following the earlier import: products/vs/<USB-folder-slug>/<file>.jpg.
     */
    public function destinationPath(string $sourceImage): string
    {
        $normalized = str_replace('\\', '/', $sourceImage);
        $relative = preg_match('#Produktet VS/(.+)$#i', $normalized, $match) ? $match[1] : basename($normalized);
        $parts = explode('/', $relative);
        $file = array_pop($parts);
        $folder = $parts === [] ? 'misc' : trim(preg_replace('/[^A-Za-z0-9]+/', '-', implode(' ', $parts)), '-');

        $stem = pathinfo($file, PATHINFO_FILENAME);
        $safeStem = trim(preg_replace('/[^A-Za-z0-9]+/', '-', $stem), '-');
        if ($safeStem !== $stem) {
            // keeps "1434." and "1434" (two different products) apart
            $safeStem .= '-'.substr(sha1($file), 0, 6);
        }

        return self::IMAGE_DIRECTORY.'/'.$folder.'/'.($safeStem !== '' ? $safeStem : substr(sha1($file), 0, 12)).'.jpg';
    }

    private function resizedJpeg(string $path): string
    {
        $info = getimagesize($path);
        if (! $info) {
            throw new RuntimeException("Not a readable image: {$path}");
        }

        [$width, $height, $type] = $info;
        $source = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            IMAGETYPE_PNG => imagecreatefrompng($path),
            IMAGETYPE_WEBP => imagecreatefromwebp($path),
            default => throw new RuntimeException("Unsupported image type: {$path}"),
        };

        $ratio = min(self::MAX_IMAGE_EDGE / $width, self::MAX_IMAGE_EDGE / $height, 1);
        $targetWidth = max(1, (int) round($width * $ratio));
        $targetHeight = max(1, (int) round($height * $ratio));
        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        imagefill($target, 0, 0, imagecolorallocate($target, 255, 255, 255));
        imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        ob_start();
        imagejpeg($target, null, 85);
        $contents = (string) ob_get_clean();
        imagedestroy($source);
        imagedestroy($target);

        return $contents;
    }

    private function localPath(string $path): string
    {
        return str_replace('\\', DIRECTORY_SEPARATOR, trim($path));
    }

    private function cents(string $value): ?int
    {
        return preg_match('/^(\d{1,8})\.(\d{2})$/', trim($value), $match) ? ((int) $match[1]) * 100 + (int) $match[2] : null;
    }

    /** Cost x 1.45, then nearest EUR 0.05, entirely in integer arithmetic. */
    private function sellingCents(int $costCents): int
    {
        $centRounded = intdiv($costCents * 145 + 50, 100);

        return $this->prices->normalizeCents($centRounded);
    }

    private function formatCents(?int $cents): ?string
    {
        return $cents === null ? null : sprintf('%d.%02d', intdiv($cents, 100), $cents % 100);
    }

    /**
     * @param  array<int, string>  $reserved
     */
    private function uniqueSlug(string $name, string $sku, array &$reserved): string
    {
        $base = Str::slug($name) ?: 'product';
        $slug = in_array($base, $reserved, true) ? $base.'-'.Str::slug($sku) : $base;
        $counter = 2;
        while (in_array($slug, $reserved, true)) {
            $slug = $base.'-'.Str::slug($sku).'-'.$counter++;
        }
        $reserved[] = $slug;

        return $slug;
    }
}
