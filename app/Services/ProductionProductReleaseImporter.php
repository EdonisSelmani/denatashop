<?php

namespace App\Services;

use App\Console\Commands\BuildProductionProductReleaseCommand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;

class ProductionProductReleaseImporter
{
    public const ARTIFACT_SHA256 = '6c98fd0e940213662155c6816a94bbe3864db602f4a08316bd5dc36d5d15bf00';

    public const ASSET_MANIFEST_SHA256 = '7e466040d8a54ab922b7884b13bf726f8aabec5dc84646f374a3e9e975992243';

    public function __construct(private readonly PriceNormalizer $prices) {}

    /** @return array<string, mixed> */
    public function validate(string $artifactPath, string $assetManifestPath): array
    {
        $artifactPath = $this->absolutePath($artifactPath);
        $assetManifestPath = $this->absolutePath($assetManifestPath);
        $artifact = $this->readJson($artifactPath, self::ARTIFACT_SHA256, 'product artifact');
        $assetManifest = $this->readJson($assetManifestPath, self::ASSET_MANIFEST_SHA256, 'asset manifest');

        if (preg_match('/[A-Za-z]:\\\\/', File::get($artifactPath)) === 1) {
            throw new RuntimeException('The production artifact contains a local drive path.');
        }

        $this->validateArtifact($artifact);
        $assetCounts = $this->validateAssets($assetManifest, $artifactPath, $artifact);

        return [
            'artifact' => $artifact,
            'artifact_path' => $artifactPath,
            'asset_manifest_path' => $assetManifestPath,
            'artifact_sha256' => self::ARTIFACT_SHA256,
            'asset_manifest_sha256' => self::ASSET_MANIFEST_SHA256,
            'product_count' => count($artifact['products']),
            'category_count' => count($artifact['categories']),
            'subcategory_count' => count($artifact['subcategories']),
            'unique_image_count' => count(array_unique(array_column($artifact['products'], 'image'))),
            'unique_thumbnail_count' => count(array_unique(array_column($artifact['products'], 'thumbnail'))),
            'asset_counts' => $assetCounts,
        ];
    }

    /** @return array<string, mixed> */
    public function plan(string $artifactPath, string $assetManifestPath): array
    {
        $validation = $this->validate($artifactPath, $assetManifestPath);
        $artifact = $validation['artifact'];
        $categories = Category::query()->get()->keyBy(fn (Category $category) => strtolower($category->slug));
        $subcategories = Subcategory::query()->with('category')->get()->keyBy(fn (Subcategory $subcategory) => strtolower($subcategory->slug));
        $existingProducts = Product::query()->with('subcategory.category')->get();
        $productsBySku = $existingProducts->keyBy(fn (Product $product) => strtoupper($product->sku));

        $categoryConflicts = [];
        $subcategoryConflicts = [];
        $categoriesToCreate = [];
        $subcategoriesToCreate = [];
        $productsToInsert = [];
        $alreadyExistingBatch = [];
        $skuConflicts = [];
        $supplierCodeConflicts = [];
        $approvedSharedCodes = array_fill_keys(array_map('strval', $artifact['approved_shared_supplier_codes']), true);

        foreach ($artifact['categories'] as $category) {
            $existing = $categories->get(strtolower($category['slug']));
            if (! $existing) {
                $categoriesToCreate[] = $category['slug'];
            } elseif ($existing->slug !== $category['slug'] || $existing->name !== $category['name'] || ! $existing->is_active) {
                $categoryConflicts[] = "Category {$category['slug']} differs from the release taxonomy.";
            }
        }

        foreach ($artifact['subcategories'] as $subcategory) {
            $existing = $subcategories->get(strtolower($subcategory['slug']));
            if (! $existing) {
                $subcategoriesToCreate[] = $subcategory['slug'];
            } elseif ($existing->slug !== $subcategory['slug']
                || $existing->name !== $subcategory['name']
                || $existing->category?->slug !== $subcategory['category_slug']
                || ! $existing->is_active) {
                $subcategoryConflicts[] = "Subcategory {$subcategory['slug']} differs from the release taxonomy.";
            }
        }

        $artifactProductsBySku = collect($artifact['products'])->keyBy(fn (array $product) => strtoupper($product['sku']));
        $expectedBatchSupplierOwners = [];
        foreach ($existingProducts as $existingProduct) {
            $artifactProduct = $artifactProductsBySku->get(strtoupper($existingProduct->sku));
            if ($artifactProduct && $this->matchesReleaseProduct($existingProduct, $artifactProduct)) {
                $expectedBatchSupplierOwners[(string) $artifactProduct['supplier_code']][] = strtoupper($existingProduct->sku);
            }
        }

        $existingSupplierOwners = [];
        foreach ($existingProducts as $existingProduct) {
            $code = (string) data_get($existingProduct->attributes, 'supplier_code');
            if ($code !== '') {
                $existingSupplierOwners[$code][] = $existingProduct;
            }
        }

        foreach ($artifact['products'] as $product) {
            $skuKey = strtoupper($product['sku']);
            $existing = $productsBySku->get($skuKey);
            if ($existing) {
                if ($this->matchesReleaseProduct($existing, $product)) {
                    $alreadyExistingBatch[] = $product['sku'];
                } else {
                    $skuConflicts[] = "SKU {$product['sku']} already exists but does not exactly match the approved release batch.";
                }

                continue;
            }

            $code = (string) $product['supplier_code'];
            $owners = $existingSupplierOwners[$code] ?? [];
            $unexpectedOwners = array_filter($owners, function (Product $owner) use ($artifactProductsBySku): bool {
                $artifactOwner = $artifactProductsBySku->get(strtoupper($owner->sku));

                return ! $artifactOwner || ! $this->matchesReleaseProduct($owner, $artifactOwner);
            });
            $expectedBatchOwners = $expectedBatchSupplierOwners[$code] ?? [];
            if ($unexpectedOwners !== [] || ($expectedBatchOwners !== [] && ! isset($approvedSharedCodes[$code]))) {
                $ownerSkus = implode(', ', array_map(fn (Product $owner) => $owner->sku, $owners));
                $supplierCodeConflicts[] = "Supplier code {$code} for {$product['sku']} is already used by {$ownerSkus}.";

                continue;
            }

            $productsToInsert[] = $product['sku'];
        }

        return array_merge($validation, [
            'categories_to_create' => $categoriesToCreate,
            'subcategories_to_create' => $subcategoriesToCreate,
            'products_to_insert' => $productsToInsert,
            'already_existing_batch' => $alreadyExistingBatch,
            'category_conflicts' => $categoryConflicts,
            'subcategory_conflicts' => $subcategoryConflicts,
            'sku_conflicts' => $skuConflicts,
            'supplier_code_conflicts' => $supplierCodeConflicts,
            'has_conflicts' => $categoryConflicts !== [] || $subcategoryConflicts !== [] || $skuConflicts !== [] || $supplierCodeConflicts !== [],
        ]);
    }

    /** @return array<string, int> */
    public function execute(string $artifactPath, string $assetManifestPath): array
    {
        $result = DB::transaction(function () use ($artifactPath, $assetManifestPath): array {
            Product::query()->lockForUpdate()->get(['id']);
            Category::query()->lockForUpdate()->get(['id']);
            Subcategory::query()->lockForUpdate()->get(['id']);
            $plan = $this->plan($artifactPath, $assetManifestPath);

            if ($plan['has_conflicts']) {
                throw new RuntimeException('Release import aborted because conflicts were detected. No database rows were changed.');
            }

            if (count($plan['products_to_insert']) + count($plan['already_existing_batch']) !== BuildProductionProductReleaseCommand::EXPECTED_PRODUCTS) {
                throw new RuntimeException('Release import plan does not account for exactly 537 products.');
            }

            $artifact = $plan['artifact'];
            $categoryIds = Category::query()->pluck('id', 'slug')->all();
            $createdCategories = 0;
            foreach ($artifact['categories'] as $category) {
                if (! isset($categoryIds[$category['slug']])) {
                    $created = Category::create($category);
                    $categoryIds[$category['slug']] = $created->id;
                    $createdCategories++;
                }
            }

            $subcategoryIds = Subcategory::query()->pluck('id', 'slug')->all();
            $createdSubcategories = 0;
            foreach ($artifact['subcategories'] as $subcategory) {
                if (! isset($subcategoryIds[$subcategory['slug']])) {
                    $created = Subcategory::create([
                        'category_id' => $categoryIds[$subcategory['category_slug']],
                        'name' => $subcategory['name'],
                        'slug' => $subcategory['slug'],
                        'description' => $subcategory['description'],
                        'is_active' => $subcategory['is_active'],
                    ]);
                    $subcategoryIds[$subcategory['slug']] = $created->id;
                    $createdSubcategories++;
                }
            }

            $insertSkus = array_fill_keys($plan['products_to_insert'], true);
            $createdProducts = 0;
            foreach ($artifact['products'] as $product) {
                if (! isset($insertSkus[$product['sku']])) {
                    continue;
                }

                $attributes = $this->releaseAttributes($product['attributes']);
                Product::create([
                    'subcategory_id' => $subcategoryIds[$product['subcategory_slug']],
                    'name' => $product['name'],
                    'slug' => $product['slug'],
                    'description' => $product['description'],
                    'price' => $product['price'],
                    'compare_price' => $product['compare_price'],
                    'stock' => $product['stock'],
                    'sku' => $product['sku'],
                    'image' => $product['image'],
                    'gallery' => $product['gallery'],
                    'attributes' => $attributes,
                    'is_active' => $product['is_active'],
                    'is_featured' => $product['is_featured'],
                ]);
                $createdProducts++;
            }

            return [
                'categories_created' => $createdCategories,
                'subcategories_created' => $createdSubcategories,
                'products_created' => $createdProducts,
                'products_already_existing' => count($plan['already_existing_batch']),
            ];
        }, 3);

        Cache::forget(PublicCatalogCache::NAVIGATION_CATEGORIES_KEY);
        Cache::forget(PublicCatalogCache::HOMEPAGE_SECTIONS_KEY);

        return $result;
    }

    private function validateArtifact(array $artifact): void
    {
        foreach (['schema_version', 'release_id', 'source_manifest_sha256', 'expected_product_count', 'categories', 'subcategories', 'products'] as $key) {
            if (! array_key_exists($key, $artifact)) {
                throw new RuntimeException("Product artifact is missing {$key}.");
            }
        }
        if ($artifact['schema_version'] !== 1
            || $artifact['release_id'] !== BuildProductionProductReleaseCommand::RELEASE_ID
            || $artifact['source_manifest_sha256'] !== BuildProductionProductReleaseCommand::SOURCE_MANIFEST_SHA256
            || $artifact['expected_product_count'] !== BuildProductionProductReleaseCommand::EXPECTED_PRODUCTS
            || count($artifact['products']) !== BuildProductionProductReleaseCommand::EXPECTED_PRODUCTS) {
            throw new RuntimeException('Product artifact identity or expected count is invalid.');
        }

        $categorySlugs = array_column($artifact['categories'], 'slug');
        $subcategorySlugs = array_column($artifact['subcategories'], 'slug');
        $skus = array_map('strtoupper', array_column($artifact['products'], 'sku'));
        if (count($categorySlugs) !== count(array_unique($categorySlugs))
            || count($subcategorySlugs) !== count(array_unique($subcategorySlugs))
            || count($skus) !== count(array_unique($skus))) {
            throw new RuntimeException('Artifact taxonomy slugs and product SKUs must be unique.');
        }

        $categories = array_fill_keys($categorySlugs, true);
        $subcategories = [];
        foreach ($artifact['subcategories'] as $subcategory) {
            if (! isset($categories[$subcategory['category_slug']])) {
                throw new RuntimeException("Subcategory {$subcategory['slug']} references an unknown category.");
            }
            $subcategories[$subcategory['slug']] = $subcategory['category_slug'];
        }

        $supplierCodes = [];
        foreach ($artifact['products'] as $product) {
            foreach (['sku', 'supplier_code', 'name', 'slug', 'price', 'stock', 'image', 'image_sha256', 'thumbnail', 'thumbnail_sha256', 'attributes', 'is_active', 'category_slug', 'subcategory_slug'] as $key) {
                if (! array_key_exists($key, $product)) {
                    throw new RuntimeException("Artifact product is missing {$key}.");
                }
            }
            if ($product['stock'] !== 10 || $product['is_active'] !== true || ! $this->prices->isNormalized($product['price'])) {
                throw new RuntimeException("Product {$product['sku']} violates the final stock, active, or price rule.");
            }
            $costCents = $this->prices->toCents((string) data_get($product, 'attributes.cost_price'));
            if ($this->prices->normalizeCents(intdiv($costCents * 145 + 50, 100)) !== $this->prices->toCents($product['price'])) {
                throw new RuntimeException("Product {$product['sku']} does not match the approved +45% result.");
            }
            if (($subcategories[$product['subcategory_slug']] ?? null) !== $product['category_slug']) {
                throw new RuntimeException("Product {$product['sku']} references conflicting taxonomy slugs.");
            }
            foreach (['image', 'thumbnail'] as $pathKey) {
                if (! $this->safeRelativePath($product[$pathKey])) {
                    throw new RuntimeException("Product {$product['sku']} has an unsafe {$pathKey} path.");
                }
            }
            if (! str_starts_with($product['image'], 'products/')
                || $product['thumbnail'] !== $this->thumbnailPath($product['image'])
                || preg_match('/^[a-f0-9]{64}$/', $product['image_sha256']) !== 1
                || preg_match('/^[a-f0-9]{64}$/', $product['thumbnail_sha256']) !== 1) {
                throw new RuntimeException("Product {$product['sku']} has invalid final asset paths or checksums.");
            }
            if (data_get($product, 'attributes.import.manifest_sha256') !== BuildProductionProductReleaseCommand::SOURCE_MANIFEST_SHA256
                || array_key_exists('source_image', (array) data_get($product, 'attributes.import', []))
                || array_key_exists('supplier_card', (array) data_get($product, 'attributes.import', []))) {
                throw new RuntimeException("Product {$product['sku']} has invalid or local-only provenance metadata.");
            }
            $supplierCodes[(string) $product['supplier_code']][] = $product['sku'];
        }

        $approved = array_fill_keys(array_map('strval', $artifact['approved_shared_supplier_codes']), true);
        foreach ($supplierCodes as $code => $owners) {
            if (count($owners) > 1 && ! isset($approved[$code])) {
                throw new RuntimeException("Supplier code {$code} is shared without explicit artifact approval.");
            }
        }

        if (count(array_unique(array_column($artifact['products'], 'image'))) !== 523
            || count(array_unique(array_column($artifact['products'], 'thumbnail'))) !== 523) {
            throw new RuntimeException('Artifact must reference exactly 523 product images and 523 thumbnails.');
        }
    }

    /** @return array<string, int> */
    private function validateAssets(array $manifest, string $artifactPath, array $artifact): array
    {
        if (($manifest['release_id'] ?? null) !== BuildProductionProductReleaseCommand::RELEASE_ID
            || ($manifest['schema_version'] ?? null) !== 1
            || ($manifest['product_artifact'] ?? null) !== BuildProductionProductReleaseCommand::ARTIFACT_PATH
            || ($manifest['product_artifact_sha256'] ?? null) !== self::ARTIFACT_SHA256
            || ($manifest['expected_counts'] ?? null) !== [
                'product_images' => 523,
                'thumbnails' => 523,
                'catalog_pdfs' => 2,
                'catalog_covers' => 2,
                'product_artifacts' => 1,
                'total' => 1051,
            ]
            || count($manifest['assets'] ?? []) !== 1051) {
            throw new RuntimeException('Asset manifest identity or expected count is invalid.');
        }

        $counts = [];
        $paths = [];
        foreach ($manifest['assets'] as $asset) {
            $path = (string) ($asset['path'] ?? '');
            if (! $this->safeRelativePath($path) || isset($paths[$path])) {
                throw new RuntimeException("Asset manifest contains an unsafe or duplicate path: {$path}");
            }
            $paths[$path] = $asset;
            $absolute = base_path($path);
            if (! File::isFile($absolute)
                || filesize($absolute) !== $asset['bytes']
                || hash_file('sha256', $absolute) !== $asset['sha256']) {
                throw new RuntimeException("Release asset failed integrity validation: {$path}");
            }
            $counts[$asset['type']] = ($counts[$asset['type']] ?? 0) + 1;
        }

        $expected = [
            'product_artifact' => 1,
            'product_image' => 523,
            'thumbnail' => 523,
            'catalog_pdf' => 2,
            'catalog_cover' => 2,
        ];
        ksort($counts);
        ksort($expected);
        if ($counts !== $expected || hash_file('sha256', $artifactPath) !== self::ARTIFACT_SHA256) {
            throw new RuntimeException('Asset manifest type counts or artifact checksum are invalid.');
        }

        foreach ($artifact['products'] as $product) {
            $imagePath = 'storage/app/public/'.$product['image'];
            $thumbnailPath = 'storage/app/public/'.$product['thumbnail'];
            $image = $paths[$imagePath] ?? null;
            $thumbnail = $paths[$thumbnailPath] ?? null;

            if (($image['type'] ?? null) !== 'product_image'
                || ($image['sha256'] ?? null) !== $product['image_sha256']
                || ($thumbnail['type'] ?? null) !== 'thumbnail'
                || ($thumbnail['sha256'] ?? null) !== $product['thumbnail_sha256']) {
                throw new RuntimeException("Product {$product['sku']} asset checksums do not match the asset manifest.");
            }
        }

        return $counts;
    }

    private function matchesReleaseProduct(Product $existing, array $product): bool
    {
        return $existing->subcategory?->slug === $product['subcategory_slug']
            && $existing->subcategory?->category?->slug === $product['category_slug']
            && $existing->name === $product['name']
            && $existing->slug === $product['slug']
            && $existing->description === $product['description']
            && $existing->price === $product['price']
            && $existing->compare_price === $product['compare_price']
            && $existing->stock === $product['stock']
            && $existing->image === $product['image']
            && $existing->gallery === $product['gallery']
            && $existing->is_active === $product['is_active']
            && $existing->is_featured === $product['is_featured']
            && $this->comparableAttributes((array) $existing->attributes) == $product['attributes'];
    }

    private function comparableAttributes(array $attributes): array
    {
        $import = (array) ($attributes['import'] ?? []);
        $sourceImage = str_replace('\\', '/', (string) ($import['source_image'] ?? ''));
        $supplierCard = str_replace('\\', '/', (string) ($import['supplier_card'] ?? ''));

        if ($sourceImage !== '') {
            $import['source_image_filename'] = basename($sourceImage);
        }
        if ($supplierCard !== '') {
            $import['supplier_card_filename'] = basename($supplierCard);
        }

        unset(
            $import['source_image'],
            $import['supplier_card'],
            $import['production_release_id'],
            $import['production_artifact_sha256'],
        );
        $attributes['import'] = $import;

        return $attributes;
    }

    private function releaseAttributes(array $attributes): array
    {
        $attributes['import']['production_release_id'] = BuildProductionProductReleaseCommand::RELEASE_ID;
        $attributes['import']['production_artifact_sha256'] = self::ARTIFACT_SHA256;

        return $attributes;
    }

    private function readJson(string $path, string $expectedHash, string $label): array
    {
        if (! File::isFile($path)) {
            throw new RuntimeException("Missing {$label}: {$path}");
        }
        if (hash_file('sha256', $path) !== $expectedHash) {
            throw new RuntimeException("{$label} SHA-256 does not match the approved immutable release.");
        }

        return json_decode(File::get($path), true, flags: JSON_THROW_ON_ERROR);
    }

    private function safeRelativePath(string $path): bool
    {
        return $path !== ''
            && ! str_contains($path, '\\')
            && ! str_contains($path, '..')
            && ! str_starts_with($path, '/')
            && preg_match('/^[A-Za-z]:/', $path) !== 1;
    }

    private function thumbnailPath(string $image): string
    {
        $info = pathinfo($image);

        return 'product-thumbs/'.($info['dirname'] !== '.' ? $info['dirname'].'/' : '').$info['filename'].'.webp';
    }

    private function absolutePath(string $path): string
    {
        return preg_match('/^[A-Za-z]:[\\\\\/]/', $path) === 1 || str_starts_with($path, DIRECTORY_SEPARATOR)
            ? $path
            : base_path($path);
    }
}
