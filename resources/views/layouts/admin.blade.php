<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin - {{ config('app.name', 'AquaFlow') }} - @yield('page_title', 'Dashboard')</title>

    <!-- Vite CSS & JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @stack('styles')
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased font-sans flex flex-col" x-data="{ sidebarOpen: false }">

    @php
        $pendingOrdersBadge = \App\Models\Order::where('status', 'pending')->count();
        $activeDeliveriesBadge = \App\Models\Delivery::whereIn('status', ['assigned', 'in_transit', 'arrived'])->count();
    @endphp

    <!-- Mobile Backdrop Overlay -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-xs md:hidden" 
         @click="sidebarOpen = false" 
         x-cloak></div>

    <!-- DEDICATED LEFT SIDEBAR NAVIGATION (Docked on Desktop md:fixed) -->
    <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-300 flex flex-col border-r border-slate-800 shadow-2xl transition-transform duration-300 ease-in-out md:translate-x-0"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">

        <!-- Sidebar Top: Brand & Depot Status -->
        <div class="h-16 flex items-center justify-between px-5 bg-slate-950 border-b border-slate-800 shrink-0">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center text-white shadow-md">
                    <i class="fa-solid fa-droplet text-lg"></i>
                </div>
                <div>
                    <div class="text-base font-black text-white tracking-tight flex items-center">
                        <span>Aqua<span class="text-cyan-400">Flow</span></span>
                        <span class="ml-2 px-1.5 py-0.2 rounded text-[9px] font-black uppercase tracking-wider bg-cyan-950 text-cyan-300 border border-cyan-800">
                            Admin
                        </span>
                    </div>
                    <span class="text-[10px] flex items-center font-semibold text-emerald-400 mt-0.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse mr-1.5"></span>
                        Depot Online • Siap Antar
                    </span>
                </div>
            </a>

            <!-- Mobile Close Button -->
            <button type="button" @click="sidebarOpen = false" class="md:hidden text-slate-400 hover:text-white p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Sidebar Navigation Menu Links -->
        <nav class="flex-1 px-3 py-4 space-y-6 overflow-y-auto text-xs font-semibold scrollbar-thin scrollbar-thumb-slate-700">

            <!-- Group 1: Operasional Utama -->
            <div class="space-y-1">
                <div class="px-3 pb-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                    Operasional Utama
                </div>

                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center px-3 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('admin.dashboard') ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-chart-pie w-5 mr-3 text-center text-sm {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-cyan-400' }}"></i>
                    <span>Ringkasan Bisnis</span>
                </a>

                <!-- Orders -->
                <a href="{{ route('admin.orders.index') }}" 
                   class="flex items-center justify-between px-3 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('admin.orders.*') ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <div class="flex items-center">
                        <i class="fa-solid fa-boxes-stacked w-5 mr-3 text-center text-sm {{ request()->routeIs('admin.orders.*') ? 'text-white' : 'text-blue-400' }}"></i>
                        <span>Kelola Pesanan</span>
                    </div>
                    @if ($pendingOrdersBadge > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-500 text-slate-950 animate-pulse">
                            {{ $pendingOrdersBadge }} Baru
                        </span>
                    @endif
                </a>

                <!-- Deliveries -->
                <a href="{{ route('admin.deliveries.index') }}" 
                   class="flex items-center justify-between px-3 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('admin.deliveries.index') || request()->routeIs('admin.deliveries.show') ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <div class="flex items-center">
                        <i class="fa-solid fa-truck-fast w-5 mr-3 text-center text-sm {{ request()->routeIs('admin.deliveries.index') || request()->routeIs('admin.deliveries.show') ? 'text-white' : 'text-emerald-400' }}"></i>
                        <span>Tugas Pengantaran</span>
                    </div>
                    @if ($activeDeliveriesBadge > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-500 text-white">
                            {{ $activeDeliveriesBadge }}
                        </span>
                    @endif
                </a>

                <!-- Live Radar Map -->
                <a href="{{ route('admin.deliveries.map') }}" 
                   class="flex items-center justify-between px-3 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('admin.deliveries.map') ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <div class="flex items-center">
                        <i class="fa-solid fa-satellite-dish w-5 mr-3 text-center text-sm text-emerald-400"></i>
                        <span>Peta Radar Real-Time</span>
                    </div>
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                </a>

                <!-- Customer Addresses Management -->
                <a href="{{ route('admin.addresses.index') }}" 
                   class="flex items-center px-3 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('admin.addresses.*') ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-map-location-dot w-5 mr-3 text-center text-sm text-cyan-400"></i>
                    <span>Alamat Pelanggan</span>
                </a>
            </div>

            <!-- Group 2: Produk & Layanan Wilayah -->
            <div class="space-y-1">
                <div class="px-3 pb-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                    Katalog & Logistik
                </div>

                <!-- Products -->
                <a href="{{ route('admin.products.index') }}" 
                   class="flex items-center px-3 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('admin.products.*') ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-bottle-water w-5 mr-3 text-center text-sm text-cyan-400"></i>
                    <span>Produk & Stok Galon</span>
                </a>

                <!-- Service Areas -->
                <a href="{{ route('admin.service-areas.index') }}" 
                   class="flex items-center px-3 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('admin.service-areas.*') ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-signs-post w-5 mr-3 text-center text-sm text-amber-400"></i>
                    <span>Wilayah & Tarif Ongkir</span>
                </a>
            </div>

            <!-- Group 3: Finansial & Reputasi -->
            <div class="space-y-1">
                <div class="px-3 pb-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                    Keuangan & Ulasan
                </div>

                <!-- Finance -->
                <a href="{{ route('admin.finance.index') }}" 
                   class="flex items-center px-3 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('admin.finance.*') ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-wallet w-5 mr-3 text-center text-sm text-emerald-400"></i>
                    <span>Keuangan & Beban</span>
                </a>

                <!-- Reviews -->
                <a href="{{ route('admin.reviews.index') }}" 
                   class="flex items-center px-3 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('admin.reviews.*') ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-star w-5 mr-3 text-center text-sm text-amber-400"></i>
                    <span>Ulasan Pelanggan</span>
                </a>
            </div>

            <!-- Group 4: Sistem & Pengguna -->
            <div class="space-y-1">
                <div class="px-3 pb-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                    Sistem & Akses
                </div>

                <!-- Users -->
                <a href="{{ route('admin.users.index') }}" 
                   class="flex items-center px-3 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('admin.users.*') ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-users-gear w-5 mr-3 text-center text-sm text-purple-400"></i>
                    <span>Manajemen Pengguna</span>
                </a>

                <!-- View Public Web -->
                <a href="{{ route('home') }}" target="_blank"
                   class="flex items-center px-3 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition duration-150">
                    <i class="fa-solid fa-arrow-up-right-from-square w-5 mr-3 text-center text-sm text-slate-400"></i>
                    <span>Buka Web Publik</span>
                </a>
            </div>

        </nav>

        <!-- Sidebar Bottom: Admin Profile & Dropup Menu with Logout -->
        <div x-data="{ userMenuOpen: false }" class="relative p-3 bg-slate-950 border-t border-slate-800 shrink-0">
            <!-- Dropup Floating Menu -->
            <div x-show="userMenuOpen"
                 x-cloak
                 @click.away="userMenuOpen = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                 class="absolute bottom-full left-2 right-2 mb-2 bg-slate-900 border border-slate-700/90 rounded-2xl shadow-2xl p-2 z-50 overflow-hidden"
                 style="display: none;">
                
                <!-- Account Summary -->
                <div class="px-3 py-2.5 bg-slate-950/80 rounded-xl mb-1.5 border border-slate-800">
                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Sedang Masuk Sebagai</p>
                    <p class="text-xs font-bold text-white truncate mt-0.5">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] text-slate-400 truncate">{{ Auth::user()->email }}</p>
                    <div class="mt-1.5 flex items-center">
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-cyan-500/10 text-cyan-400 border border-cyan-500/30">
                            <i class="fa-solid fa-shield-halved mr-1 text-[9px]"></i>
                            {{ strtoupper(str_replace('_', ' ', Auth::user()->role)) }}
                        </span>
                    </div>
                </div>

                <!-- Fast Actions -->
                <div class="space-y-0.5">
                    <a href="{{ route('admin.addresses.index') }}" 
                       class="flex items-center px-3 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800 transition">
                        <i class="fa-solid fa-map-location-dot w-5 text-slate-400 text-center mr-2"></i>
                        <span>Kelola Alamat Pelanggan</span>
                    </a>
                    <a href="{{ route('admin.users.index') }}" 
                       class="flex items-center px-3 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800 transition">
                        <i class="fa-solid fa-users-gear w-5 text-slate-400 text-center mr-2"></i>
                        <span>Manajemen Pengguna</span>
                    </a>
                    <a href="{{ route('home') }}" target="_blank"
                       class="flex items-center px-3 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800 transition">
                        <i class="fa-solid fa-globe w-5 text-slate-400 text-center mr-2"></i>
                        <span>Buka Web Publik</span>
                    </a>
                </div>

                <div class="my-1.5 border-t border-slate-800"></div>

                <!-- Logout Action -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" 
                            class="w-full flex items-center px-3 py-2 rounded-xl text-xs font-bold text-rose-400 hover:text-white hover:bg-rose-600 transition group cursor-pointer">
                        <i class="fa-solid fa-arrow-right-from-bracket w-5 text-rose-400 group-hover:text-white text-center mr-2"></i>
                        <span>Keluar (Logout)</span>
                    </button>
                </form>
            </div>

            <!-- Profile Trigger Button -->
            <button type="button" 
                    @click="userMenuOpen = !userMenuOpen"
                    class="w-full flex items-center justify-between p-1.5 rounded-xl hover:bg-slate-900 transition text-left group cursor-pointer">
                <div class="flex items-center space-x-2.5 overflow-hidden">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-cyan-600 to-blue-600 text-white font-black flex items-center justify-center text-xs shrink-0 shadow-sm group-hover:scale-105 transition-transform">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-xs font-bold text-white truncate group-hover:text-cyan-300 transition">
                            {{ Auth::user()->name }}
                        </div>
                        <div class="text-[10px] text-cyan-400 uppercase font-semibold tracking-wider">
                            {{ str_replace('_', ' ', Auth::user()->role) }}
                        </div>
                    </div>
                </div>

                <div class="text-slate-400 group-hover:text-white transition p-1 shrink-0">
                    <i class="fa-solid fa-chevron-up text-xs transition-transform duration-200" :class="{'rotate-180': userMenuOpen}"></i>
                </div>
            </button>
        </div>

    </aside>

    <!-- MAIN RIGHT CONTENT WRAPPER (Docked beside left sidebar on desktop md:pl-64) -->
    <div class="flex-1 flex flex-col min-w-0 md:pl-64">

        <!-- Top Header Bar -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-30 shadow-2xs">
            <div class="flex items-center space-x-3">
                <!-- Hamburger menu button for Mobile/Tablet -->
                <button type="button" 
                        @click="sidebarOpen = true" 
                        class="md:hidden p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>

                <div>
                    <h1 class="text-base sm:text-lg font-black text-slate-900 tracking-tight flex items-center">
                        @yield('page_title', 'Admin Panel')
                    </h1>
                </div>
            </div>

            <!-- Header Right Shortcut Controls -->
            <div class="flex items-center space-x-2 sm:space-x-3">
                <a href="{{ route('home') }}" target="_blank" 
                   class="text-xs font-bold text-slate-600 hover:text-cyan-600 bg-slate-50 hover:bg-slate-100 border border-slate-200 px-3 py-1.5 rounded-xl transition flex items-center shadow-2xs">
                    <i class="fa-solid fa-globe mr-1.5 text-slate-400"></i>
                    <span class="hidden sm:inline">Lihat Web Publik</span>
                    <span class="sm:hidden">Web</span>
                </a>

                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', \App\Models\BusinessSetting::get('company_whatsapp', '62881026146848')) }}" target="_blank"
                   class="text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-3 py-1.5 rounded-xl transition flex items-center shadow-2xs">
                    <i class="fa-brands fa-whatsapp mr-1.5 text-emerald-600 text-sm"></i>
                    <span class="hidden sm:inline">WhatsApp CS</span>
                    <span class="sm:hidden">WA</span>
                </a>
            </div>
        </header>

        <!-- Flash Alert Messages -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full mt-4">
            @if (session('success'))
                <div class="mb-4 flex items-center p-4 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-2xl shadow-xs">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-lg mr-3 shrink-0"></i>
                    <div class="text-xs sm:text-sm font-semibold">{{ session('success') }}</div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 flex items-center p-4 bg-rose-50 border border-rose-200 text-rose-900 rounded-2xl shadow-xs">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600 text-lg mr-3 shrink-0"></i>
                    <div class="text-xs sm:text-sm font-semibold">{{ session('error') }}</div>
                </div>
            @endif

            @if (session('info'))
                <div class="mb-4 flex items-center p-4 bg-sky-50 border border-sky-200 text-sky-900 rounded-2xl shadow-xs">
                    <i class="fa-solid fa-circle-info text-sky-600 text-lg mr-3 shrink-0"></i>
                    <div class="text-xs sm:text-sm font-semibold">{{ session('info') }}</div>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-4 bg-rose-50 border border-rose-200 text-rose-900 rounded-2xl shadow-xs">
                    <div class="flex items-center mb-1 text-xs sm:text-sm font-bold">
                        <i class="fa-solid fa-circle-xmark text-rose-600 mr-2"></i>
                        <span>Mohon periksa kesalahan input berikut:</span>
                    </div>
                    <ul class="list-disc list-inside text-xs pl-4 space-y-0.5 text-rose-700">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <!-- Main Dynamic Content Body -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
            @yield('content')
        </main>

        <!-- Admin Footer -->
        <footer class="bg-white border-t border-slate-200 py-4 px-6 text-xs text-slate-400 flex flex-col sm:flex-row justify-between items-center gap-2">
            <div>
                &copy; {{ date('Y') }} <strong>AquaFlow Management System</strong> &bull; Panel Kendali Depot & Distribusi Galon
            </div>
            <div class="text-[11px] text-slate-400">
                Waktu Server: <span class="font-mono text-slate-600">{{ now()->format('d M Y, H:i') }} WIB</span>
            </div>
        </footer>

    </div>

    @stack('scripts')
</body>
</html>
