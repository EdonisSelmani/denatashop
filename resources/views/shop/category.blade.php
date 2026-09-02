@extends('layouts.app')

@php
    $pageSubcategory = $subcategory ?? null;
    $canonicalPath = $pageSubcategory
        ? route('subcategory.show', [$category->slug, $pageSubcategory->slug], false)
        : route('category.show', $category->slug, false);
    $isDuplicateFilteredPage = request()->routeIs('category.show') && $pageSubcategory;
    $hasFilterQuery = request()->hasAny(['min_price', 'max_price', 'availability', 'sort']);
    $structuredData = [
        App\Support\Seo::breadcrumbSchema(array_values(array_filter([
            ['name' => 'Ballina', 'url' => route('home', [], false)],
            ['name' => 'Produktet', 'url' => route('shop', [], false)],
            ['name' => $category->name, 'url' => route('category.show', $category->slug, false)],
            $pageSubcategory ? ['name' => $pageSubcategory->name, 'url' => $canonicalPath] : null,
        ]))),
    ];
@endphp

@section('title', App\Support\Seo::categoryTitle($category, $pageSubcategory))
@section('meta_description', App\Support\Seo::categoryDescription($category, $pageSubcategory))
@section('canonical', App\Support\Seo::canonical($canonicalPath))
@section('seo_image', $category->image ? App\Support\Seo::storageImage($category->image) : App\Support\Seo::image())
@section('robots', ($isDuplicateFilteredPage || $hasFilterQuery) ? 'noindex,follow' : 'index,follow')

@section('content')
<div class="container-custom py-6 md:py-8">
    <nav class="mb-5 flex items-center gap-2 text-sm text-[#6B6F74]" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-[#9A712E]">Ballina</a>
        <span>/</span>
        <a href="{{ route('shop') }}" class="hover:text-[#9A712E]">Produktet</a>
        <span>/</span>
        @if($pageSubcategory)
            <a href="{{ route('category.show', $category->slug) }}" class="hover:text-[#9A712E]">{{ $category->name }}</a>
            <span>/</span>
            <span class="font-semibold text-[#15181B]">{{ $pageSubcategory->name }}</span>
        @else
            <span class="font-semibold text-[#15181B]">{{ $category->name }}</span>
        @endif
    </nav>

    <header class="mb-6 overflow-hidden rounded-lg border border-[#2A2D31] bg-[#15181B] p-6 text-white shadow-[0_24px_70px_rgba(21,24,27,0.12)]">
        <div class="grid gap-6 lg:grid-cols-[1fr_auto] lg:items-end">
            <div>
                <p class="text-sm font-black uppercase text-[#D7B16D]">{{ $pageSubcategory ? 'Nënkategori' : 'Kategori' }}</p>
                <h1 class="mt-2 text-3xl font-black text-white md:text-4xl">{{ $pageSubcategory?->name ?? $category->name }}</h1>
                @if($pageSubcategory?->description || $category->description)
                    <p class="mt-3 max-w-3xl leading-7 text-[#D8D1C6]">{{ $pageSubcategory?->description ?? $category->description }}</p>
                @else
                    <p class="mt-3 max-w-3xl leading-7 text-[#D8D1C6]">
                        Shfletoni produkte të zgjedhura për {{ $pageSubcategory?->name ?? $category->name }} në DenataShop.
                    </p>
                @endif
                <p class="mt-3 text-sm font-semibold text-[#D8D1C6]">{{ $products->total() }} produkte aktive.</p>
            </div>
            <a href="{{ route('shop') }}" class="inline-flex items-center justify-center gap-2 rounded-md border border-white/20 bg-white/10 px-5 py-3 text-sm font-black text-white transition hover:border-[#D7B16D] hover:text-[#D7B16D]">
                Te gjitha produktet
                <x-store.icon name="arrow-right" class="h-4 w-4" />
            </a>
        </div>
    </header>

    @if($subcategories->count())
        <div class="mb-8 flex gap-2 overflow-x-auto pb-2">
            <a href="{{ route('category.show', $category->slug) }}"
               class="whitespace-nowrap rounded-full border {{ $pageSubcategory ? 'border-[#E1D9CB] bg-white text-[#22272B]' : 'border-[#B88A3B] bg-[#B88A3B]/10 text-[#9A712E]' }} px-4 py-2 text-sm font-black shadow-sm transition hover:border-[#B88A3B] hover:text-[#9A712E]">
                Të gjitha
            </a>
            @foreach($subcategories as $subcategory)
                <a href="{{ route('subcategory.show', [$category->slug, $subcategory->slug]) }}"
                   class="whitespace-nowrap rounded-full border {{ $pageSubcategory?->id === $subcategory->id ? 'border-[#B88A3B] bg-[#B88A3B]/10 text-[#9A712E]' : 'border-[#E1D9CB] bg-white text-[#22272B]' }} px-4 py-2 text-sm font-black shadow-sm transition hover:border-[#B88A3B] hover:text-[#9A712E]">
                    {{ $subcategory->name }}
                </a>
            @endforeach
        </div>
    @endif

    @if($products->count())
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($products as $product)
                <x-store.product-card :product="$product" />
            @endforeach
        </div>

        <div class="mt-8 max-w-full overflow-x-auto pb-1">
            {{ $products->withQueryString()->links() }}
        </div>
    @else
        <x-store.empty-state
            icon="package"
            title="Nuk ka produkte ne kete kategori"
            text="Kjo kategori mund te plotesohet me vone. Shiko katalogun e plote per produkte te tjera."
            action="Shiko katalogun"
            :href="route('shop')" />
    @endif
</div>
@endsection
