<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Support\Str;

class Seo
{
    public static function baseUrl(): string
    {
        return rtrim((string) config('seo.base_url', config('app.url')), '/');
    }

    public static function canonical(?string $path = null): string
    {
        if ($path === null) {
            $requestPath = request()->path();
            $path = $requestPath === '/' ? '/' : '/'.ltrim($requestPath, '/');
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            $parts = parse_url($path);
            $path = $parts['path'] ?? '/';
        }

        return self::absoluteUrl($path ?: '/');
    }

    public static function absoluteUrl(?string $path = '/'): string
    {
        $baseUrl = self::baseUrl();

        if (! $path) {
            return $baseUrl;
        }

        if (Str::startsWith($path, '//')) {
            $path = 'https:'.$path;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            $parts = parse_url($path);
            $path = ($parts['path'] ?? '/').(isset($parts['query']) ? '?'.$parts['query'] : '');
        }

        return $baseUrl.'/'.ltrim($path, '/');
    }

    public static function image(?string $path = null): string
    {
        return self::absoluteUrl($path ?: (string) config('seo.default_image'));
    }

    public static function storageImage(?string $path): string
    {
        return $path ? self::image('storage/'.ltrim($path, '/')) : self::image();
    }

    public static function description(?string $text = null, int $limit = 158): string
    {
        $text = trim((string) preg_replace('/\s+/u', ' ', strip_tags((string) $text)));

        if ($text === '') {
            $text = (string) config('seo.description');
        }

        return Str::limit($text, $limit, '');
    }

    public static function jsonLd(array $data): string
    {
        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?: '{}';
    }

    public static function websiteSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => config('seo.site_name'),
            'url' => self::baseUrl(),
            'inLanguage' => 'sq',
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => self::absoluteUrl('/shop?search={search_term_string}'),
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    public static function organizationSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => config('seo.brand_name'),
            'url' => self::baseUrl(),
            'logo' => self::image((string) config('seo.logo')),
        ];
    }

    public static function breadcrumbSchema(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($items)
                ->values()
                ->map(fn (array $item, int $index): array => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $item['name'],
                    'item' => self::absoluteUrl($item['url'] ?? '/'),
                ])
                ->all(),
        ];
    }

    public static function categoryTitle(Category $category, ?Subcategory $subcategory = null): string
    {
        if ($subcategory) {
            return "{$subcategory->name} - Blej online ne Kosove | DenataShop";
        }

        return "{$category->name} | DenataShop Kosove";
    }

    public static function categoryDescription(Category $category, ?Subcategory $subcategory = null): string
    {
        if ($subcategory) {
            return self::description(
                $subcategory->description ?: "{$subcategory->name} nga kategoria {$category->name} ne DenataShop. Shfleto produkte te zgjedhura per projekte shtepie dhe pune ne Kosove."
            );
        }

        return self::description(
            $category->description ?: "Shfleto produkte nga kategoria {$category->name} ne DenataShop, me zgjedhje per shtepi, pune dhe projekte ne Kosove."
        );
    }

    public static function productTitle(Product $product): string
    {
        return "{$product->name} | DenataShop";
    }

    public static function productDescription(Product $product): string
    {
        $categoryName = $product->subcategory?->category?->name;
        $suffix = $categoryName ? " Kategoria: {$categoryName}." : '';

        return self::description(($product->description ?: $product->name).$suffix);
    }

    public static function productSchema(Product $product): array
    {
        $images = collect([$product->image])
            ->merge(collect($product->gallery ?? []))
            ->filter()
            ->map(fn (string $path): string => self::storageImage($path))
            ->unique()
            ->values();

        if ($images->isEmpty()) {
            $images->push(self::image());
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'image' => $images->all(),
            'description' => self::productDescription($product),
            'sku' => $product->sku,
            'offers' => [
                '@type' => 'Offer',
                'url' => self::canonical(route('product.show', $product->slug, false)),
                'priceCurrency' => 'EUR',
                'price' => number_format((float) $product->price, 2, '.', ''),
                'availability' => $product->stock > 0
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'seller' => [
                    '@type' => 'Organization',
                    'name' => config('seo.brand_name'),
                    'url' => self::baseUrl(),
                ],
            ],
        ];
    }
}
