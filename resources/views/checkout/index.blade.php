@extends('layouts.app')

@section('title', 'Checkout | Denata Shop')
@section('robots', 'noindex,nofollow')

@section('content')
<div class="bg-[#F7F5F1]">
    <div class="container-custom py-8 sm:py-10">
        <nav class="mb-6 flex flex-wrap items-center gap-2 text-sm font-semibold text-[#6B6F74]">
            <a href="{{ route('home') }}" class="hover:text-[#9A712E]">Ballina</a>
            <span>/</span>
            <a href="{{ route('cart.index') }}" class="hover:text-[#9A712E]">Shporta</a>
            <span>/</span>
            <span class="text-[#15181B]">Checkout</span>
        </nav>

        <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div>
                <p class="text-sm font-black uppercase text-[#9A712E]">Porosia</p>
                <h1 class="mt-2 text-3xl font-black text-[#15181B] sm:text-4xl">Perfundo blerjen</h1>
            </div>
            <div class="inline-flex w-fit items-center gap-2 rounded-md border border-[#D8D1C6] bg-white px-4 py-2 text-sm font-bold text-[#22272B]">
                <x-store.icon name="lock" class="h-4 w-4 text-[#25865A]" />
                Pagese me para ne dore
            </div>
        </div>

        @if(session('error'))
            <div class="mb-6 rounded-md border border-[#C9473D]/30 bg-[#C9473D]/10 px-4 py-3 text-sm font-semibold text-[#C9473D]">
                {{ session('error') }}
            </div>
        @endif

        @if($couponError)
            <div class="mb-6 rounded-md border border-[#B88A3B]/35 bg-[#B88A3B]/10 px-4 py-3 text-sm font-semibold text-[#7b5a25]">
                {{ $couponError }}
            </div>
        @endif

        <form method="POST" action="{{ route('checkout.store') }}" class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_390px]">
            @csrf

            <section class="rounded-lg border border-[#E5E1DA] bg-white p-5 shadow-[0_18px_45px_rgba(21,24,27,0.06)] sm:p-7">
                <div class="mb-6 flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-md bg-[#15181B] text-white">
                        <x-store.icon name="truck" class="h-5 w-5" />
                    </span>
                    <div>
                        <h2 class="text-xl font-black text-[#15181B]">Te dhenat e dergeses</h2>
                        <p class="text-sm font-semibold text-[#6B6F74]">Plotesoni te dhenat per dorezim.</p>
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="customer_name" class="mb-2 block text-sm font-black text-[#22272B]">Emri dhe mbiemri</label>
                        <input id="customer_name" name="customer_name" value="{{ old('customer_name', auth()->user()?->name) }}" class="store-input" required>
                        @error('customer_name') <p class="mt-1 text-sm font-semibold text-[#C9473D]">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="customer_email" class="mb-2 block text-sm font-black text-[#22272B]">Email</label>
                        <input id="customer_email" type="email" name="customer_email" value="{{ old('customer_email', auth()->user()?->email) }}" class="store-input" required>
                        @error('customer_email') <p class="mt-1 text-sm font-semibold text-[#C9473D]">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="customer_phone" class="mb-2 block text-sm font-black text-[#22272B]">Telefoni</label>
                        <input id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}" class="store-input" required>
                        @error('customer_phone') <p class="mt-1 text-sm font-semibold text-[#C9473D]">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="shipping_city" class="mb-2 block text-sm font-black text-[#22272B]">Qyteti</label>
                        <input id="shipping_city" name="shipping_city" value="{{ old('shipping_city') }}" class="store-input" required>
                        @error('shipping_city') <p class="mt-1 text-sm font-semibold text-[#C9473D]">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="shipping_address" class="mb-2 block text-sm font-black text-[#22272B]">Adresa</label>
                        <input id="shipping_address" name="shipping_address" value="{{ old('shipping_address') }}" class="store-input" required>
                        @error('shipping_address') <p class="mt-1 text-sm font-semibold text-[#C9473D]">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="shipping_postal_code" class="mb-2 block text-sm font-black text-[#22272B]">Kodi postar</label>
                        <input id="shipping_postal_code" name="shipping_postal_code" value="{{ old('shipping_postal_code') }}" class="store-input">
                        @error('shipping_postal_code') <p class="mt-1 text-sm font-semibold text-[#C9473D]">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="coupon_code" class="mb-2 block text-sm font-black text-[#22272B]">Kuponi</label>
                        <input id="coupon_code" name="coupon_code" value="{{ old('coupon_code', request('coupon_code')) }}" class="store-input" placeholder="p.sh. DENATA10">
                        @error('coupon_code') <p class="mt-1 text-sm font-semibold text-[#C9473D]">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="notes" class="mb-2 block text-sm font-black text-[#22272B]">Shenime</label>
                        <textarea id="notes" name="notes" rows="4" class="store-input">{{ old('notes') }}</textarea>
                        @error('notes') <p class="mt-1 text-sm font-semibold text-[#C9473D]">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <aside class="h-fit rounded-lg border border-[#E5E1DA] bg-white p-5 shadow-[0_18px_45px_rgba(21,24,27,0.06)] lg:sticky lg:top-32">
                <h2 class="text-xl font-black text-[#15181B]">Permbledhje</h2>

                <div class="mt-5 space-y-4 border-b border-[#E5E1DA] pb-5">
                    @foreach($cartItems as $item)
                        <div class="flex gap-3">
                            <img src="{{ $item->product->thumbnail_url }}" alt="{{ $item->product->name }}" loading="lazy" decoding="async" class="h-16 w-16 rounded-md bg-[#F7F5F1] object-contain p-2">
                            <div class="min-w-0 flex-1">
                                <p class="line-clamp-2 text-sm font-black text-[#15181B]">{{ $item->product->name }}</p>
                                <p class="mt-1 text-sm font-semibold text-[#6B6F74]">{{ $item->quantity }} x &euro;{{ number_format($item->product->price, 2) }}</p>
                            </div>
                            <p class="text-sm font-black text-[#15181B]">&euro;{{ number_format($item->product->price * $item->quantity, 2) }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="space-y-3 border-b border-[#E5E1DA] py-5 text-sm font-semibold text-[#6B6F74]">
                    <div class="flex justify-between gap-4">
                        <span>Subtotal</span>
                        <span class="font-black text-[#15181B]">&euro;{{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span>Transporti</span>
                        <span class="font-black text-[#15181B]">&euro;{{ number_format($shippingTotal, 2) }}</span>
                    </div>
                    @if(($memberDiscountTotal ?? 0) > 0)
                        <div class="flex justify-between gap-4 text-[#25865A]">
                            <span>Zbritje llogarie 7%</span>
                            <span>-&euro;{{ number_format($memberDiscountTotal, 2) }}</span>
                        </div>
                    @else
                        <div class="rounded-md border border-[#D8D1C6] bg-[#F7F5F1] p-3 text-[#22272B]">
                            Hape nje llogari dhe perfito 7% zbritje ne produktet e porosise.
                        </div>
                    @endif
                    @if(($couponDiscountTotal ?? 0) > 0)
                        <div class="flex justify-between gap-4 text-[#25865A]">
                            <span>Zbritje {{ $coupon?->code }}</span>
                            <span>-&euro;{{ number_format($couponDiscountTotal, 2) }}</span>
                        </div>
                    @endif
                </div>

                <div class="flex justify-between gap-4 py-5 text-xl font-black text-[#15181B]">
                    <span>Total</span>
                    <span>&euro;{{ number_format($total, 2) }}</span>
                </div>

                <div class="mb-5 rounded-md bg-[#15181B] p-4 text-sm font-semibold text-white">
                    Pagesa behet me para ne dore gjate dorezimit.
                </div>

                <button type="submit" class="btn-primary flex w-full items-center justify-center gap-2">
                    Konfirmo porosine
                    <x-store.icon name="check" class="h-4 w-4" />
                </button>
            </aside>
        </form>
    </div>
</div>
@endsection
