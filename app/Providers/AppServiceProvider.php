<?php

namespace App\Providers;

use App\Services\PublicCatalogCache;
use Illuminate\Support\Facades\Auth;
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
            $view->with('categories', app(PublicCatalogCache::class)->navigationCategories());
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
