<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ShopPublicTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_shop_category_and_product_pages_load(): void
    {
        [$category, $product] = $this->createCatalogProduct();

        $this->get(route('home'))->assertOk();
        $this->get(route('shop'))->assertOk();
        $this->get(route('category.show', $category->slug))->assertOk();
        $this->get(route('product.show', $product->slug))->assertOk();
    }

    public function test_invalid_public_slugs_return_404(): void
    {
        $this->get(route('category.show', 'missing-category'))->assertNotFound();
        $this->get(route('product.show', 'missing-product'))->assertNotFound();
    }

    public function test_shop_search_handles_special_characters_safely(): void
    {
        $this->createCatalogProduct([
            'name' => 'QA Search Product',
            'slug' => 'qa-search-product',
            'sku' => 'QA-SEARCH',
        ]);

        foreach ([
            'QA Search Product',
            'qa search',
            'Çekiç',
            '   spaces   ',
            '%',
            '_',
            '<script>alert(1)</script>',
            "' OR 1=1 --",
        ] as $term) {
            $this->get(route('shop', ['search' => $term]))->assertOk();
        }
    }

    public function test_shop_filters_and_sorting_load(): void
    {
        [$category, $product] = $this->createCatalogProduct();

        $this->get(route('shop', [
            'category' => $category->slug,
            'subcategory' => $product->subcategory->slug,
            'min_price' => 0,
            'max_price' => 50,
            'sort' => 'price_low',
        ]))->assertOk();
    }

    public function test_public_filters_hide_empty_subcategories(): void
    {
        Cache::flush();

        [$category, $product] = $this->createCatalogProduct();
        Subcategory::create([
            'category_id' => $category->id,
            'name' => 'QA Empty Subcategory',
            'slug' => 'qa-empty-subcategory',
            'is_active' => true,
        ]);

        $this->get(route('shop'))
            ->assertOk()
            ->assertSee($product->subcategory->name)
            ->assertDontSee('QA Empty Subcategory');

        $this->get(route('category.show', $category->slug))
            ->assertOk()
            ->assertSee($product->subcategory->name)
            ->assertDontSee('QA Empty Subcategory');
    }

    private function createCatalogProduct(array $overrides = []): array
    {
        $category = Category::create([
            'name' => 'QA Shop Category',
            'slug' => 'qa-shop-category',
            'is_active' => true,
        ]);

        $subcategory = Subcategory::create([
            'category_id' => $category->id,
            'name' => 'QA Shop Subcategory',
            'slug' => 'qa-shop-subcategory',
            'is_active' => true,
        ]);

        $product = Product::create(array_merge([
            'subcategory_id' => $subcategory->id,
            'name' => 'QA Shop Product',
            'slug' => 'qa-shop-product',
            'description' => 'QA product for public shop tests.',
            'price' => 20,
            'stock' => 10,
            'sku' => 'QA-SHOP-PRODUCT',
            'is_active' => true,
            'is_featured' => false,
        ], $overrides));

        return [$category, $product];
    }
}
