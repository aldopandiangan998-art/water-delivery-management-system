<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin - {{ config('app.name', 'AquaFlow') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @stack('styles')
</head>
<body class="h-full flex antialiased text-slate-800" x-data="{ sidebarOpen: false }">

    <!-- Mobile sidebar backdrop -->
    <div x-show="sidebarOpen" class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-xs sm:hidden" @click="sidebarOpen = false" x-cloak></div>

    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-300 transition-transform duration-200 ease-in-out sm:translate-x-0 sm:static sm:inset-0 flex flex-col">
        <!-- Logo -->
        <div class="h-16 flex items-center px-6 bg-slate-950 border-b border-slate-800">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2.5">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center text-white shadow-sm">
                    <i class="fa-solid fa-droplet text-lg"></i>
                </div>
                <div>
                    <span class="text-lg font-bold text-white tracking-tight">Aqua<span class="text-cyan-400">Flow</span></span>
                    <span class="text-[10px] block font-semibold text-cyan-400 uppercase tracking-wider">Admin Portal</span>
                </div>
            </a>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto text-sm font-medium">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-cyan-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-chart-pie w-5 mr-3 text-center"></i> Ringkasan Bisnis
            </a>

            <div class="pt-3 pb-1 px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Operasional</div>

            <a href="{{ route('admin.orders.index') }}" class="flex items-center px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.orders.*') ? 'bg-cyan-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-boxes-stacked w-5 mr-3 text-center"></i> Kelola Pesanan
            </a>

            <a href="{{ route('admin.deliveries.index') }}" class="flex items-center px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.deliveries.index') ? 'bg-cyan-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-truck w-5 mr-3 text-center"></i> Tugas Pengantaran
            </a>

            <a href="{{ route('admin.deliveries.map') }}" class="flex items-center px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.deliveries.map') ? 'bg-cyan-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-map-location-dot w-5 mr-3 text-center text-emerald-400"></i> Peta Kurir Real-Time
            </a>

            <div class="pt-3 pb-1 px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Katalog & Stok</div>

            <a href="{{ route('admin.products.index') }}" class="flex items-center px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.products.*') ? 'bg-cyan-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-bottle-water w-5 mr-3 text-center"></i> Produk & Stok Galon
            </a>

            <a href="{{ route('admin.service-areas.index') }}" class="flex items-center px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.service-areas.*') ? 'bg-cyan-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-signs-post w-5 mr-3 text-center"></i> Wilayah & Tarif Ongkir
            </a>

            <div class="pt-3 pb-1 px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Keuangan & Ulasan</div>

            <a href="{{ route('admin.finance.index') }}" class="flex items-center px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.finance.*') ? 'bg-cyan-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-wallet w-5 mr-3 text-center"></i> Keuangan & Pengeluaran
            </a>

            <a href="{{ route('admin.reviews.index') }}" class="flex items-center px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.reviews.*') ? 'bg-cyan-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-star w-5 mr-3 text-center text-amber-400"></i> Ulasan Pelanggan
            </a>

            <a href="{{ route('admin.users.index') }}" class="flex items-center px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.users.*') ? 'bg-cyan-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-users-gear w-5 mr-3 text-center"></i> Manajemen Pengguna
            </a>
        </nav>

        <!-- User bottom card -->
        <div class="p-3 bg-slate-950 border-t border-slate-800">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2.5 overflow-hidden">
                    <div class="w-8 h-8 rounded-lg bg-cyan-600 text-white font-bold flex items-center justify-center text-xs">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-xs font-semibold text-white truncate">{{ Auth::user()->name }}</div>
                        <div class="text-[10px] text-cyan-400 uppercase font-medium">{{ Auth::user()->role }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Keluar" class="text-slate-400 hover:text-rose-400 p-1.5 rounded-lg transition">
                        <i class="fa-solid fa-power-off"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content wrapper -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <!-- Top header -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 z-10 sticky top-0 shadow-2xs">
            <div class="flex items-center">
                <button @click="sidebarOpen = !sidebarOpen" class="sm:hidden text-slate-500 hover:text-slate-700 mr-3">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
                <h1 class="text-base sm:text-lg font-bold text-slate-800">@yield('page_title', 'Admin Panel')</h1>
            </div>

            <div class="flex items-center space-x-3">
                <a href="{{ route('home') }}" target="_blank" class="text-xs font-semibold text-slate-600 hover:text-cyan-600 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition flex items-center">
                    <i class="fa-solid fa-arrow-up-right-from-square mr-1.5"></i> Lihat Web
                </a>
            </div>
        </header>

        <!-- Flash messages -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 w-full mt-4">
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium flex items-center">
                    <i class="fa-solid fa-circle-check text-emerald-600 mr-2.5"></i> {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm font-medium flex items-center">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600 mr-2.5"></i> {{ session('error') }}
                </div>
            @endif
        </div>

        <!-- Content body -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
