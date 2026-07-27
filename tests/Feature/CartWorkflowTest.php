<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_product_to_cart_and_quantity_respects_stock(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct(['stock' => 2]);

        $response = $this->actingAs($user)->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('cart_item.quantity', 2);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_cart_rejects_invalid_quantities(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct();

        foreach ([0, -1, 'abc'] as $quantity) {
            $this->actingAs($user)->postJson(route('cart.add'), [
                'product_id' => $product->id,
                'quantity' => $quantity,
            ])->assertUnprocessable();
        }
    }

    public function test_user_cannot_update_or_remove_another_users_cart_item(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $product = $this->createProduct();
        $cartItem = CartItem::create([
            'user_id' => $owner->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->actingAs($otherUser)
            ->putJson(route('cart.update', $cartItem), ['quantity' => 2])
            ->assertNotFound();

        $this->actingAs($otherUser)
            ->deleteJson(route('cart.remove', $cartItem))
            ->assertNotFound();

        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'user_id' => $owner->id,
            'quantity' => 1,
        ]);
    }

    private function createProduct(array $overrides = []): Product
    {
        $category = Category::create([
            'name' => 'QA Cart Category',
            'slug' => 'qa-cart-category',
            'is_active' => true,
        ]);

        $subcategory = Subcategory::create([
            'category_id' => $category->id,
            'name' => 'QA Cart Subcategory',
            'slug' => 'qa-cart-subcategory',
            'is_active' => true,
        ]);

        return Product::create(array_merge([
            'subcategory_id' => $subcategory->id,
            'name' => 'QA Cart Product',
            'slug' => 'qa-cart-product',
            'description' => 'QA product for cart tests.',
            'price' => 20,
            'stock' => 10,
            'sku' => 'QA-CART-PRODUCT',
            'is_active' => true,
            'is_featured' => false,
        ], $overrides));
    }
}
