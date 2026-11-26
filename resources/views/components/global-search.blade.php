<!-- Global Quick Command / Search Modal (Ctrl+K or /) -->
<div 
    x-data="{
        open: false,
        query: '',
        activeIndex: 0,
        items: [
            @if(auth()->check() && auth()->user()->role === 'admin')
                { title: '{{ __('Dashboard') }}', category: '{{ __('Navigasi') }}', icon: 'fa-solid fa-chart-pie', url: '{{ route('admin.dashboard') }}' },
                { title: '{{ __('Daftar Pesanan') }}', category: '{{ __('Pesanan') }}', icon: 'fa-solid fa-cart-shopping', url: '{{ route('admin.orders.index') }}' },
                { title: '{{ __('Peta Pengantaran') }}', category: '{{ __('Pengantaran') }}', icon: 'fa-solid fa-map-location-dot', url: '{{ route('admin.deliveries.map') }}' },
                { title: '{{ __('Katalog Produk & Stok') }}', category: '{{ __('Produk') }}', icon: 'fa-solid fa-bottle-water', url: '{{ route('admin.products.index') }}' },
                { title: '{{ __('Tambah Produk Baru') }}', category: '{{ __('Produk') }}', icon: 'fa-solid fa-plus', url: '{{ route('admin.products.create') }}' },
                { title: '{{ __('Laporan Keuangan & Biaya') }}', category: '{{ __('Keuangan') }}', icon: 'fa-solid fa-receipt', url: '{{ route('admin.finance.index') }}' },
                { title: '{{ __('Kelola Pengguna') }}', category: '{{ __('Pengguna') }}', icon: 'fa-solid fa-users', url: '{{ route('admin.users.index') }}' },
                { title: '{{ __('Area Layanan Pengantaran') }}', category: '{{ __('Pengaturan') }}', icon: 'fa-solid fa-draw-polygon', url: '{{ route('admin.service-areas.index') }}' },
                { title: '{{ __('Profil & Pengaturan Akun') }}', category: '{{ __('Akun') }}', icon: 'fa-solid fa-user-gear', url: '{{ route('admin.profile') }}' },
            @elseif(auth()->check())
                { title: '{{ __('Dashboard Member') }}', category: '{{ __('Navigasi') }}', icon: 'fa-solid fa-house', url: '{{ route('customer.dashboard') }}' },
                { title: '{{ __('Riwayat Pesanan Saya') }}', category: '{{ __('Pesanan') }}', icon: 'fa-solid fa-clock-rotate-left', url: '{{ route('customer.orders.index') }}' },
                { title: '{{ __('Buku Alamat Pengantaran') }}', category: '{{ __('Alamat') }}', icon: 'fa-solid fa-location-dot', url: '{{ route('customer.addresses.index') }}' },
                { title: '{{ __('Profil & Lokasi Depot Nova Jaya') }}', category: '{{ __('Informasi') }}', icon: 'fa-solid fa-store', url: '{{ route('customer.store-profile') }}' },
                { title: '{{ __('Pengaturan Akun & Profil') }}', category: '{{ __('Akun') }}', icon: 'fa-solid fa-user-gear', url: '{{ route('customer.profile') }}' },
            @else
                { title: '{{ __('Beranda') }}', category: '{{ __('Navigasi') }}', icon: 'fa-solid fa-house', url: '/' },
                { title: '{{ __('Masuk ke Akun') }}', category: '{{ __('Autentikasi') }}', icon: 'fa-solid fa-right-to-bracket', url: '{{ route('login') }}' },
                { title: '{{ __('Daftar Akun Baru') }}', category: '{{ __('Autentikasi') }}', icon: 'fa-solid fa-user-plus', url: '{{ route('register') }}' },
            @endif
        ],
        get filteredItems() {
            if (!this.query.trim()) return this.items;
            const q = this.query.toLowerCase();
            return this.items.filter(item => 
                item.title.toLowerCase().includes(q) || 
                item.category.toLowerCase().includes(q)
            );
        },
        openSearch() {
            this.open = true;
            this.query = '';
            this.activeIndex = 0;
            this.$nextTick(() => {
                if (this.$refs.searchInput) this.$refs.searchInput.focus();
            });
        },
        closeSearch() {
            this.open = false;
        },
        selectItem(index) {
            const item = this.filteredItems[index];
            if (item) {
                window.location.href = item.url;
            }
        },
        nextItem() {
            if (this.activeIndex < this.filteredItems.length - 1) {
                this.activeIndex++;
            } else {
                this.activeIndex = 0;
            }
        },
        prevItem() {
            if (this.activeIndex > 0) {
                this.activeIndex--;
            } else {
                this.activeIndex = this.filteredItems.length - 1;
            }
        }
    }"
    @open-global-search.window="openSearch()"
    @keydown.window.prevent.ctrl.k="openSearch()"
    @keydown.window.prevent.cmd.k="openSearch()"
    @keydown.window="/"="
        if (!['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName) && !open) {
            $event.preventDefault();
            openSearch();
        }
    "
    @keydown.escape.window="closeSearch()"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto p-4 sm:p-6 md:p-20 flex items-start justify-center"
    style="display: none;"
>
    <!-- Backdrop -->
    <div 
        x-show="open"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
        @click="closeSearch()"
    ></div>

    <!-- Modal Box -->
    <div 
        x-show="open"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="relative w-full max-w-xl rounded-3xl bg-white dark:bg-slate-900 shadow-2xl border border-slate-100 dark:border-slate-800 overflow-hidden"
        @click.stop
    >
        <!-- Search Input Bar -->
        <div class="relative flex items-center border-b border-slate-100 dark:border-slate-800 px-4">
            <i class="fa-solid fa-magnifying-glass text-slate-400 text-sm mr-3"></i>
            <input 
                x-ref="searchInput"
                x-model="query"
                @keydown.arrow-down.prevent="nextItem()"
                @keydown.arrow-up.prevent="prevItem()"
                @keydown.enter.prevent="selectItem(activeIndex)"
                type="text" 
                placeholder="{{ __('Cari menu, pesanan, halaman, atau pintasan...') }}"
                class="w-full bg-transparent py-4 text-sm font-medium text-slate-900 dark:text-white placeholder-slate-400 focus:outline-hidden"
            />
            <kbd class="hidden sm:inline-block px-2 py-0.5 text-[10px] font-bold text-slate-400 bg-slate-100 dark:bg-slate-800 rounded-md border border-slate-200 dark:border-slate-700">ESC</kbd>
        </div>

        <!-- Results List -->
        <div class="max-h-80 overflow-y-auto p-2 space-y-1">
            <template x-for="(item, index) in filteredItems" :key="index">
                <a 
                    :href="item.url" 
                    @mouseenter="activeIndex = index"
                    :class="activeIndex === index ? 'bg-cyan-50 dark:bg-cyan-950/60 text-cyan-900 dark:text-cyan-200' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60'"
                    class="flex items-center justify-between p-3 rounded-2xl transition cursor-pointer"
                >
                    <div class="flex items-center space-x-3">
                        <div 
                            :class="activeIndex === index ? 'bg-cyan-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400'"
                            class="w-9 h-9 rounded-xl flex items-center justify-center text-sm shrink-0 transition"
                        >
                            <i :class="item.icon"></i>
                        </div>
                        <div>
                            <div class="text-xs sm:text-sm font-bold" x-text="item.title"></div>
                            <div class="text-[10px] text-slate-400 uppercase tracking-wider mt-0.5" x-text="item.category"></div>
                        </div>
                    </div>
                    <i class="fa-solid fa-arrow-right text-xs opacity-0" :class="activeIndex === index ? 'opacity-100 text-cyan-600 dark:text-cyan-400' : ''"></i>
                </a>
            </template>

            <div x-show="filteredItems.length === 0" class="py-10 text-center text-xs text-slate-400">
                <i class="fa-solid fa-magnifying-glass text-2xl mb-2 text-slate-300 dark:text-slate-600 block"></i>
                {{ __('Tidak ada hasil yang cocok dengan pencarian.') }}
            </div>
        </div>

        <!-- Footer Shortcuts Info -->
        <div class="bg-slate-50 dark:bg-slate-950/60 border-t border-slate-100 dark:border-slate-800 px-4 py-2.5 flex items-center justify-between text-[11px] text-slate-400">
            <div class="flex items-center space-x-3">
                <span><kbd class="font-mono bg-white dark:bg-slate-800 px-1 py-0.5 rounded border border-slate-200 dark:border-slate-700">&uarr;&darr;</kbd> {{ __('Navigasi') }}</span>
                <span><kbd class="font-mono bg-white dark:bg-slate-800 px-1 py-0.5 rounded border border-slate-200 dark:border-slate-700">&crarr;</kbd> {{ __('Pilih') }}</span>
            </div>
            <span><kbd class="font-mono bg-white dark:bg-slate-800 px-1 py-0.5 rounded border border-slate-200 dark:border-slate-700">Ctrl</kbd> + <kbd class="font-mono bg-white dark:bg-slate-800 px-1 py-0.5 rounded border border-slate-200 dark:border-slate-700">K</kbd></span>
        </div>
    </div>
</div>
