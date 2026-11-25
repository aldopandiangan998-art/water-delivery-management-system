@props([
    'variant' => 'neutral',
    'size' => 'md',
    'dot' => false,
])

@php
    $variantClasses = match($variant) {
        'primary' => 'bg-cyan-50 dark:bg-cyan-950/50 text-cyan-700 dark:text-cyan-300 border-cyan-200 dark:border-cyan-800',
        'success' => 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
        'warning' => 'bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800',
        'danger' => 'bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800',
        'info' => 'bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800',
        default => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700',
    };

    $sizeClasses = match($size) {
        'sm' => 'px-2 py-0.5 text-[9px]',
        'lg' => 'px-3 py-1 text-xs',
        default => 'px-2.5 py-0.5 text-[10px]',
    };

    $dotColor = match($variant) {
        'primary' => 'bg-cyan-500',
        'success' => 'bg-emerald-500',
        'warning' => 'bg-amber-500',
        'danger' => 'bg-rose-500',
        'info' => 'bg-blue-500',
        default => 'bg-slate-400',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center font-bold uppercase tracking-wider rounded-full border {$variantClasses} {$sizeClasses}"]) }}>
    @if ($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }} mr-1.5 animate-pulse"></span>
    @endif
    {{ $slot }}
</span>
