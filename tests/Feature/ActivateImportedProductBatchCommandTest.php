<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ActivateImportedProductBatchCommandTest extends TestCase
{
    use RefreshDatabase;

    private const HASH = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';

    public function test_count_mismatch_aborts_without_changing_any_product(): void
    {
        $product = $this->makeImportedProduct('BATCH-1', '1.63', '1.10');

        $this->artisan('catalog:activate-imported-batch', [
            'manifest-sha256' => self::HASH,
            '--expected' => 2,
            '--execute' => true,
        ])->assertFailed();

        $product->refresh();
        $this->assertFalse($product->is_active);
        $this->assertSame('1.63', $product->price);
        $this->assertSame(10, $product->stock);
    }

    public function test_exact_batch_is_snapshotted_normalized_and_activated_without_touching_protected_data(): void
    {
        Storage::fake('local');
        $first = $this->makeImportedProduct('BATCH-1', '1.63', '1.10');
        $second = $this->makeImportedProduct('BATCH-2', '2.17', '1.50');
        $unrelated = $this->makeImportedProduct('OTHER-1', '7.77', '5.00', str_repeat('b', 64));
        $beforeAttributes = [$first->attributes, $second->attributes];

        $this->artisan('catalog:activate-imported-batch', [
            'manifest-sha256' => self::HASH,
            '--expected' => 2,
            '--execute' => true,
        ])->assertSuccessful();

        $this->assertSame('1.65', $first->fresh()->price);
        $this->assertSame('2.15', $second->fresh()->price);
        $this->assertTrue($first->fresh()->is_active);
        $this->assertTrue($second->fresh()->is_active);
        $this->assertSame(10, $first->fresh()->stock);
        $this->assertSame($beforeAttributes, [$first->fresh()->attributes, $second->fresh()->attributes]);
        $this->assertSame('7.77', $unrelated->fresh()->price);
        $this->assertFalse($unrelated->fresh()->is_active);
        $this->assertCount(1, Storage::disk('local')->allFiles('import/activation-snapshots'));
    }

    public function test_minimum_price_command_changes_only_below_floor_batch_prices(): void
    {
        Storage::fake('local');
        $belowFloor = $this->makeImportedProduct('BATCH-LOW', '0.29', '0.20', self::HASH, true);
        $aboveFloor = $this->makeImportedProduct('BATCH-HIGH', '2.20', '1.50', self::HASH, true);
        $unrelated = $this->makeImportedProduct('OTHER-LOW', '0.29', '0.20', str_repeat('b', 64), true);
        $beforeAttributes = [$belowFloor->attributes, $aboveFloor->attributes];

        $this->artisan('catalog:enforce-imported-minimum-price', [
            'manifest-sha256' => self::HASH,
            '--expected' => 2,
            '--execute' => true,
        ])->assertSuccessful();

        $this->assertSame('1.50', $belowFloor->fresh()->price);
        $this->assertSame('2.20', $aboveFloor->fresh()->price);
        $this->assertSame('0.29', $unrelated->fresh()->price);
        $this->assertTrue($belowFloor->fresh()->is_active);
        $this->assertSame(10, $belowFloor->fresh()->stock);
        $this->assertSame($beforeAttributes, [$belowFloor->fresh()->attributes, $aboveFloor->fresh()->attributes]);
        $this->assertCount(1, Storage::disk('local')->allFiles('import/minimum-price-snapshots'));
    }

    private function makeImportedProduct(
        string $sku,
        string $price,
        string $costPrice,
        string $hash = self::HASH,
        bool $isActive = false
    ): Product {
        $category = Category::firstOrCreate(['slug' => 'batch'], ['name' => 'Batch', 'is_active' => true]);
        $subcategory = Subcategory::firstOrCreate(
            ['slug' => 'batch-items'],
            ['category_id' => $category->id, 'name' => 'Batch items', 'is_active' => true]
        );

        return Product::create([
            'subcategory_id' => $subcategory->id,
            'name' => $sku,
            'slug' => strtolower($sku),
            'description' => '',
            'price' => $price,
            'stock' => 10,
            'sku' => $sku,
            'image' => 'products/vs/'.$sku.'.jpg',
            'attributes' => [
                'cost_price' => $costPrice,
                'supplier_code' => $sku,
                'import' => [
                    'source' => 'FINAL-product-import-review-v2.csv',
                    'manifest_sha256' => $hash,
                    'manifest_row' => 1,
                ],
            ],
            'is_active' => $isActive,
        ]);
    }
}
