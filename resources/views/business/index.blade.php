@extends('layouts.app')

@section('title', 'Zgjidhje për Investitorë & Blerje me Shumicë | Denata Shop')
@section('description', 'Katalogë Denata për investitorë, kontraktorë dhe blerje me shumicë në ngrohje, instalime, sanitari dhe banjo.')
@section('robots', 'index,follow')

@php
    $catalogs = [
        [
            'title' => 'Katalogu Ngrohje & Instalime',
            'description' => 'Produkte për ngrohje, sisteme uji, tuba, fitinga dhe sisteme instalimi për projekte profesionale.',
            'pdf' => asset('catalogs/denata-katalogu-ngrohje-instalime.pdf'),
            'cover' => asset('images/catalogs/denata-katalogu-ngrohje-instalime-cover.png'),
            'download' => 'DENATA-Katalogu-Ngrohje-Instalime.pdf',
        ],
        [
            'title' => 'Katalogu Sanitari & Banjo',
            'description' => 'Produkte sanitare dhe banjo, duke përfshirë tualete, fontana inkaso, xham dushi dhe rubineta.',
            'pdf' => asset('catalogs/denata-katalogu-sanitari-banjo.pdf'),
            'cover' => asset('images/catalogs/denata-katalogu-sanitari-banjo-cover.png'),
            'download' => 'DENATA-Katalogu-Sanitari-Banjo.pdf',
        ],
    ];
@endphp

@section('content')
<div
    class="bg-[#F7F6F3]"
    x-data="{
        quoteOpen: false,
        previousFocus: null,
        openQuote() {
            this.previousFocus = document.activeElement;
            this.quoteOpen = true;
            this.$nextTick(() => this.$refs.quoteClose.focus());
        },
        closeQuote() {
            this.quoteOpen = false;
            this.$nextTick(() => this.previousFocus?.focus());
        },
        trapQuoteFocus(event) {
            const focusable = [...this.$refs.quotePanel.querySelectorAll('a[href], button:not([disabled])')];
            if (!focusable.length) return;
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    }"
>
    <section class="border-b border-[#2A2D31] bg-[#15181B] text-white">
        <div class="container-custom py-14 sm:py-20 lg:py-24">
            <div class="max-w-4xl">
                <p class="text-sm font-black uppercase tracking-[0.2em] text-[#D7B16D]">Denata për profesionistë</p>
                <h1 class="mt-4 text-4xl font-black leading-tight sm:text-5xl lg:text-6xl">
                    Zgjidhje për Investitorë &amp; Blerje me Shumicë
                </h1>
                <p class="mt-6 max-w-3xl text-base font-semibold leading-8 text-[#D8D1C6] sm:text-lg">
                    Katalogë të dedikuar për investitorë, kontraktorë dhe klientë që kërkojnë produkte për projekte ose sasi të mëdha.
                </p>
            </div>
        </div>
    </section>

    <section id="kataloget" class="container-custom py-12 sm:py-16 lg:py-20">
        <div class="mb-8 max-w-3xl sm:mb-10">
            <p class="text-sm font-black uppercase tracking-[0.18em] text-[#9A712E]">Katalogët profesionalë</p>
            <h2 class="mt-3 text-3xl font-black text-[#111111] sm:text-4xl">Eksploroni gamën Denata</h2>
            <p class="mt-4 text-base font-semibold leading-7 text-[#6B7280]">
                Shikoni katalogët online ose shkarkojini për përdorim gjatë planifikimit të projektit tuaj.
            </p>
        </div>

        <div class="grid gap-7 lg:grid-cols-2">
            @foreach($catalogs as $catalog)
                <article class="group overflow-hidden rounded-lg border border-[#E5E7EB] bg-white shadow-[0_20px_55px_rgba(17,17,17,0.08)]">
                    <a href="{{ $catalog['pdf'] }}" target="_blank" rel="noopener noreferrer" class="block overflow-hidden bg-[#111111] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#C9A14A]" aria-label="Shiko {{ $catalog['title'] }}">
                        <div class="aspect-[16/10] overflow-hidden">
                            <img src="{{ $catalog['cover'] }}" alt="Kopertina e {{ $catalog['title'] }}" class="h-full w-full object-contain transition duration-300 group-hover:scale-[1.015]" loading="lazy" decoding="async">
                        </div>
                    </a>

                    <div class="p-6 sm:p-7">
                        <div class="flex items-start gap-4">
                            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-md bg-[#F7F5F1] text-[#9A712E]">
                                <x-store.icon name="package" class="h-5 w-5" />
                            </span>
                            <div>
                                <h3 class="text-xl font-black text-[#111111] sm:text-2xl">{{ $catalog['title'] }}</h3>
                                <p class="mt-3 text-sm font-semibold leading-7 text-[#6B7280]">{{ $catalog['description'] }}</p>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                            <a href="{{ $catalog['pdf'] }}" target="_blank" rel="noopener noreferrer" class="btn-primary inline-flex flex-1 items-center justify-center gap-2">
                                Shiko katalogun
                                <x-store.icon name="arrow-right" class="h-4 w-4" />
                            </a>
                            <a href="{{ $catalog['pdf'] }}" download="{{ $catalog['download'] }}" class="btn-secondary inline-flex flex-1 items-center justify-center gap-2">
                                Shkarko PDF
                                <x-store.icon name="arrow-right" class="h-4 w-4" />
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="container-custom pb-14 sm:pb-20 lg:pb-24">
        <div class="overflow-hidden rounded-lg border border-[#2A2D31] bg-[#15181B] px-6 py-9 text-white shadow-[0_24px_70px_rgba(17,17,17,0.14)] sm:px-10 sm:py-11 lg:flex lg:items-center lg:justify-between lg:gap-10">
            <div class="max-w-3xl">
                <p class="text-sm font-black uppercase tracking-[0.18em] text-[#D7B16D]">OFERTA PËR PROJEKTE</p>
                <h2 class="mt-3 text-2xl font-black sm:text-3xl">Jeni investitor dhe kërkoni ofertë për projektin tuaj?</h2>
                <p class="mt-3 text-sm font-semibold leading-7 text-[#D8D1C6]">
                    Na kontaktoni për çmime dhe oferta të personalizuara për projekte dhe porosi në sasi të mëdha.
                </p>
            </div>
            <button
                type="button"
                class="btn-primary mt-6 inline-flex shrink-0 items-center justify-center gap-2 bg-[#C9A14A] text-[#111111] hover:bg-white lg:mt-0"
                aria-haspopup="dialog"
                aria-controls="quote-contact-modal"
                @click="openQuote()"
            >
                Kërko ofertë
                <x-store.icon name="arrow-right" class="h-4 w-4" />
            </button>
        </div>
    </section>

    <div
        id="quote-contact-modal"
        x-show="quoteOpen"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-[70] flex items-end justify-center p-4 sm:items-center sm:p-6"
        @keydown.escape.window="if (quoteOpen) closeQuote()"
    >
        <button type="button" tabindex="-1" class="absolute inset-0 bg-[#111111]/70 backdrop-blur-sm" aria-label="Mbyll kontaktet" @click="closeQuote()"></button>

        <section
            x-ref="quotePanel"
            role="dialog"
            aria-modal="true"
            aria-labelledby="quote-contact-title"
            aria-describedby="quote-contact-description"
            class="relative z-10 w-full max-w-xl rounded-lg border border-[#D8D1C6] bg-white p-6 shadow-2xl sm:p-8"
            @keydown.tab="trapQuoteFocus($event)"
        >
            <button
                x-ref="quoteClose"
                type="button"
                class="absolute right-4 top-4 inline-flex h-10 w-10 items-center justify-center rounded-md border border-[#E5E7EB] text-[#111111] transition hover:border-[#C9A14A] hover:text-[#9A712E] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#C9A14A]"
                aria-label="Mbyll dritaren e kontakteve"
                @click="closeQuote()"
            >
                <x-store.icon name="x" class="h-5 w-5" />
            </button>

            <p class="text-sm font-black uppercase tracking-[0.18em] text-[#9A712E]">OFERTA PËR PROJEKTE</p>
            <h2 id="quote-contact-title" class="mt-3 pr-12 text-2xl font-black text-[#111111] sm:text-3xl">Kontaktoni për ofertë</h2>
            <p id="quote-contact-description" class="mt-4 text-sm font-semibold leading-7 text-[#6B7280] sm:text-base">
                Për oferta për projekte ose porosi në sasi të mëdha, na kontaktoni direkt në njërin nga numrat më poshtë ose përmes emailit.
            </p>

            <div class="mt-7 grid gap-4 sm:grid-cols-2">
                <article data-contact-option class="rounded-lg border border-[#E5E7EB] bg-[#F7F6F3] p-5">
                    <p class="text-xs font-black uppercase tracking-[0.16em] text-[#9A712E]">Kontakti 1</p>
                    <p class="mt-3 text-xl font-black text-[#111111]">+383 49 240 360</p>
                    <a href="tel:+38349240360" class="btn-primary mt-5 inline-flex w-full items-center justify-center gap-2">
                        Telefono
                        <x-store.icon name="arrow-right" class="h-4 w-4" />
                    </a>
                </article>

                <article data-contact-option class="rounded-lg border border-[#E5E7EB] bg-[#F7F6F3] p-5">
                    <p class="text-xs font-black uppercase tracking-[0.16em] text-[#9A712E]">Kontakti 2</p>
                    <p class="mt-3 text-xl font-black text-[#111111]">+383 49 535 362</p>
                    <a href="tel:+38349535362" class="btn-primary mt-5 inline-flex w-full items-center justify-center gap-2">
                        Telefono
                        <x-store.icon name="arrow-right" class="h-4 w-4" />
                    </a>
                </article>

                <article data-contact-option class="min-w-0 rounded-lg border border-[#E5E7EB] bg-[#F7F6F3] p-5 sm:col-span-2">
                    <p class="text-xs font-black uppercase tracking-[0.16em] text-[#9A712E]">Email</p>
                    <p class="mt-3 break-all text-xl font-black text-[#111111]">info@denatashop.com</p>
                    <a href="mailto:info@denatashop.com" class="btn-primary mt-5 inline-flex w-full items-center justify-center gap-2">
                        Dërgo email
                        <x-store.icon name="arrow-right" class="h-4 w-4" />
                    </a>
                </article>
            </div>
        </section>
    </div>
</div>
@endsection
