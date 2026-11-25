@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'icon' => null,
    'iconRight' => null,
    'href' => null,
    'loading' => false,
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-bold tracking-tight transition-all duration-200 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed focus:outline-hidden focus:ring-2 focus:ring-offset-2 select-none active:scale-[0.98]';

    $sizeClasses = match($size) {
        'sm' => 'px-3 py-1.5 text-xs rounded-lg min-h-[36px]',
        'lg' => 'px-6 py-3.5 text-base rounded-2xl min-h-[48px]',
        default => 'px-4 py-2.5 text-xs sm:text-sm rounded-xl min-h-[44px]',
    };

    $variantClasses = match($variant) {
        'secondary' => 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700 focus:ring-slate-400',
        'ghost' => 'bg-transparent hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 border border-transparent focus:ring-slate-400',
        'danger' => 'bg-rose-600 hover:bg-rose-700 text-white shadow-xs focus:ring-rose-500 shadow-rose-600/20',
        'success' => 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs focus:ring-emerald-500 shadow-emerald-600/20',
        default => 'bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white shadow-xs focus:ring-cyan-500 shadow-cyan-600/20',
    };

    $classes = "{$baseClasses} {$sizeClasses} {$variantClasses}";
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            <i class="{{ $icon }} mr-2 shrink-0"></i>
        @endif
        {{ $slot }}
        @if ($iconRight)
            <i class="{{ $iconRight }} ml-2 shrink-0"></i>
        @endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            <i class="{{ $icon }} mr-2 shrink-0"></i>
        @endif
        {{ $slot }}
        @if ($iconRight)
            <i class="{{ $iconRight }} ml-2 shrink-0"></i>
        @endif
    </button>
@endif
