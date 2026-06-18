@props(['product'])

<div class="group bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-200 relative">
    <div class="absolute top-3 left-3 z-10 flex gap-2">
        @if($product->compare_price && $product->compare_price > $product->price)
            <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full">
                -{{ round((($product->compare_price - $product->price) / $product->compare_price) * 100) }}%
            </span>
        @endif
        @if($product->is_featured)
            <span class="bg-yellow-500 text-white text-xs px-2 py-1 rounded-full">Featured</span>
        @endif
        @if($product->stock < 5 && $product->stock > 0)
            <span class="bg-orange-500 text-white text-xs px-2 py-1 rounded-full">Stok i kufizuar</span>
        @endif
    </div>

    @auth
        <button class="add-to-wishlist absolute top-3 right-3 z-10 bg-white rounded-full p-2 shadow-md hover:bg-red-50 transition"
                data-product-id="{{ $product->id }}"
                data-url="{{ route('wishlist.toggle') }}">
            <svg class="w-5 h-5 {{ in_array($product->id, $wishlistProductIds ?? []) ? 'text-red-500 fill-current' : 'text-gray-600' }}"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
            </svg>
        </button>
    @endauth

    <a href="{{ route('product.show', $product->slug) }}" class="block overflow-hidden bg-gray-100">
        <img src="{{ $product->thumbnail_url }}"
             alt="{{ $product->name }}"
             loading="lazy"
             decoding="async"
             class="w-full h-64 object-cover">
    </a>

    <div class="p-4">
        @if($product->subcategory?->category)
            <a href="{{ route('category.show', $product->subcategory->category->slug) }}" class="text-xs text-gray-500 hover:text-blue-600">
                {{ $product->subcategory->category->name }}
            </a>
        @endif

        <a href="{{ route('product.show', $product->slug) }}">
            <h3 class="font-semibold text-gray-800 mt-1 mb-2 hover:text-blue-600 transition line-clamp-2">
                {{ $product->name }}
            </h3>
        </a>

        <div class="flex items-baseline gap-2 mb-3">
            <span class="text-2xl font-bold text-blue-600">€{{ number_format($product->price, 2) }}</span>
            @if($product->compare_price && $product->compare_price > $product->price)
                <span class="text-sm text-gray-500 line-through">€{{ number_format($product->compare_price, 2) }}</span>
            @endif
        </div>

        @if($product->stock > 0)
            <button class="add-to-cart w-full bg-gray-900 text-white py-2 rounded-lg text-sm hover:bg-blue-600 transition flex items-center justify-center gap-2"
                    data-product-id="{{ $product->id }}"
                    data-url="{{ route('cart.add') }}"
                    data-quantity="1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.5 6M17 13l1.5 6M9 21h6M12 17v4"></path>
                </svg>
                <span>Shto ne Shporte</span>
            </button>
        @else
            <button class="w-full bg-gray-300 text-gray-500 py-2 rounded-lg cursor-not-allowed" disabled>
                Nuk ka stok
            </button>
        @endif
    </div>
</div>
