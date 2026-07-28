<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center gap-2 rounded-md border border-[#D8D1C6] bg-white px-4 py-2 text-sm font-black text-[#15181B] transition hover:border-[#B88A3B] hover:text-[#9A712E] focus:outline-none focus:ring-2 focus:ring-[#B88A3B] focus:ring-offset-2 disabled:opacity-60']) }}>
    {{ $slot }}
</button>
