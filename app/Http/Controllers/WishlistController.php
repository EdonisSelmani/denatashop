<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }
    
    public function index()
    {
        $wishlistItems = Auth::user()->favorites()
            ->with('subcategory:id,category_id,name,slug')
            ->where('is_active', true)
            ->get();
        
        return view('wishlist.index', compact('wishlistItems'));
    }
    
    public function toggle(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to manage wishlist',
                'redirect' => route('login')
            ]);
        }
        
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);
        
        $product = Product::findOrFail($request->product_id);
        $user = Auth::user();
        
        if ($user->favorites()->where('product_id', $product->id)->exists()) {
            $user->favorites()->detach($product->id);
            $isFavorited = false;
            $message = 'Removed from wishlist';
        } else {
            $user->favorites()->attach($product->id);
            $isFavorited = true;
            $message = 'Added to wishlist';
        }
        
        $wishlistCount = $user->favorites()->count();
        
        return response()->json([
            'success' => true,
            'message' => $message,
            'is_favorited' => $isFavorited,
            'wishlist_count' => $wishlistCount
        ]);
    }
    
    public function getCount()
    {
        return response()->json([
            'count' => Auth::check() ? Auth::user()->favorites()->count() : 0
        ]);
    }
}
