@extends('layouts.app')

@section('title', 'Lista e deshirave - Denata Shop')

@section('content')
<div class="container-custom py-6 md:py-8">
    <nav class="mb-5 flex items-center gap-2 text-sm text-[#6B6F74]" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-[#9A712E]">Ballina</a>
        <span>/</span>
        <span class="font-semibold text-[#15181B]">Lista e deshirave</span>
    </nav>

    <div class="mb-8 rounded-lg border border-[#2A2D31] bg-[#15181B] p-6 text-white shadow-[0_24px_70px_rgba(21,24,27,0.12)] sm:flex sm:items-end sm:justify-between sm:gap-4">
        <div>
            <p class="text-sm font-black uppercase text-[#D7B16D]">Produktet e ruajtura</p>
            <h1 class="mt-2 text-3xl font-black text-white md:text-4xl">Lista e deshirave</h1>
            <p class="mt-2 text-sm text-[#D8D1C6]">{{ $wishlistItems->count() }} produkte te ruajtura.</p>
        </div>
        <a href="{{ route('shop') }}" class="mt-4 inline-flex items-center justify-center gap-2 rounded-md border border-white/20 bg-white/10 px-5 py-3 text-sm font-black text-white transition hover:border-[#D7B16D] hover:text-[#D7B16D] sm:mt-0">
            Shiko katalogun
            <x-store.icon name="arrow-right" class="h-4 w-4" />
        </a>
    </div>

    @if($wishlistItems->count())
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($wishlistItems as $product)
                @php
                    $hasDiscount = $product->compare_price && (float) $product->compare_price > (float) $product->price;
                    $discount = $hasDiscount ? round(((float) $product->compare_price - (float) $product->price) / (float) $product->compare_price * 100) : null;
                @endphp
                <article class="wishlist-item group flex h-full flex-col overflow-hidden rounded-lg border border-[#E1D9CB] bg-white shadow-[0_10px_28px_rgba(21,24,27,0.04)] transition hover:-translate-y-0.5 hover:border-[#B88A3B] hover:shadow-[0_18px_45px_rgba(21,24,27,0.10)]" data-product-id="{{ $product->id }}">
                    <div class="relative border-b border-[#E5E1DA] bg-[#F7F5F1]">
                        <a href="{{ route('product.show', $product->slug) }}" class="flex aspect-[4/3] items-center justify-center p-4">
                            <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}" loading="lazy" decoding="async" width="320" height="240" class="h-full w-full object-contain transition group-hover:scale-[1.03]">
                        </a>
                        <button type="button" class="remove-from-wishlist absolute right-3 top-3 inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#E5E1DA] bg-white text-[#C9473D] shadow-sm transition hover:border-[#C9473D]" data-product-id="{{ $product->id }}" aria-label="Hiq nga lista e deshirave">
                            <x-store.icon name="heart" class="h-5 w-5 fill-current" />
                        </button>
                        @if($hasDiscount)
                            <span class="absolute left-3 top-3 rounded-full bg-[#C9473D] px-2.5 py-1 text-xs font-bold text-white">-{{ $discount }}%</span>
                        @endif
                    </div>

                    <div class="flex flex-1 flex-col p-4">
                        <p class="text-xs font-black uppercase text-[#6B6F74]">{{ $product->subcategory?->name }}</p>
                        <a href="{{ route('product.show', $product->slug) }}" class="mt-2 min-h-[3rem] text-sm font-black leading-6 text-[#15181B] transition hover:text-[#9A712E]">
                            {{ Str::limit($product->name, 58) }}
                        </a>
                        <div class="mt-3 flex items-end gap-2">
                            <span class="text-xl font-black text-[#15181B]">&euro;{{ number_format((float) $product->price, 2) }}</span>
                            @if($hasDiscount)
                                <span class="pb-0.5 text-sm text-[#6B6F74] line-through">&euro;{{ number_format((float) $product->compare_price, 2) }}</span>
                            @endif
                        </div>
                        <button type="button"
                                class="add-to-cart btn-primary mt-4 inline-flex w-full items-center justify-center gap-2 disabled:cursor-not-allowed disabled:bg-[#6B6F74]"
                                data-product-id="{{ $product->id }}"
                                data-url="{{ route('cart.add') }}"
                                data-quantity="1"
                                @disabled($product->stock <= 0)>
                            <x-store.icon name="cart" class="h-4 w-4" />
                            {{ $product->stock > 0 ? 'Shto ne shporte' : 'Nuk ka stok' }}
                        </button>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <x-store.empty-state
            icon="heart"
            title="Lista e deshirave eshte bosh"
            text="Ruaj produktet qe te pelqejne dhe kthehu tek ato kur te jesh gati per porosi."
            action="Shiko produktet"
            :href="route('shop')" />
    @endif
</div>

@push('scripts')
<script>
    document.querySelectorAll('.remove-from-wishlist').forEach(button => {
        button.addEventListener('click', async function() {
            const productId = this.dataset.productId;
            const formData = new FormData();
            formData.append('product_id', productId);

            try {
                const response = await fetch('{{ route("wishlist.toggle") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    this.closest('.wishlist-item').remove();

                    if (data.wishlist_count !== undefined) {
                        document.getElementById('wishlist-count').textContent = data.wishlist_count;
                    }

                    window.showToast?.('Produkti u hoq nga lista', 'success');

                    if (document.querySelectorAll('.wishlist-item').length === 0) {
                        location.reload();
                    }
                }
            } catch (error) {
                window.showToast?.('Lista nuk u perditesua', 'error');
            }
        });
    });
</script>
@endpush
@endsection
