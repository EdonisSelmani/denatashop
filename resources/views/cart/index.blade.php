@extends('layouts.app')

@section('title', 'Shporta ime - Denata Shop')
@section('robots', 'noindex,follow')

@section('content')
<div class="container-custom py-6 md:py-8">
    <nav class="mb-5 flex items-center gap-2 text-sm text-[#6B6F74]" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-[#9A712E]">Ballina</a>
        <span>/</span>
        <span class="font-semibold text-[#15181B]">Shporta</span>
    </nav>

    <div class="mb-8 rounded-lg border border-[#2A2D31] bg-[#15181B] p-6 text-white shadow-[0_24px_70px_rgba(21,24,27,0.12)] sm:flex sm:items-end sm:justify-between sm:gap-4">
        <div>
            <p class="text-sm font-black uppercase text-[#D7B16D]">Porosia</p>
            <h1 class="mt-2 text-3xl font-black text-white md:text-4xl">Shporta ime</h1>
        </div>
        <a href="{{ route('shop') }}" class="mt-4 inline-flex items-center justify-center gap-2 rounded-md border border-white/20 bg-white/10 px-5 py-3 text-sm font-black text-white transition hover:border-[#D7B16D] hover:text-[#D7B16D] sm:mt-0">
            Vazhdo blerjen
            <x-store.icon name="arrow-right" class="h-4 w-4" />
        </a>
    </div>

    @if($cartItems->count())
        <div class="grid gap-8 lg:grid-cols-[1fr_360px]">
            <section class="space-y-4" aria-label="Produktet ne shporte">
                @foreach($cartItems as $item)
                    <article class="cart-item rounded-lg border border-[#E1D9CB] bg-white p-4 shadow-[0_12px_34px_rgba(21,24,27,0.05)]" data-item-id="{{ $item->id }}" data-price="{{ $item->product->price }}">
                        <div class="grid gap-4 sm:grid-cols-[132px_1fr_auto]">
                            <a href="{{ route('product.show', $item->product->slug) }}" class="flex h-32 w-32 items-center justify-center rounded-md bg-[#F7F5F1] p-3">
                                <img src="{{ $item->product->thumbnail_url }}"
                                     alt="{{ $item->product->name }}"
                                     loading="lazy"
                                     decoding="async"
                                     width="128"
                                     height="128"
                                     class="h-full w-full object-contain">
                            </a>

                            <div class="min-w-0">
                                <p class="text-xs font-black uppercase text-[#9A712E]">{{ $item->product->subcategory?->name ?? 'Produkt' }}</p>
                                <a href="{{ route('product.show', $item->product->slug) }}" class="mt-1 block text-lg font-black text-[#15181B] transition hover:text-[#9A712E]">
                                    {{ $item->product->name }}
                                </a>
                                <p class="mt-1 text-sm font-semibold text-[#6B6F74]">SKU: {{ $item->product->sku }}</p>
                                <p class="mt-3 text-lg font-black text-[#15181B]">&euro;{{ number_format((float) $item->product->price, 2) }}</p>

                                <div class="mt-4 flex flex-wrap items-center gap-3">
                                    <div class="inline-flex items-center rounded-md border border-[#D8D1C6] bg-[#F7F5F1]">
                                        <button type="button" class="decrease-qty inline-flex h-10 w-10 items-center justify-center text-[#15181B] hover:text-[#9A712E]" data-id="{{ $item->id }}" aria-label="Zvogelo sasine">
                                            <x-store.icon name="minus" class="h-4 w-4" />
                                        </button>
                                        <span class="quantity-display flex h-10 w-12 items-center justify-center border-x border-[#D8D1C6] bg-white text-sm font-black">{{ $item->quantity }}</span>
                                        <button type="button" class="increase-qty inline-flex h-10 w-10 items-center justify-center text-[#15181B] hover:text-[#9A712E]" data-id="{{ $item->id }}" aria-label="Rrit sasine">
                                            <x-store.icon name="plus" class="h-4 w-4" />
                                        </button>
                                    </div>

                                    <button type="button" class="remove-item inline-flex items-center gap-2 rounded-md border border-[#E5E1DA] px-3 py-2 text-sm font-bold text-[#C9473D] transition hover:border-[#C9473D]" data-id="{{ $item->id }}">
                                        <x-store.icon name="trash" class="h-4 w-4" />
                                        Hiq
                                    </button>
                                </div>
                            </div>

                            <div class="text-left sm:text-right">
                                <p class="text-xs font-bold uppercase text-[#6B6F74]">Totali</p>
                                <p class="item-subtotal mt-1 text-xl font-black text-[#15181B]">&euro;{{ number_format((float) $item->product->price * $item->quantity, 2) }}</p>
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>

            <aside class="lg:sticky lg:top-36 lg:self-start">
                <div class="rounded-lg border border-[#E1D9CB] bg-white p-6 shadow-[0_18px_55px_rgba(21,24,27,0.08)]">
                    <h2 class="text-xl font-black text-[#15181B]">Permbledhje</h2>

                    <div class="mt-5 space-y-3 border-b border-[#E5E1DA] pb-5 text-sm">
                        <div class="flex justify-between gap-4">
                            <span class="text-[#6B6F74]">Nentotali</span>
                            <span id="cart-subtotal" class="font-bold text-[#15181B]">&euro;{{ number_format((float) $subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span class="text-[#6B6F74]">Transporti</span>
                            <span class="font-bold text-[#15181B]">3-6 dite</span>
                        </div>
                        @if(($memberDiscount ?? 0) > 0)
                            <div class="flex justify-between gap-4 text-[#25865A]">
                                <span>Zbritje llogarie 7%</span>
                                <span id="member-discount" class="font-bold">-&euro;{{ number_format((float) $memberDiscount, 2) }}</span>
                            </div>
                        @else
                            <div class="rounded-md bg-[#F7F5F1] p-3 text-[#6B6F74]">
                                Hyni ose hapni llogari per 7% zbritje ne produktet e porosise.
                            </div>
                        @endif
                    </div>

                    <div class="mt-5 flex justify-between gap-4">
                        <span class="text-lg font-black text-[#15181B]">Totali</span>
                        <span id="cart-total" class="text-2xl font-black text-[#15181B]">&euro;{{ number_format((float) $total, 2) }}</span>
                    </div>

                    <a href="{{ route('checkout.index') }}" class="btn-primary mt-6 flex w-full items-center justify-center gap-2">
                        Vazhdo ne checkout
                        <x-store.icon name="arrow-right" class="h-4 w-4" />
                    </a>
                </div>
            </aside>
        </div>
    @else
        <x-store.empty-state
            icon="cart"
            title="Shporta juaj eshte bosh"
            text="Shto produktet qe te duhen per projektin tend dhe kthehu ketu per te vazhduar porosine."
            action="Shiko produktet"
            :href="route('shop')" />
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.increase-qty').forEach(button => {
            button.addEventListener('click', async function() {
                const itemId = this.dataset.id;
                const cartItem = this.closest('.cart-item');
                const quantitySpan = cartItem.querySelector('.quantity-display');
                const newQty = parseInt(quantitySpan.textContent) + 1;
                await updateQuantity(itemId, newQty, cartItem);
            });
        });

        document.querySelectorAll('.decrease-qty').forEach(button => {
            button.addEventListener('click', async function() {
                const itemId = this.dataset.id;
                const cartItem = this.closest('.cart-item');
                const quantitySpan = cartItem.querySelector('.quantity-display');
                const currentQty = parseInt(quantitySpan.textContent);
                if (currentQty > 1) {
                    await updateQuantity(itemId, currentQty - 1, cartItem);
                }
            });
        });

        document.querySelectorAll('.remove-item').forEach(button => {
            button.addEventListener('click', async function() {
                const itemId = this.dataset.id;
                const cartItem = this.closest('.cart-item');
                if (confirm('A jeni i sigurt qe doni ta hiqni kete produkt?')) {
                    await removeItem(itemId, cartItem);
                }
            });
        });
    });

    async function updateQuantity(itemId, newQuantity, cartItem) {
        try {
            const response = await fetch(`/cart/${itemId}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ quantity: newQuantity })
            });

            const data = await response.json();

            if (data.success) {
                cartItem.querySelector('.quantity-display').textContent = newQuantity;
                const price = parseFloat(cartItem.dataset.price);
                cartItem.querySelector('.item-subtotal').textContent = '\u20ac' + (price * newQuantity).toFixed(2);

                if (data.cart_total !== undefined) {
                    document.getElementById('cart-subtotal').textContent = '\u20ac' + (data.cart_subtotal ?? data.cart_total).toFixed(2);
                    document.getElementById('cart-total').textContent = '\u20ac' + data.cart_total.toFixed(2);
                    const memberDiscount = document.getElementById('member-discount');
                    if (memberDiscount && data.member_discount !== undefined) {
                        memberDiscount.textContent = '-\u20ac' + data.member_discount.toFixed(2);
                    }
                }

                if (data.cart_count !== undefined) {
                    const cartCountElement = document.getElementById('cart-count');
                    if (cartCountElement) {
                        cartCountElement.textContent = data.cart_count;
                    }
                }

                window.showToast?.('Sasia u perditesua', 'success');
            } else {
                window.showToast?.(data.message || 'Gabim gjate perditesimit', 'error');
            }
        } catch (error) {
            window.showToast?.('Ndodhi nje gabim', 'error');
        }
    }

    async function removeItem(itemId, cartItem) {
        try {
            const response = await fetch(`/cart/${itemId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                cartItem.remove();

                if (data.cart_count !== undefined) {
                    const cartCountElement = document.getElementById('cart-count');
                    if (cartCountElement) {
                        cartCountElement.textContent = data.cart_count;
                    }
                }

                if (document.querySelectorAll('.cart-item').length === 0) {
                    location.reload();
                } else if (data.cart_total !== undefined) {
                    document.getElementById('cart-subtotal').textContent = '\u20ac' + (data.cart_subtotal ?? data.cart_total).toFixed(2);
                    document.getElementById('cart-total').textContent = '\u20ac' + data.cart_total.toFixed(2);
                    const memberDiscount = document.getElementById('member-discount');
                    if (memberDiscount && data.member_discount !== undefined) {
                        memberDiscount.textContent = '-\u20ac' + data.member_discount.toFixed(2);
                    }
                }

                window.showToast?.('Produkti u hoq nga shporta', 'success');
            } else {
                window.showToast?.(data.message || 'Gabim gjate heqjes', 'error');
            }
        } catch (error) {
            window.showToast?.('Ndodhi nje gabim', 'error');
        }
    }
</script>
@endpush
@endsection
