<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_and_user_is_forbidden(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_user_cannot_view_another_users_order(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $order = $this->createOrder($owner);

        $this->actingAs($otherUser)
            ->get(route('orders.show', $order))
            ->assertForbidden();
    }

    public function test_checkout_success_page_requires_order_owner_or_recent_checkout_session(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $order = $this->createOrder($owner);

        $this->actingAs($otherUser)
            ->get(route('checkout.success', $order->order_number))
            ->assertForbidden();

        $this->actingAs($owner)
            ->get(route('checkout.success', $order->order_number))
            ->assertOk();
    }

    public function test_guest_order_success_page_requires_recent_checkout_session(): void
    {
        $order = $this->createOrder();

        $this->get(route('checkout.success', $order->order_number))
            ->assertForbidden();

        $this->withSession(['checkout_recent_order_number' => $order->order_number])
            ->get(route('checkout.success', $order->order_number))
            ->assertOk();
    }

    private function createOrder(?User $user = null): Order
    {
        $product = $this->createProduct();

        $order = Order::create([
            'user_id' => $user?->id,
            'order_number' => 'QA-'.uniqid(),
            'status' => Order::STATUS_PENDING,
            'payment_method' => 'cash_on_delivery',
            'payment_status' => 'unpaid',
            'customer_name' => 'QA Test User',
            'customer_email' => 'qa@example.com',
            'customer_phone' => '+38344111222',
            'shipping_city' => 'Prishtine',
            'shipping_address' => 'QA Test Address',
            'subtotal' => 20,
            'shipping_total' => 0,
            'discount_total' => 0,
            'member_discount_total' => 0,
            'total' => 20,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'unit_price' => 20,
            'quantity' => 1,
            'total' => 20,
        ]);

        return $order;
    }

    private function createProduct(): Product
    {
        $category = Category::create([
            'name' => 'QA Category',
            'slug' => 'qa-category',
            'is_active' => true,
        ]);

        $subcategory = Subcategory::create([
            'category_id' => $category->id,
            'name' => 'QA Subcategory',
            'slug' => 'qa-subcategory',
            'is_active' => true,
        ]);

        return Product::create([
            'subcategory_id' => $subcategory->id,
            'name' => 'QA Product',
            'slug' => 'qa-product',
            'description' => 'QA product for authorization tests.',
            'price' => 20,
            'stock' => 5,
            'sku' => 'QA-PRODUCT',
            'is_active' => true,
            'is_featured' => false,
        ]);
    }
}
