@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-md border border-[#D8D1C6] bg-white px-4 py-3 text-sm font-semibold text-[#17191C] shadow-sm transition placeholder:text-[#8A8177] focus:border-[#B88A3B] focus:outline-none focus:ring-2 focus:ring-[#B88A3B]/20 disabled:opacity-60']) }}>
