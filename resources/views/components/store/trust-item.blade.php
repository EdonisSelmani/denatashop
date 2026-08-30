@props(['icon', 'title', 'text'])

<div {{ $attributes->merge(['class' => 'rounded-lg border border-[#E1D9CB] bg-white p-5 shadow-[0_10px_30px_rgba(21,24,27,0.04)]']) }}>
    <div class="flex items-start gap-4">
        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-md bg-[#15181B] text-[#D7B16D]">
            <x-store.icon :name="$icon" class="h-5 w-5" />
        </span>
        <div>
            <h3 class="font-black text-[#17191C]">{{ $title }}</h3>
            <p class="mt-1 text-sm leading-6 text-[#6B6F74]">{{ $text }}</p>
        </div>
    </div>
</div>
