@props([
    'model' => 'viewMode',
    'gridLabel' => __('Grid'),
    'listLabel' => __('List'),
])

<div class="inline-flex items-center p-1 bg-slate-100 dark:bg-slate-800/80 rounded-xl border border-slate-200 dark:border-slate-700 space-x-1 shrink-0">
    <button type="button" 
            @click="{{ $model }} = 'grid'" 
            :class="{{ $model }} === 'grid' ? 'bg-white dark:bg-slate-900 text-cyan-600 dark:text-cyan-400 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200'"
            class="px-2.5 py-1 rounded-lg text-xs flex items-center space-x-1.5 transition cursor-pointer"
            title="{{ $gridLabel }}">
        <i class="fa-solid fa-table-cells text-xs"></i>
        <span class="hidden sm:inline">{{ $gridLabel }}</span>
    </button>
    <button type="button" 
            @click="{{ $model }} = 'list'" 
            :class="{{ $model }} === 'list' ? 'bg-white dark:bg-slate-900 text-cyan-600 dark:text-cyan-400 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200'"
            class="px-2.5 py-1 rounded-lg text-xs flex items-center space-x-1.5 transition cursor-pointer"
            title="{{ $listLabel }}">
        <i class="fa-solid fa-list text-xs"></i>
        <span class="hidden sm:inline">{{ $listLabel }}</span>
    </button>
</div>
