@props([
    'align' => 'right',
    'width' => '48',
    'contentClasses' => 'py-1 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700',
])

@php
    $alignmentClasses = match ($align) {
        'left' => 'left-0 origin-top-left',
        'top' => 'top-0 origin-top',
        default => 'right-0 origin-top-right',
    };

    $widthClass = match ($width) {
        '48' => 'w-48',
        '56' => 'w-56',
        '64' => 'w-64',
        default => 'w-48',
    };
@endphp

<div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
    <div @click="open = ! open">
        {{ $trigger }}
    </div>

    <div x-show="open"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute z-50 mt-2 {{ $widthClass }} rounded-2xl shadow-xl {{ $alignmentClasses }} {{ $contentClasses }}"
         style="display: none;"
         @click="open = false">
        {{ $content }}
    </div>
</div>
