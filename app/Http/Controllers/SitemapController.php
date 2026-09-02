<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use App\Support\Seo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = collect([
            [
                'loc' => Seo::absoluteUrl(route('home', [], false)),
                'lastmod' => null,
                'changefreq' => 'daily',
                'priority' => '1.0',
            ],
            [
                'loc' => Seo::absoluteUrl(route('shop', [], false)),
                'lastmod' => null,
                'changefreq' => 'daily',
                'priority' => '0.9',
            ],
        ]);

        Category::query()
            ->select(['id', 'slug', 'updated_at'])
            ->where('is_active', true)
            ->whereHas('products', fn (Builder $query) => $query->where('products.is_active', true))
            ->orderBy('slug')
            ->get()
            ->each(fn (Category $category) => $urls->push([
                'loc' => Seo::absoluteUrl(route('category.show', $category->slug, false)),
                'lastmod' => $category->updated_at,
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ]));

        Subcategory::query()
            ->select(['id', 'category_id', 'slug', 'updated_at'])
            ->with(['category:id,slug,is_active'])
            ->where('is_active', true)
            ->whereHas('category', fn (Builder $query) => $query->where('is_active', true))
            ->whereHas('products', fn (Builder $query) => $query->where('products.is_active', true))
            ->orderBy('slug')
            ->get()
            ->each(function (Subcategory $subcategory) use ($urls): void {
                if (! $subcategory->category) {
                    return;
                }

                $urls->push([
                    'loc' => Seo::absoluteUrl(route('subcategory.show', [$subcategory->category->slug, $subcategory->slug], false)),
                    'lastmod' => $subcategory->updated_at,
                    'changefreq' => 'weekly',
                    'priority' => '0.7',
                ]);
            });

        Product::query()
            ->select(['slug', 'updated_at'])
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->get()
            ->each(fn (Product $product) => $urls->push([
                'loc' => Seo::absoluteUrl(route('product.show', $product->slug, false)),
                'lastmod' => $product->updated_at,
                'changefreq' => 'weekly',
                'priority' => '0.6',
            ]));

        return response()
            ->view('seo.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
