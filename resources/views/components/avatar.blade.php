@props([
    'name' => '',
    'size' => 'md',
    'src' => null,
])

@php
    $sizeClasses = match($size) {
        'sm' => 'w-8 h-8 text-xs',
        'lg' => 'w-14 h-14 text-xl',
        default => 'w-10 h-10 text-sm',
    };
    $initial = strtoupper(substr($name ?: 'U', 0, 1));
@endphp

<div {{ $attributes->merge(['class' => "rounded-2xl bg-gradient-to-tr from-cyan-600 to-blue-600 text-white font-black flex items-center justify-center shrink-0 shadow-2xs select-none {$sizeClasses}"]) }}>
    @if ($src)
        <img src="{{ $src }}" alt="{{ $name }}" class="w-full h-full object-cover rounded-2xl">
    @else
        <span>{{ $initial }}</span>
    @endif
</div>
