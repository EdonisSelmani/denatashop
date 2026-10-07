<?php

namespace App\Http\Controllers;

use App\Mail\AdminNewOrderMail;
use App\Mail\CustomerOrderConfirmationMail;
use App\Models\Coupon;
use App\Models\Order;
use App\Services\CartService;
use App\Services\PriceNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckoutController extends Controller
{
    private const RECENT_ORDER_SESSION_KEY = 'checkout_recent_order_number';

    public function index(Request $request, CartService $cart, PriceNormalizer $prices)
    {
        if ($redirect = $this->unverifiedUserRedirect($request)) {
            return $redirect;
        }

        $cartItems = $cart->items();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Shporta juaj eshte bosh.');
        }

        $subtotalCents = $cart->subtotalCents($cartItems);
        $shippingCents = 0;
        $memberDiscountCents = $cart->memberDiscountCents($subtotalCents);
        $couponBaseCents = max(0, $subtotalCents - $memberDiscountCents);
        [$coupon, $couponDiscountCents, $couponError] = $this->resolveCoupon($request->input('coupon_code'), $couponBaseCents);
        $discountCents = $memberDiscountCents + $couponDiscountCents;
        $payableMerchandiseCents = max(0, $subtotalCents - $discountCents);
        $minimumOrderCents = (int) config('shop.minimum_order_cents', 1000);

        $subtotal = $prices->formatCents($subtotalCents);
        $shippingTotal = $prices->formatCents($shippingCents);
        $memberDiscountTotal = $prices->formatCents($memberDiscountCents);
        $couponDiscountTotal = $prices->formatCents($couponDiscountCents);
        $discountTotal = $prices->formatCents($discountCents);
        $total = $prices->formatCents($payableMerchandiseCents + $shippingCents);
        $minimumOrder = $prices->formatCents($minimumOrderCents);
        $meetsMinimumOrder = $payableMerchandiseCents >= $minimumOrderCents;

        return view('checkout.index', compact(
            'cartItems',
            'subtotal',
            'shippingTotal',
            'memberDiscountTotal',
            'couponDiscountTotal',
            'discountTotal',
            'total',
            'coupon',
            'couponError',
            'minimumOrder',
            'meetsMinimumOrder'
        ));
    }

    public function store(Request $request, CartService $cart, PriceNormalizer $prices)
    {
        if ($redirect = $this->unverifiedUserRedirect($request)) {
            return $redirect;
        }

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
            $order = DB::transaction(function () use ($cart, $validated, $prices) {
                $cartItems = $cart->items();

                if ($cartItems->isEmpty()) {
                    throw new \RuntimeException('Shporta juaj eshte bosh.');
                }

                foreach ($cartItems as $item) {
                    if (! $item->product || ! $item->product->is_active || $item->quantity > $item->product->stock) {
                        throw new \RuntimeException('Nje produkt ne shporte nuk eshte me i disponueshem ne sasine e kerkuar.');
                    }
                }

                $subtotalCents = $cart->subtotalCents($cartItems);
                $shippingCents = 0;
                $memberDiscountCents = $cart->memberDiscountCents($subtotalCents);
                $couponBaseCents = max(0, $subtotalCents - $memberDiscountCents);
                [$coupon, $couponDiscountCents, $couponError] = $this->resolveCoupon($validated['coupon_code'] ?? null, $couponBaseCents);

                if ($couponError) {
                    throw new \RuntimeException($couponError);
                }

                $discountCents = $memberDiscountCents + $couponDiscountCents;
                $payableMerchandiseCents = max(0, $subtotalCents - $discountCents);

                if ($payableMerchandiseCents < (int) config('shop.minimum_order_cents', 1000)) {
                    throw new \RuntimeException('Porosia minimale është 10,00 €.');
                }

                $order = Order::create(array_merge($validated, [
                    'user_id' => Auth::id(),
                    'coupon_id' => $coupon?->id,
                    'coupon_code' => $coupon?->code,
                    'order_number' => $this->makeOrderNumber(),
                    'status' => Order::STATUS_PENDING,
                    'payment_method' => 'cash_on_delivery',
                    'payment_status' => 'unpaid',
                    'subtotal' => $prices->formatCents($subtotalCents),
                    'shipping_total' => $prices->formatCents($shippingCents),
                    'discount_total' => $prices->formatCents($discountCents),
                    'member_discount_total' => $prices->formatCents($memberDiscountCents),
                    'total' => $prices->formatCents($payableMerchandiseCents + $shippingCents),
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

        $this->sendOrderEmails($order);

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
            $number = 'DN-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }

    private function resolveCoupon(?string $code, int $subtotalCents): array
    {
        $code = trim((string) $code);

        if ($code === '') {
            return [null, 0, null];
        }

        $coupon = Coupon::where('code', strtoupper($code))->first();

        if (! $coupon || ! $coupon->isUsableForCents($subtotalCents)) {
            return [null, 0, 'Kuponi nuk eshte valid ose nuk ploteson kushtet.'];
        }

        return [$coupon, $coupon->discountCentsFor($subtotalCents), null];
    }

    private function unverifiedUserRedirect(Request $request): ?RedirectResponse
    {
        if ($request->user() && ! $request->user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice')->with(
                'error',
                'Duhet ta verifikoni emailin para se të bëni porosi.'
            );
        }

        return null;
    }

    private function sendOrderEmails(Order $order): void
    {
        $order->loadMissing('items');

        $adminEmail = config('shop.orders.admin_email');

        if ($adminEmail) {
            $this->attemptOrderEmail($order, 'admin', function () use ($adminEmail, $order) {
                Mail::to($adminEmail)->send(new AdminNewOrderMail($order));
            });
        }

        $this->attemptOrderEmail($order, 'customer', function () use ($order) {
            Mail::to($order->customer_email)->send(new CustomerOrderConfirmationMail($order));
        });
    }

    private function attemptOrderEmail(Order $order, string $recipientType, callable $send): void
    {
        try {
            $send();
        } catch (\Throwable $exception) {
            Log::warning('checkout:order-email-failed', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'recipient_type' => $recipientType,
                'error' => class_basename($exception),
            ]);
        }
    }
}
