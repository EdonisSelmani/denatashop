@props(['title', 'description' => null, 'href', 'icon' => 'home', 'image' => null, 'count' => null, 'tone' => 'light', 'featured' => false])

<a href="{{ $href }}"
   {{ $attributes->merge(['class' => 'group flex min-h-[106px] items-center gap-3 overflow-hidden rounded-xl border border-[#E5E7EB] bg-white p-3 shadow-[0_8px_22px_rgba(17,17,17,0.035)] transition duration-200 hover:-translate-y-0.5 hover:border-[#C9A14A] hover:shadow-[0_14px_30px_rgba(17,17,17,0.07)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#C9A14A]']) }}>
    <div class="flex h-20 w-[38%] shrink-0 items-center justify-center rounded-lg bg-[#F7F6F3]">
        @if($image)
            <img src="{{ $image }}" alt="{{ $title }}" width="112" height="88" loading="lazy" class="h-full w-full object-contain p-1.5 transition duration-200 group-hover:scale-[1.03]">
        @else
            <x-store.icon :name="$icon" class="h-8 w-8 text-[#C9A14A]" />
        @endif
    </div>

    <div class="min-w-0 flex-1">
        <h3 class="truncate text-sm font-black text-[#111111] xl:text-base">{{ $title }}</h3>
        <span class="mt-2 inline-flex max-w-full items-center gap-1.5 whitespace-nowrap text-xs font-bold text-[#111111]">
            Shiko më shumë
            <x-store.icon name="arrow-right" class="h-3.5 w-3.5 shrink-0 text-[#9A712E] transition group-hover:translate-x-1" />
        </span>
    </div>
</a>
