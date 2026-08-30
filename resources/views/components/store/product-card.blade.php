@props(['product', 'badge' => null])

@php
    $hasDiscount = $product->compare_price && (float) $product->compare_price > (float) $product->price;
    $discount = $hasDiscount ? round(((float) $product->compare_price - (float) $product->price) / (float) $product->compare_price * 100) : null;
    $isFavorited = in_array($product->id, $wishlistProductIds ?? [], true);
@endphp

<article {{ $attributes->merge(['class' => 'group flex h-full flex-col overflow-hidden rounded-lg border border-[#E1D9CB] bg-white shadow-[0_10px_28px_rgba(21,24,27,0.04)] transition duration-200 hover:-translate-y-0.5 hover:border-[#C9A35B] hover:shadow-[0_18px_45px_rgba(21,24,27,0.10)]']) }}>
    <div class="relative border-b border-[#E5E1DA] bg-[#F7F5F1]">
        <a href="{{ route('product.show', $product->slug) }}" class="flex aspect-[5/4] items-center justify-center p-3.5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#B88A3B]">
            <img src="{{ $product->thumbnail_url }}"
                 alt="{{ $product->name }}"
                 loading="lazy"
                 decoding="async"
                 width="320"
                 height="240"
                 class="h-full w-full object-contain transition duration-200 group-hover:scale-[1.03]">
        </a>

        <div class="absolute left-3 top-3 flex flex-wrap gap-2">
            @if($hasDiscount)
                <span class="rounded-full bg-[#C9473D] px-2.5 py-1 text-xs font-black text-white shadow-sm">-{{ $discount }}%</span>
            @elseif($badge)
                <span class="rounded-full bg-[#15181B] px-2.5 py-1 text-xs font-black text-white shadow-sm">{{ $badge }}</span>
            @elseif($product->is_featured)
                <span class="rounded-full bg-[#B88A3B] px-2.5 py-1 text-xs font-black text-white shadow-sm">Zgjedhur</span>
            @endif
        </div>

        @auth
            <button type="button"
                    class="add-to-wishlist absolute right-3 top-3 inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#E5E1DA] bg-white/95 text-[#15181B] shadow-sm transition hover:border-[#C9473D] hover:text-[#C9473D] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#B88A3B] {{ $isFavorited ? 'is-favorited text-[#C9473D]' : '' }}"
                    data-product-id="{{ $product->id }}"
                    data-url="{{ route('wishlist.toggle') }}"
                    aria-label="Shto ose hiq nga lista e deshirave">
                <x-store.icon name="heart" class="h-5 w-5 {{ $isFavorited ? 'fill-current' : '' }}" />
            </button>
        @else
            <a href="{{ route('login') }}"
               class="absolute right-3 top-3 inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#E5E1DA] bg-white/95 text-[#15181B] shadow-sm transition hover:border-[#B88A3B] hover:text-[#B88A3B] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#B88A3B]"
               aria-label="Hyni per te ruajtur produktin">
                <x-store.icon name="heart" class="h-5 w-5" />
            </a>
        @endauth
    </div>

    <div class="flex flex-1 flex-col p-4">
        <div class="mb-2 flex items-center justify-between gap-3 text-xs font-semibold uppercase text-[#6B6F74]">
            <span class="truncate">{{ $product->subcategory?->name ?? 'Produkt' }}</span>
            <span class="{{ $product->stock > 0 ? 'text-[#25865A]' : 'text-[#C9473D]' }}">{{ $product->stock > 0 ? 'Ne stok' : 'Jashte stokut' }}</span>
        </div>

        <a href="{{ route('product.show', $product->slug) }}" class="focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#B88A3B]">
            <h3 class="line-clamp-2 min-h-[2.75rem] text-sm font-black leading-[1.45] text-[#17191C] transition group-hover:text-[#9A712E]">
                {{ Str::limit($product->name, 58) }}
            </h3>
        </a>

        <div class="mt-3 flex flex-wrap items-end gap-2">
            <span class="text-[1.35rem] font-black leading-none text-[#15181B]">&euro;{{ number_format((float) $product->price, 2) }}</span>
            @if($hasDiscount)
                <span class="text-sm text-[#6B6F74] line-through">&euro;{{ number_format((float) $product->compare_price, 2) }}</span>
            @endif
        </div>

        <div class="mt-4 flex gap-2">
            <a href="{{ route('product.show', $product->slug) }}"
               class="inline-flex h-10 flex-1 items-center justify-center rounded-md border border-[#E5E1DA] px-3 text-sm font-black text-[#22272B] transition hover:border-[#B88A3B] hover:text-[#9A712E] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#B88A3B]">
                Detajet
            </a>
            <button type="button"
                    class="add-to-cart inline-flex h-10 flex-1 items-center justify-center gap-2 rounded-md bg-[#15181B] px-3 text-sm font-black text-white transition hover:bg-[#B88A3B] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#B88A3B] disabled:cursor-not-allowed disabled:bg-[#6B6F74]"
                    data-product-id="{{ $product->id }}"
                    data-url="{{ route('cart.add') }}"
                    data-quantity="1"
                    @disabled($product->stock <= 0)>
                <x-store.icon name="cart" class="h-4 w-4" />
                <span>{{ $product->stock > 0 ? 'Shto' : 'Ska stok' }}</span>
            </button>
        </div>
    </div>
</article>
