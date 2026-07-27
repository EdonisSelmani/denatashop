<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    private const RECENT_ORDER_SESSION_KEY = 'checkout_recent_order_number';

    public function index(Request $request, CartService $cart)
    {
        $cartItems = $cart->items();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Shporta juaj eshte bosh.');
        }

        $subtotal = $cart->subtotal($cartItems);
        $shippingTotal = 0;
        $memberDiscountTotal = $cart->memberDiscount($subtotal);
        $couponBase = max(0, $subtotal - $memberDiscountTotal);
        [$coupon, $couponDiscountTotal, $couponError] = $this->resolveCoupon($request->input('coupon_code'), $couponBase);
        $discountTotal = $memberDiscountTotal + $couponDiscountTotal;
        $total = max(0, $subtotal - $discountTotal) + $shippingTotal;

        return view('checkout.index', compact(
            'cartItems',
            'subtotal',
            'shippingTotal',
            'memberDiscountTotal',
            'couponDiscountTotal',
            'discountTotal',
            'total',
            'coupon',
            'couponError'
        ));
    }

    public function store(Request $request, CartService $cart)
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'shipping_city' => ['required', 'string', 'max:120'],
            'shipping_address' => ['required', 'string', 'max:255'],
            'shipping_postal_code' => ['nullable', 'string', 'max:30'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $order = DB::transaction(function () use ($cart, $validated) {
                $cartItems = $cart->items();

                if ($cartItems->isEmpty()) {
                    throw new \RuntimeException('Shporta juaj eshte bosh.');
                }

                foreach ($cartItems as $item) {
                    if (! $item->product || ! $item->product->is_active || $item->quantity > $item->product->stock) {
                        throw new \RuntimeException('Nje produkt ne shporte nuk eshte me i disponueshem ne sasine e kerkuar.');
                    }
                }

                $subtotal = $cart->subtotal($cartItems);
                $shippingTotal = 0;
                $memberDiscountTotal = $cart->memberDiscount($subtotal);
                $couponBase = max(0, $subtotal - $memberDiscountTotal);
                [$coupon, $couponDiscountTotal, $couponError] = $this->resolveCoupon($validated['coupon_code'] ?? null, $couponBase);

                if ($couponError) {
                    throw new \RuntimeException($couponError);
                }

                $discountTotal = $memberDiscountTotal + $couponDiscountTotal;

                $order = Order::create(array_merge($validated, [
                    'user_id' => Auth::id(),
                    'coupon_id' => $coupon?->id,
                    'coupon_code' => $coupon?->code,
                    'order_number' => $this->makeOrderNumber(),
                    'status' => Order::STATUS_PENDING,
                    'payment_method' => 'cash_on_delivery',
                    'payment_status' => 'unpaid',
                    'subtotal' => $subtotal,
                    'shipping_total' => $shippingTotal,
                    'discount_total' => $discountTotal,
                    'member_discount_total' => $memberDiscountTotal,
                    'total' => max(0, $subtotal - $discountTotal) + $shippingTotal,
                ]));

                $coupon?->increment('used_count');

                foreach ($cartItems as $item) {
                    $order->items()->create([
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name,
                        'product_sku' => $item->product->sku,
                        'unit_price' => $item->product->price,
                        'quantity' => $item->quantity,
                        'total' => $item->product->price * $item->quantity,
                    ]);

                    $item->product->decrement('stock', $item->quantity);
                }

                $cart->clear();

                return $order;
            });
        } catch (\RuntimeException $exception) {
            return redirect()->route('cart.index')->with('error', $exception->getMessage());
        }

        session()->put(self::RECENT_ORDER_SESSION_KEY, $order->order_number);

        return redirect()->route('checkout.success', $order->order_number)->with('success', 'Porosia u krijua me sukses.');
    }

    public function success(string $orderNumber)
    {
        $order = Order::with('items.product')->where('order_number', $orderNumber)->firstOrFail();

        $recentOrderNumber = session(self::RECENT_ORDER_SESSION_KEY);
        $isRecentCheckout = $recentOrderNumber && hash_equals((string) $recentOrderNumber, $order->order_number);
        $isOwner = Auth::check()
            && $order->user_id !== null
            && (int) $order->user_id === Auth::id();

        abort_unless($isOwner || $isRecentCheckout, 403);

        return view('orders.show', compact('order'));
    }

    private function makeOrderNumber(): string
    {
        do {
            $number = 'DN-' . now()->format('Ymd') . '-' . str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }

    private function resolveCoupon(?string $code, float $subtotal): array
    {
        $code = trim((string) $code);

        if ($code === '') {
            return [null, 0.0, null];
        }

        $coupon = Coupon::where('code', strtoupper($code))->first();

        if (! $coupon || ! $coupon->isUsableFor($subtotal)) {
            return [null, 0.0, 'Kuponi nuk eshte valid ose nuk ploteson kushtet.'];
        }

        return [$coupon, $coupon->discountFor($subtotal), null];
    }
}
