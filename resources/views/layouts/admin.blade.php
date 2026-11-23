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
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased font-sans flex flex-col" 
      x-data="{ 
          sidebarOpen: false,
          sidebarExpanded: (localStorage.getItem('admin_sidebar_expanded') !== 'false')
      }"
      x-init="$watch('sidebarExpanded', val => localStorage.setItem('admin_sidebar_expanded', val))">

    @php
        $pendingOrdersBadge = \App\Models\Order::where('status', 'pending')->count();
        $activeDeliveriesBadge = \App\Models\Delivery::whereIn('status', ['assigned', 'in_transit', 'arrived'])->count();
    @endphp

    <!-- Mobile Backdrop Overlay with Smooth Fade -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-xs md:hidden" 
         @click="sidebarOpen = false" 
         x-cloak></div>

    <!-- DEDICATED LEFT SIDEBAR NAVIGATION WITH SMOOTH ANIMATION -->
    <aside class="fixed inset-y-0 left-0 z-50 bg-slate-900 text-slate-300 flex flex-col border-r border-slate-800 shadow-2xl transition-all duration-300 ease-in-out"
           :class="{
               'w-64': sidebarExpanded || sidebarOpen,
               'w-20': !sidebarExpanded && !sidebarOpen,
               'translate-x-0': sidebarOpen,
               '-translate-x-full md:translate-x-0': !sidebarOpen
           }">

        <!-- Sidebar Top: Brand & Depot Status -->
        <div class="h-16 flex items-center justify-between px-4 bg-slate-950 border-b border-slate-800 shrink-0 overflow-hidden">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 overflow-hidden">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center text-white shadow-md shrink-0">
                    <i class="fa-solid fa-droplet text-lg"></i>
                </div>
                <div x-show="sidebarExpanded || sidebarOpen"
                     x-transition:enter="transition-all ease-out duration-250 delay-75"
                     x-transition:enter-start="opacity-0 -translate-x-3"
                     x-transition:enter-end="opacity-100 translate-x-0"
                     class="overflow-hidden whitespace-nowrap">
                    <div class="text-base font-black text-white tracking-tight flex items-center">
                        <span>Aqua<span class="text-cyan-400">Flow</span></span>
                        <span class="ml-2 px-1.5 py-0.2 rounded text-[9px] font-black uppercase tracking-wider bg-cyan-950 text-cyan-300 border border-cyan-800">
                            Admin
                        </span>
                    </div>
                    <span class="text-[10px] flex items-center font-semibold text-emerald-400 mt-0.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse mr-1.5 shrink-0"></span>
                        Depot Online • Siap Antar
                    </span>
                </div>
            </a>

            <!-- Toggle Icon Button in Sidebar Header (Smooth Desktop Collapse & Mobile Close) -->
            <button type="button" 
                    @click="if (window.innerWidth < 768) { sidebarOpen = false; } else { sidebarExpanded = !sidebarExpanded; }" 
                    class="text-slate-400 hover:text-white hover:bg-slate-800 p-1.5 rounded-xl transition duration-200"
                    title="Buka / Tutup Navigasi">
                <i class="fa-solid text-sm" 
                   :class="(sidebarExpanded || sidebarOpen) ? 'fa-angles-left' : 'fa-angles-right'"></i>
            </button>
        </div>

        <!-- Sidebar Navigation Menu Links -->
        <nav class="flex-1 px-3 py-4 space-y-5 overflow-y-auto overflow-x-hidden text-xs font-semibold scrollbar-thin scrollbar-thumb-slate-700">

            <!-- Group 1: Operasional Utama -->
            <div class="space-y-1">
                <div x-show="sidebarExpanded || sidebarOpen"
                     x-transition:enter="transition-opacity duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     class="px-3 pb-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider whitespace-nowrap">
                    Operasional Utama
                </div>

                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard') }}" 
                   title="Ringkasan Bisnis"
                   class="flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.dashboard') ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-chart-pie w-5 text-center text-sm shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-cyan-400' }}"></i>
                    <span x-show="sidebarExpanded || sidebarOpen"
                          x-transition:enter="transition-all ease-out duration-200 delay-50"
                          x-transition:enter-start="opacity-0 -translate-x-2"
                          x-transition:enter-end="opacity-100 translate-x-0"
                          class="ml-3 truncate whitespace-nowrap">
                        Ringkasan Bisnis
                    </span>
                </a>

                <!-- Orders -->
                <a href="{{ route('admin.orders.index') }}" 
                   title="Kelola Pesanan"
                   class="flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.orders.*') ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <div class="flex items-center overflow-hidden">
                        <i class="fa-solid fa-boxes-stacked w-5 text-center text-sm shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('admin.orders.*') ? 'text-white' : 'text-blue-400' }}"></i>
                        <span x-show="sidebarExpanded || sidebarOpen"
                              x-transition:enter="transition-all ease-out duration-200 delay-50"
                              x-transition:enter-start="opacity-0 -translate-x-2"
                              x-transition:enter-end="opacity-100 translate-x-0"
                              class="ml-3 truncate whitespace-nowrap">
                            Kelola Pesanan
                        </span>
                    </div>
                    @if ($pendingOrdersBadge > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-500 text-slate-950 animate-pulse shrink-0"
                              :class="{ 'hidden': !sidebarExpanded && !sidebarOpen }">
                            {{ $pendingOrdersBadge }} Baru
                        </span>
                    @endif
                </a>

                <!-- Deliveries -->
                <a href="{{ route('admin.deliveries.index') }}" 
                   title="Tugas Pengantaran"
                   class="flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.deliveries.index') || request()->routeIs('admin.deliveries.show') ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <div class="flex items-center overflow-hidden">
                        <i class="fa-solid fa-truck-fast w-5 text-center text-sm shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('admin.deliveries.index') || request()->routeIs('admin.deliveries.show') ? 'text-white' : 'text-emerald-400' }}"></i>
                        <span x-show="sidebarExpanded || sidebarOpen"
                              x-transition:enter="transition-all ease-out duration-200 delay-50"
                              x-transition:enter-start="opacity-0 -translate-x-2"
                              x-transition:enter-end="opacity-100 translate-x-0"
                              class="ml-3 truncate whitespace-nowrap">
                            Tugas Pengantaran
                        </span>
                    </div>
                    @if ($activeDeliveriesBadge > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-500 text-white shrink-0"
                              :class="{ 'hidden': !sidebarExpanded && !sidebarOpen }">
                            {{ $activeDeliveriesBadge }}
                        </span>
                    @endif
                </a>

                <!-- Live Radar Map -->
                <a href="{{ route('admin.deliveries.map') }}" 
                   title="Radar Peta Real-Time"
                   class="flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.deliveries.map') ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <div class="flex items-center overflow-hidden">
                        <i class="fa-solid fa-satellite-dish w-5 text-center text-sm text-emerald-400 shrink-0 transition-transform duration-200 group-hover:scale-110"></i>
                        <span x-show="sidebarExpanded || sidebarOpen"
                              x-transition:enter="transition-all ease-out duration-200 delay-50"
                              x-transition:enter-start="opacity-0 -translate-x-2"
                              x-transition:enter-end="opacity-100 translate-x-0"
                              class="ml-3 truncate whitespace-nowrap">
                            Peta Radar Real-Time
                        </span>
                    </div>
                    <span class="relative flex h-2 w-2 shrink-0">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                </a>

                <!-- Customer Addresses Management -->
                <a href="{{ route('admin.addresses.index') }}" 
                   title="Alamat Pelanggan"
                   class="flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.addresses.*') ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-map-location-dot w-5 text-center text-sm text-cyan-400 shrink-0 transition-transform duration-200 group-hover:scale-110"></i>
                    <span x-show="sidebarExpanded || sidebarOpen"
                          x-transition:enter="transition-all ease-out duration-200 delay-50"
                          x-transition:enter-start="opacity-0 -translate-x-2"
                          x-transition:enter-end="opacity-100 translate-x-0"
                          class="ml-3 truncate whitespace-nowrap">
                        Alamat Pelanggan
                    </span>
                </a>
            </div>

            <!-- Group 2: Produk & Layanan Wilayah -->
            <div class="space-y-1">
                <div x-show="sidebarExpanded || sidebarOpen"
                     x-transition:enter="transition-opacity duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     class="px-3 pb-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider whitespace-nowrap">
                    Katalog & Logistik
                </div>

                <!-- Products -->
                <a href="{{ route('admin.products.index') }}" 
                   title="Produk & Stok Galon"
                   class="flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.products.*') ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-bottle-water w-5 text-center text-sm text-cyan-400 shrink-0 transition-transform duration-200 group-hover:scale-110"></i>
                    <span x-show="sidebarExpanded || sidebarOpen"
                          x-transition:enter="transition-all ease-out duration-200 delay-50"
                          x-transition:enter-start="opacity-0 -translate-x-2"
                          x-transition:enter-end="opacity-100 translate-x-0"
                          class="ml-3 truncate whitespace-nowrap">
                        Produk & Stok Galon
                    </span>
                </a>

                <!-- Service Areas -->
                <a href="{{ route('admin.service-areas.index') }}" 
                   title="Wilayah & Tarif Ongkir"
                   class="flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.service-areas.*') ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-signs-post w-5 text-center text-sm text-amber-400 shrink-0 transition-transform duration-200 group-hover:scale-110"></i>
                    <span x-show="sidebarExpanded || sidebarOpen"
                          x-transition:enter="transition-all ease-out duration-200 delay-50"
                          x-transition:enter-start="opacity-0 -translate-x-2"
                          x-transition:enter-end="opacity-100 translate-x-0"
                          class="ml-3 truncate whitespace-nowrap">
                        Wilayah & Tarif Ongkir
                    </span>
                </a>
            </div>

            <!-- Group 3: Finansial & Reputasi -->
            <div class="space-y-1">
                <div x-show="sidebarExpanded || sidebarOpen"
                     x-transition:enter="transition-opacity duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     class="px-3 pb-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider whitespace-nowrap">
                    Keuangan & Ulasan
                </div>

                <!-- Finance -->
                <a href="{{ route('admin.finance.index') }}" 
                   title="Keuangan & Beban"
                   class="flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.finance.*') ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-wallet w-5 text-center text-sm text-emerald-400 shrink-0 transition-transform duration-200 group-hover:scale-110"></i>
                    <span x-show="sidebarExpanded || sidebarOpen"
                          x-transition:enter="transition-all ease-out duration-200 delay-50"
                          x-transition:enter-start="opacity-0 -translate-x-2"
                          x-transition:enter-end="opacity-100 translate-x-0"
                          class="ml-3 truncate whitespace-nowrap">
                        Keuangan & Beban
                    </span>
                </a>

                <!-- Reviews -->
                <a href="{{ route('admin.reviews.index') }}" 
                   title="Ulasan Pelanggan"
                   class="flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.reviews.*') ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-star w-5 text-center text-sm text-amber-400 shrink-0 transition-transform duration-200 group-hover:scale-110"></i>
                    <span x-show="sidebarExpanded || sidebarOpen"
                          x-transition:enter="transition-all ease-out duration-200 delay-50"
                          x-transition:enter-start="opacity-0 -translate-x-2"
                          x-transition:enter-end="opacity-100 translate-x-0"
                          class="ml-3 truncate whitespace-nowrap">
                        Ulasan Pelanggan
                    </span>
                </a>
            </div>

            <!-- Group 4: Sistem & Pengguna -->
            <div class="space-y-1">
                <div x-show="sidebarExpanded || sidebarOpen"
                     x-transition:enter="transition-opacity duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     class="px-3 pb-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider whitespace-nowrap">
                    Sistem & Akses
                </div>

                <!-- Users -->
                <a href="{{ route('admin.users.index') }}" 
                   title="Manajemen Pengguna"
                   class="flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.users.*') ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-users-gear w-5 text-center text-sm text-purple-400 shrink-0 transition-transform duration-200 group-hover:scale-110"></i>
                    <span x-show="sidebarExpanded || sidebarOpen"
                          x-transition:enter="transition-all ease-out duration-200 delay-50"
                          x-transition:enter-start="opacity-0 -translate-x-2"
                          x-transition:enter-end="opacity-100 translate-x-0"
                          class="ml-3 truncate whitespace-nowrap">
                        Manajemen Pengguna
                    </span>
                </a>

                <!-- View Public Web -->
                <a href="{{ route('home') }}" target="_blank"
                   title="Buka Web Publik"
                   class="flex items-center px-3 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition-all duration-200 group">
                    <i class="fa-solid fa-arrow-up-right-from-square w-5 text-center text-sm text-slate-400 shrink-0 transition-transform duration-200 group-hover:scale-110"></i>
                    <span x-show="sidebarExpanded || sidebarOpen"
                          x-transition:enter="transition-all ease-out duration-200 delay-50"
                          x-transition:enter-start="opacity-0 -translate-x-2"
                          x-transition:enter-end="opacity-100 translate-x-0"
                          class="ml-3 truncate whitespace-nowrap">
                        Buka Web Publik
                    </span>
                </a>
            </div>

        </nav>

        <!-- Sidebar Bottom: Admin Profile & Fast Logout -->
        <div class="p-3 bg-slate-950 border-t border-slate-800 shrink-0">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2.5 overflow-hidden">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-cyan-600 to-blue-600 text-white font-black flex items-center justify-center text-xs shrink-0 shadow-sm">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div x-show="sidebarExpanded || sidebarOpen"
                         x-transition:enter="transition-all ease-out duration-200 delay-50"
                         x-transition:enter-start="opacity-0 -translate-x-2"
                         x-transition:enter-end="opacity-100 translate-x-0"
                         class="overflow-hidden whitespace-nowrap">
                        <div class="text-xs font-bold text-white truncate">{{ Auth::user()->name }}</div>
                        <div class="text-[10px] text-cyan-400 uppercase font-semibold tracking-wider">
                            {{ str_replace('_', ' ', Auth::user()->role) }}
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}"
                      :class="{ 'hidden': !sidebarExpanded && !sidebarOpen }">
                    @csrf
                    <button type="submit" title="Keluar dari Akun" 
                            class="text-slate-400 hover:text-rose-400 hover:bg-slate-800/80 p-2 rounded-xl transition duration-150">
                        <i class="fa-solid fa-power-off text-sm"></i>
                    </button>
                </form>
            </div>
        </div>

    </aside>

    <!-- MAIN RIGHT CONTENT WRAPPER WITH SMOOTH PADDING TRANSITION -->
    <div class="flex-1 flex flex-col min-w-0 transition-all duration-300 ease-in-out"
         :class="sidebarExpanded ? 'md:pl-64' : 'md:pl-20'">

        <!-- Top Header Bar -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-30 shadow-2xs">
            <div class="flex items-center space-x-3">
                <!-- SMOOTH SIDEBAR TOGGLE BUTTON (Works seamlessly on both Desktop & Mobile) -->
                <button type="button" 
                        @click="if (window.innerWidth < 768) { sidebarOpen = !sidebarOpen; } else { sidebarExpanded = !sidebarExpanded; }" 
                        class="p-2.5 rounded-xl text-slate-600 hover:text-cyan-600 hover:bg-cyan-50 border border-slate-200 transition-all duration-200 flex items-center justify-center group shadow-2xs"
                        title="Buka / Tutup Navigasi Sidebar">
                    <i class="fa-solid fa-bars-staggered text-base group-hover:scale-110 transition-transform duration-200"></i>
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
