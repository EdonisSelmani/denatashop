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

    public function test_search_suggestions_prioritize_active_name_prefix_matches(): void
    {
        [, $product] = $this->createCatalogProduct([
            'name' => 'Lavaman Alpha',
            'slug' => 'lavaman-alpha',
            'sku' => 'QA-LAVAMAN-ALPHA',
            'price' => 12.5,
        ]);

        Product::create([
            'subcategory_id' => $product->subcategory_id,
            'name' => 'Sink Lavaman',
            'slug' => 'sink-lavaman',
            'description' => 'Contains match should be ranked after prefix matches.',
            'price' => 18.9,
            'stock' => 8,
            'sku' => 'QA-SINK-LAVAMAN',
            'is_active' => true,
            'is_featured' => false,
        ]);

        Product::create([
            'subcategory_id' => $product->subcategory_id,
            'name' => 'Inactive Lavaman',
            'slug' => 'inactive-lavaman',
            'description' => 'Inactive product should not appear.',
            'price' => 18.9,
            'stock' => 8,
            'sku' => 'QA-INACTIVE-LAVAMAN',
            'is_active' => false,
            'is_featured' => false,
        ]);

        foreach (range(2, 9) as $index) {
            Product::create([
                'subcategory_id' => $product->subcategory_id,
                'name' => 'Lavaman Suggestion '.$index,
                'slug' => 'lavaman-suggestion-'.$index,
                'description' => 'Additional active suggestion.',
                'price' => 10 + $index,
                'stock' => 5,
                'sku' => 'QA-LAVAMAN-'.$index,
                'is_active' => true,
                'is_featured' => false,
            ]);
        }

        $response = $this->getJson(route('search.suggestions', ['q' => 'lavaman']));

        $response
            ->assertOk()
            ->assertJsonCount(8, 'suggestions')
            ->assertJsonPath('suggestions.0.name', 'Lavaman Alpha')
            ->assertJsonPath('suggestions.0.price', '12.50')
            ->assertJsonPath('has_more', true)
            ->assertJsonMissing(['name' => 'Inactive Lavaman']);
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

    public function test_public_catalog_pages_render_seo_metadata(): void
    {
        [$category, $product] = $this->createCatalogProduct();

        $this->get(route('shop', ['search' => 'QA']))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://denatashop.com/shop">', false)
            ->assertSee('<meta name="robots" content="noindex,follow">', false);

        $this->get(route('subcategory.show', [$category->slug, $product->subcategory->slug]))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://denatashop.com/category/qa-shop-category/qa-shop-subcategory">', false)
            ->assertSee('<meta name="robots" content="index,follow">', false)
            ->assertSee('"@type": "BreadcrumbList"', false);

        $this->get(route('product.show', $product->slug))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://denatashop.com/product/qa-shop-product">', false)
            ->assertSee('<meta name="robots" content="index,follow">', false)
            ->assertSee('"@type": "Product"', false)
            ->assertSee('"priceCurrency": "EUR"', false);
    }

    public function test_sitemap_includes_public_catalog_urls_and_excludes_private_pages(): void
    {
        [$category, $product] = $this->createCatalogProduct();

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('https://denatashop.com/', false)
            ->assertSee('https://denatashop.com/category/'.$category->slug, false)
            ->assertSee('https://denatashop.com/category/'.$category->slug.'/'.$product->subcategory->slug, false)
            ->assertSee('https://denatashop.com/product/'.$product->slug, false)
            ->assertDontSee('/login', false)
            ->assertDontSee('/cart', false)
            ->assertDontSee('/admin', false);
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
