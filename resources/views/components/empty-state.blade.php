@props([
    'icon' => 'fa-solid fa-inbox',
    'title' => 'Belum Ada Data',
    'description' => 'Data untuk kategori atau halaman ini belum tersedia saat ini.',
    'action' => null,
])

<div {{ $attributes->merge(['class' => 'p-8 sm:p-12 text-center rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-900/50 flex flex-col items-center justify-center space-y-3']) }}>
    <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 flex items-center justify-center text-2xl">
        <i class="{{ $icon }}"></i>
    </div>
    <div class="max-w-sm space-y-1">
        <h4 class="text-base font-bold text-slate-800 dark:text-slate-200">
            {{ $title }}
        </h4>
        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
            {{ $description }}
        </p>
    </div>
    @if ($action)
        <div class="pt-2">
            {{ $action }}
        </div>
    @endif
</div>
