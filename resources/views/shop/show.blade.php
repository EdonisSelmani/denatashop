@extends('layouts.app')

@section('title', $product->name . ' - Denata Shop')
@section('meta_description', Str::limit(strip_tags($product->description), 155))
@section('og_type', 'product')

@section('content')
@php
    $hasDiscount = $product->compare_price && (float) $product->compare_price > (float) $product->price;
    $gallery = collect($product->gallery ?? [])->filter()->values();
    $isFavorited = in_array($product->id, $wishlistProductIds ?? [], true);
@endphp

<div class="container-custom py-8">
    <nav class="mb-5 flex flex-wrap items-center gap-2 text-sm text-[#6B6F74]" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-[#9A712E]">Ballina</a>
        <span>/</span>
        <a href="{{ route('shop') }}" class="hover:text-[#9A712E]">Produktet</a>
        @if($product->subcategory?->category)
            <span>/</span>
            <a href="{{ route('category.show', $product->subcategory->category->slug) }}" class="hover:text-[#9A712E]">{{ $product->subcategory->category->name }}</a>
        @endif
        <span>/</span>
        <span class="font-semibold text-[#15181B]">{{ Str::limit($product->name, 42) }}</span>
    </nav>

    <div class="grid gap-8 lg:grid-cols-[1.08fr_0.92fr]">
        <section x-data="{ image: @js($product->image_url) }" class="space-y-4">
            <div class="relative rounded-lg border border-[#E5E1DA] bg-white p-4">
                @if($hasDiscount)
                    <span class="absolute left-5 top-5 rounded-full bg-[#C9473D] px-3 py-1 text-xs font-black text-white">
                        Oferta
                    </span>
                @endif
                <div class="flex aspect-square items-center justify-center rounded-md bg-[#F7F5F1] p-6">
                    <img :src="image"
                         src="{{ $product->image_url }}"
                         alt="{{ $product->name }}"
                         width="720"
                         height="720"
                         decoding="async"
                         class="h-full w-full object-contain">
                </div>
            </div>

            @if($gallery->count())
                <div class="grid grid-cols-4 gap-3 sm:grid-cols-6">
                    <button type="button" @click="image = @js($product->image_url)" class="rounded-md border border-[#B88A3B] bg-white p-2 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#B88A3B]">
                        <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}" class="aspect-square w-full object-contain">
                    </button>
                    @foreach($gallery as $image)
                        <button type="button" @click="image = @js(asset('storage/' . $image))" class="rounded-md border border-[#E5E1DA] bg-white p-2 transition hover:border-[#B88A3B] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#B88A3B]">
                            <img src="{{ asset('storage/' . $image) }}" alt="{{ $product->name }}" loading="lazy" decoding="async" class="aspect-square w-full object-contain">
                        </button>
                    @endforeach
                </div>
            @endif
        </section>

        <aside class="lg:sticky lg:top-36 lg:self-start">
            <div class="rounded-lg border border-[#E5E1DA] bg-white p-6">
                <div class="flex flex-wrap items-center gap-2 text-sm font-bold text-[#6B6F74]">
                    @if($product->subcategory?->category)
                        <a href="{{ route('category.show', $product->subcategory->category->slug) }}" class="text-[#9A712E] hover:text-[#15181B]">{{ $product->subcategory->category->name }}</a>
                        <span>/</span>
                    @endif
                    <span>{{ $product->subcategory?->name }}</span>
                </div>

                <h1 class="mt-3 text-3xl font-black leading-tight text-[#15181B]">{{ $product->name }}</h1>
                <p class="mt-3 text-sm font-semibold text-[#6B6F74]">SKU: <span class="text-[#22272B]">{{ $product->sku }}</span></p>

                <div class="mt-6 border-y border-[#E5E1DA] py-5">
                    <div class="flex flex-wrap items-end gap-3">
                        <span class="text-4xl font-black text-[#15181B]">&euro;{{ number_format((float) $product->price, 2) }}</span>
                        @if($hasDiscount)
                            <span class="pb-1 text-lg text-[#6B6F74] line-through">&euro;{{ number_format((float) $product->compare_price, 2) }}</span>
                            <span class="mb-1 rounded-full bg-[#C9473D]/10 px-3 py-1 text-sm font-black text-[#C9473D]">
                                Kurseni &euro;{{ number_format((float) $product->compare_price - (float) $product->price, 2) }}
                            </span>
                        @endif
                    </div>

                    <div class="mt-4 inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-black {{ $product->stock > 0 ? 'bg-[#25865A]/10 text-[#25865A]' : 'bg-[#C9473D]/10 text-[#C9473D]' }}">
                        <span class="h-2 w-2 rounded-full {{ $product->stock > 0 ? 'bg-[#25865A]' : 'bg-[#C9473D]' }}"></span>
                        {{ $product->stock > 0 ? 'Ne stok: ' . $product->stock : 'Nuk ka stok' }}
                    </div>
                </div>

                <div x-data="{ qty: 1, max: {{ max(1, (int) $product->stock) }} }" class="mt-6 space-y-5">
                    <div>
                        <label for="product-quantity" class="mb-2 block text-sm font-black text-[#15181B]">Sasia</label>
                        <div class="inline-flex items-center rounded-md border border-[#D8D1C6] bg-[#F7F5F1]">
                            <button type="button" @click="qty = Math.max(1, qty - 1)" class="inline-flex h-11 w-11 items-center justify-center text-[#15181B] hover:text-[#9A712E]" aria-label="Zvogelo sasine">
                                <x-store.icon name="minus" class="h-4 w-4" />
                            </button>
                            <input id="product-quantity" type="number" min="1" max="{{ max(1, (int) $product->stock) }}" x-model.number="qty" class="h-11 w-16 border-x border-[#D8D1C6] bg-white text-center font-bold focus:border-[#B88A3B] focus:ring-[#B88A3B]">
                            <button type="button" @click="qty = Math.min(max, qty + 1)" class="inline-flex h-11 w-11 items-center justify-center text-[#15181B] hover:text-[#9A712E]" aria-label="Rrit sasine">
                                <x-store.icon name="plus" class="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-[1fr_auto]">
                        <button type="button"
                                class="add-to-cart btn-primary inline-flex items-center justify-center gap-2 disabled:cursor-not-allowed disabled:bg-[#6B6F74]"
                                data-product-id="{{ $product->id }}"
                                data-url="{{ route('cart.add') }}"
                                :data-quantity="qty"
                                @disabled($product->stock <= 0)>
                            <x-store.icon name="cart" class="h-5 w-5" />
                            Shto ne shporte
                        </button>

                        @auth
                            <button type="button"
                                    class="add-to-wishlist inline-flex h-12 items-center justify-center gap-2 rounded-md border border-[#D8D1C6] bg-white px-5 font-bold text-[#15181B] transition hover:border-[#C9473D] hover:text-[#C9473D] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#B88A3B] {{ $isFavorited ? 'is-favorited text-[#C9473D]' : '' }}"
                                    data-product-id="{{ $product->id }}"
                                    data-url="{{ route('wishlist.toggle') }}">
                                <x-store.icon name="heart" class="h-5 w-5 {{ $isFavorited ? 'fill-current' : '' }}" />
                                Ruaj
                            </button>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex h-12 items-center justify-center gap-2 rounded-md border border-[#D8D1C6] bg-white px-5 font-bold text-[#15181B] transition hover:border-[#B88A3B] hover:text-[#9A712E]">
                                <x-store.icon name="heart" class="h-5 w-5" />
                                Ruaj
                            </a>
                        @endauth
                    </div>
                </div>

                <div class="mt-6 grid gap-3 text-sm text-[#6B6F74] sm:grid-cols-3">
                    <div class="rounded-md bg-[#F7F5F1] p-3"><x-store.icon name="shield" class="mb-2 h-5 w-5 text-[#B88A3B]" /> Produkt i kontrolluar</div>
                    <div class="rounded-md bg-[#F7F5F1] p-3"><x-store.icon name="truck" class="mb-2 h-5 w-5 text-[#B88A3B]" /> Dergese ne Kosove</div>
                    <div class="rounded-md bg-[#F7F5F1] p-3"><x-store.icon name="headset" class="mb-2 h-5 w-5 text-[#B88A3B]" /> Mbeshtetje per porosi</div>
                </div>
            </div>
        </aside>
    </div>

    <section class="mt-10 rounded-lg border border-[#E5E1DA] bg-white p-6">
        <h2 class="text-2xl font-black text-[#15181B]">Pershkrimi</h2>
        <p class="mt-4 leading-8 text-[#6B6F74]">{{ $product->description }}</p>
    </section>

    @if($relatedProducts->count())
        <section class="mt-12">
            <div class="mb-6 flex items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-black uppercase text-[#9A712E]">Te ngjashme</p>
                    <h2 class="mt-1 text-2xl font-black text-[#15181B]">Produkte nga e njejta kategori</h2>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($relatedProducts as $related)
                    <x-store.product-card :product="$related" />
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
