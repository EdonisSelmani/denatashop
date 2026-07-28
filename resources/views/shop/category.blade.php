@extends('layouts.app')

@section('title', $category->name . ' - Denata Shop')
@section('meta_description', 'Produkte nga kategoria ' . $category->name . ' ne Denata Shop.')

@section('content')
<div class="container-custom py-8">
    <nav class="mb-5 flex items-center gap-2 text-sm text-[#6B6F74]" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-[#9A712E]">Ballina</a>
        <span>/</span>
        <a href="{{ route('shop') }}" class="hover:text-[#9A712E]">Produktet</a>
        <span>/</span>
        <span class="font-semibold text-[#15181B]">{{ $category->name }}</span>
    </nav>

    <header class="mb-8 overflow-hidden rounded-lg border border-[#E5E1DA] bg-white p-6">
        <div class="grid gap-6 lg:grid-cols-[1fr_auto] lg:items-end">
            <div>
                <p class="text-sm font-black uppercase text-[#9A712E]">Kategori</p>
                <h1 class="mt-2 text-3xl font-black text-[#15181B]">{{ $category->name }}</h1>
                @if($category->description)
                    <p class="mt-3 max-w-3xl leading-7 text-[#6B6F74]">{{ $category->description }}</p>
                @endif
                <p class="mt-3 text-sm font-semibold text-[#6B6F74]">{{ $products->total() }} produkte aktive.</p>
            </div>
            <a href="{{ route('shop') }}" class="btn-secondary inline-flex items-center justify-center gap-2">
                Te gjitha produktet
                <x-store.icon name="arrow-right" class="h-4 w-4" />
            </a>
        </div>
    </header>

    @if($subcategories->count())
        <div class="mb-8 flex gap-2 overflow-x-auto pb-2">
            @foreach($subcategories as $subcategory)
                <a href="{{ route('shop', ['category' => $category->slug, 'subcategory' => $subcategory->slug]) }}"
                   class="whitespace-nowrap rounded-full border border-[#E5E1DA] bg-white px-4 py-2 text-sm font-bold text-[#22272B] transition hover:border-[#B88A3B] hover:text-[#9A712E]">
                    {{ $subcategory->name }}
                </a>
            @endforeach
        </div>
    @endif

    @if($products->count())
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($products as $product)
                <x-store.product-card :product="$product" />
            @endforeach
        </div>

        <div class="mt-8">
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
