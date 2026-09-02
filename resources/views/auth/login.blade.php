@extends('layouts.app')

@section('title', 'Hyrja ne llogari - Denata Shop')
@section('robots', 'noindex,follow')

@section('content')
<section class="bg-[#F7F5F1] px-4 py-12 sm:px-6 lg:px-8">
    <div class="mx-auto grid max-w-5xl overflow-hidden rounded-lg border border-[#E5E1DA] bg-white shadow-[0_24px_70px_rgba(21,24,27,0.08)] lg:grid-cols-[0.9fr_1.1fr]">
        <div class="hidden bg-[#15181B] p-10 text-white lg:flex lg:flex-col lg:justify-between">
            <div>
                <img src="{{ asset('images/denata-shop-logo-web.png') }}" alt="Denata Shop" class="max-h-[70px] w-auto rounded bg-white p-1 object-contain">
                <h1 class="mt-10 text-3xl font-black">Hyr ne llogarine tende</h1>
                <p class="mt-4 leading-8 text-[#E5E1DA]">Ruaj produktet, menaxho porosite dhe vazhdo blerjen me katalogun Denata Shop.</p>
            </div>
            <div class="grid gap-3 text-sm text-[#E5E1DA]">
                <span class="inline-flex items-center gap-2"><x-store.icon name="shield" class="h-4 w-4 text-[#B88A3B]" /> Akses i mbrojtur</span>
                <span class="inline-flex items-center gap-2"><x-store.icon name="cart" class="h-4 w-4 text-[#B88A3B]" /> Shporte dhe wishlist</span>
            </div>
        </div>

        <div class="p-6 sm:p-10">
            <div class="text-center lg:text-left">
                <a href="{{ route('home') }}" class="inline-flex lg:hidden">
                    <img src="{{ asset('images/denata-shop-logo-web.png') }}" alt="Denata Shop" class="max-h-[58px] w-auto object-contain">
                </a>
                <p class="mt-6 text-sm font-black uppercase text-[#9A712E] lg:mt-0">Mire se vini</p>
                <h2 class="mt-2 text-3xl font-black text-[#15181B]">Hyni ne llogari</h2>
                <p class="mt-2 text-sm text-[#6B6F74]">Perdor email-in dhe fjalekalimin tuaj per te vazhduar.</p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                @csrf

                <div>
                    <label for="email" class="mb-2 block text-sm font-bold text-[#15181B]">Adresa email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="store-input w-full" placeholder="ju@example.com">
                    @error('email')
                        <p class="mt-2 text-sm font-semibold text-[#C9473D]">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="mb-2 block text-sm font-bold text-[#15181B]">Fjalekalimi</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password" class="store-input w-full" placeholder="********">
                    @error('password')
                        <p class="mt-2 text-sm font-semibold text-[#C9473D]">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <label class="flex items-center gap-2 text-sm font-semibold text-[#6B6F74]">
                        <input type="checkbox" name="remember" class="rounded border-[#D8D1C6] text-[#B88A3B] focus:ring-[#B88A3B]">
                        Mbaj mend
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm font-bold text-[#9A712E] hover:text-[#15181B]">Harruat fjalekalimin?</a>
                    @endif
                </div>

                <button type="submit" class="btn-primary w-full">Hyni ne llogari</button>
            </form>

            <p class="mt-6 text-center text-sm text-[#6B6F74]">
                Nuk keni llogari?
                <a href="{{ route('register') }}" class="font-bold text-[#9A712E] hover:text-[#15181B]">Regjistrohu tani</a>
            </p>
        </div>
    </div>
</section>
@endsection
