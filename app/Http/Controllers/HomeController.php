<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Get categories with subcategories count
        $categories = Category::where('is_active', true)
            ->withCount('subcategories')
            ->get();
        
        // Featured Products
        $featuredProducts = Product::with('subcategory.category')
            ->where('is_active', true)
            ->where('is_featured', true)
            ->latest()
            ->take(8)
            ->get();
        
        // New Arrivals (latest products)
        $newProducts = Product::with('subcategory.category')
            ->where('is_active', true)
            ->latest()
            ->take(8)
            ->get();
        
        // Best Sellers (by cart items count - you may need to implement this logic)
        $bestSellers = Product::with('subcategory.category')
            ->where('is_active', true)
            ->withCount('cartItems')
            ->orderBy('cart_items_count', 'desc')
            ->take(8)
            ->get();
        
        // Discount Products (products with compare_price)
        $discountProducts = Product::with('subcategory.category')
            ->where('is_active', true)
            ->whereNotNull('compare_price')
            ->whereRaw('compare_price > price')
            ->latest()
            ->take(8)
            ->get();
        
        return view('home.index', compact(
            'categories', 
            'featuredProducts', 
            'newProducts', 
            'bestSellers', 
            'discountProducts'
        ));
    }
}
