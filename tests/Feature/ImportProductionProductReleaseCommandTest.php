<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use App\Services\ProductionProductReleaseImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ImportProductionProductReleaseCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_immutable_release_and_every_referenced_asset_pass_integrity_validation(): void
    {
        $result = app(ProductionProductReleaseImporter::class)->validate($this->artifactPath(), $this->assetManifestPath());

        $this->assertSame(537, $result['product_count']);
        $this->assertSame(523, $result['unique_image_count']);
        $this->assertSame(523, $result['unique_thumbnail_count']);
        $this->assertSame([
            'catalog_cover' => 2,
            'catalog_pdf' => 2,
            'product_artifact' => 1,
            'product_image' => 523,
            'thumbnail' => 523,
        ], $result['asset_counts']);
        $this->assertSame(ProductionProductReleaseImporter::ARTIFACT_SHA256, $result['artifact_sha256']);
        $this->assertSame(ProductionProductReleaseImporter::ASSET_MANIFEST_SHA256, $result['asset_manifest_sha256']);
    }

    public function test_dry_run_accepts_an_exact_legacy_batch_row_but_blocks_a_changed_existing_sku_without_writing(): void
    {
        $artifact = json_decode(File::get($this->artifactPath()), true, flags: JSON_THROW_ON_ERROR);
        $this->createTaxonomy($artifact);
        $releaseProduct = $artifact['products'][0];
        $existing = $this->createLegacyBatchProduct($releaseProduct);
        $before = $existing->fresh()->getAttributes();

        $this->artisan('catalog:import-production-release')
            ->expectsOutputToContain('Mode: DRY RUN')
            ->expectsOutputToContain('Expected existing batch')
            ->expectsOutputToContain('Dry run complete. All artifacts are valid and no database rows were changed.')
            ->assertSuccessful();

        $plan = app(ProductionProductReleaseImporter::class)->plan($this->artifactPath(), $this->assetManifestPath());
        $this->assertSame([$releaseProduct['sku']], $plan['already_existing_batch']);
        $this->assertCount(536, $plan['products_to_insert']);
        $this->assertSame($before, $existing->fresh()->getAttributes());
        $this->assertDatabaseCount('products', 1);

        $existing->update(['price' => '999.95']);
        $changed = $existing->fresh()->getAttributes();
        $plan = app(ProductionProductReleaseImporter::class)->plan($this->artifactPath(), $this->assetManifestPath());

        $this->assertSame([], $plan['already_existing_batch']);
        $this->assertCount(1, $plan['sku_conflicts']);
        $this->assertTrue($plan['has_conflicts']);
        $this->assertSame($changed, $existing->fresh()->getAttributes());
        $this->assertDatabaseCount('products', 1);
    }

    private function createTaxonomy(array $artifact): void
    {
        $categoryIds = [];
        foreach ($artifact['categories'] as $category) {
            $created = Category::create($category);
            $categoryIds[$created->slug] = $created->id;
        }

        foreach ($artifact['subcategories'] as $subcategory) {
            Subcategory::create([
                'category_id' => $categoryIds[$subcategory['category_slug']],
                'name' => $subcategory['name'],
                'slug' => $subcategory['slug'],
                'description' => $subcategory['description'],
                'is_active' => $subcategory['is_active'],
            ]);
        }
    }

    private function createLegacyBatchProduct(array $product): Product
    {
        $attributes = $product['attributes'];
        $attributes['import']['source_image'] = 'D:\\verified-source\\'.$attributes['import']['source_image_filename'];
        $attributes['import']['supplier_card'] = 'D:\\verified-cards\\'.$attributes['import']['supplier_card_filename'];
        unset($attributes['import']['source_image_filename'], $attributes['import']['supplier_card_filename']);

        return Product::create([
            'subcategory_id' => Subcategory::where('slug', $product['subcategory_slug'])->value('id'),
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
    }

    private function artifactPath(): string
    {
        return base_path('database/releases/denatashop-products-537-v1.json');
    }

    private function assetManifestPath(): string
    {
        return base_path('database/releases/denatashop-products-537-v1-assets.json');
    }
}
