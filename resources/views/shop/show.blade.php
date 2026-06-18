{{-- resources/views/shop/show.blade.php --}}
@extends('layouts.app')

@section('title', $product->name)
@section('meta_description', Str::limit(strip_tags($product->description), 155))
@section('og_type', 'product')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="grid md:grid-cols-2 gap-8 p-8">
            <!-- Product Images -->
            <div>
                <div class="relative">
                    <img src="{{ $product->image_url }}" 
                         alt="{{ $product->name }}"
                         decoding="async"
                         class="w-full rounded-lg shadow-md">
                    
                    @if($product->compare_price && $product->compare_price > $product->price)
                        <span class="absolute top-4 left-4 bg-red-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                            Sale
                        </span>
                    @endif
                </div>
                
                @if($product->gallery)
                    <div class="grid grid-cols-4 gap-4 mt-4">
                        @foreach((array) $product->gallery as $image)
                            <img src="{{ asset('storage/' . $image) }}" 
                                 alt="{{ $product->name }}"
                                 loading="lazy"
                                 decoding="async"
                                 class="rounded-lg cursor-pointer hover:opacity-75 transition"
                                 onclick="this.parentElement.parentElement.previousElementSibling.querySelector('img').src = this.src">
                        @endforeach
                    </div>
                @endif
            </div>
            
            <!-- Product Info -->
            <div>
                <h1 class="text-3xl font-bold mb-4">{{ $product->name }}</h1>
                <p class="text-gray-600 mb-4">{{ $product->subcategory->name }}</p>
                
                <div class="mb-4">
                    @if($product->compare_price && $product->compare_price > $product->price)
                        <span class="text-3xl font-bold text-red-600">
                            €{{ number_format($product->price, 2) }}
                        </span>
                        <span class="text-lg text-gray-500 line-through ml-2">
                            €{{ number_format($product->compare_price, 2) }}
                        </span>
                        <span class="ml-2 text-green-600 font-semibold">
                            Kurseni €{{ number_format($product->compare_price - $product->price, 2) }}
                        </span>
                    @else
                        <span class="text-3xl font-bold text-gray-900">
                            €{{ number_format($product->price, 2) }}
                        </span>
                    @endif
                </div>
                
                <!-- Stock Status -->
                <div class="mb-4">
                    @if($product->stock > 0)
                        <span class="text-green-600 font-semibold">
                            Ne stok ({{ $product->stock }})
                        </span>
                    @else
                        <span class="text-red-600 font-semibold">
                            Nuk ka stok
                        </span>
                    @endif
                </div>
                
                <!-- Description -->
                <div class="mb-6">
                    <h3 class="font-semibold text-lg mb-2">Pershkrimi</h3>
                    <p class="text-gray-700">{{ $product->description }}</p>
                </div>
                
                <!-- Attributes -->
                @if($product->attributes)
                    @php $attrs = json_decode($product->attributes, true); @endphp
                    @if(isset($attrs['sizes']))
                        <div class="mb-4">
                            <h4 class="font-medium mb-2">Size</h4>
                            <div class="flex space-x-2">
                                @foreach($attrs['sizes'] as $size)
                                    <button class="size-option border rounded px-3 py-1 hover:bg-gray-100">
                                        {{ $size }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    @if(isset($attrs['colors']))
                        <div class="mb-4">
                            <h4 class="font-medium mb-2">Color</h4>
                            <div class="flex space-x-2">
                                @foreach($attrs['colors'] as $color)
                                    <button class="color-option w-8 h-8 rounded-full border-2" 
                                            style="background-color: {{ strtolower($color) }}"
                                            title="{{ $color }}">
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif
                
                <!-- Quantity -->
                <div class="mb-6">
                    <label class="block font-medium mb-2">Sasia</label>
                    <input type="number" id="product-quantity" value="1" min="1" max="{{ $product->stock }}"
                           class="w-24 border rounded-lg px-3 py-2">
                </div>
                
                <!-- Action Buttons -->
                <div class="flex space-x-4">
    
    <!-- Add to Cart Button - VERSIONI I KORRIGJUAR -->
    <button id="add-to-cart-btn" 
            data-product-id="{{ $product->id }}"
            data-url="{{ route('cart.add') }}"
            class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition flex items-center justify-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.5 6M17 13l1.5 6M9 21h6M12 17v4"></path>
        </svg>
        <span>Shto në Shportë</span>
    </button>
    
    <!-- Wishlist Button -->
    @auth
        <button id="wishlist-btn"
                data-product-id="{{ $product->id }}"
                data-url="{{ route('wishlist.toggle') }}"
                class="px-6 py-3 rounded-lg border-2 {{ in_array($product->id, $wishlistProductIds ?? []) ? 'border-red-500 text-red-500' : 'border-gray-300 text-gray-700' }} hover:border-red-500 hover:text-red-500 transition">
            <svg class="w-6 h-6" fill="currentColor" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
            </svg>
        </button>
    @endauth
</div>
            </div>
        </div>
    </div>
    
    <!-- Related Products -->
    @if($relatedProducts->count())
        <div class="mt-12">
            <h2 class="text-2xl font-bold mb-6">You May Also Like</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($relatedProducts as $related)
                    @include('shop.partials.product-card', ['product' => $related])
                @endforeach
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    // Kontrollo nëse elementet ekzistojnë para se t'i përdorim
    document.addEventListener('DOMContentLoaded', function() {
        
        // Quantity controls - vetëm nëse elementi ekziston
        const quantityInput = document.getElementById('product-quantity');
        const decreaseBtn = document.getElementById('decrease-qty');
        const increaseBtn = document.getElementById('increase-qty');
        
        if (quantityInput && decreaseBtn && increaseBtn) {
            decreaseBtn.addEventListener('click', function() {
                let currentValue = parseInt(quantityInput.value);
                if (currentValue > 1) {
                    quantityInput.value = currentValue - 1;
                }
            });
            
            increaseBtn.addEventListener('click', function() {
                let currentValue = parseInt(quantityInput.value);
                let maxValue = parseInt(quantityInput.max);
                if (currentValue < maxValue) {
                    quantityInput.value = currentValue + 1;
                }
            });
        }
        
        // Add to Cart button
        const addToCartBtn = document.getElementById('add-to-cart-btn');
        if (addToCartBtn) {
            // Remove existing listeners
            const newBtn = addToCartBtn.cloneNode(true);
            addToCartBtn.parentNode.replaceChild(newBtn, addToCartBtn);
            
            newBtn.addEventListener('click', async function(e) {
                e.preventDefault();
                
                // Get quantity safely
                let quantity = 1;
                const qtyInput = document.getElementById('product-quantity');
                if (qtyInput) {
                    quantity = parseInt(qtyInput.value) || 1;
                }
                
                const productId = this.dataset.productId;
                const url = this.dataset.url;
                
                if (!productId || !url) {
                    showToast('Gabim: Mungojnë të dhënat', 'error');
                    return;
                }
                
                // Disable button
                const originalText = this.innerHTML;
                this.innerHTML = '<div class="loader w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div> Duke shtuar...';
                this.disabled = true;
                
                try {
                    const formData = new FormData();
                    formData.append('product_id', productId);
                    formData.append('quantity', quantity);
                    
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        showToast(data.message, 'success');
                        const cartCount = document.getElementById('cart-count');
                        if (cartCount && data.cart_count !== undefined) {
                            cartCount.textContent = data.cart_count;
                        }
                        this.innerHTML = '✓ Shtuar!';
                        setTimeout(() => {
                            this.innerHTML = originalText;
                            this.disabled = false;
                        }, 2000);
                    } else if (data.redirect) {
                        showToast('Ju lutemi hyni në llogari', 'info');
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1000);
                    } else {
                        showToast(data.message || 'Gabim', 'error');
                        this.innerHTML = originalText;
                        this.disabled = false;
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast('Ndodhi një gabim', 'error');
                    this.innerHTML = originalText;
                    this.disabled = false;
                }
            });
        }
        
        // Wishlist button
        const wishlistBtn = document.getElementById('wishlist-btn');
        if (wishlistBtn) {
            const newWishlistBtn = wishlistBtn.cloneNode(true);
            wishlistBtn.parentNode.replaceChild(newWishlistBtn, wishlistBtn);
            
            newWishlistBtn.addEventListener('click', async function(e) {
                e.preventDefault();
                
                const productId = this.dataset.productId;
                const url = this.dataset.url;
                
                if (!productId || !url) return;
                
                try {
                    const formData = new FormData();
                    formData.append('product_id', productId);
                    
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        showToast(data.message, 'success');
                        const wishlistCount = document.getElementById('wishlist-count');
                        if (wishlistCount && data.wishlist_count !== undefined) {
                            wishlistCount.textContent = data.wishlist_count;
                        }
                        // Toggle heart color
                        if (data.is_favorited) {
                            this.classList.add('border-red-500', 'text-red-500');
                            this.classList.remove('border-gray-300', 'text-gray-700');
                        } else {
                            this.classList.add('border-gray-300', 'text-gray-700');
                            this.classList.remove('border-red-500', 'text-red-500');
                        }
                    } else if (data.redirect) {
                        showToast('Ju lutemi hyni në llogari', 'info');
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1000);
                    } else {
                        showToast(data.message, 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast('Ndodhi një gabim', 'error');
                }
            });
        }
    });
    
    // Toast function
    function showToast(message, type = 'success') {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'fixed bottom-4 right-4 z-50 space-y-2';
            document.body.appendChild(container);
        }
        
        const toast = document.createElement('div');
        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            info: 'bg-blue-500',
            warning: 'bg-yellow-500'
        };
        
        toast.className = `${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full`;
        toast.innerHTML = `
            <div class="flex items-center space-x-2">
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">×</button>
            </div>
        `;
        
        container.appendChild(toast);
        setTimeout(() => toast.classList.remove('translate-x-full'), 100);
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
</script>
@endpush
@endsection
