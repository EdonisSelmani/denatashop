<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use App\Services\PublicCatalogCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request, PublicCatalogCache $catalogCache)
    {
        $categories = $catalogCache->navigationCategories();
        $query = $this->productCardQuery();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%")
                    ->orWhere('products.description', 'like', "%{$search}%")
                    ->orWhere('products.sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $category = $catalogCache->categoryBySlug($request->category)
                ?? Category::where('slug', $request->category)->first();

            if ($category) {
                $subcategoryIds = $category->relationLoaded('subcategories')
                    ? $category->subcategories->pluck('id')
                    : $category->subcategories()->pluck('id');

                $subcategoryIds->isNotEmpty()
                    ? $query->whereIn('products.subcategory_id', $subcategoryIds)
                    : $query->whereRaw('1 = 0');
            }
        }

        if ($request->filled('subcategory')) {
            $subcategory = $catalogCache->subcategoryBySlug($request->subcategory)
                ?? Subcategory::where('slug', $request->subcategory)->first();

            if ($subcategory) {
                $query->where('products.subcategory_id', $subcategory->id);
            }
        }

        if ($request->filled('min_price')) {
            $query->where('products.price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('products.price', '<=', $request->max_price);
        }

        if ($request->get('availability') === 'in_stock') {
            $query->where('products.stock', '>', 0);
        }

        match ($request->get('sort', 'latest')) {
            'price_low' => $query->orderBy('products.price'),
            'price_high' => $query->orderByDesc('products.price'),
            'name_asc' => $query->orderBy('products.name'),
            'name_desc' => $query->orderByDesc('products.name'),
            default => $query->latest('products.created_at'),
        };

        $products = $query->paginate(12)->withQueryString();
        $catalogCache->attachSubcategoryRelations($products->getCollection());

        if ($request->ajax()) {
            return view('shop.partials.product_grid', compact('products'))->render();
        }

        return view('shop.index', compact('products', 'categories'));
    }

    public function show($slug, PublicCatalogCache $catalogCache)
    {
        $product = Product::query()
            ->select([
                'id',
                'subcategory_id',
                'name',
                'slug',
                'description',
                'price',
                'compare_price',
                'stock',
                'sku',
                'image',
                'gallery',
                'is_active',
                'is_featured',
                'created_at',
            ])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();
        $catalogCache->attachSubcategoryRelations(new EloquentCollection([$product]));

        $relatedProducts = $this->productCardQuery()
            ->where('products.subcategory_id', $product->subcategory_id)
            ->where('products.id', '!=', $product->id)
            ->limit(4)
            ->get();
        $catalogCache->attachSubcategoryRelations($relatedProducts);

        return view('shop.show', compact('product', 'relatedProducts'));
    }

    public function category($slug, Request $request, PublicCatalogCache $catalogCache)
    {
        $category = $catalogCache->categoryBySlug($slug);

        if (! $category) {
            $category = Category::where('slug', $slug)
                ->where('is_active', true)
                ->firstOrFail();
        }

        $subcategories = $category->relationLoaded('subcategories')
            ? $category->subcategories
            : $category->subcategories()
                ->where('subcategories.is_active', true)
                ->whereHas('products', fn ($query) => $query->where('products.is_active', true))
                ->get();
        $subcategoryIds = $subcategories->pluck('id');

        $query = $this->productCardQuery();
        $subcategoryIds->isNotEmpty()
            ? $query->whereIn('products.subcategory_id', $subcategoryIds)
            : $query->whereRaw('1 = 0');

        if ($request->filled('subcategory')) {
            $subcategory = $subcategories->firstWhere('slug', $request->subcategory)
                ?? Subcategory::where('slug', $request->subcategory)->first();

            if ($subcategory) {
                $query->where('products.subcategory_id', $subcategory->id);
            }
        }

        if ($request->filled('min_price')) {
            $query->where('products.price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('products.price', '<=', $request->max_price);
        }

        if ($request->get('availability') === 'in_stock') {
            $query->where('products.stock', '>', 0);
        }

        match ($request->get('sort', 'latest')) {
            'price_low' => $query->orderBy('products.price'),
            'price_high' => $query->orderByDesc('products.price'),
            'name_asc' => $query->orderBy('products.name'),
            'name_desc' => $query->orderByDesc('products.name'),
            default => $query->latest('products.created_at'),
        };

        $products = $query->paginate(12)->withQueryString();
        $catalogCache->attachSubcategoryRelations($products->getCollection());

        return view('shop.category', compact('category', 'products', 'subcategories'));
    }

    private function productCardQuery(): Builder
    {
        return Product::query()
            ->select([
                'products.id',
                'products.subcategory_id',
                'products.name',
                'products.slug',
                'products.description',
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
}
