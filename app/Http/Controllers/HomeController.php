<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_active', true)
            ->whereHas('products', fn ($query) => $query->where('products.is_active', true))
            ->with(['subcategories' => fn ($query) => $query
                ->where('subcategories.is_active', true)
                ->whereHas('products', fn ($productQuery) => $productQuery->where('products.is_active', true))])
            ->withCount(['products as active_products_count' => fn ($query) => $query->where('products.is_active', true)])
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
            ->whereColumn('compare_price', '>', 'price')
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
