@extends('layouts.app')

@section('title', 'Regjistrohu - Denata Shop')

@section('content')
<section class="bg-[#F7F5F1] px-4 py-12 sm:px-6 lg:px-8">
    <div class="mx-auto grid max-w-5xl overflow-hidden rounded-lg border border-[#E5E1DA] bg-white shadow-[0_24px_70px_rgba(21,24,27,0.08)] lg:grid-cols-[0.9fr_1.1fr]">
        <div class="hidden bg-[#15181B] p-10 text-white lg:flex lg:flex-col lg:justify-between">
            <div>
                <img src="{{ asset('images/denata-shop-logo-web.png') }}" alt="Denata Shop" class="max-h-[70px] w-auto rounded bg-white p-1 object-contain">
                <h1 class="mt-10 text-3xl font-black">Krijo llogari Denata</h1>
                <p class="mt-4 leading-8 text-[#E5E1DA]">Ruaj produktet e preferuara dhe vazhdo porosite me te dhena te plotesuara.</p>
            </div>
            <div class="grid gap-3 text-sm text-[#E5E1DA]">
                <span class="inline-flex items-center gap-2"><x-store.icon name="heart" class="h-4 w-4 text-[#B88A3B]" /> Wishlist personale</span>
                <span class="inline-flex items-center gap-2"><x-store.icon name="truck" class="h-4 w-4 text-[#B88A3B]" /> Porosi me te shpejta</span>
            </div>
        </div>

        <div class="p-6 sm:p-10">
            <div class="text-center lg:text-left">
                <a href="{{ route('home') }}" class="inline-flex lg:hidden">
                    <img src="{{ asset('images/denata-shop-logo-web.png') }}" alt="Denata Shop" class="max-h-[58px] w-auto object-contain">
                </a>
                <p class="mt-6 text-sm font-black uppercase text-[#9A712E] lg:mt-0">Llogari e re</p>
                <h2 class="mt-2 text-3xl font-black text-[#15181B]">Regjistrohu</h2>
                <p class="mt-2 text-sm text-[#6B6F74]">Plotesoni te dhenat per te krijuar llogarine tuaj.</p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5">
                @csrf

                <div>
                    <label for="name" class="mb-2 block text-sm font-bold text-[#15181B]">Emri i plote</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="store-input w-full" placeholder="Emri juaj">
                    @error('name')
                        <p class="mt-2 text-sm font-semibold text-[#C9473D]">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="mb-2 block text-sm font-bold text-[#15181B]">Adresa email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" class="store-input w-full" placeholder="ju@example.com">
                    @error('email')
                        <p class="mt-2 text-sm font-semibold text-[#C9473D]">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="mb-2 block text-sm font-bold text-[#15181B]">Fjalekalimi</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password" class="store-input w-full" placeholder="********">
                    @error('password')
                        <p class="mt-2 text-sm font-semibold text-[#C9473D]">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="mb-2 block text-sm font-bold text-[#15181B]">Konfirmo fjalekalimin</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="store-input w-full" placeholder="********">
                </div>

                <button type="submit" class="btn-primary w-full">Krijo llogari</button>
            </form>

            <p class="mt-6 text-center text-sm text-[#6B6F74]">
                Keni tashme llogari?
                <a href="{{ route('login') }}" class="font-bold text-[#9A712E] hover:text-[#15181B]">Hyni ketu</a>
            </p>
        </div>
    </div>
</section>
@endsection
