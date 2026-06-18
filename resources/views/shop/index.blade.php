{{-- resources/views/shop/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Shop')
@section('meta_description', 'Shfletoni produktet, filtroni sipas kategorise dhe gjeni ofertat me te reja.')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar Filters -->
        <div class="lg:w-1/4">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Filters</h3>
                
                <!-- Categories -->
                <div class="mb-6">
                    <h4 class="font-medium mb-2">Categories</h4>
                    @foreach($categories as $category)
                        <div class="mb-2">
                            <a href="{{ route('shop', ['category' => $category->slug]) }}" 
                               class="text-gray-600 hover:text-blue-600">
                                {{ $category->name }}
                            </a>
                            @if($category->subcategories->count())
                                <div class="ml-4 mt-1 space-y-1">
                                    @foreach($category->subcategories as $sub)
                                        <a href="{{ route('shop', ['subcategory' => $sub->slug]) }}" 
                                           class="text-sm text-gray-500 hover:text-blue-600 block">
                                            {{ $sub->name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                
                <!-- Price Range -->
                <div class="mb-6">
                    <h4 class="font-medium mb-2">Price Range</h4>
                    <div class="space-y-2">
                        <a href="{{ route('shop', array_merge(request()->except('min_price', 'max_price'), ['min_price' => 0, 'max_price' => 50])) }}" 
                           class="block text-gray-600 hover:text-blue-600">
                            Nen €50
                        </a>
                        <a href="{{ route('shop', array_merge(request()->except('min_price', 'max_price'), ['min_price' => 50, 'max_price' => 100])) }}" 
                           class="block text-gray-600 hover:text-blue-600">
                            €50 - €100
                        </a>
                        <a href="{{ route('shop', array_merge(request()->except('min_price', 'max_price'), ['min_price' => 100, 'max_price' => 200])) }}" 
                           class="block text-gray-600 hover:text-blue-600">
                            €100 - €200
                        </a>
                        <a href="{{ route('shop', array_merge(request()->except('min_price', 'max_price'), ['min_price' => 200])) }}" 
                           class="block text-gray-600 hover:text-blue-600">
                            €200+
                        </a>
                    </div>
                </div>
                
                <!-- Sort By -->
                <div>
                    <h4 class="font-medium mb-2">Sort By</h4>
                    <select onchange="window.location.href=this.value" class="w-full border rounded-lg px-3 py-2">
                        <option value="{{ route('shop', array_merge(request()->except('sort'), ['sort' => 'latest'])) }}">
                            Latest
                        </option>
                        <option value="{{ route('shop', array_merge(request()->except('sort'), ['sort' => 'price_low'])) }}">
                            Price: Low to High
                        </option>
                        <option value="{{ route('shop', array_merge(request()->except('sort'), ['sort' => 'price_high'])) }}">
                            Price: High to Low
                        </option>
                        <option value="{{ route('shop', array_merge(request()->except('sort'), ['sort' => 'name_asc'])) }}">
                            Name: A to Z
                        </option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Products Grid -->
        <div class="lg:w-3/4">
            @if(request('search'))
                <div class="mb-6">
                    <p class="text-gray-600">Search results for: <strong>{{ request('search') }}</strong></p>
                </div>
            @endif
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($products as $product)
                    @include('shop.partials.product-card', ['product' => $product])
                @empty
                    <div class="col-span-full text-center py-12">
                        <p class="text-gray-500">No products found.</p>
                    </div>
                @endforelse
            </div>
            
            <div class="mt-8">
                {{ $products->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
