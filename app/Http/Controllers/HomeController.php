<?php

namespace App\Http\Controllers;

use App\Services\PublicCatalogCache;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function index(PublicCatalogCache $catalogCache)
    {
        $requestStartedAt = microtime(true);
        $log = function (string $stage, array $context = []) use ($requestStartedAt): void {
            Log::info($stage, array_merge([
                'elapsed_ms' => round((microtime(true) - $requestStartedAt) * 1000, 2),
            ], $context));
        };
        $time = function (string $stage, callable $callback, array $context = []) use ($log) {
            $startedAt = microtime(true);
            $log($stage.':start', $context);
            $result = $callback();
            $log($stage.':complete', array_merge([
                'duration_ms' => round((microtime(true) - $startedAt) * 1000, 2),
                'count' => is_countable($result) ? count($result) : null,
            ], $context));

            return $result;
        };

        $log('homepage:start');

        $categoriesCacheState = $catalogCache->hasNavigationCategories() ? 'hit' : 'miss';
        $categories = $time(
            'homepage:categories',
            fn () => $catalogCache->navigationCategories(),
            ['cache' => $categoriesCacheState]
        );

        $sectionsCacheState = $catalogCache->hasHomepageSections() ? 'hit' : 'miss';
        foreach (['featured-products', 'new-products', 'best-sellers', 'discount-products'] as $section) {
            $log('homepage:'.$section.':start', [
                'cache' => $sectionsCacheState,
                'source' => 'homepage:product-sections',
            ]);
        }

        $productSections = $time(
            'homepage:product-sections',
            fn () => $catalogCache->homepageSections(),
            ['cache' => $sectionsCacheState]
        );

        $featuredProducts = $productSections['featuredProducts'];
        $newProducts = $productSections['newProducts'];
        $bestSellers = $productSections['bestSellers'];
        $discountProducts = $productSections['discountProducts'];

        foreach ([
            'featured-products' => $featuredProducts,
            'new-products' => $newProducts,
            'best-sellers' => $bestSellers,
            'discount-products' => $discountProducts,
        ] as $section => $products) {
            $log('homepage:'.$section.':complete', [
                'cache' => $sectionsCacheState,
                'source' => 'homepage:product-sections',
                'duration_ms' => 0.0,
                'count' => count($products),
            ]);
        }

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
