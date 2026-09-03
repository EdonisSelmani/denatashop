<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', config('seo.locale', app()->getLocale())) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $decodeSeoSection = fn (string $section): string => trim(html_entity_decode($__env->yieldContent($section), ENT_QUOTES, 'UTF-8'));
        $seoTitle = $decodeSeoSection('title');
        $seoDescription = $decodeSeoSection('meta_description');
        $seoCanonical = $decodeSeoSection('canonical');
        $seoImage = $decodeSeoSection('seo_image');
        $seoRobots = $decodeSeoSection('robots');
        $seoType = $decodeSeoSection('seo_type') ?: $decodeSeoSection('og_type');
    @endphp
    <x-seo.head
        :title="$seoTitle ?: null"
        :description="$seoDescription ?: null"
        :canonical="$seoCanonical ?: null"
        :image="$seoImage ?: null"
        :robots="$seoRobots ?: 'index,follow'"
        :type="$seoType ?: 'website'"
        :structured-data="$structuredData ?? []"
    />
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#15181B">

    <x-analytics.google-analytics />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-w-0 overflow-x-hidden bg-[#F7F5F1] font-sans text-[#17191C] antialiased">
    <div class="flex min-h-screen min-w-0 flex-col">
        <x-store.header />

        <main class="min-w-0 flex-1">
            @if(session('success'))
                <div class="mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
                    <div class="rounded-md border border-[#25865A]/30 bg-[#25865A]/10 px-4 py-3 text-sm font-semibold text-[#1f6d49]">
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
                    <div class="rounded-md border border-[#C9473D]/30 bg-[#C9473D]/10 px-4 py-3 text-sm font-semibold text-[#C9473D]">
                        {{ session('error') }}
                    </div>
                </div>
            @endif

            @isset($header)
                <section class="border-b border-[#E5E1DA] bg-white">
                    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </section>
            @endisset

            @isset($slot)
                {{ $slot }}
            @else
                @yield('content')
            @endisset
        </main>

        <x-store.footer />
    </div>

    <script>
        window.showToast = function(message, type = 'success') {
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.className = 'fixed bottom-4 right-4 z-50 space-y-2 px-4 sm:px-0';
                document.body.appendChild(container);
            }

            const toast = document.createElement('div');
            const colors = {
                success: 'bg-[#25865A]',
                error: 'bg-[#C9473D]',
                info: 'bg-[#15181B]',
                warning: 'bg-[#B88A3B]'
            };

            toast.className = `${colors[type] || colors.success} max-w-sm rounded-md px-5 py-3 text-sm font-semibold text-white shadow-lg transform transition-all duration-300 translate-x-full`;
            toast.innerHTML = `
                <div class="flex items-center gap-3">
                    <span>${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-white/80 hover:text-white" aria-label="Mbyll njoftimin">&times;</button>
                </div>
            `;

            container.appendChild(toast);
            setTimeout(() => toast.classList.remove('translate-x-full'), 100);
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    </script>
    @stack('scripts')
</body>
</html>
