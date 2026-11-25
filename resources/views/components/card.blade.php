@props([
    'title' => null,
    'subtitle' => null,
    'action' => null,
    'footer' => null,
    'padding' => 'p-5 sm:p-6',
])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xs overflow-hidden transition-all duration-200']) }}>
    @if ($title || $subtitle || $action)
        <div class="px-5 py-4 sm:px-6 sm:py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between gap-4">
            <div>
                @if ($title)
                    <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white tracking-tight">
                        {{ $title }}
                    </h3>
                @endif
                @if ($subtitle)
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        {{ $subtitle }}
                    </p>
                @endif
            </div>
            @if ($action)
                <div class="shrink-0">
                    {{ $action }}
                </div>
            @endif
        </div>
    @endif

    <div class="{{ $padding }}">
        {{ $slot }}
    </div>

    @if ($footer)
        <div class="px-5 py-3.5 sm:px-6 bg-slate-50/70 dark:bg-slate-950/50 border-t border-slate-100 dark:border-slate-800">
            {{ $footer }}
        </div>
    @endif
</div>
