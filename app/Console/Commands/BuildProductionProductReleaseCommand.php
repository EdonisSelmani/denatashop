<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\PriceNormalizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Throwable;

class BuildProductionProductReleaseCommand extends Command
{
    public const RELEASE_ID = 'denatashop-products-537-v1';

    public const SOURCE_MANIFEST_SHA256 = '21464fe9016b9c77bb9ac4d904d4d45918e6ae3c6a3a84afa74a0ac752de04dd';

    public const EXPECTED_PRODUCTS = 537;

    public const ARTIFACT_PATH = 'database/releases/denatashop-products-537-v1.json';

    public const ASSET_MANIFEST_PATH = 'database/releases/denatashop-products-537-v1-assets.json';

    protected $signature = 'catalog:build-production-release
        {--artifact= : Output path for the deterministic product artifact}
        {--asset-manifest= : Output path for the deterministic asset manifest}';

    protected $description = 'Build the immutable 537-product production artifact from the verified local batch.';

    public function handle(PriceNormalizer $prices): int
    {
        if (app()->environment('production')) {
            $this->error('Refusing to build a release artifact in production.');

            return self::FAILURE;
        }

        try {
            $artifactPath = $this->absolutePath((string) ($this->option('artifact') ?: self::ARTIFACT_PATH));
            $assetManifestPath = $this->absolutePath((string) ($this->option('asset-manifest') ?: self::ASSET_MANIFEST_PATH));
            $products = Product::query()
                ->with('subcategory.category')
                ->where('attributes->import->manifest_sha256', self::SOURCE_MANIFEST_SHA256)
                ->orderBy('sku')
                ->get();

            $this->validateBatch($products, $prices);

            $categories = $products->pluck('subcategory.category')->unique('slug')->sortBy('slug')->map(fn ($category) => [
                'slug' => $category->slug,
                'name' => $category->name,
                'description' => $category->description,
                'image' => $category->image,
                'is_active' => (bool) $category->is_active,
            ])->values()->all();

            $subcategories = $products->pluck('subcategory')->unique('slug')->sortBy('slug')->map(fn ($subcategory) => [
                'slug' => $subcategory->slug,
                'category_slug' => $subcategory->category->slug,
                'name' => $subcategory->name,
                'description' => $subcategory->description,
                'is_active' => (bool) $subcategory->is_active,
            ])->values()->all();

            $artifactProducts = $products->map(function (Product $product): array {
                $image = str_replace('\\', '/', (string) $product->image);
                $thumbnail = $this->thumbnailPath($image);
                $attributes = $this->productionAttributes((array) $product->attributes);

                return [
                    'sku' => $product->sku,
                    'supplier_code' => (string) data_get($attributes, 'supplier_code'),
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'description' => $product->description,
                    'price' => $product->price,
                    'compare_price' => $product->compare_price,
                    'stock' => $product->stock,
                    'image' => $image,
                    'image_sha256' => hash_file('sha256', storage_path('app/public/'.$image)),
                    'thumbnail' => $thumbnail,
                    'thumbnail_sha256' => hash_file('sha256', storage_path('app/public/'.$thumbnail)),
                    'gallery' => $product->gallery,
                    'attributes' => $attributes,
                    'is_active' => (bool) $product->is_active,
                    'is_featured' => (bool) $product->is_featured,
                    'category_slug' => $product->subcategory->category->slug,
                    'subcategory_slug' => $product->subcategory->slug,
                ];
            })->values()->all();

            $artifact = [
                'schema_version' => 1,
                'release_id' => self::RELEASE_ID,
                'source_manifest_sha256' => self::SOURCE_MANIFEST_SHA256,
                'expected_product_count' => self::EXPECTED_PRODUCTS,
                'approved_shared_supplier_codes' => ['1434', '2144'],
                'pricing' => [
                    'markup_percent' => 45,
                    'increment_cents' => PriceNormalizer::INCREMENT_CENTS,
                    'minimum_selling_price_cents' => PriceNormalizer::MINIMUM_SELLING_PRICE_CENTS,
                    'prices_are_final' => true,
                ],
                'categories' => $categories,
                'subcategories' => $subcategories,
                'products' => $artifactProducts,
            ];

            File::ensureDirectoryExists(dirname($artifactPath));
            File::put($artifactPath, $this->json($artifact));
            $artifactHash = hash_file('sha256', $artifactPath);

            $assets = $this->assetEntries($artifactPath, $artifactHash, $artifactProducts);
            $assetManifest = [
                'schema_version' => 1,
                'release_id' => self::RELEASE_ID,
                'product_artifact' => $this->relativePath($artifactPath),
                'product_artifact_sha256' => $artifactHash,
                'expected_counts' => [
                    'product_images' => 523,
                    'thumbnails' => 523,
                    'catalog_pdfs' => 2,
                    'catalog_covers' => 2,
                    'product_artifacts' => 1,
                    'total' => 1051,
                ],
                'assets' => $assets,
                'excluded_from_release' => [
                    'public/build-new.zip',
                    'public/build-seo.zip',
                    '.env',
                    'database/*.sqlite',
                    'node_modules/',
                    'vendor/',
                    '.phpunit.result.cache',
                    'storage/debugbar/',
                    'storage/framework/testing/',
                    'storage/app/private/import/',
                ],
            ];

            File::ensureDirectoryExists(dirname($assetManifestPath));
            File::put($assetManifestPath, $this->json($assetManifest));

            $this->info('Production release artifacts built without changing the database.');
            $this->line('Products: '.count($artifactProducts));
            $this->line('Product artifact: '.$this->relativePath($artifactPath));
            $this->line('Product artifact SHA-256: '.$artifactHash);
            $this->line('Asset manifest: '.$this->relativePath($assetManifestPath));
            $this->line('Asset manifest SHA-256: '.hash_file('sha256', $assetManifestPath));
            $this->line('Assets: '.count($assets));

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }

    private function validateBatch($products, PriceNormalizer $prices): void
    {
        if ($products->count() !== self::EXPECTED_PRODUCTS || $products->pluck('sku')->map('strtoupper')->unique()->count() !== self::EXPECTED_PRODUCTS) {
            throw new RuntimeException('The verified source batch must contain exactly 537 unique SKUs.');
        }

        foreach ($products as $product) {
            if (! $product->is_active || $product->stock !== 10 || ! $prices->isNormalized($product->price)) {
                throw new RuntimeException("Product {$product->sku} does not match the approved active/stock/price state.");
            }

            $costCents = $prices->toCents((string) data_get($product->attributes, 'cost_price'));
            $markedUpCents = intdiv($costCents * 145 + 50, 100);
            if ($prices->normalizeCents($markedUpCents) !== $prices->toCents($product->price)) {
                throw new RuntimeException("Product {$product->sku} does not match the approved +45% pricing result.");
            }

            $image = str_replace('\\', '/', (string) $product->image);
            $thumbnail = $this->thumbnailPath($image);
            foreach ([$image, $thumbnail] as $path) {
                if (! $this->safeStoragePath($path) || ! File::isFile(storage_path('app/public/'.$path))) {
                    throw new RuntimeException("Missing or unsafe release asset for {$product->sku}: {$path}");
                }
            }
        }
    }

    private function productionAttributes(array $attributes): array
    {
        $import = (array) ($attributes['import'] ?? []);
        $sourceImage = str_replace('\\', '/', (string) ($import['source_image'] ?? ''));
        $supplierCard = str_replace('\\', '/', (string) ($import['supplier_card'] ?? ''));
        unset($import['source_image'], $import['supplier_card']);
        $import['source_image_filename'] = basename($sourceImage);
        $import['supplier_card_filename'] = basename($supplierCard);
        $attributes['import'] = $import;

        return $attributes;
    }

    private function assetEntries(string $artifactPath, string $artifactHash, array $products): array
    {
        $entries = [[
            'type' => 'product_artifact',
            'path' => $this->relativePath($artifactPath),
            'bytes' => filesize($artifactPath),
            'sha256' => $artifactHash,
        ]];

        $paths = [];
        foreach ($products as $product) {
            $paths['storage/app/public/'.$product['image']] = 'product_image';
            $paths['storage/app/public/'.$product['thumbnail']] = 'thumbnail';
        }
        $paths += [
            'public/catalogs/denata-katalogu-ngrohje-instalime.pdf' => 'catalog_pdf',
            'public/catalogs/denata-katalogu-sanitari-banjo.pdf' => 'catalog_pdf',
            'public/images/catalogs/denata-katalogu-ngrohje-instalime-cover.png' => 'catalog_cover',
            'public/images/catalogs/denata-katalogu-sanitari-banjo-cover.png' => 'catalog_cover',
        ];
        ksort($paths);

        foreach ($paths as $path => $type) {
            $absolute = base_path($path);
            if (! File::isFile($absolute)) {
                throw new RuntimeException("Release asset is missing: {$path}");
            }
            $entries[] = [
                'type' => $type,
                'path' => str_replace('\\', '/', $path),
                'bytes' => filesize($absolute),
                'sha256' => hash_file('sha256', $absolute),
            ];
        }

        if (count($entries) !== 1051) {
            throw new RuntimeException('The release asset manifest must contain exactly 1,051 entries.');
        }

        return $entries;
    }

    private function thumbnailPath(string $image): string
    {
        $info = pathinfo($image);

        return 'product-thumbs/'.($info['dirname'] !== '.' ? $info['dirname'].'/' : '').$info['filename'].'.webp';
    }

    private function safeStoragePath(string $path): bool
    {
        return $path !== '' && ! str_contains($path, '\\') && ! str_contains($path, '..') && ! str_starts_with($path, '/');
    }

    private function absolutePath(string $path): string
    {
        return preg_match('/^[A-Za-z]:[\\\\\/]/', $path) === 1 || str_starts_with($path, DIRECTORY_SEPARATOR)
            ? $path
            : base_path($path);
    }

    private function relativePath(string $path): string
    {
        return str_replace('\\', '/', ltrim(str_replace(base_path(), '', $path), '\\/'));
    }

    private function json(array $data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
    }
}
