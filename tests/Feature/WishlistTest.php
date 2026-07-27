<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_toggle_wishlist_without_login(): void
    {
        $product = $this->createProduct();

        $this->postJson(route('wishlist.toggle'), [
            'product_id' => $product->id,
        ])->assertUnauthorized();

        $this->post(route('wishlist.toggle'), [
            'product_id' => $product->id,
        ])->assertRedirect(route('login'));
    }

    public function test_user_can_add_and_remove_wishlist_item_without_duplicates(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct();

        $this->actingAs($user)->postJson(route('wishlist.toggle'), [
            'product_id' => $product->id,
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('is_favorited', true)
            ->assertJsonPath('wishlist_count', 1);

        $this->assertDatabaseCount('favorites', 1);

        $this->actingAs($user)->postJson(route('wishlist.toggle'), [
            'product_id' => $product->id,
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('is_favorited', false)
            ->assertJsonPath('wishlist_count', 0);

        $this->assertDatabaseCount('favorites', 0);
    }

    public function test_wishlist_rejects_invalid_product(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('wishlist.toggle'), [
            'product_id' => 999999,
        ])->assertUnprocessable();
    }

    private function createProduct(): Product
    {
        $category = Category::create([
            'name' => 'QA Wishlist Category',
            'slug' => 'qa-wishlist-category',
            'is_active' => true,
        ]);

        $subcategory = Subcategory::create([
            'category_id' => $category->id,
            'name' => 'QA Wishlist Subcategory',
            'slug' => 'qa-wishlist-subcategory',
            'is_active' => true,
        ]);

        return Product::create([
            'subcategory_id' => $subcategory->id,
            'name' => 'QA Wishlist Product',
            'slug' => 'qa-wishlist-product',
            'description' => 'QA product for wishlist tests.',
            'price' => 20,
            'stock' => 10,
            'sku' => 'QA-WISHLIST-PRODUCT',
            'is_active' => true,
            'is_featured' => false,
        ]);
    }
}
