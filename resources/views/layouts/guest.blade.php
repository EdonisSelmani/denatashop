<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Denata Shop - Produkte per shtepi, pune dhe kopsht</title>
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
        <link rel="manifest" href="{{ asset('site.webmanifest') }}">
        <meta name="theme-color" content="#15181B">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F7F5F1] font-sans text-[#17191C] antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
            <a href="{{ route('home') }}" class="mb-6">
                <img src="{{ asset('images/denata-shop-logo-web.png') }}" alt="Denata Shop" class="max-h-[70px] w-auto object-contain">
            </a>

            <div class="w-full max-w-md rounded-lg border border-[#E5E1DA] bg-white p-6 shadow-[0_18px_45px_rgba(21,24,27,0.08)]">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
