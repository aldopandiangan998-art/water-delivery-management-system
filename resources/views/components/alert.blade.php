@props([
    'variant' => 'info',
    'title' => null,
    'dismissible' => false,
])

@php
    $variantConfig = match($variant) {
        'success' => [
            'bg' => 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200',
            'icon' => 'fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400',
        ],
        'danger', 'error' => [
            'bg' => 'bg-rose-50 dark:bg-rose-950/40 border-rose-200 dark:border-rose-800 text-rose-900 dark:text-rose-200',
            'icon' => 'fa-solid fa-circle-exclamation text-rose-600 dark:text-rose-400',
        ],
        'warning' => [
            'bg' => 'bg-amber-50 dark:bg-amber-950/40 border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-200',
            'icon' => 'fa-solid fa-triangle-exclamation text-amber-600 dark:text-amber-400',
        ],
        default => [
            'bg' => 'bg-sky-50 dark:bg-sky-950/40 border-sky-200 dark:border-sky-800 text-sky-900 dark:text-sky-200',
            'icon' => 'fa-solid fa-circle-info text-sky-600 dark:text-sky-400',
        ],
    };
@endphp

<div x-data="{ show: true }" x-show="show" {{ $attributes->merge(['class' => "p-4 rounded-2xl border flex items-start gap-3 text-xs sm:text-sm {$variantConfig['bg']}"]) }}>
    <i class="{{ $variantConfig['icon'] }} text-base mt-0.5 shrink-0"></i>
    <div class="flex-1">
        @if ($title)
            <div class="font-bold mb-0.5">{{ $title }}</div>
        @endif
        <div class="leading-relaxed">{{ $slot }}</div>
    </div>
    @if ($dismissible)
        <button type="button" @click="show = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition">
            <i class="fa-solid fa-xmark text-sm"></i>
        </button>
    @endif
</div>
