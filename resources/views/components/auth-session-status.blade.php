@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'text-sm font-semibold text-[#25865A]']) }}>
        {{ $status }}
    </div>
@endif
