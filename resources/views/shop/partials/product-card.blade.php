{{-- resources/views/shop/partials/product-card.blade.php --}}
<div class="group bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-all duration-300">
    <div class="relative overflow-hidden">
        <a href="{{ route('product.show', $product->slug) }}">
            <img src="{{ $product->thumbnail_url }}" 
                 alt="{{ $product->name }}"
                 loading="lazy"
                 decoding="async"
                 class="w-full h-64 object-cover">
        </a>
        
        @if($product->compare_price && $product->compare_price > $product->price)
            <span class="absolute top-2 left-2 bg-red-500 text-white px-2 py-1 text-xs rounded">
                Sale
            </span>
        @endif
        
        @auth
            <button class="add-to-wishlist absolute top-2 right-2 bg-white rounded-full p-2 shadow-md hover:bg-gray-100 transition"
                    data-product-id="{{ $product->id }}"
                    data-url="{{ route('wishlist.toggle') }}">
                <svg class="w-5 h-5 {{ in_array($product->id, $wishlistProductIds ?? []) ? 'text-red-500 fill-current' : 'text-gray-600' }}" 
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
            </button>
        @endauth
    </div>
    
    <div class="p-4">
        <a href="{{ route('product.show', $product->slug) }}">
            <h3 class="font-semibold text-lg mb-1 hover:text-blue-600 transition">
                {{ Str::limit($product->name, 40) }}
            </h3>
        </a>
        <p class="text-gray-600 text-sm mb-2">
            {{ $product->subcategory->name }}
        </p>
        <div class="flex items-center justify-between">
            <div>
                <span class="text-xl font-bold text-gray-900">
                    €{{ number_format($product->price, 2) }}
                </span>
                @if($product->compare_price && $product->compare_price > $product->price)
                    <span class="text-sm text-gray-500 line-through ml-2">
                        €{{ number_format($product->compare_price, 2) }}
                    </span>
                @endif
            </div>
            
            <button class="add-to-cart bg-blue-600 text-white px-3 py-2 rounded-lg hover:bg-blue-700 transition flex items-center space-x-1"
                    data-product-id="{{ $product->id }}"
                    data-url="{{ route('cart.add') }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.5 6M17 13l1.5 6M9 21h6M12 17v4"></path>
                </svg>
                <span>Add</span>
            </button>
        </div>
    </div>
</div>
