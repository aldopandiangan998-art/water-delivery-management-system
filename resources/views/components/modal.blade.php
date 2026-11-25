@props([
    'name',
    'title' => null,
    'maxWidth' => 'md',
])

@php
    $maxWidthClass = match ($maxWidth) {
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
        default => 'sm:max-w-md',
    };
@endphp

<div 
    x-data="{ show: false }"
    x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
    x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
    x-on:keydown.escape.window="show = false"
    x-show="show"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0"
    style="display: none;">
    
    <!-- Backdrop -->
    <div 
        x-show="show" 
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
        @click="show = false">
    </div>

    <!-- Dialog Container -->
    <div 
        x-show="show" 
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="mb-6 bg-white dark:bg-slate-900 rounded-3xl overflow-hidden shadow-2xl transform transition-all sm:w-full {{ $maxWidthClass }} sm:mx-auto border border-slate-100 dark:border-slate-800">
        
        @if ($title)
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight">
                    {{ $title }}
                </h3>
                <button type="button" @click="show = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
        @endif

        <div class="p-6">
            {{ $slot }}
        </div>
    </div>
</div>
