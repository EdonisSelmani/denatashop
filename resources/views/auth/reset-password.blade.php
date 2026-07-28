@extends('layouts.app')

@section('title', 'Fjalekalim i ri - Denata Shop')

@section('content')
<section class="bg-[#F7F5F1] px-4 py-12 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-md rounded-lg border border-[#E5E1DA] bg-white p-6 shadow-[0_18px_45px_rgba(21,24,27,0.08)] sm:p-8">
        <div class="text-center">
            <img src="{{ asset('images/denata-shop-logo-web.png') }}" alt="Denata Shop" class="mx-auto max-h-[64px] w-auto object-contain">
            <p class="mt-6 text-sm font-black uppercase text-[#9A712E]">Siguria</p>
            <h1 class="mt-2 text-2xl font-black text-[#15181B]">Vendos fjalekalim te ri</h1>
        </div>

        <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
                <label for="email" class="mb-2 block text-sm font-bold text-[#15181B]">Adresa email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" class="store-input w-full">
                @error('email')
                    <p class="mt-2 text-sm font-semibold text-[#C9473D]">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="mb-2 block text-sm font-bold text-[#15181B]">Fjalekalimi i ri</label>
                <input id="password" type="password" name="password" required autocomplete="new-password" class="store-input w-full">
                @error('password')
                    <p class="mt-2 text-sm font-semibold text-[#C9473D]">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="mb-2 block text-sm font-bold text-[#15181B]">Konfirmo fjalekalimin</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="store-input w-full">
            </div>

            <button type="submit" class="btn-primary w-full">Rivendos fjalekalimin</button>
        </form>
    </div>
</section>
@endsection
