<?php

namespace Tests\Feature;

use App\Mail\AdminNewOrderMail;
use App\Mail\CustomerOrderConfirmationMail;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_order_from_cart(): void
    {
        Mail::fake();
        config(['shop.orders.admin_email' => 'orders@denatashop.test']);

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

        $order = Order::with('items')->firstOrFail();
        $this->assertSame('cash_on_delivery', $order->payment_method);

        Mail::assertSent(AdminNewOrderMail::class, function (AdminNewOrderMail $mail) use ($order) {
            return $mail->hasTo('orders@denatashop.test')
                && $mail->order->is($order)
                && str_contains($mail->render(), $order->order_number)
                && str_contains($mail->render(), 'Test Product');
        });

        Mail::assertSent(CustomerOrderConfirmationMail::class, function (CustomerOrderConfirmationMail $mail) use ($order) {
            $rendered = $mail->render();

            return $mail->hasTo('test@example.com')
                && $mail->order->is($order)
                && str_contains($rendered, $order->order_number)
                && str_contains($rendered, 'Test Product')
                && str_contains($rendered, 'Pagesë me para në dorëzim')
                && ! str_contains($rendered, 'cash_on_delivery');
        });
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

    public function test_direct_checkout_post_rejects_9_99_without_creating_an_order_or_decrementing_stock(): void
    {
        Mail::fake();
        $product = $this->makeProduct('9.99', 'MIN-999');

        $response = $this->withSession(['guest_cart' => [$product->id => 1]])
            ->post(route('checkout.store'), $this->checkoutData());

        $response->assertRedirect(route('cart.index'))
            ->assertSessionHas('error', 'Porosia minimale është 10,00 €.');
        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_checkout_accepts_exactly_10_00(): void
    {
        Mail::fake();
        $product = $this->makeProduct('10.00', 'MIN-1000');

        $this->withSession(['guest_cart' => [$product->id => 1]])
            ->post(route('checkout.store'), $this->checkoutData())
            ->assertRedirect();

        $this->assertDatabaseHas('orders', ['subtotal' => 10, 'total' => 10]);
        $this->assertSame(4, $product->fresh()->stock);
    }

    public function test_coupon_cannot_reduce_payable_merchandise_below_minimum(): void
    {
        Mail::fake();
        $product = $this->makeProduct('10.50', 'MIN-COUPON');
        $coupon = Coupon::create([
            'code' => 'ONEOFF',
            'type' => Coupon::TYPE_FIXED,
            'value' => '1.00',
            'minimum_order_total' => '0.00',
            'is_active' => true,
        ]);

        $response = $this->withSession(['guest_cart' => [$product->id => 1]])
            ->post(route('checkout.store'), $this->checkoutData(['coupon_code' => $coupon->code]));

        $response->assertRedirect(route('cart.index'))
            ->assertSessionHas('error', 'Porosia minimale është 10,00 €.');
        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(0, $coupon->fresh()->used_count);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_unverified_registered_user_cannot_bypass_verification_by_posting_checkout(): void
    {
        Mail::fake();
        $user = User::factory()->unverified()->create();
        $product = $this->makeProduct('20.00', 'UNVERIFIED');
        CartItem::create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($user)
            ->post(route('checkout.store'), $this->checkoutData())
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHas('error', 'Duhet ta verifikoni emailin para se të bëni porosi.');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(5, $product->fresh()->stock);
        $this->assertDatabaseHas('cart_items', ['user_id' => $user->id, 'product_id' => $product->id]);
    }

    public function test_cart_and_checkout_show_the_minimum_order_requirement(): void
    {
        $product = $this->makeProduct('9.99', 'MIN-UI');

        $this->withSession(['guest_cart' => [$product->id => 1]])
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Porosia minimale është 10,00 €', false);

        $this->withSession(['guest_cart' => [$product->id => 1]])
            ->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('Porosia minimale është 10,00 €', false);
    }

    private function makeProduct(string $price, string $sku): Product
    {
        $category = Category::create([
            'name' => 'Category '.$sku,
            'slug' => 'category-'.strtolower($sku),
            'is_active' => true,
        ]);
        $subcategory = Subcategory::create([
            'category_id' => $category->id,
            'name' => 'Subcategory '.$sku,
            'slug' => 'subcategory-'.strtolower($sku),
            'is_active' => true,
        ]);

        return Product::create([
            'subcategory_id' => $subcategory->id,
            'name' => 'Product '.$sku,
            'slug' => 'product-'.strtolower($sku),
            'description' => 'Minimum order test product.',
            'price' => $price,
            'stock' => 5,
            'sku' => $sku,
            'is_active' => true,
            'is_featured' => false,
        ]);
    }

    private function checkoutData(array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'Test User',
            'customer_email' => 'test@example.com',
            'customer_phone' => '+38344111222',
            'shipping_city' => 'Prishtine',
            'shipping_address' => 'Rruga Test 1',
            'shipping_postal_code' => '10000',
        ], $overrides);
    }
}
