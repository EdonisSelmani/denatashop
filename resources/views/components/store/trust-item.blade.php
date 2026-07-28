@props(['icon', 'title', 'text'])

<div {{ $attributes->merge(['class' => 'rounded-lg border border-[#E5E1DA] bg-white p-5']) }}>
    <div class="flex items-start gap-4">
        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-md bg-[#F7F5F1] text-[#B88A3B]">
            <x-store.icon :name="$icon" class="h-5 w-5" />
        </span>
        <div>
            <h3 class="font-black text-[#17191C]">{{ $title }}</h3>
            <p class="mt-1 text-sm leading-6 text-[#6B6F74]">{{ $text }}</p>
        </div>
    </div>
</div>
