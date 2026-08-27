<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class CartService
{
    public function items(): Collection
    {
        if (Auth::check()) {
            $this->mergeGuestCart();

            return Auth::user()->cartItems()->with('product.subcategory')->get();
        }

        $cart = session('guest_cart', []);

        if ($cart === []) {
            return collect();
        }

        $products = Product::with('subcategory')->whereIn('id', array_keys($cart))->get()->keyBy('id');

        return collect($cart)
            ->map(function (int $quantity, int|string $productId) use ($products) {
                $product = $products->get((int) $productId);

                if (! $product) {
                    return null;
                }

                return (object) [
                    'id' => $product->id,
                    'product_id' => $product->id,
                    'product' => $product,
                    'quantity' => $quantity,
                ];
            })
            ->filter()
            ->values();
    }

    public function count(): int
    {
        if (Auth::check()) {
            $this->mergeGuestCart();

            return (int) Auth::user()->cartItems()->sum('quantity');
        }

        return array_sum(session('guest_cart', []));
    }

    public function subtotal(Collection $items): float
    {
        return (float) $items->sum(fn ($item) => $item->product->price * $item->quantity);
    }

    public function memberDiscount(float $subtotal): float
    {
        return Auth::check() ? round($subtotal * 0.07, 2) : 0.0;
    }

    public function add(Product $product, int $quantity): int
    {
        if (Auth::check()) {
            $cartItem = Auth::user()->cartItems()->firstOrNew(['product_id' => $product->id]);
            $cartItem->quantity = min(($cartItem->exists ? $cartItem->quantity : 0) + $quantity, $product->stock);
            $cartItem->save();

            return $cartItem->quantity;
        }

        $cart = session('guest_cart', []);
        $current = $cart[$product->id] ?? 0;
        $cart[$product->id] = min($current + $quantity, $product->stock);
        session(['guest_cart' => $cart]);

        return $cart[$product->id];
    }

    public function update(int $id, int $quantity): void
    {
        if (Auth::check()) {
            $cartItem = Auth::user()->cartItems()->where('id', $id)->with('product')->firstOrFail();
            $cartItem->quantity = min($quantity, $cartItem->product->stock);
            $cartItem->save();

            return;
        }

        $cart = session('guest_cart', []);
        $product = Product::findOrFail($id);
        $cart[$id] = min($quantity, $product->stock);
        session(['guest_cart' => $cart]);
    }

    public function remove(int $id): void
    {
        if (Auth::check()) {
            Auth::user()->cartItems()->where('id', $id)->firstOrFail()->delete();

            return;
        }

        $cart = session('guest_cart', []);
        unset($cart[$id]);
        session(['guest_cart' => $cart]);
    }

    public function clear(): void
    {
        if (Auth::check()) {
            Auth::user()->cartItems()->delete();

            return;
        }

        session()->forget('guest_cart');
    }

    private function mergeGuestCart(): void
    {
        $guestCart = session('guest_cart', []);

        if (empty($guestCart)) {
            return;
        }

        $products = Product::whereIn('id', array_keys($guestCart))->get()->keyBy('id');

        foreach ($guestCart as $productId => $quantity) {
            $product = $products->get((int) $productId);

            if (! $product || ! $product->is_active || $product->stock <= 0) {
                continue;
            }

            $cartItem = Auth::user()->cartItems()->firstOrNew(['product_id' => $product->id]);
            $cartItem->quantity = min(($cartItem->exists ? $cartItem->quantity : 0) + (int) $quantity, $product->stock);
            $cartItem->save();
        }

        session()->forget('guest_cart');
    }
}
