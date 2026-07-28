@props(['title', 'description', 'href', 'icon' => 'home', 'count' => null, 'tone' => 'dark', 'featured' => false])

<a href="{{ $href }}"
   {{ $attributes->merge(['class' => ($featured ? 'md:col-span-2 ' : '') . 'group relative overflow-hidden rounded-lg border border-[#E5E1DA] bg-white p-5 transition duration-200 hover:-translate-y-0.5 hover:border-[#B88A3B] hover:shadow-[0_18px_45px_rgba(21,24,27,0.10)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#B88A3B]']) }}>
    <div class="absolute -right-10 -top-10 h-28 w-28 rotate-45 border border-[#E5E1DA] bg-[#F7F5F1] transition group-hover:border-[#B88A3B]"></div>
    <div class="relative flex h-full flex-col gap-5">
        <div class="flex items-start justify-between gap-4">
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-md bg-[#15181B] text-[#B88A3B]">
                <x-store.icon :name="$icon" class="h-6 w-6" />
            </span>
            @if(! is_null($count))
                <span class="rounded-full border border-[#E5E1DA] px-3 py-1 text-xs font-bold text-[#6B6F74]">{{ $count }} produkte</span>
            @endif
        </div>
        <div>
            <h3 class="text-xl font-black text-[#17191C]">{{ $title }}</h3>
            <p class="mt-2 max-w-sm text-sm leading-6 text-[#6B6F74]">{{ $description }}</p>
        </div>
        <span class="mt-auto inline-flex items-center gap-2 text-sm font-bold text-[#9A712E]">
            Eksploro
            <x-store.icon name="arrow-right" class="h-4 w-4 transition group-hover:translate-x-1" />
        </span>
    </div>
</a>
