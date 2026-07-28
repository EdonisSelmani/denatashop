@extends('layouts.app')

@section('title', 'Produktet - Denata Shop')
@section('meta_description', 'Shfletoni produktet e Denata Shop dhe filtroni sipas kategorise, cmimit dhe disponueshmerise.')

@section('content')
@php
    $selectedCategory = $categories->firstWhere('slug', request('category'));
    $selectedSubcategory = $categories->flatMap->subcategories->firstWhere('slug', request('subcategory'));
    $chips = collect();
    if(request('search')) {
        $chips->push(['label' => 'Kerkim: ' . request('search'), 'href' => route('shop', request()->except('search', 'page'))]);
    }
    if($selectedCategory) {
        $chips->push(['label' => $selectedCategory->name, 'href' => route('shop', request()->except('category', 'subcategory', 'page'))]);
    }
    if($selectedSubcategory) {
        $chips->push(['label' => $selectedSubcategory->name, 'href' => route('shop', request()->except('subcategory', 'page'))]);
    }
    if(request('min_price') || request('max_price')) {
        $chips->push(['label' => 'Cmimi: ' . (request('min_price') ?: '0') . ' - ' . (request('max_price') ?: 'max'), 'href' => route('shop', request()->except('min_price', 'max_price', 'page'))]);
    }
    if(request('availability') === 'in_stock') {
        $chips->push(['label' => 'Ne stok', 'href' => route('shop', request()->except('availability', 'page'))]);
    }
@endphp

<div x-data="{ filtersOpen: false }" class="container-custom py-8">
    <nav class="mb-5 flex items-center gap-2 text-sm text-[#6B6F74]" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-[#9A712E]">Ballina</a>
        <span>/</span>
        <span class="font-semibold text-[#15181B]">Produktet</span>
    </nav>

    <div class="mb-8 rounded-lg border border-[#E5E1DA] bg-white p-5">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-black uppercase text-[#9A712E]">Katalogu</p>
                <h1 class="mt-2 text-3xl font-black text-[#15181B]">Produkte per projektin tend</h1>
                <p class="mt-2 text-sm text-[#6B6F74]">{{ $products->total() }} produkte aktive ne katalog.</p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <button type="button" @click="filtersOpen = true" class="btn-secondary inline-flex items-center justify-center gap-2 lg:hidden">
                    <x-store.icon name="filter" class="h-4 w-4" />
                    Filtrat
                </button>
                <form action="{{ route('shop') }}" method="GET" class="flex items-center gap-2">
                    @foreach(request()->except('sort', 'page') as $key => $value)
                        @if(! is_array($value) && filled($value))
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <label for="sort" class="sr-only">Rendit</label>
                    <select id="sort" name="sort" onchange="this.form.submit()" class="store-input min-w-[190px]">
                        <option value="latest" @selected(request('sort', 'latest') === 'latest')>Me te rejat</option>
                        <option value="price_low" @selected(request('sort') === 'price_low')>Cmimi: i ulet</option>
                        <option value="price_high" @selected(request('sort') === 'price_high')>Cmimi: i larte</option>
                        <option value="name_asc" @selected(request('sort') === 'name_asc')>Emri: A-Z</option>
                        <option value="name_desc" @selected(request('sort') === 'name_desc')>Emri: Z-A</option>
                    </select>
                </form>
            </div>
        </div>

        @if($chips->count())
            <div class="mt-5 flex flex-wrap gap-2">
                @foreach($chips as $chip)
                    <a href="{{ $chip['href'] }}" class="inline-flex items-center gap-2 rounded-full border border-[#D8D1C6] bg-[#F7F5F1] px-3 py-1.5 text-xs font-bold text-[#22272B] transition hover:border-[#B88A3B] hover:text-[#9A712E]">
                        {{ $chip['label'] }}
                        <span aria-hidden="true">&times;</span>
                    </a>
                @endforeach
                <a href="{{ route('shop') }}" class="inline-flex items-center rounded-full bg-[#15181B] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#B88A3B]">Pastro te gjitha</a>
            </div>
        @endif
    </div>

    <div class="grid gap-8 lg:grid-cols-[300px_1fr]">
        <aside class="hidden lg:block">
            <div class="sticky top-36 rounded-lg border border-[#E5E1DA] bg-white p-5">
                @include('shop.partials.filters', ['categories' => $categories])
            </div>
        </aside>

        <section aria-label="Produktet">
            @include('shop.partials.product_grid', ['products' => $products])
        </section>
    </div>

    <div x-show="filtersOpen" x-cloak class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-[#15181B]/55" @click="filtersOpen = false"></div>
        <aside class="absolute right-0 top-0 h-full w-full max-w-sm overflow-y-auto bg-[#F7F5F1] p-4 shadow-2xl">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-black text-[#15181B]">Filtrat</h2>
                <button type="button" @click="filtersOpen = false" class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-[#E5E1DA] bg-white" aria-label="Mbyll filtrat">
                    <x-store.icon name="x" class="h-5 w-5" />
                </button>
            </div>
            <div class="rounded-lg border border-[#E5E1DA] bg-white p-5">
                @include('shop.partials.filters', ['categories' => $categories])
            </div>
        </aside>
    </div>
</div>
@endsection
