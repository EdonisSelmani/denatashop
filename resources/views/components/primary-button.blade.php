<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 rounded-md border border-transparent bg-[#15181B] px-4 py-2 text-sm font-black text-white transition hover:bg-[#9A712E] focus:outline-none focus:ring-2 focus:ring-[#B88A3B] focus:ring-offset-2 disabled:opacity-60']) }}>
    {{ $slot }}
</button>
