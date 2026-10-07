<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ImportedProductBatchActivator
{
    public function __construct(private readonly PriceNormalizer $prices) {}

    /** @return Collection<int, Product> */
    public function products(string $manifestSha256): Collection
    {
        return Product::query()
            ->where('attributes->import->manifest_sha256', $manifestSha256)
            ->orderBy('id')
            ->get();
    }

    /** @return array<string, mixed> */
    public function inspect(string $manifestSha256, int $expectedCount = 537): array
    {
        if (! preg_match('/^[a-f0-9]{64}$/', $manifestSha256)) {
            throw new RuntimeException('Manifest SHA-256 must be 64 lowercase hexadecimal characters.');
        }

        $products = $this->products($manifestSha256);
        $count = $products->count();

        if ($count !== $expectedCount) {
            throw new RuntimeException("Expected exactly {$expectedCount} products for manifest {$manifestSha256}; found {$count}. Nothing was changed.");
        }

        if ($products->pluck('sku')->unique()->count() !== $expectedCount) {
            throw new RuntimeException('The imported batch does not contain the expected number of unique SKUs. Nothing was changed.');
        }

        $wrongStock = $products->where('stock', '!==', 10);
        if ($wrongStock->isNotEmpty()) {
            throw new RuntimeException('Every product in the imported batch must have stock 10. Nothing was changed.');
        }

        $wrongSource = $products->reject(fn (Product $product) => data_get($product->attributes, 'import.source') === 'FINAL-product-import-review-v2.csv');
        if ($wrongSource->isNotEmpty()) {
            throw new RuntimeException('The batch is not exclusively from the approved v2 manifest. Nothing was changed.');
        }

        return [
            'count' => $count,
            'inactive_count' => $products->where('is_active', false)->count(),
            'stock_10_count' => $products->where('stock', 10)->count(),
            'below_minimum_count' => $products->filter(
                fn (Product $product) => $this->prices->toCents($product->price) < PriceNormalizer::MINIMUM_SELLING_PRICE_CENTS
            )->count(),
            'price_change_count' => $products->filter(fn (Product $product) => $product->price !== $this->prices->normalize($product->price))->count(),
            'products' => $products,
        ];
    }

    /** @return array<string, mixed> */
    public function activate(string $manifestSha256, int $expectedCount = 537): array
    {
        $inspection = $this->inspect($manifestSha256, $expectedCount);
        /** @var Collection<int, Product> $products */
        $products = $inspection['products'];

        if ($inspection['inactive_count'] !== $expectedCount) {
            throw new RuntimeException("All {$expectedCount} products must still be inactive before activation. Nothing was changed.");
        }

        $snapshotPath = $this->writeSnapshot($manifestSha256, $products);
        $before = $products->keyBy('id')->map(fn (Product $product) => [
            'cost_price' => data_get($product->attributes, 'cost_price'),
            'attributes' => $product->attributes,
            'stock' => $product->stock,
            'sku' => $product->sku,
            'subcategory_id' => $product->subcategory_id,
            'image' => $product->image,
        ]);
        $examples = [];
        $changed = 0;

        DB::transaction(function () use ($manifestSha256, $expectedCount, $products, $before, &$changed, &$examples): void {
            $locked = Product::query()->whereKey($products->modelKeys())->lockForUpdate()->orderBy('id')->get();

            if ($locked->count() !== $expectedCount
                || $locked->contains(fn (Product $product) => data_get($product->attributes, 'import.manifest_sha256') !== $manifestSha256)
                || $locked->contains(fn (Product $product) => $product->stock !== 10 || $product->is_active)) {
                throw new RuntimeException('The imported batch changed after validation. Transaction rolled back.');
            }

            foreach ($locked as $product) {
                $oldPrice = $product->price;
                $newPrice = $this->prices->normalize($oldPrice);
                if ($oldPrice !== $newPrice) {
                    $changed++;
                    if (count($examples) < 10) {
                        $examples[] = ['sku' => $product->sku, 'before' => $oldPrice, 'after' => $newPrice];
                    }
                }

                $product->forceFill(['price' => $newPrice, 'is_active' => true])->save();

                $original = $before->get($product->id);
                if ($product->fresh()->only(['attributes', 'stock', 'sku', 'subcategory_id', 'image']) !== collect($original)->except('cost_price')->all()
                    || data_get($product->fresh()->attributes, 'cost_price') !== $original['cost_price']) {
                    throw new RuntimeException("Protected data changed for {$product->sku}. Transaction rolled back.");
                }
            }
        });

        $after = $this->inspect($manifestSha256, $expectedCount);
        if ($after['stock_10_count'] !== $expectedCount
            || $after['products']->where('is_active', true)->count() !== $expectedCount
            || $after['products']->contains(fn (Product $product) => ! $this->prices->isNormalized($product->price))) {
            throw new RuntimeException('Post-update verification failed.');
        }

        Cache::forget(PublicCatalogCache::NAVIGATION_CATEGORIES_KEY);
        Cache::forget(PublicCatalogCache::HOMEPAGE_SECTIONS_KEY);

        return [
            'count' => $expectedCount,
            'changed_prices' => $changed,
            'examples' => $examples,
            'snapshot_path' => $snapshotPath,
        ];
    }

    /** @return array<string, mixed> */
    public function enforceMinimumSellingPrice(string $manifestSha256, int $expectedCount = 537): array
    {
        $inspection = $this->inspect($manifestSha256, $expectedCount);
        /** @var Collection<int, Product> $products */
        $products = $inspection['products'];

        if ($products->where('is_active', true)->count() !== $expectedCount) {
            throw new RuntimeException("All {$expectedCount} products must remain active. Nothing was changed.");
        }

        $snapshotPath = $this->writeSnapshot($manifestSha256, $products, 'minimum-price-snapshots');
        $before = $products->keyBy('id')->map(fn (Product $product) => [
            'attributes' => $product->attributes,
            'stock' => $product->stock,
            'is_active' => $product->is_active,
            'sku' => $product->sku,
            'subcategory_id' => $product->subcategory_id,
            'image' => $product->image,
        ]);
        $examples = [];
        $changed = 0;

        DB::transaction(function () use ($manifestSha256, $expectedCount, $products, $before, &$changed, &$examples): void {
            $locked = Product::query()->whereKey($products->modelKeys())->lockForUpdate()->orderBy('id')->get();

            if ($locked->count() !== $expectedCount
                || $locked->contains(fn (Product $product) => data_get($product->attributes, 'import.manifest_sha256') !== $manifestSha256)
                || $locked->contains(fn (Product $product) => $product->stock !== 10 || ! $product->is_active)) {
                throw new RuntimeException('The imported batch changed after validation. Transaction rolled back.');
            }

            foreach ($locked as $product) {
                if ($this->prices->toCents($product->price) < PriceNormalizer::MINIMUM_SELLING_PRICE_CENTS) {
                    $oldPrice = $product->price;
                    $newPrice = $this->prices->normalize($oldPrice);
                    $product->forceFill(['price' => $newPrice])->save();
                    $changed++;
                    if (count($examples) < 10) {
                        $examples[] = ['sku' => $product->sku, 'before' => $oldPrice, 'after' => $newPrice];
                    }
                }

                if ($product->fresh()->only(['attributes', 'stock', 'is_active', 'sku', 'subcategory_id', 'image']) !== $before->get($product->id)) {
                    throw new RuntimeException("Protected data changed for {$product->sku}. Transaction rolled back.");
                }
            }
        });

        $after = $this->inspect($manifestSha256, $expectedCount);
        if ($after['below_minimum_count'] !== 0
            || $after['stock_10_count'] !== $expectedCount
            || $after['products']->where('is_active', true)->count() !== $expectedCount
            || $after['products']->contains(fn (Product $product) => ! $this->prices->isNormalized($product->price))) {
            throw new RuntimeException('Post-update minimum-price verification failed.');
        }

        Cache::forget(PublicCatalogCache::NAVIGATION_CATEGORIES_KEY);
        Cache::forget(PublicCatalogCache::HOMEPAGE_SECTIONS_KEY);

        return [
            'count' => $expectedCount,
            'changed_prices' => $changed,
            'examples' => $examples,
            'snapshot_path' => $snapshotPath,
        ];
    }

    /** @param Collection<int, Product> $products */
    private function writeSnapshot(string $manifestSha256, Collection $products, string $directory = 'activation-snapshots'): string
    {
        $relativePath = 'import/'.$directory.'/'.now()->format('Ymd-His').'-'.$manifestSha256.'.json';
        $snapshot = $products->map(fn (Product $product) => [
            'sku' => $product->sku,
            'selling_price' => $product->price,
            'cost_price' => data_get($product->attributes, 'cost_price'),
            'stock' => $product->stock,
            'is_active' => $product->is_active,
            'subcategory_id' => $product->subcategory_id,
            'image' => $product->image,
            'attributes' => $product->attributes,
        ])->values();
        Storage::disk('local')->put($relativePath, json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        return Storage::disk('local')->path($relativePath);
    }
}
