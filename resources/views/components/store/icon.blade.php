@props(['name', 'class' => 'h-5 w-5'])

@switch($name)
    @case('search')
        <svg {{ $attributes->merge(['class' => $class]) }} fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 21-4.35-4.35m1.1-5.4a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z"/></svg>
        @break
    @case('user')
        <svg {{ $attributes->merge(['class' => $class]) }} fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0"/></svg>
        @break
    @case('heart')
        <svg {{ $attributes->merge(['class' => $class]) }} fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20.1 5.9a5.1 5.1 0 0 0-7.2 0l-.9.9-.9-.9a5.1 5.1 0 0 0-7.2 7.2l.9.9L12 21.2l7.2-7.2.9-.9a5.1 5.1 0 0 0 0-7.2Z"/></svg>
        @break
    @case('cart')
        <svg {{ $attributes->merge(['class' => $class]) }} fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 4.5h2l1.4 10.15a2 2 0 0 0 2 1.7h7.7a2 2 0 0 0 1.95-1.55l1.1-5.05H7.1M10 20a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm8 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
        @break
    @case('menu')
        <svg {{ $attributes->merge(['class' => $class]) }} fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16M4 12h16M4 17h16"/></svg>
        @break
    @case('x')
        <svg {{ $attributes->merge(['class' => $class]) }} fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 6 12 12M18 6 6 18"/></svg>
        @break
    @case('chevron-down')
        <svg {{ $attributes->merge(['class' => $class]) }} fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 9 6 6 6-6"/></svg>
        @break
    @case('arrow-right')
        <svg {{ $attributes->merge(['class' => $class]) }} fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 12h14m-6-6 6 6-6 6"/></svg>
        @break
    @case('filter')
        <svg {{ $attributes->merge(['class' => $class]) }} fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M7 12h10m-7 6h4"/></svg>
        @break
    @case('trash')
        <svg {{ $attributes->merge(['class' => $class]) }} fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 7h14m-9 4v6m4-6v6M9 7V5h6v2m-8 0 1 13h8l1-13"/></svg>
        @break
    @case('minus')
        <svg {{ $attributes->merge(['class' => $class]) }} fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 12h12"/></svg>
        @break
    @case('plus')
        <svg {{ $attributes->merge(['class' => $class]) }} fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v12m-6-6h12"/></svg>
        @break
    @case('check')
        <svg {{ $attributes->merge(['class' => $class]) }} fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m5 13 4 4L19 7"/></svg>
        @break
    @case('shield')
        <svg {{ $attributes->merge(['class' => $class]) }} fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3.5 19 6v5.5c0 4.4-2.8 7.55-7 9-4.2-1.45-7-4.6-7-9V6l7-2.5Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m8.75 12.25 2.2 2.2 4.6-5"/></svg>
        @break
    @case('truck')
        <svg {{ $attributes->merge(['class' => $class]) }} fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.5 6.5h11v9h-11v-9Zm11 3h3.2l2.8 3v3h-6v-6Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 18a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Zm9 0a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z"/></svg>
        @break
    @case('lock')
        <svg {{ $attributes->merge(['class' => $class]) }} fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 10V7.75a5 5 0 0 1 10 0V10m-11 0h12v10H6V10Z"/></svg>
        @break
    @case('headset')
        <svg {{ $attributes->merge(['class' => $class]) }} fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 13v-1a8 8 0 1 1 16 0v1M6 17H5a2 2 0 0 1-2-2v-1a2 2 0 0 1 2-2h1v5Zm12 0h1a2 2 0 0 0 2-2v-1a2 2 0 0 0-2-2h-1v5Zm0 0c0 2-1.5 3-4.5 3"/></svg>
        @break
    @case('tap')
        <svg {{ $attributes->merge(['class' => $class]) }} fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 6h6m-3-3v6m-5 3h7a4 4 0 0 1 4 4v2h-4v-2a1 1 0 0 0-1-1H6v3H3v-6h3Zm12 1h3v5h-3z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 20c0 1-.7 1.8-1.5 1.8S6 21 6 20c0-.9 1.5-2.7 1.5-2.7S9 19.1 9 20Z"/></svg>
        @break
    @case('wrench')
        <svg {{ $attributes->merge(['class' => $class]) }} fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.7 6.3a4.8 4.8 0 0 0 5.4 6.4L11 21.8 6.2 17l9.1-9.1a4.8 4.8 0 0 1-.6-1.6Z"/></svg>
        @break
    @case('leaf')
        <svg {{ $attributes->merge(['class' => $class]) }} fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 19c9.5 1.5 14-4.5 14-14-8.5.5-14 5-14 14Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 19c2.5-4.5 5.8-7.7 10-10"/></svg>
        @break
    @case('bolt')
        <svg {{ $attributes->merge(['class' => $class]) }} fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 2 4.5 13h6L9 22l10.5-13h-6L13 2Z"/></svg>
        @break
    @case('battery')
        <svg {{ $attributes->merge(['class' => $class]) }} fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 8h14v8H4V8Zm14 2h2v4h-2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m9.5 14 1-2H9l1.5-2"/></svg>
        @break
    @case('home')
        <svg {{ $attributes->merge(['class' => $class]) }} fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m3.5 11.5 8.5-7 8.5 7M6 10v10h12V10M10 20v-5h4v5"/></svg>
        @break
    @case('package')
        <svg {{ $attributes->merge(['class' => $class]) }} fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m12 3 8 4.4v9.2L12 21l-8-4.4V7.4L12 3Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m4.5 7.6 7.5 4.1 7.5-4.1M12 21v-9.3"/></svg>
        @break
    @default
        <svg {{ $attributes->merge(['class' => $class]) }} fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16M4 12h16M4 17h16"/></svg>
@endswitch
