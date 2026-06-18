<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_order_from_cart(): void
    {
        $user = User::factory()->create();
        $category = Category::create([
            'name' => 'Shoes',
            'slug' => 'shoes',
            'is_active' => true,
        ]);
        $subcategory = Subcategory::create([
            'category_id' => $category->id,
            'name' => 'Sneakers',
            'slug' => 'sneakers',
            'is_active' => true,
        ]);
        $product = Product::create([
            'subcategory_id' => $subcategory->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'description' => 'A product for checkout testing.',
            'price' => 25,
            'stock' => 5,
            'sku' => 'TP-001',
            'is_active' => true,
            'is_featured' => false,
        ]);

        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'customer_name' => 'Test User',
            'customer_email' => 'test@example.com',
            'customer_phone' => '+38344111222',
            'shipping_city' => 'Prishtine',
            'shipping_address' => 'Rruga Test 1',
            'shipping_postal_code' => '10000',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseHas('order_items', [
            'product_name' => 'Test Product',
            'quantity' => 2,
        ]);
        $this->assertDatabaseHas('orders', [
            'subtotal' => 50,
            'member_discount_total' => 3.5,
            'discount_total' => 3.5,
            'total' => 46.5,
        ]);
        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_user_can_apply_coupon_during_checkout(): void
    {
        $user = User::factory()->create();
        $category = Category::create([
            'name' => 'Tools',
            'slug' => 'tools',
            'is_active' => true,
        ]);
        $subcategory = Subcategory::create([
            'category_id' => $category->id,
            'name' => 'Hand Tools',
            'slug' => 'hand-tools',
            'is_active' => true,
        ]);
        $product = Product::create([
            'subcategory_id' => $subcategory->id,
            'name' => 'Hammer',
            'slug' => 'hammer',
            'description' => 'A product for coupon testing.',
            'price' => 50,
            'stock' => 4,
            'sku' => 'HAM-001',
            'is_active' => true,
            'is_featured' => false,
        ]);
        $coupon = Coupon::create([
            'code' => 'DENATA10',
            'type' => Coupon::TYPE_PERCENT,
            'value' => 10,
            'minimum_order_total' => 20,
            'is_active' => true,
        ]);

        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'customer_name' => 'Test User',
            'customer_email' => 'test@example.com',
            'customer_phone' => '+38344111222',
            'shipping_city' => 'Prishtine',
            'shipping_address' => 'Rruga Test 1',
            'shipping_postal_code' => '10000',
            'coupon_code' => 'denata10',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'coupon_id' => $coupon->id,
            'coupon_code' => 'DENATA10',
            'subtotal' => 100,
            'member_discount_total' => 7,
            'discount_total' => 16.3,
            'total' => 83.7,
        ]);
        $this->assertSame(1, $coupon->fresh()->used_count);
    }

    public function test_guest_can_create_order_from_session_cart(): void
    {
        $category = Category::create([
            'name' => 'Garden',
            'slug' => 'garden',
            'is_active' => true,
        ]);
        $subcategory = Subcategory::create([
            'category_id' => $category->id,
            'name' => 'Tools',
            'slug' => 'garden-tools',
            'is_active' => true,
        ]);
        $product = Product::create([
            'subcategory_id' => $subcategory->id,
            'name' => 'Rake',
            'slug' => 'rake',
            'description' => 'A product for guest checkout testing.',
            'price' => 30,
            'stock' => 5,
            'sku' => 'RAKE-001',
            'is_active' => true,
            'is_featured' => false,
        ]);

        $this->withSession([
            'guest_cart' => [
                $product->id => 2,
            ],
        ])->post(route('checkout.store'), [
            'customer_name' => 'Guest User',
            'customer_email' => 'guest@example.com',
            'customer_phone' => '+38344111222',
            'shipping_city' => 'Prishtine',
            'shipping_address' => 'Rruga Test 1',
            'shipping_postal_code' => '10000',
        ])->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'user_id' => null,
            'customer_email' => 'guest@example.com',
            'subtotal' => 60,
            'member_discount_total' => 0,
            'discount_total' => 0,
            'total' => 60,
        ]);
        $this->assertSame(3, $product->fresh()->stock);
    }
}
