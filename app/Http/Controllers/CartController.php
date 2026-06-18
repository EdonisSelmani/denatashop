<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index(CartService $cart)
    {
        $cartItems = $cart->items();
        $subtotal = $cart->subtotal($cartItems);
        $memberDiscount = $cart->memberDiscount($subtotal);
        $total = max(0, $subtotal - $memberDiscount);

        return view('cart.index', compact('cartItems', 'subtotal', 'memberDiscount', 'total'));
    }

    public function add(Request $request, CartService $cart)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'sometimes|integer|min:1|max:99',
        ]);

        $product = Product::where('is_active', true)->findOrFail($request->product_id);

        if ($product->stock <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Produkti nuk ka stok.',
            ]);
        }

        $newQuantity = $cart->add($product, (int) ($request->quantity ?? 1));

        return response()->json([
            'success' => true,
            'message' => 'Produkti u shtua ne shporte.',
            'cart_count' => $cart->count(),
            'cart_item' => [
                'id' => Auth::check()
                    ? CartItem::where('user_id', Auth::id())->where('product_id', $product->id)->value('id')
                    : $product->id,
                'quantity' => $newQuantity,
            ],
        ]);
    }

    public function update(Request $request, int $id, CartService $cart)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        $cart->update($id, (int) $request->quantity);
        $cartItems = $cart->items();
        $subtotal = $cart->subtotal($cartItems);
        $memberDiscount = $cart->memberDiscount($subtotal);

        return response()->json([
            'success' => true,
            'message' => 'Shporta u perditesua.',
            'cart_subtotal' => $subtotal,
            'member_discount' => $memberDiscount,
            'cart_total' => max(0, $subtotal - $memberDiscount),
            'cart_count' => $cart->count(),
        ]);
    }

    public function remove(int $id, CartService $cart)
    {
        $cart->remove($id);
        $cartItems = $cart->items();
        $subtotal = $cart->subtotal($cartItems);
        $memberDiscount = $cart->memberDiscount($subtotal);

        return response()->json([
            'success' => true,
            'message' => 'Produkti u fshi nga shporta.',
            'cart_subtotal' => $subtotal,
            'member_discount' => $memberDiscount,
            'cart_total' => max(0, $subtotal - $memberDiscount),
            'cart_count' => $cart->count(),
        ]);
    }

    public function getCartCount(CartService $cart)
    {
        return $cart->count();
    }
}
