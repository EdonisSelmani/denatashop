<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer([
            'layouts.navigation',
            'layouts.footer',
            'components.store.header',
            'components.store.mobile-menu',
            'components.store.footer',
        ], function ($view) {
            $categories = Cache::remember('navigation.categories', now()->addMinutes(10), fn () => Category::where('is_active', true)
                ->whereHas('products', fn ($query) => $query->where('products.is_active', true))
                ->with(['subcategories' => fn ($query) => $query
                    ->where('subcategories.is_active', true)
                    ->whereHas('products', fn ($productQuery) => $productQuery->where('products.is_active', true))])
                ->get());

            $view->with('categories', $categories);
        });

        View::composer('*', function ($view) {
            static $sharedUserData = null;

            if ($sharedUserData === null) {
                $user = Auth::user();

                $sharedUserData = [
                    'cartCount' => $user ? $user->cartItems()->sum('quantity') : array_sum(session('guest_cart', [])),
                    'wishlistCount' => $user ? $user->favorites()->count() : 0,
                    'wishlistProductIds' => $user ? $user->favorites()->pluck('products.id')->all() : [],
                ];
            }

            $view->with($sharedUserData);
        });
    }
}
