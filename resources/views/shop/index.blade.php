@extends('layouts.app')

@php
    $hasSearchOrFilter = request()->hasAny(['search', 'category', 'subcategory', 'min_price', 'max_price', 'availability', 'sort']);
    $shopTitle = request('search')
        ? 'Kërkim për ' . request('search') . ' | DenataShop'
        : 'Produktet | DenataShop Kosovë';
    $shopDescription = request('search')
        ? 'Rezultate kërkimi në katalogun DenataShop për produkte sanitare, vegla pune, ujësjellës, kopsht dhe elektronikë.'
        : 'Shfleto katalogun DenataShop për sanitari, vegla pune, vegla kopshti, ujësjellës dhe elektronikë për projekte në Kosovë.';
    $structuredData = [
        App\Support\Seo::breadcrumbSchema([
            ['name' => 'Ballina', 'url' => route('home', [], false)],
            ['name' => 'Produktet', 'url' => route('shop', [], false)],
        ]),
    ];
@endphp

@section('title', $shopTitle)
@section('meta_description', $shopDescription)
@section('canonical', App\Support\Seo::canonical(route('shop', [], false)))
@section('robots', $hasSearchOrFilter ? 'noindex,follow' : 'index,follow')

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

<div x-data="{ filtersOpen: false }" class="container-custom py-6 md:py-8">
    <nav class="mb-5 flex items-center gap-2 text-sm text-[#6B6F74]" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-[#9A712E]">Ballina</a>
        <span>/</span>
        <span class="font-semibold text-[#15181B]">Produktet</span>
    </nav>

    <div class="mb-6 overflow-hidden rounded-lg border border-[#E1D9CB] bg-[#15181B] p-5 text-white shadow-[0_24px_70px_rgba(21,24,27,0.12)] md:p-6">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-black uppercase text-[#D7B16D]">Katalogu</p>
                <h1 class="mt-2 text-3xl font-black text-white md:text-4xl">Produkte per projektin tend</h1>
                <p class="mt-2 text-sm text-[#D8D1C6]">{{ $products->total() }} produkte aktive ne katalog.</p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <button type="button" @click="filtersOpen = true" class="inline-flex items-center justify-center gap-2 rounded-md border border-white/20 bg-white/10 px-5 py-3 text-sm font-black text-white transition hover:border-[#D7B16D] hover:text-[#D7B16D] lg:hidden">
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
                    <select id="sort" name="sort" onchange="this.form.submit()" class="min-w-[190px] rounded-md border-white/20 bg-white px-3 py-2.5 text-sm font-bold text-[#15181B] shadow-sm focus:border-[#D7B16D] focus:ring-[#D7B16D]">
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
                    <a href="{{ $chip['href'] }}" rel="nofollow" class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-xs font-bold text-white transition hover:border-[#D7B16D] hover:text-[#D7B16D]">
                        {{ $chip['label'] }}
                        <span aria-hidden="true">&times;</span>
                    </a>
                @endforeach
                <a href="{{ route('shop') }}" class="inline-flex items-center rounded-full bg-[#D7B16D] px-3 py-1.5 text-xs font-black text-[#15181B] transition hover:bg-white">Pastro te gjitha</a>
            </div>
        @endif
    </div>

    <div class="grid gap-7 lg:grid-cols-[290px_minmax(0,1fr)]">
        <aside class="hidden lg:block">
            <div class="sticky top-36 rounded-lg border border-[#E1D9CB] bg-white p-5 shadow-[0_12px_34px_rgba(21,24,27,0.05)]">
                @include('shop.partials.filters', ['categories' => $categories])
            </div>
        </aside>

        <section class="min-w-0" aria-label="Produktet">
            @include('shop.partials.product_grid', ['products' => $products])
        </section>
    </div>

    <div x-show="filtersOpen" x-cloak class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-[#15181B]/65 backdrop-blur-sm" @click="filtersOpen = false"></div>
        <aside class="absolute right-0 top-0 h-[100dvh] w-[90vw] max-w-[420px] overflow-y-auto bg-[#F7F5F1] p-4 shadow-2xl">
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
