<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'AquaFlow') }} - @yield('title', 'Portal Pelanggan')</title>

    <!-- Vite CSS & JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @stack('styles')
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased font-sans flex flex-col" 
      x-data="{ 
          sidebarOpen: false,
          sidebarExpanded: (localStorage.getItem('customer_sidebar_expanded') !== 'false')
      }"
      x-init="$watch('sidebarExpanded', val => localStorage.setItem('customer_sidebar_expanded', val))">

    @php
        $activeCustomerOrdersCount = \App\Models\Order::where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'confirmed', 'processing', 'ready_for_delivery'])
            ->count();
        $gallonDeposit = Auth::user()->profile?->gallon_deposit_balance ?? 0;
        $companyWhatsapp = \App\Models\BusinessSetting::get('company_whatsapp', '62881026146848');
        $cleanWhatsapp = preg_replace('/[^0-9]/', '', $companyWhatsapp);
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

    <!-- DEDICATED LEFT SIDEBAR NAVIGATION FOR CUSTOMER (Smooth Animated Dock) -->
    <aside class="fixed inset-y-0 left-0 z-50 bg-slate-900 text-slate-300 flex flex-col border-r border-slate-800 shadow-2xl transition-all duration-300 ease-in-out"
           :class="{
               'w-64': sidebarExpanded || sidebarOpen,
               'w-20': !sidebarExpanded && !sidebarOpen,
               'translate-x-0': sidebarOpen,
               '-translate-x-full md:translate-x-0': !sidebarOpen
           }">

        <!-- Sidebar Top: Brand & Member Status -->
        <div class="h-16 flex items-center justify-between px-4 bg-slate-950 border-b border-slate-800 shrink-0 overflow-hidden">
            <a href="{{ route('customer.dashboard') }}" class="flex items-center space-x-3 overflow-hidden">
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
                            Member
                        </span>
                    </div>
                    <span class="text-[10px] flex items-center font-semibold text-emerald-400 mt-0.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse mr-1.5 shrink-0"></span>
                        Pelanggan Terverifikasi
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

        <!-- Sidebar Customer Saldo Gallon Card -->
        <div class="p-3 bg-slate-950/40 border-b border-slate-800/80 overflow-hidden">
            <div class="p-3 rounded-2xl bg-gradient-to-br from-slate-800 to-slate-850 border border-slate-700/60 shadow-xs">
                <div class="flex items-center justify-between">
                    <span x-show="sidebarExpanded || sidebarOpen" 
                          x-transition:enter="transition-opacity duration-200"
                          x-transition:enter-start="opacity-0"
                          x-transition:enter-end="opacity-100"
                          class="text-[10px] font-bold uppercase tracking-wider text-slate-400 truncate">
                        Titipan Galon
                    </span>
                    <i class="fa-solid fa-bottle-water text-cyan-400 text-sm mx-auto md:mx-0"></i>
                </div>
                <div class="text-xl font-black text-white mt-1" :class="{ 'text-center md:text-left': !sidebarExpanded && !sidebarOpen }">
                    {{ $gallonDeposit }} <span x-show="sidebarExpanded || sidebarOpen" class="text-xs font-normal text-slate-400">galon</span>
                </div>
                <div x-show="sidebarExpanded || sidebarOpen" 
                     x-transition:enter="transition-opacity duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     class="text-[10px] text-cyan-300 mt-0.5 truncate">
                    Siap tukar saat pesan
                </div>
            </div>
        </div>

        <!-- Sidebar Navigation Menu Links -->
        <nav class="flex-1 px-3 py-4 space-y-5 overflow-y-auto overflow-x-hidden text-xs font-semibold scrollbar-thin scrollbar-thumb-slate-700">

            <!-- Group 1: Layanan Pesanan -->
            <div class="space-y-1">
                <div x-show="sidebarExpanded || sidebarOpen"
                     x-transition:enter="transition-opacity duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     class="px-3 pb-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider whitespace-nowrap">
                    Layanan Pesanan
                </div>

                <!-- Dashboard -->
                <a href="{{ route('customer.dashboard') }}" 
                   title="Dashboard Saya"
                   class="flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('customer.dashboard') ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-house w-5 text-center text-sm shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('customer.dashboard') ? 'text-white' : 'text-cyan-400' }}"></i>
                    <span x-show="sidebarExpanded || sidebarOpen"
                          x-transition:enter="transition-all ease-out duration-200 delay-50"
                          x-transition:enter-start="opacity-0 -translate-x-2"
                          x-transition:enter-end="opacity-100 translate-x-0"
                          class="ml-3 truncate whitespace-nowrap">
                        Dashboard Saya
                    </span>
                </a>

                <!-- Order Water -->
                <a href="{{ route('customer.orders.create') }}" 
                   title="Pesan Air Galon"
                   class="flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('customer.orders.create') ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <div class="flex items-center overflow-hidden">
                        <i class="fa-solid fa-cart-plus w-5 text-center text-sm shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('customer.orders.create') ? 'text-white' : 'text-blue-400' }}"></i>
                        <span x-show="sidebarExpanded || sidebarOpen"
                              x-transition:enter="transition-all ease-out duration-200 delay-50"
                              x-transition:enter-start="opacity-0 -translate-x-2"
                              x-transition:enter-end="opacity-100 translate-x-0"
                              class="ml-3 truncate whitespace-nowrap">
                            Pesan Air Galon
                        </span>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black bg-cyan-500 text-white shadow-2xs shrink-0"
                          :class="{ 'hidden': !sidebarExpanded && !sidebarOpen }">
                        Pesan Cepat
                    </span>
                </a>

                <!-- Orders History & Tracking -->
                <a href="{{ route('customer.orders.index') }}" 
                   title="Pesanan Saya"
                   class="flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('customer.orders.index') || request()->routeIs('customer.orders.show') || request()->routeIs('customer.orders.track') ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <div class="flex items-center overflow-hidden">
                        <i class="fa-solid fa-box-archive w-5 text-center text-sm shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('customer.orders.index') || request()->routeIs('customer.orders.show') || request()->routeIs('customer.orders.track') ? 'text-white' : 'text-emerald-400' }}"></i>
                        <span x-show="sidebarExpanded || sidebarOpen"
                              x-transition:enter="transition-all ease-out duration-200 delay-50"
                              x-transition:enter-start="opacity-0 -translate-x-2"
                              x-transition:enter-end="opacity-100 translate-x-0"
                              class="ml-3 truncate whitespace-nowrap">
                            Pesanan Saya
                        </span>
                    </div>
                    @if ($activeCustomerOrdersCount > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-500 text-slate-950 animate-pulse shrink-0"
                              :class="{ 'hidden': !sidebarExpanded && !sidebarOpen }">
                            {{ $activeCustomerOrdersCount }} Aktif
                        </span>
                    @endif
                </a>

                <!-- Address Book -->
                <a href="{{ route('customer.addresses.index') }}" 
                   title="Alamat Pengantaran"
                   class="flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('customer.addresses.*') ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-location-dot w-5 text-center text-sm shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('customer.addresses.*') ? 'text-white' : 'text-purple-400' }}"></i>
                    <span x-show="sidebarExpanded || sidebarOpen"
                          x-transition:enter="transition-all ease-out duration-200 delay-50"
                          x-transition:enter-start="opacity-0 -translate-x-2"
                          x-transition:enter-end="opacity-100 translate-x-0"
                          class="ml-3 truncate whitespace-nowrap">
                        Alamat Pengantaran
                    </span>
                </a>
            </div>

            <!-- Group 2: Akun & Bantuan -->
            <div class="space-y-1">
                <div x-show="sidebarExpanded || sidebarOpen"
                     x-transition:enter="transition-opacity duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     class="px-3 pb-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider whitespace-nowrap">
                    Akun & Bantuan
                </div>

                <!-- Profile -->
                <a href="{{ route('customer.profile') }}" 
                   title="Profil & Kata Sandi"
                   class="flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 group {{ request()->routeIs('customer.profile') ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-user-gear w-5 text-center text-sm shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('customer.profile') ? 'text-white' : 'text-amber-400' }}"></i>
                    <span x-show="sidebarExpanded || sidebarOpen"
                          x-transition:enter="transition-all ease-out duration-200 delay-50"
                          x-transition:enter-start="opacity-0 -translate-x-2"
                          x-transition:enter-end="opacity-100 translate-x-0"
                          class="ml-3 truncate whitespace-nowrap">
                        Profil & Kata Sandi
                    </span>
                </a>

                <!-- WhatsApp CS Support -->
                <a href="https://wa.me/{{ $cleanWhatsapp }}?text=Halo%20Admin%20AquaFlow,%20saya%20pelanggan%20mau%20tanya%20pesanan" target="_blank"
                   title="Hubungi CS Depot"
                   class="flex items-center px-3 py-2.5 rounded-xl text-slate-300 hover:bg-slate-800 hover:text-emerald-400 transition-all duration-200 group">
                    <i class="fa-brands fa-whatsapp w-5 text-center text-sm text-emerald-400 shrink-0 transition-transform duration-200 group-hover:scale-110"></i>
                    <span x-show="sidebarExpanded || sidebarOpen"
                          x-transition:enter="transition-all ease-out duration-200 delay-50"
                          x-transition:enter-start="opacity-0 -translate-x-2"
                          x-transition:enter-end="opacity-100 translate-x-0"
                          class="ml-3 truncate whitespace-nowrap">
                        Hubungi CS Depot
                    </span>
                </a>

                <!-- Visit Public Web -->
                <a href="{{ route('home') }}" target="_blank"
                   title="Lihat Web Utama"
                   class="flex items-center px-3 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition-all duration-200 group">
                    <i class="fa-solid fa-globe w-5 text-center text-sm text-slate-400 shrink-0 transition-transform duration-200 group-hover:scale-110"></i>
                    <span x-show="sidebarExpanded || sidebarOpen"
                          x-transition:enter="transition-all ease-out duration-200 delay-50"
                          x-transition:enter-start="opacity-0 -translate-x-2"
                          x-transition:enter-end="opacity-100 translate-x-0"
                          class="ml-3 truncate whitespace-nowrap">
                        Lihat Web Utama
                    </span>
                </a>
            </div>

        </nav>

        <!-- Sidebar Bottom: Customer Profile & Fast Logout -->
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
                            {{ str_replace('_', ' ', Auth::user()->profile?->customer_type ?? 'Household') }}
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
                        @yield('title', 'Portal Pelanggan')
                    </h1>
                </div>
            </div>

            <!-- Header Right Shortcut Controls -->
            <div class="flex items-center space-x-2 sm:space-x-3">
                <!-- Direct Order CTA -->
                <a href="{{ route('customer.orders.create') }}" 
                   class="px-3.5 py-1.5 rounded-xl text-xs font-extrabold text-white bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 shadow-sm transition flex items-center">
                    <i class="fa-solid fa-cart-plus mr-1.5"></i>
                    <span>Pesan Air</span>
                </a>

                <!-- WhatsApp CS button -->
                <a href="https://wa.me/{{ $cleanWhatsapp }}?text=Halo%20Admin%20AquaFlow,%20saya%20mau%20tanya%20pesanan" target="_blank"
                   class="text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-3 py-1.5 rounded-xl transition flex items-center shadow-2xs">
                    <i class="fa-brands fa-whatsapp mr-1.5 text-emerald-600 text-sm"></i>
                    <span class="hidden sm:inline">CS WhatsApp</span>
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
        <main class="flex-1 pb-16 md:pb-8">
            @yield('content')
        </main>

        <!-- Customer Portal Footer -->
        <footer class="bg-white border-t border-slate-200 py-4 px-6 text-xs text-slate-500 flex flex-col sm:flex-row justify-between items-center gap-2">
            <div class="flex items-center space-x-2">
                <span class="font-bold text-slate-800">AquaFlow Member Portal</span>
                <span>&bull;</span>
                <span>Layanan Pesan Antar Air Minum Higienis</span>
            </div>
            <div class="text-[11px] text-slate-400">
                Pusat Bantuan WhatsApp: <a href="https://wa.me/{{ $cleanWhatsapp }}" target="_blank" class="font-bold text-emerald-600 hover:underline">{{ \App\Models\BusinessSetting::get('company_phone', '0881-0261-46848') }}</a>
            </div>
        </footer>

    </div>

    <!-- Floating WhatsApp Support Button -->
    <a href="https://wa.me/{{ $cleanWhatsapp }}?text=Halo%20Admin%20AquaFlow,%20saya%20mau%20tanya%20pesanan" target="_blank" 
       class="fixed bottom-6 right-5 z-40 bg-emerald-500 hover:bg-emerald-600 text-white w-12 h-12 sm:w-14 sm:h-14 rounded-full flex items-center justify-center shadow-lg hover:shadow-xl hover:scale-105 transition duration-200">
        <i class="fa-brands fa-whatsapp text-2xl sm:text-3xl"></i>
    </a>

    @stack('scripts')
</body>
</html>
