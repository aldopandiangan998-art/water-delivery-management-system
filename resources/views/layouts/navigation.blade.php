<nav x-data="{ open: false }" class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Left: Logo & Links -->
            <div class="flex">
                <a href="{{ route('home') }}" class="flex items-center space-x-2">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-cyan-600 to-blue-500 flex items-center justify-center text-white shadow-xs">
                        <i class="fa-solid fa-droplet text-lg"></i>
                    </div>
                    <span class="text-xl font-bold text-slate-900 tracking-tight">Aqua<span class="text-cyan-600">Flow</span></span>
                </a>

                <div class="hidden sm:-my-px sm:ml-8 sm:flex sm:space-x-6 items-center text-sm font-medium">
                    <a href="{{ route('home') }}" class="text-slate-600 hover:text-cyan-600 transition px-1 py-2">Beranda</a>
                    <a href="{{ route('home') }}#produk" class="text-slate-600 hover:text-cyan-600 transition px-1 py-2">Katalog Produk</a>
                    <a href="{{ route('public.service-areas') }}" class="text-slate-600 hover:text-cyan-600 transition px-1 py-2">Area Antar</a>
                    <a href="{{ route('home') }}#testimoni" class="text-slate-600 hover:text-cyan-600 transition px-1 py-2">Ulasan</a>
                    <a href="{{ route('home') }}#faq" class="text-slate-600 hover:text-cyan-600 transition px-1 py-2">FAQ</a>
                </div>
            </div>

            <!-- Right: Actions based on auth -->
            <div class="hidden sm:flex sm:items-center sm:space-x-3">
                @auth
                    @if (Auth::user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-3.5 py-1.5 rounded-lg text-sm font-semibold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition border border-indigo-200">
                            <i class="fa-solid fa-gauge-high mr-1.5"></i> Admin Dashboard
                        </a>
                    @endif

                    <div x-data="{ dropdownOpen: false }" class="relative">
                        <button @click="dropdownOpen = !dropdownOpen" class="flex items-center space-x-2 text-sm font-medium text-slate-700 hover:text-cyan-600 bg-slate-50 hover:bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200 transition">
                            <div class="w-7 h-7 rounded-full bg-cyan-100 text-cyan-700 font-bold flex items-center justify-center text-xs">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <span class="max-w-[120px] truncate">{{ Auth::user()->name }}</span>
                            <i class="fa-solid fa-chevron-down text-xs text-slate-400"></i>
                        </button>

                        <div x-show="dropdownOpen" @click.away="dropdownOpen = false" x-cloak
                             class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-100 py-1.5 z-50">
                            <a href="{{ route('customer.dashboard') }}" class="flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                <i class="fa-solid fa-house mr-2.5 text-slate-400 w-4"></i> Dashboard Saya
                            </a>
                            <a href="{{ route('customer.orders.create') }}" class="flex items-center px-4 py-2 text-sm font-medium text-cyan-600 hover:bg-cyan-50">
                                <i class="fa-solid fa-cart-plus mr-2.5 text-cyan-500 w-4"></i> Pesan Air Sekarang
                            </a>
                            <a href="{{ route('customer.orders.index') }}" class="flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                <i class="fa-solid fa-clock-rotate-left mr-2.5 text-slate-400 w-4"></i> Riwayat Pesanan
                            </a>
                            <a href="{{ route('customer.addresses.index') }}" class="flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                <i class="fa-solid fa-location-dot mr-2.5 text-slate-400 w-4"></i> Alamat Saya
                            </a>
                            <a href="{{ route('customer.profile') }}" class="flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                <i class="fa-solid fa-user-gear mr-2.5 text-slate-400 w-4"></i> Pengaturan Profil
                            </a>
                            <hr class="my-1 border-slate-100">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 text-left">
                                    <i class="fa-solid fa-arrow-right-from-bracket mr-2.5 w-4"></i> Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-700 hover:text-cyan-600 px-3 py-2 transition">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold bg-gradient-to-r from-cyan-600 to-blue-600 text-white shadow-xs hover:from-cyan-500 hover:to-blue-500 transition">
                        Daftar Akun
                    </a>
                @endauth
            </div>

            <!-- Mobile menu button -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = !open" class="inline-flex items-center justify-center p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100">
                    <i :class="open ? 'fa-solid fa-xmark text-lg' : 'fa-solid fa-bars text-lg'"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile menu -->
    <div x-show="open" x-cloak class="sm:hidden border-t border-slate-200 bg-white px-4 pt-2 pb-4 space-y-1">
        <a href="{{ route('home') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-slate-700 hover:bg-slate-50">Beranda</a>
        <a href="{{ route('home') }}#produk" class="block px-3 py-2 rounded-lg text-base font-medium text-slate-700 hover:bg-slate-50">Katalog Produk</a>
        <a href="{{ route('public.service-areas') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-slate-700 hover:bg-slate-50">Area Layanan</a>
        <a href="{{ route('home') }}#testimoni" class="block px-3 py-2 rounded-lg text-base font-medium text-slate-700 hover:bg-slate-50">Ulasan Pelanggan</a>

        <div class="pt-3 border-t border-slate-100">
            @auth
                <div class="px-3 py-2 text-xs font-semibold text-slate-400 uppercase">Akun: {{ Auth::user()->name }}</div>
                <a href="{{ route('customer.dashboard') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-slate-700 hover:bg-slate-50">Dashboard</a>
                <a href="{{ route('customer.orders.create') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-cyan-600 bg-cyan-50">Pesan Air Sekarang</a>
                <a href="{{ route('customer.orders.index') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-slate-700 hover:bg-slate-50">Pesanan Saya</a>
                @if (Auth::user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-indigo-600 bg-indigo-50">Admin Panel</a>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="w-full text-left px-3 py-2 rounded-lg text-base font-medium text-rose-600 hover:bg-rose-50">Keluar</button>
                </form>
            @else
                <div class="flex space-x-2 pt-2">
                    <a href="{{ route('login') }}" class="flex-1 text-center py-2.5 rounded-xl border border-slate-200 text-slate-700 font-semibold text-sm">Masuk</a>
                    <a href="{{ route('register') }}" class="flex-1 text-center py-2.5 rounded-xl bg-cyan-600 text-white font-semibold text-sm">Daftar</a>
                </div>
            @endauth
        </div>
    </div>
</nav>
