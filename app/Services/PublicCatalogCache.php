<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PublicCatalogCache
{
    public const TTL_SECONDS = 300;
    public const NAVIGATION_CATEGORIES_KEY = 'public.navigation.categories.v2';
    public const HOMEPAGE_SECTIONS_KEY = 'public.homepage.sections.v2';

    public function hasNavigationCategories(): bool
    {
        return Cache::has(self::NAVIGATION_CATEGORIES_KEY);
    }

    public function hasHomepageSections(): bool
    {
        return Cache::has(self::HOMEPAGE_SECTIONS_KEY);
    }

    public function navigationCategories(): EloquentCollection
    {
        return Cache::remember(self::NAVIGATION_CATEGORIES_KEY, self::TTL_SECONDS, fn () => Category::query()
            ->where('is_active', true)
            ->whereHas('products', fn (Builder $query) => $query->where('products.is_active', true))
            ->with(['subcategories' => fn ($query) => $query
                ->select('id', 'category_id', 'name', 'slug', 'is_active')
                ->where('subcategories.is_active', true)
                ->whereHas('products', fn (Builder $productQuery) => $productQuery->where('products.is_active', true))])
            ->withCount(['products as active_products_count' => fn (Builder $query) => $query->where('products.is_active', true)])
            ->get());
    }

    public function homepageSections(): array
    {
        return Cache::remember(self::HOMEPAGE_SECTIONS_KEY, self::TTL_SECONDS, function () {
            $featuredProducts = $this->baseHomepageProductQuery()
                ->where('products.is_featured', true)
                ->latest('products.created_at')
                ->take(8)
                ->get();

            $newProducts = $this->baseHomepageProductQuery()
                ->latest('products.created_at')
                ->take(8)
                ->get();

            $bestSellers = $this->baseHomepageProductQuery()
                ->leftJoinSub(
                    DB::table('cart_items')
                        ->select('product_id')
                        ->selectRaw('COUNT(*) as cart_items_count')
                        ->groupBy('product_id'),
                    'cart_counts',
                    fn ($join) => $join->on('products.id', '=', 'cart_counts.product_id')
                )
                ->addSelect(DB::raw('COALESCE(cart_counts.cart_items_count, 0) as cart_items_count'))
                ->orderByDesc('cart_items_count')
                ->latest('products.created_at')
                ->take(8)
                ->get();

            $discountProducts = $this->baseHomepageProductQuery()
                ->whereNotNull('products.compare_price')
                ->whereColumn('products.compare_price', '>', 'products.price')
                ->latest('products.created_at')
                ->take(8)
                ->get();

            $this->loadSharedHomepageRelations(
                $featuredProducts,
                $newProducts,
                $bestSellers,
                $discountProducts
            );

            return [
                'featuredProducts' => $featuredProducts,
                'newProducts' => $newProducts,
                'bestSellers' => $bestSellers,
                'discountProducts' => $discountProducts,
            ];
        });
    }

    private function baseHomepageProductQuery(): Builder
    {
        return Product::query()
            ->select([
                'products.id',
                'products.subcategory_id',
                'products.name',
                'products.slug',
                'products.price',
                'products.compare_price',
                'products.stock',
                'products.sku',
                'products.image',
                'products.is_active',
                'products.is_featured',
                'products.created_at',
            ])
            ->where('products.is_active', true);
    }

    private function loadSharedHomepageRelations(EloquentCollection ...$collections): void
    {
        $models = [];

        foreach ($collections as $collection) {
            array_push($models, ...$collection->all());
        }

        if ($models === []) {
            return;
        }

        (new EloquentCollection($models))->load([
            'subcategory:id,category_id,name,slug',
        ]);
    }
}
