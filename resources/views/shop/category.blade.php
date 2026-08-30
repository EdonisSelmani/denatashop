@extends('layouts.app')

@section('title', $category->name . ' - Denata Shop')
@section('meta_description', 'Produkte nga kategoria ' . $category->name . ' ne Denata Shop.')

@section('content')
<div class="container-custom py-6 md:py-8">
    <nav class="mb-5 flex items-center gap-2 text-sm text-[#6B6F74]" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-[#9A712E]">Ballina</a>
        <span>/</span>
        <a href="{{ route('shop') }}" class="hover:text-[#9A712E]">Produktet</a>
        <span>/</span>
        <span class="font-semibold text-[#15181B]">{{ $category->name }}</span>
    </nav>

    <header class="mb-6 overflow-hidden rounded-lg border border-[#2A2D31] bg-[#15181B] p-6 text-white shadow-[0_24px_70px_rgba(21,24,27,0.12)]">
        <div class="grid gap-6 lg:grid-cols-[1fr_auto] lg:items-end">
            <div>
                <p class="text-sm font-black uppercase text-[#D7B16D]">Kategori</p>
                <h1 class="mt-2 text-3xl font-black text-white md:text-4xl">{{ $category->name }}</h1>
                @if($category->description)
                    <p class="mt-3 max-w-3xl leading-7 text-[#D8D1C6]">{{ $category->description }}</p>
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
            @foreach($subcategories as $subcategory)
                <a href="{{ route('shop', ['category' => $category->slug, 'subcategory' => $subcategory->slug]) }}"
                   class="whitespace-nowrap rounded-full border border-[#E1D9CB] bg-white px-4 py-2 text-sm font-black text-[#22272B] shadow-sm transition hover:border-[#B88A3B] hover:text-[#9A712E]">
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
