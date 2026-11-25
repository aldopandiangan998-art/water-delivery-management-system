@props([
    'type' => 'card', // 'card', 'table', 'text'
])

@if ($type === 'table')
    <div class="animate-pulse space-y-3 p-4">
        <div class="h-6 bg-slate-200 dark:bg-slate-800 rounded-lg w-1/4"></div>
        <div class="space-y-2">
            <div class="h-10 bg-slate-100 dark:bg-slate-800/60 rounded-xl"></div>
            <div class="h-10 bg-slate-100 dark:bg-slate-800/60 rounded-xl"></div>
            <div class="h-10 bg-slate-100 dark:bg-slate-800/60 rounded-xl"></div>
        </div>
    </div>
@elseif ($type === 'text')
    <div class="animate-pulse space-y-2">
        <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded-md w-3/4"></div>
        <div class="h-3 bg-slate-100 dark:bg-slate-800/60 rounded-md w-1/2"></div>
    </div>
@else
    <div class="animate-pulse p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 space-y-4">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-slate-200 dark:bg-slate-800"></div>
            <div class="flex-1 space-y-2">
                <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded-md w-1/3"></div>
                <div class="h-3 bg-slate-100 dark:bg-slate-800/60 rounded-md w-1/4"></div>
            </div>
        </div>
        <div class="h-20 bg-slate-100 dark:bg-slate-800/40 rounded-xl"></div>
    </div>
@endif
