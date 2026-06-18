{{-- resources/views/cart/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Shporta ime')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold mb-8">Shporta ime</h1>
    
    @if($cartItems->count())
        <div class="grid lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                @foreach($cartItems as $item)
                    <div class="cart-item bg-white rounded-lg shadow-md p-4 mb-4" data-item-id="{{ $item->id }}" data-price="{{ $item->product->price }}">
                        <div class="flex flex-col sm:flex-row gap-4">
                            <img src="{{ $item->product->thumbnail_url }}" alt="{{ $item->product->name }}" 
                                 loading="lazy"
                                 decoding="async"
                                 class="w-32 h-32 object-cover rounded">
                            
                            <div class="flex-1">
                                <h3 class="font-semibold text-lg">{{ $item->product->name }}</h3>
                                <p class="text-gray-600 text-sm">{{ $item->product->subcategory->name ?? 'Produkt' }}</p>
                                <p class="text-gray-800 font-bold mt-2">€{{ number_format($item->product->price, 2) }}</p>
                                
                                <div class="flex items-center space-x-4 mt-4">
                                    <!-- Quantity Controls -->
                                    <div class="flex items-center border rounded-lg">
                                        <button class="decrease-qty px-3 py-1 hover:bg-gray-100 transition" data-id="{{ $item->id }}">
                                            -
                                        </button>
                                        <span class="quantity-display w-12 text-center font-medium">
                                            {{ $item->quantity }}
                                        </span>
                                        <button class="increase-qty px-3 py-1 hover:bg-gray-100 transition" data-id="{{ $item->id }}">
                                            +
                                        </button>
                                    </div>
                                    
                                    <!-- Remove Button -->
                                    <button class="remove-item text-red-500 hover:text-red-700 transition" data-id="{{ $item->id }}">
                                        <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Fshi
                                    </button>
                                </div>
                            </div>
                            
                            <div class="text-right">
                                <p class="font-bold text-lg item-subtotal">
                                    €{{ number_format($item->product->price * $item->quantity, 2) }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-24">
                    <h3 class="text-xl font-bold mb-4">Përmbledhje</h3>
                    
                    <div class="space-y-2 border-b pb-4">
                        <div class="flex justify-between">
                            <span>Totali</span>
                            <span id="cart-subtotal">€{{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Transporti</span>
                            <span>3-6 ditë</span>
                        </div>
                        @if(($memberDiscount ?? 0) > 0)
                            <div class="flex justify-between text-green-700">
                                <span>Zbritje llogarie 7%</span>
                                <span id="member-discount">-€{{ number_format($memberDiscount, 2) }}</span>
                            </div>
                        @else
                            <div class="text-sm text-blue-700 bg-blue-50 rounded p-2">
                                Hyni ose hapni llogari per 7% zbritje ne produktet e porosise.
                            </div>
                        @endif
                    </div>
                    
                    <div class="flex justify-between mt-4 pb-4 border-b">
                        <span class="font-bold text-lg">Totali</span>
                        <span class="font-bold text-lg" id="cart-total">€{{ number_format($total, 2) }}</span>
                    </div>
                    
                    <a href="{{ route('checkout.index') }}" class="block text-center w-full bg-blue-600 text-white py-3 rounded-lg mt-6 hover:bg-blue-700 transition">
                       Continue to Checkout
                    </a>
                    
                    <a href="{{ route('shop') }}" class="block text-center text-blue-600 mt-4 hover:underline">
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <svg class="w-24 h-24 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.5 6M17 13l1.5 6M9 21h6M12 17v4"></path>
            </svg>
            <h3 class="text-xl font-semibold mb-2">Shporta juaj është bosh</h3>
            <p class="text-gray-600 mb-4">Nuk keni shtuar asnjë produkt në shportë.</p>
            <a href="{{ route('shop') }}" class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                Filloni blerjet
            </a>
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // Increase quantity
        document.querySelectorAll('.increase-qty').forEach(button => {
            button.addEventListener('click', async function() {
                const itemId = this.dataset.id;
                const cartItem = this.closest('.cart-item');
                const quantitySpan = cartItem.querySelector('.quantity-display');
                let currentQty = parseInt(quantitySpan.textContent);
                const newQty = currentQty + 1;
                
                await updateQuantity(itemId, newQty, cartItem);
            });
        });
        
        // Decrease quantity
        document.querySelectorAll('.decrease-qty').forEach(button => {
            button.addEventListener('click', async function() {
                const itemId = this.dataset.id;
                const cartItem = this.closest('.cart-item');
                const quantitySpan = cartItem.querySelector('.quantity-display');
                let currentQty = parseInt(quantitySpan.textContent);
                
                if (currentQty > 1) {
                    const newQty = currentQty - 1;
                    await updateQuantity(itemId, newQty, cartItem);
                }
            });
        });
        
        // Remove item
        document.querySelectorAll('.remove-item').forEach(button => {
            button.addEventListener('click', async function() {
                const itemId = this.dataset.id;
                const cartItem = this.closest('.cart-item');
                
                if (confirm('A jeni i sigurt që doni ta fshini këtë produkt?')) {
                    await removeItem(itemId, cartItem);
                }
            });
        });
    });
    
    async function updateQuantity(itemId, newQuantity, cartItem) {
        try {
            const response = await fetch(`/cart/${itemId}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ quantity: newQuantity })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Update quantity display
                cartItem.querySelector('.quantity-display').textContent = newQuantity;
                
                // Update item subtotal
                const price = parseFloat(cartItem.dataset.price);
                const newSubtotal = price * newQuantity;
                cartItem.querySelector('.item-subtotal').textContent = '€' + newSubtotal.toFixed(2);
                
                // Update cart totals
                if (data.cart_total !== undefined) {
                    document.getElementById('cart-subtotal').textContent = '€' + (data.cart_subtotal ?? data.cart_total).toFixed(2);
                    document.getElementById('cart-total').textContent = '€' + data.cart_total.toFixed(2);
                    const memberDiscount = document.getElementById('member-discount');
                    if (memberDiscount && data.member_discount !== undefined) {
                        memberDiscount.textContent = '-€' + data.member_discount.toFixed(2);
                    }
                }
                
                // Update navbar cart count
                if (data.cart_count !== undefined) {
                    const cartCountElement = document.getElementById('cart-count');
                    if (cartCountElement) {
                        cartCountElement.textContent = data.cart_count;
                    }
                }
                
                showToast('Sasia u përditësua', 'success');
            } else {
                showToast(data.message || 'Gabim gjatë përditësimit', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Ndodhi një gabim', 'error');
        }
    }
    
    async function removeItem(itemId, cartItem) {
        try {
            const response = await fetch(`/cart/${itemId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Remove item from DOM
                cartItem.remove();
                
                // Update navbar cart count
                if (data.cart_count !== undefined) {
                    const cartCountElement = document.getElementById('cart-count');
                    if (cartCountElement) {
                        cartCountElement.textContent = data.cart_count;
                    }
                }
                
                // Check if cart is empty
                const remainingItems = document.querySelectorAll('.cart-item').length;
                if (remainingItems === 0) {
                    location.reload();
                } else {
                    // Recalculate totals if needed
                    if (data.cart_total !== undefined) {
                        document.getElementById('cart-subtotal').textContent = '€' + (data.cart_subtotal ?? data.cart_total).toFixed(2);
                        document.getElementById('cart-total').textContent = '€' + data.cart_total.toFixed(2);
                        const memberDiscount = document.getElementById('member-discount');
                        if (memberDiscount && data.member_discount !== undefined) {
                            memberDiscount.textContent = '-€' + data.member_discount.toFixed(2);
                        }
                    }
                }
                
                showToast('Produkti u fshi nga shporta', 'success');
            } else {
                showToast(data.message || 'Gabim gjatë fshirjes', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Ndodhi një gabim', 'error');
        }
    }
    
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
