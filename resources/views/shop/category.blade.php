@extends('layouts.app')

@section('title', $category->name)
@section('meta_description', 'Produkte nga kategoria ' . $category->name . ' me oferta dhe stok te perditesuar.')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold">{{ $category->name }}</h1>
            @if($category->description)
                <p class="text-gray-600 mt-2">{{ $category->description }}</p>
            @endif
        </div>
        <a href="{{ route('shop') }}" class="text-blue-600 hover:text-blue-800">Te gjitha produktet</a>
    </div>

    @if($subcategories->count())
        <div class="flex flex-wrap gap-2 mb-8">
            @foreach($subcategories as $subcategory)
                <a href="{{ route('shop', ['category' => $category->slug, 'subcategory' => $subcategory->slug]) }}"
                   class="px-4 py-2 bg-white border rounded-full text-sm text-gray-700 hover:border-blue-500 hover:text-blue-600">
                    {{ $subcategory->name }}
                </a>
            @endforeach
        </div>
    @endif

    @if($products->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($products as $product)
                @include('shop.partials.product-card', ['product' => $product])
            @endforeach
        </div>

        <div class="mt-8">
            {{ $products->withQueryString()->links() }}
        </div>
    @else
        <div class="bg-white border border-gray-200 text-gray-700 px-4 py-10 rounded-lg text-center">
            <p class="font-semibold">Nuk u gjeten produkte ne kete kategori.</p>
            <a href="{{ route('shop') }}" class="inline-block mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Shiko te gjitha produktet
            </a>
        </div>
    @endif
</div>
@endsection
