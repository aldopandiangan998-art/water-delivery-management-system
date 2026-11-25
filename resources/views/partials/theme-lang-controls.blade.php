@php
    $currentLocale = app()->getLocale();
@endphp

<div class="flex items-center space-x-2" x-data="themeSwitcher()">
    <!-- Language Selector Dropdown -->
    <div class="relative" x-data="{ langOpen: false }">
        <button type="button" 
                @click="langOpen = !langOpen" 
                @click.away="langOpen = false"
                class="px-2.5 py-1.5 rounded-xl text-xs font-bold border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition flex items-center shadow-2xs cursor-pointer"
                title="{{ __('Ganti Bahasa') }}">
            <i class="fa-solid fa-globe mr-1.5 text-cyan-600 dark:text-cyan-400 text-xs"></i>
            <span class="uppercase tracking-wider font-extrabold">{{ $currentLocale }}</span>
            <i class="fa-solid fa-chevron-down ml-1 text-[10px] text-slate-400 dark:text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': langOpen }"></i>
        </button>

        <div x-show="langOpen" 
             x-cloak
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 translate-y-1 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-1 scale-95"
             class="absolute right-0 mt-2 w-36 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl py-1.5 z-50 text-xs font-semibold overflow-hidden">
            <a href="{{ route('language.switch', ['locale' => 'id', 'return' => url()->full()]) }}" 
               class="flex items-center justify-between px-3.5 py-2 hover:bg-cyan-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 {{ $currentLocale === 'id' ? 'font-bold text-cyan-600 dark:text-cyan-400 bg-cyan-50/50 dark:bg-slate-700/60' : '' }} transition">
                <span class="flex items-center">
                    <span class="mr-2 text-sm">🇮🇩</span> Indonesia
                </span>
                @if ($currentLocale === 'id')
                    <i class="fa-solid fa-check text-cyan-600 dark:text-cyan-400 text-xs"></i>
                @endif
            </a>
            <a href="{{ route('language.switch', ['locale' => 'en', 'return' => url()->full()]) }}" 
               class="flex items-center justify-between px-3.5 py-2 hover:bg-cyan-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 {{ $currentLocale === 'en' ? 'font-bold text-cyan-600 dark:text-cyan-400 bg-cyan-50/50 dark:bg-slate-700/60' : '' }} transition">
                <span class="flex items-center">
                    <span class="mr-2 text-sm">🇬🇧</span> English
                </span>
                @if ($currentLocale === 'en')
                    <i class="fa-solid fa-check text-cyan-600 dark:text-cyan-400 text-xs"></i>
                @endif
            </a>
        </div>
    </div>

    <!-- Dark / Light Mode Toggle Button -->
    <button type="button" 
            @click="toggleTheme()" 
            class="p-2 rounded-xl text-xs font-bold border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition flex items-center justify-center cursor-pointer shadow-2xs group"
            :title="isDark ? '{{ __('Mode Terang') }}' : '{{ __('Mode Gelap') }}'">
        <i x-show="!isDark" class="fa-solid fa-moon text-indigo-600 text-sm group-hover:scale-110 transition-transform"></i>
        <i x-show="isDark" x-cloak class="fa-solid fa-sun text-amber-400 text-sm group-hover:scale-110 transition-transform"></i>
    </button>
</div>

@once
<script>
function themeSwitcher() {
    return {
        isDark: document.documentElement.classList.contains('dark'),
        init() {
            this.isDark = document.documentElement.classList.contains('dark');
            window.addEventListener('theme-changed', (e) => {
                this.isDark = e.detail.isDark;
            });
        },
        toggleTheme() {
            this.isDark = !document.documentElement.classList.contains('dark');
            if (this.isDark) {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            }
            window.dispatchEvent(new CustomEvent('theme-changed', { detail: { isDark: this.isDark } }));
        }
    };
}
</script>
@endonce
