<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function index()
    {
        $requestStartedAt = microtime(true);
        $log = function (string $stage, array $context = []) use ($requestStartedAt): void {
            Log::info($stage, array_merge([
                'elapsed_ms' => round((microtime(true) - $requestStartedAt) * 1000, 2),
            ], $context));
        };
        $time = function (string $stage, callable $callback) use ($log) {
            $startedAt = microtime(true);
            $log($stage.':start');
            $result = $callback();
            $log($stage.':complete', [
                'duration_ms' => round((microtime(true) - $startedAt) * 1000, 2),
                'count' => is_countable($result) ? count($result) : null,
            ]);

            return $result;
        };

        $log('homepage:start');

        $categories = $time('homepage:categories', fn () => Category::where('is_active', true)
            ->whereHas('products', fn ($query) => $query->where('products.is_active', true))
            ->with(['subcategories' => fn ($query) => $query
                ->where('subcategories.is_active', true)
                ->whereHas('products', fn ($productQuery) => $productQuery->where('products.is_active', true))])
            ->withCount(['products as active_products_count' => fn ($query) => $query->where('products.is_active', true)])
            ->get());
        
        // Featured Products
        $featuredProducts = $time('homepage:featured-products', fn () => Product::with('subcategory.category')
            ->where('is_active', true)
            ->where('is_featured', true)
            ->latest()
            ->take(8)
            ->get());
        
        // New Arrivals (latest products)
        $newProducts = $time('homepage:new-products', fn () => Product::with('subcategory.category')
            ->where('is_active', true)
            ->latest()
            ->take(8)
            ->get());
        
        // Best Sellers (by cart items count - you may need to implement this logic)
        $bestSellers = $time('homepage:best-sellers', fn () => Product::with('subcategory.category')
            ->where('is_active', true)
            ->withCount('cartItems')
            ->orderBy('cart_items_count', 'desc')
            ->take(8)
            ->get());
        
        // Discount Products (products with compare_price)
        $discountProducts = $time('homepage:discount-products', fn () => Product::with('subcategory.category')
            ->where('is_active', true)
            ->whereNotNull('compare_price')
            ->whereColumn('compare_price', '>', 'price')
            ->latest()
            ->take(8)
            ->get());

        $log('homepage:view-start');
        
        $html = view('home.index', compact(
            'categories', 
            'featuredProducts', 
            'newProducts', 
            'bestSellers', 
            'discountProducts'
        ))->render();

        $log('homepage:view-complete', [
            'bytes' => strlen($html),
        ]);

        return response($html);
    }
}
