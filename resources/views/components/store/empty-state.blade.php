@props(['icon' => 'package', 'title', 'text' => null, 'action' => null, 'href' => null])

<div {{ $attributes->merge(['class' => 'rounded-lg border border-dashed border-[#D8D1C6] bg-white p-10 text-center']) }}>
    <div class="mx-auto inline-flex h-14 w-14 items-center justify-center rounded-md bg-[#F7F5F1] text-[#B88A3B]">
        <x-store.icon :name="$icon" class="h-7 w-7" />
    </div>
    <h2 class="mt-5 text-xl font-black text-[#17191C]">{{ $title }}</h2>
    @if($text)
        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-[#6B6F74]">{{ $text }}</p>
    @endif
    @if($action && $href)
        <a href="{{ $href }}" class="mt-6 inline-flex items-center justify-center rounded-md bg-[#15181B] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#B88A3B] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#B88A3B]">
            {{ $action }}
        </a>
    @endif
</div>
