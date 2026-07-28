@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-black text-[#22272B]']) }}>
    {{ $value ?? $slot }}
</label>
