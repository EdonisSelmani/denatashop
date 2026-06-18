{{-- resources/views/home/index.blade.php --}}
@extends('layouts.app')

@section('title', 'E-Shop - Albanian Marketplace')
@section('meta_description', 'Dyqan online me produkte origjinale, oferta speciale dhe dergese te shpejte ne Kosove.')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    
    <!-- Hero Section me Sidebar  -->
    <div class="flex flex-col lg:flex-row gap-6 mb-10">
        <!-- LEFT SIDEBAR - KATEGORITË -->
        <div class="lg:w-1/4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gray-50 px-5 py-3 border-b border-gray-100">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                        Të gjitha kategoritë
                    </h3>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($categories as $category)
                        <a href="{{ route('shop', ['category' => $category->slug]) }}"
                           class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition group">
                            <div class="flex items-center gap-3">
                                <span class="text-gray-700 group-hover:text-blue-600 transition font-medium">
                                    {{ $category->name }}
                                </span>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
        
        <!-- RIGHT SIDE - HERO BANNER -->
        <div class="lg:w-3/4">
            <div class="relative bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl overflow-hidden shadow-lg h-[320px] md:h-[380px]">
                <div class="absolute inset-0 bg-black/20"></div>
                <div class="relative h-full flex items-center px-8 md:px-12">
                    <div class="text-white max-w-lg">
                        <h2 class="text-3xl md:text-4xl font-bold mb-3">Oferta Speciale</h2>
                        <p class="text-lg mb-6 opacity-90">Deri në 50% zbritje për produktet e zgjedhura</p>
                        <div class="flex gap-4">
                            <a href="{{ route('shop') }}" class="bg-white text-gray-900 px-6 py-2 rounded-full font-semibold hover:bg-gray-100 transition transform hover:scale-105">
                                Shporto Tani →
                            </a>
                            <a href="{{ route('shop') }}" class="border-2 border-white text-white px-6 py-2 rounded-full font-semibold hover:bg-white/10 transition">
                                Shiko të gjitha
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Banner dekoret -->
                <div class="absolute bottom-0 right-0 opacity-10">
                    <svg class="w-64 h-64" fill="white" viewBox="0 0 24 24">
                        <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.5 6M17 13l1.5 6M9 21h6M12 17v4"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl p-4 text-center shadow-sm hover:shadow-md transition">
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </div>
            <p class="font-semibold text-gray-800">Blerje të sigurta</p>
            <p class="text-xs text-gray-500">Pagesë e garantuar</p>
        </div>
        
        <div class="bg-white rounded-xl p-4 text-center shadow-sm hover:shadow-md transition">
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
            </div>
            <p class="font-semibold text-gray-800">Dërgesa të shpejta</p>
            <p class="text-xs text-gray-500">Kudo në Kosovë</p>
        </div>
        
        <div class="bg-white rounded-xl p-4 text-center shadow-sm hover:shadow-md transition">
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
            <p class="font-semibold text-gray-800">Mbi 100,000 produkte</p>
            <p class="text-xs text-gray-500">Originale dhe me garancion</p>
        </div>
        
        <div class="bg-white rounded-xl p-4 text-center shadow-sm hover:shadow-md transition">
            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <p class="font-semibold text-gray-800">Çmimi më i mirë</p>
            <p class="text-xs text-gray-500">I garantuar në çdo produkt</p>
        </div>
    </div>
    
    <!-- PRODUKTET E VEÇUARA -->
    <div class="mb-12">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Produktet e Veçuara</h2>
                <p class="text-sm text-gray-500 mt-1">Zgjidhjet më të mira për ju</p>
            </div>
            <a href="{{ route('shop', ['featured' => 1]) }}" class="text-blue-600 hover:text-blue-700 text-sm font-semibold flex items-center gap-1">
                Shiko të gjitha
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6">
            @forelse($featuredProducts as $product)
                <div class="bg-white rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden group">
                    <a href="{{ route('product.show', $product->slug) }}" class="block">
                        <div class="relative overflow-hidden bg-gray-100">
                            <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}"
                                 loading="lazy"
                                 decoding="async"
                                 class="w-full h-48 object-cover">
                            @if($product->compare_price && $product->compare_price > $product->price)
                                <span class="absolute top-2 left-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full">
                                    -{{ round((($product->compare_price - $product->price) / $product->compare_price) * 100) }}%
                                </span>
                            @endif
                        </div>
                        <div class="p-3">
                            <h3 class="font-semibold text-gray-800 text-sm line-clamp-2 mb-1">{{ $product->name }}</h3>
                            <div class="flex items-baseline gap-2 mt-2">
                                <span class="text-lg font-bold text-blue-600">€{{ number_format($product->price, 2) }}</span>
                                @if($product->compare_price && $product->compare_price > $product->price)
                                    <span class="text-xs text-gray-500 line-through">€{{ number_format($product->compare_price, 2) }}</span>
                                @endif
                            </div>
                            <button class="add-to-cart w-full mt-3 bg-gray-900 text-white py-2 rounded-lg text-sm hover:bg-blue-600 transition"
                                    data-product-id="{{ $product->id }}"
                                    data-url="{{ route('cart.add') }}">
                                Shto në Shportë
                            </button>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-span-full text-center py-10 text-gray-500">
                    Nuk ka produkte të veçuara për momentin.
                </div>
            @endforelse
        </div>
    </div>
    
    <!-- BANNER PROMOCIONAL -->
    <div class="bg-gradient-to-r from-orange-500 to-red-500 rounded-xl p-6 mb-12 text-white">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <div class="text-center md:text-left mb-4 md:mb-0">
                <h3 class="text-2xl font-bold mb-1">🔥 Flash Sale</h3>
                <p class="opacity-90">Zbritje deri në 40% për 24 orët e ardhshme</p>
            </div>
            <div class="flex gap-3">
                <div class="bg-white/20 rounded-lg px-4 py-2 text-center">
                    <div class="text-2xl font-bold" id="hours">00</div>
                    <div class="text-xs">Orë</div>
                </div>
                <div class="bg-white/20 rounded-lg px-4 py-2 text-center">
                    <div class="text-2xl font-bold" id="minutes">00</div>
                    <div class="text-xs">Minuta</div>
                </div>
                <div class="bg-white/20 rounded-lg px-4 py-2 text-center">
                    <div class="text-2xl font-bold" id="seconds">00</div>
                    <div class="text-xs">Sekonda</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- PRODUKTET E REJA -->
    <div class="mb-12">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Produktet e Reja</h2>
                <p class="text-sm text-gray-500 mt-1">Mbërritjet më të fundit</p>
            </div>
            <a href="{{ route('shop', ['sort' => 'latest']) }}" class="text-blue-600 hover:text-blue-700 text-sm font-semibold flex items-center gap-1">
                Shiko të gjitha
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6">
            @forelse($newProducts as $product)
                <div class="bg-white rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden group">
                    <a href="{{ route('product.show', $product->slug) }}" class="block">
                        <div class="relative overflow-hidden bg-gray-100">
                            <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}"
                                 loading="lazy"
                                 decoding="async"
                                 class="w-full h-48 object-cover">
                            <span class="absolute top-2 left-2 bg-green-500 text-white text-xs px-2 py-1 rounded-full">New</span>
                        </div>
                        <div class="p-3">
                            <h3 class="font-semibold text-gray-800 text-sm line-clamp-2 mb-1">{{ $product->name }}</h3>
                            <div class="flex items-baseline gap-2 mt-2">
                                <span class="text-lg font-bold text-blue-600">€{{ number_format($product->price, 2) }}</span>
                            </div>
                            <button class="add-to-cart w-full mt-3 bg-gray-900 text-white py-2 rounded-lg text-sm hover:bg-blue-600 transition"
                                    data-product-id="{{ $product->id }}"
                                    data-url="{{ route('cart.add') }}">
                                Shto në Shportë
                            </button>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-span-full text-center py-10 text-gray-500">
                    Nuk ka produkte të reja për momentin.
                </div>
            @endforelse
        </div>
    </div>
    
    <!-- PRODUKTET ME ZBRITJE -->
    <div class="mb-12">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Oferta Speciale</h2>
                <p class="text-sm text-gray-500 mt-1">Produkte me çmime të zbritura</p>
            </div>
            <a href="{{ route('shop') }}" class="text-blue-600 hover:text-blue-700 text-sm font-semibold flex items-center gap-1">
                Shiko të gjitha
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6">
            @forelse($discountProducts as $product)
                <div class="bg-white rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden group">
                    <a href="{{ route('product.show', $product->slug) }}" class="block">
                        <div class="relative overflow-hidden bg-gray-100">
                            <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}"
                                 loading="lazy"
                                 decoding="async"
                                 class="w-full h-48 object-cover">
                            <span class="absolute top-2 left-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full">
                                -{{ round((($product->compare_price - $product->price) / $product->compare_price) * 100) }}%
                            </span>
                        </div>
                        <div class="p-3">
                            <h3 class="font-semibold text-gray-800 text-sm line-clamp-2 mb-1">{{ $product->name }}</h3>
                            <div class="flex items-baseline gap-2 mt-2">
                                <span class="text-lg font-bold text-red-600">€{{ number_format($product->price, 2) }}</span>
                                <span class="text-xs text-gray-500 line-through">€{{ number_format($product->compare_price, 2) }}</span>
                            </div>
                            <button class="add-to-cart w-full mt-3 bg-gray-900 text-white py-2 rounded-lg text-sm hover:bg-blue-600 transition"
                                    data-product-id="{{ $product->id }}"
                                    data-url="{{ route('cart.add') }}">
                                Shto në Shportë
                            </button>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-span-full text-center py-10 text-gray-500">
                    Nuk ka produkte me zbritje për momentin.
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
    // Countdown timer për flash sale
    function startCountdown() {
        const targetDate = new Date();
        targetDate.setHours(targetDate.getHours() + 24);
        
        function updateCountdown() {
            const now = new Date();
            const diff = targetDate - now;
            
            if (diff <= 0) {
                document.getElementById('hours').textContent = '00';
                document.getElementById('minutes').textContent = '00';
                document.getElementById('seconds').textContent = '00';
                return;
            }
            
            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (3600000)) / (1000 * 60));
            const seconds = Math.floor((diff % (60000)) / 1000);
            
            document.getElementById('hours').textContent = String(hours).padStart(2, '0');
            document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
            document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
        }
        
        updateCountdown();
        setInterval(updateCountdown, 1000);
    }
    
    startCountdown();
</script>
@endsection
