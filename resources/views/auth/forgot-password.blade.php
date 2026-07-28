@extends('layouts.app')

@section('title', 'Rivendos fjalekalimin - Denata Shop')

@section('content')
<section class="bg-[#F7F5F1] px-4 py-12 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-md rounded-lg border border-[#E5E1DA] bg-white p-6 shadow-[0_18px_45px_rgba(21,24,27,0.08)] sm:p-8">
        <div class="text-center">
            <img src="{{ asset('images/denata-shop-logo-web.png') }}" alt="Denata Shop" class="mx-auto max-h-[64px] w-auto object-contain">
            <p class="mt-6 text-sm font-black uppercase text-[#9A712E]">Rikuperim</p>
            <h1 class="mt-2 text-2xl font-black text-[#15181B]">Rivendos fjalekalimin</h1>
            <p class="mt-2 text-sm leading-6 text-[#6B6F74]">Shkruani email-in dhe do t'ju dergojme nje link per rivendosje.</p>
        </div>

        @session('status')
            <div class="mt-6 rounded-md border border-[#25865A]/30 bg-[#25865A]/10 p-3 text-sm font-semibold text-[#25865A]">
                {{ $value }}
            </div>
        @endsession

        <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-5">
            @csrf
            <div>
                <label for="email" class="mb-2 block text-sm font-bold text-[#15181B]">Adresa email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="store-input w-full" placeholder="ju@example.com">
                @error('email')
                    <p class="mt-2 text-sm font-semibold text-[#C9473D]">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn-primary w-full">Dergo linkun</button>
            <a href="{{ route('login') }}" class="block text-center text-sm font-bold text-[#9A712E] hover:text-[#15181B]">Kthehu te hyrja</a>
        </form>
    </div>
</section>
@endsection
