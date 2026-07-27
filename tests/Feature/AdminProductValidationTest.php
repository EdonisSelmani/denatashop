<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProductValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_product_upload_rejects_svg_images(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['is_admin' => true]);
        $subcategory = $this->createSubcategory();
        $svg = UploadedFile::fake()->createWithContent(
            'qa-product.svg',
            '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"></svg>'
        );

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'subcategory_id' => $subcategory->id,
                'name' => 'QA SVG Product',
                'description' => 'SVG uploads should be rejected.',
                'price' => 20,
                'stock' => 5,
                'sku' => 'QA-SVG-PRODUCT',
                'image' => $svg,
            ])
            ->assertSessionHasErrors('image');

        $this->assertDatabaseMissing('products', [
            'sku' => 'QA-SVG-PRODUCT',
        ]);
    }

    public function test_admin_product_validation_rejects_duplicate_sku_and_invalid_price(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $subcategory = $this->createSubcategory();

        Product::create([
            'subcategory_id' => $subcategory->id,
            'name' => 'QA Existing Product',
            'slug' => 'qa-existing-product',
            'description' => 'Existing product.',
            'price' => 20,
            'stock' => 5,
            'sku' => 'QA-DUPLICATE-SKU',
            'is_active' => true,
            'is_featured' => false,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'subcategory_id' => $subcategory->id,
                'name' => 'QA Invalid Product',
                'description' => 'Invalid product.',
                'price' => -1,
                'stock' => 5,
                'sku' => 'QA-DUPLICATE-SKU',
            ])
            ->assertSessionHasErrors(['price', 'sku']);
    }

    private function createSubcategory(): Subcategory
    {
        $category = Category::create([
            'name' => 'QA Admin Category',
            'slug' => 'qa-admin-category',
            'is_active' => true,
        ]);

        return Subcategory::create([
            'category_id' => $category->id,
            'name' => 'QA Admin Subcategory',
            'slug' => 'qa-admin-subcategory',
            'is_active' => true,
        ]);
    }
}
