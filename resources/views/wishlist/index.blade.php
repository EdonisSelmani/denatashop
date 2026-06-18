{{-- resources/views/wishlist/index.blade.php --}}
@extends('layouts.app')

@section('title', 'My Wishlist')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold mb-8">My Wishlist</h1>
    
    @if($wishlistItems->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($wishlistItems as $product)
                <div class="group bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 wishlist-item" data-product-id="{{ $product->id }}">
                    <div class="relative overflow-hidden">
                        <a href="{{ route('product.show', $product->slug) }}">
                            <img src="{{ $product->thumbnail_url }}" 
                                 alt="{{ $product->name }}"
                                 loading="lazy"
                                 decoding="async"
                                 class="w-full h-64 object-cover">
                        </a>
                        
                        <button class="remove-from-wishlist absolute top-2 right-2 bg-white rounded-full p-2 shadow-md hover:bg-red-50 transition"
                                data-product-id="{{ $product->id }}">
                            <svg class="w-5 h-5 text-red-500 fill-current" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </button>
                        
                        @if($product->compare_price && $product->compare_price > $product->price)
                            <span class="absolute top-2 left-2 bg-red-500 text-white px-2 py-1 text-xs rounded">
                                Sale
                            </span>
                        @endif
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
                            
                            <button class="add-to-cart bg-blue-600 text-white px-3 py-2 rounded-lg hover:bg-blue-700 transition"
                                    data-product-id="{{ $product->id }}"
                                    data-url="{{ route('cart.add') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.5 6M17 13l1.5 6M9 21h6M12 17v4"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <svg class="w-24 h-24 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
            </svg>
            <h3 class="text-xl font-semibold mb-2">Your wishlist is empty</h3>
            <p class="text-gray-600 mb-4">Save your favorite items here!</p>
            <a href="{{ route('shop') }}" class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                Browse Products
            </a>
        </div>
    @endif
</div>

@push('scripts')
<script>
    // Remove from wishlist
    document.querySelectorAll('.remove-from-wishlist').forEach(button => {
        button.addEventListener('click', async function(e) {
            const productId = this.dataset.productId;
            const formData = new FormData();
            formData.append('product_id', productId);
            
            try {
                const response = await fetch('{{ route("wishlist.toggle") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Remove item from DOM
                    this.closest('.wishlist-item').remove();
                    
                    // Update wishlist count
                    if (data.wishlist_count !== undefined) {
                        document.getElementById('wishlist-count').textContent = data.wishlist_count;
                    }
                    
                    showToast('Removed from wishlist', 'success');
                    
                    // Check if wishlist is empty
                    if (document.querySelectorAll('.wishlist-item').length === 0) {
                        location.reload();
                    }
                }
            } catch (error) {
                showToast('Error updating wishlist', 'error');
            }
        });
    });
</script>
@endpush
@endsection
