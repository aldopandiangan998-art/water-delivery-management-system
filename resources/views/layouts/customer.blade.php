<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50 dark:bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'AquaFlow') }} - @yield('title', __('Portal Pelanggan'))</title>

    <!-- Dark Mode Initial Script to Prevent Flicker -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <!-- Vite CSS & JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @stack('styles')

    <style>
        /* Sleek custom scrollbar for customer sidebar navigation */
        .custom-sidebar-scroll {
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: rgba(148, 163, 184, 0.35) transparent;
        }
        .custom-sidebar-scroll::-webkit-scrollbar {
            width: 6px;
        }
        .custom-sidebar-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-sidebar-scroll::-webkit-scrollbar-thumb {
            background-color: rgba(148, 163, 184, 0.35);
            border-radius: 9999px;
        }
        .custom-sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background-color: rgba(148, 163, 184, 0.65);
        }
    </style>
</head>
<body class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 antialiased font-sans flex flex-col" x-data="{ sidebarOpen: (window.innerWidth >= 768) }">

    @php
        $activeCustomerOrdersCount = \App\Models\Order::where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'confirmed', 'processing', 'ready_for_delivery'])
            ->count();
        $companyWhatsapp = \App\Models\BusinessSetting::get('company_whatsapp', '6283186109775');
        $cleanWhatsapp = preg_replace('/[^0-9]/', '', $companyWhatsapp);
    @endphp

    <!-- Mobile Backdrop Overlay -->
    <div x-show="sidebarOpen" 
         @click="sidebarOpen = false" 
         x-cloak
         class="md:hidden fixed inset-0 z-30 bg-slate-950/60 backdrop-blur-xs transition-opacity duration-300">
    </div>

    <!-- DEDICATED LEFT SIDEBAR NAVIGATION FOR CUSTOMER (Non-blocking & Independently Scrollable) -->
    <aside class="fixed inset-y-0 left-0 z-40 h-screen max-h-screen bg-slate-900 text-slate-300 flex flex-col border-r border-slate-800 shadow-2xl transition-all duration-300 ease-in-out"
           :class="sidebarOpen ? 'w-64 translate-x-0' : '-translate-x-full md:translate-x-0 md:w-16'">

        <!-- Sidebar Top: Brand & Member Status -->
        <div class="h-16 flex items-center border-b border-slate-800 shrink-0 px-3 transition-all duration-300 bg-slate-950"
             :class="sidebarOpen ? 'justify-between' : 'justify-center'">
            <a href="{{ route('customer.dashboard') }}" 
               x-show="sidebarOpen"
               class="flex items-center space-x-2.5 overflow-hidden group">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center text-white shadow-md shrink-0 group-hover:scale-105 transition-transform duration-200">
                    <i class="fa-solid fa-droplet text-base"></i>
                </div>
                <div class="overflow-hidden">
                    <div class="text-sm font-black text-white tracking-tight flex items-center whitespace-nowrap">
                        <span>Aqua<span class="text-cyan-400">Flow</span></span>
                        <span class="ml-1.5 px-1.5 py-0.5 rounded text-[8px] font-black uppercase tracking-wider bg-cyan-950 text-cyan-300 border border-cyan-800">
                            Member
                        </span>
                    </div>
                    <span class="text-[9px] flex items-center font-semibold text-emerald-400 mt-0.5 truncate whitespace-nowrap">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse mr-1 shrink-0"></span>
                        {{ __('Pelanggan') }}
                    </span>
                </div>
            </a>

            <!-- Toggle Button (Inside sidebar navigation) -->
            <button type="button" @click="sidebarOpen = !sidebarOpen" 
                    class="text-slate-300 hover:text-white p-2 rounded-xl hover:bg-slate-800 transition cursor-pointer flex items-center justify-center shrink-0" 
                    :title="sidebarOpen ? '{{ __('Tutup / Perkecil Navigasi') }}' : '{{ __('Buka / Perlebar Navigasi') }}'">
                <i class="fa-solid fa-bars-staggered text-base"></i>
            </button>
        </div>


        <!-- Sidebar Navigation Menu Links (Independently Scrollable) -->
        <nav class="flex-1 min-h-0 py-4 space-y-5 overflow-y-auto overscroll-contain custom-sidebar-scroll text-xs font-semibold transition-all duration-300"
             :class="sidebarOpen ? 'px-3' : 'px-2'">

            <!-- Group 1: Layanan Pesanan -->
            <div class="space-y-1">
                <div class="pb-1 flex items-center" :class="sidebarOpen ? 'px-3 justify-between' : 'justify-center'">
                    <span x-show="sidebarOpen" class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400 whitespace-nowrap">{{ __('Layanan Pesanan') }}</span>
                    <span class="h-px bg-slate-800" :class="sidebarOpen ? 'w-8' : 'w-6 my-1'"></span>
                </div>

                <!-- Dashboard -->
                @php $isActive = request()->routeIs('customer.dashboard'); @endphp
                <a href="{{ route('customer.dashboard') }}" 
                   class="group relative flex items-center rounded-xl transition-all duration-200 {{ $isActive ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                   :class="sidebarOpen ? 'px-3 py-2 justify-start' : 'px-1.5 py-2 justify-center'"
                   title="{{ __('Dashboard Saya') }}">
                    @if ($isActive)
                        <span class="absolute left-0 top-2 bottom-2 w-1 bg-white rounded-r-full"></span>
                    @endif
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 shrink-0 {{ $isActive ? 'bg-white/10 text-white' : 'text-cyan-400 group-hover:text-cyan-300 group-hover:bg-cyan-500/10' }}"
                         :class="sidebarOpen ? 'mr-2.5' : 'mr-0'">
                        <i class="fa-solid fa-house text-sm"></i>
                    </div>
                    <span x-show="sidebarOpen" class="truncate whitespace-nowrap">{{ __('Dashboard Saya') }}</span>
                </a>

                <!-- Order Water -->
                @php $isActive = request()->routeIs('customer.orders.create'); @endphp
                <a href="{{ route('customer.orders.create') }}" 
                   class="group relative flex items-center rounded-xl transition-all duration-200 {{ $isActive ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                   :class="sidebarOpen ? 'px-3 py-2 justify-between' : 'px-1.5 py-2 justify-center'"
                   title="{{ __('Pesan Air Galon') }}">
                    @if ($isActive)
                        <span class="absolute left-0 top-2 bottom-2 w-1 bg-white rounded-r-full"></span>
                    @endif
                    <div class="flex items-center min-w-0">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 shrink-0 {{ $isActive ? 'bg-white/10 text-white' : 'text-blue-400 group-hover:text-blue-300 group-hover:bg-blue-500/10' }}"
                             :class="sidebarOpen ? 'mr-2.5' : 'mr-0'">
                            <i class="fa-solid fa-cart-plus text-sm"></i>
                        </div>
                        <span x-show="sidebarOpen" class="truncate whitespace-nowrap">{{ __('Pesan Air Galon') }}</span>
                    </div>
                    <span x-show="sidebarOpen" class="px-2 py-0.5 rounded-full text-[9px] font-black bg-cyan-500 text-white shadow-2xs whitespace-nowrap">
                        {{ __('Pesan Cepat') }}
                    </span>
                </a>

                <!-- Orders History & Tracking -->
                @php $isActive = request()->routeIs('customer.orders.index') || request()->routeIs('customer.orders.show') || request()->routeIs('customer.orders.track'); @endphp
                <a href="{{ route('customer.orders.index') }}" 
                   class="group relative flex items-center rounded-xl transition-all duration-200 {{ $isActive ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                   :class="sidebarOpen ? 'px-3 py-2 justify-between' : 'px-1.5 py-2 justify-center'"
                   title="{{ __('Pesanan Saya') }}">
                    @if ($isActive)
                        <span class="absolute left-0 top-2 bottom-2 w-1 bg-white rounded-r-full"></span>
                    @endif
                    <div class="flex items-center min-w-0">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 shrink-0 relative {{ $isActive ? 'bg-white/10 text-white' : 'text-emerald-400 group-hover:text-emerald-300 group-hover:bg-emerald-500/10' }}"
                             :class="sidebarOpen ? 'mr-2.5' : 'mr-0'">
                            <i class="fa-solid fa-box-archive text-sm"></i>
                            @if ($activeCustomerOrdersCount > 0)
                                <span x-show="!sidebarOpen" class="absolute -top-1 -right-1 w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse border border-slate-900"></span>
                            @endif
                        </div>
                        <span x-show="sidebarOpen" class="truncate whitespace-nowrap">{{ __('Pesanan Saya') }}</span>
                    </div>
                    @if ($activeCustomerOrdersCount > 0)
                        <span x-show="sidebarOpen" class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-500 text-slate-950 animate-pulse whitespace-nowrap">
                            {{ $activeCustomerOrdersCount }} {{ __('Aktif') }}
                        </span>
                    @endif
                </a>

                <!-- Address Book -->
                @php $isActive = request()->routeIs('customer.addresses.*'); @endphp
                <a href="{{ route('customer.addresses.index') }}" 
                   class="group relative flex items-center rounded-xl transition-all duration-200 {{ $isActive ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                   :class="sidebarOpen ? 'px-3 py-2 justify-start' : 'px-1.5 py-2 justify-center'"
                   title="{{ __('Alamat Pengantaran') }}">
                    @if ($isActive)
                        <span class="absolute left-0 top-2 bottom-2 w-1 bg-white rounded-r-full"></span>
                    @endif
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 shrink-0 {{ $isActive ? 'bg-white/10 text-white' : 'text-purple-400 group-hover:text-purple-300 group-hover:bg-purple-500/10' }}"
                         :class="sidebarOpen ? 'mr-2.5' : 'mr-0'">
                        <i class="fa-solid fa-location-dot text-sm"></i>
                    </div>
                    <span x-show="sidebarOpen" class="truncate whitespace-nowrap">{{ __('Alamat Pengantaran') }}</span>
                </a>
            </div>

            <!-- Group 2: Akun & Bantuan -->
            <div class="space-y-1">
                <div class="pb-1 flex items-center" :class="sidebarOpen ? 'px-3 justify-between' : 'justify-center'">
                    <span x-show="sidebarOpen" class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400 whitespace-nowrap">{{ __('Informasi & Toko') }}</span>
                    <span class="h-px bg-slate-800" :class="sidebarOpen ? 'w-8' : 'w-6 my-1'"></span>
                </div>

                <!-- Store Profile & Map -->
                @php $isActive = request()->routeIs('customer.store-profile'); @endphp
                <a href="{{ route('customer.store-profile') }}" 
                   class="group relative flex items-center rounded-xl transition-all duration-200 {{ $isActive ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                   :class="sidebarOpen ? 'px-3 py-2 justify-between' : 'px-1.5 py-2 justify-center'"
                   title="{{ __('Profil & Lokasi Toko') }}">
                    @if ($isActive)
                        <span class="absolute left-0 top-2 bottom-2 w-1 bg-white rounded-r-full"></span>
                    @endif
                    <div class="flex items-center min-w-0">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 shrink-0 {{ $isActive ? 'bg-white/10 text-white' : 'text-blue-400 group-hover:text-blue-300 group-hover:bg-blue-500/10' }}"
                             :class="sidebarOpen ? 'mr-2.5' : 'mr-0'">
                            <i class="fa-solid fa-store text-sm"></i>
                        </div>
                        <span x-show="sidebarOpen" class="truncate whitespace-nowrap">{{ __('Profil & Lokasi Toko') }}</span>
                    </div>
                    <span x-show="sidebarOpen" class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-blue-500/20 text-blue-300 border border-blue-500/30 whitespace-nowrap">
                        Maps
                    </span>
                </a>
            </div>

        </nav>

        <!-- Sidebar Bottom: Customer Profile & Dropup Menu with Logout -->
        <div x-data="{ userMenuOpen: false }" 
             class="relative bg-slate-950 border-t border-slate-800 shrink-0 transition-all duration-300"
             :class="sidebarOpen ? 'p-3' : 'p-2 flex justify-center'">
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
                 class="absolute bottom-full mb-2 bg-slate-900 border border-slate-700/90 rounded-2xl shadow-2xl p-2 z-50 overflow-hidden"
                 :class="sidebarOpen ? 'left-2 right-2' : 'left-2 w-60'"
                 style="display: none;">
                
                <!-- Account Summary -->
                <div class="px-3 py-2.5 bg-slate-950/80 rounded-xl mb-1.5 border border-slate-800">
                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">{{ __('Akun Pelanggan') }}</p>
                    <p class="text-xs font-bold text-white truncate mt-0.5">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] text-slate-400 truncate">{{ Auth::user()->email }}</p>
                    <div class="mt-1.5 flex items-center">
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-cyan-500/10 text-cyan-400 border border-cyan-500/30">
                            <i class="fa-solid fa-user-tag mr-1 text-[9px]"></i>
                            {{ strtoupper(str_replace('_', ' ', Auth::user()->profile?->customer_type ?? 'Household')) }}
                        </span>
                    </div>
                </div>

                <!-- Fast Actions -->
                <div class="space-y-0.5">
                    <a href="{{ route('customer.profile') }}" 
                       class="flex items-center px-3 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800 transition">
                        <i class="fa-solid fa-user-gear w-5 text-slate-400 text-center mr-2"></i>
                        <span>{{ __('Profil & Kata Sandi') }}</span>
                    </a>
                </div>

                <div class="my-1.5 border-t border-slate-800"></div>

                <!-- Logout Action -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" 
                            class="w-full flex items-center px-3 py-2 rounded-xl text-xs font-bold text-rose-400 hover:text-white hover:bg-rose-600 transition group cursor-pointer">
                        <i class="fa-solid fa-arrow-right-from-bracket w-5 text-rose-400 group-hover:text-white text-center mr-2"></i>
                        <span>{{ __('Keluar (Logout)') }}</span>
                    </button>
                </form>
            </div>

            <!-- Profile Trigger Button -->
            <button type="button" 
                    @click="userMenuOpen = !userMenuOpen"
                    class="flex items-center p-1.5 rounded-xl hover:bg-slate-900 transition text-left group cursor-pointer border border-transparent"
                    :class="sidebarOpen ? 'w-full justify-between' : 'justify-center'"
                    title="{{ Auth::user()->name }}">
                <div class="flex items-center overflow-hidden" :class="sidebarOpen ? 'space-x-2.5' : ''">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-cyan-600 to-blue-600 text-white font-black flex items-center justify-center text-xs shrink-0 shadow-sm group-hover:scale-105 transition-transform duration-200">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="overflow-hidden" x-show="sidebarOpen">
                        <div class="text-xs font-bold text-white truncate group-hover:text-cyan-300 transition">
                            {{ Auth::user()->name }}
                        </div>
                        <div class="text-[10px] text-cyan-400 uppercase font-semibold tracking-wider truncate">
                            {{ str_replace('_', ' ', Auth::user()->profile?->customer_type ?? 'Household') }}
                        </div>
                    </div>
                </div>

                <div x-show="sidebarOpen" class="text-slate-400 group-hover:text-white transition p-1 shrink-0">
                    <i class="fa-solid fa-chevron-up text-xs transition-transform duration-200" :class="{'rotate-180': userMenuOpen}"></i>
                </div>
            </button>
        </div>

    </aside>

    <!-- MAIN RIGHT CONTENT WRAPPER (Smooth Responsive Transition, Non-blocking) -->
    <div class="flex-1 flex flex-col min-w-0 transition-all duration-300 ease-in-out"
         :class="sidebarOpen ? 'md:pl-64' : 'md:pl-16'">

        <!-- Top Header Bar -->
        <header class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-30 shadow-2xs">
            <div class="flex items-center space-x-3">
                <!-- Mobile only toggle button if screen is small so mobile users can easily open -->
                <button type="button" 
                        @click="sidebarOpen = !sidebarOpen" 
                        class="md:hidden p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-cyan-50 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700 transition cursor-pointer"
                        title="{{ __('Buka / Tutup Navigasi') }}">
                    <i class="fa-solid fa-bars-staggered text-base"></i>
                </button>

                <div>
                    <h1 class="text-base sm:text-lg font-black text-slate-900 dark:text-white tracking-tight flex items-center">
                        @yield('title', __('Portal Pelanggan'))
                    </h1>
                </div>
            </div>

            <!-- Header Right Shortcut Controls -->
            <div class="flex items-center space-x-2 sm:space-x-3">
                <!-- Global Quick Search Trigger -->
                <button type="button" 
                        @click="$dispatch('open-global-search')"
                        class="hidden md:inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700 transition cursor-pointer space-x-2">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    <span>{{ __('Cari...') }}</span>
                    <kbd class="px-1.5 py-0.5 text-[10px] font-mono bg-white dark:bg-slate-900 rounded border border-slate-200 dark:border-slate-700">Ctrl+K</kbd>
                </button>

                <!-- Notification Bell -->
                <x-notification-bell />

                <!-- Theme and Language Switcher -->
                @include('partials.theme-lang-controls')

                <!-- Direct Order CTA -->
                <a href="{{ route('customer.orders.create') }}" 
                   class="px-3.5 py-1.5 rounded-xl text-xs font-extrabold text-white bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 shadow-sm transition flex items-center">
                    <i class="fa-solid fa-cart-plus mr-1.5"></i>
                    <span>{{ __('Pesan Air') }}</span>
                </a>
            </div>
        </header>

        <!-- Breadcrumbs Navigation Sub-header Bar -->
        <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border-b border-slate-200/80 dark:border-slate-800/80 px-4 sm:px-6 lg:px-8 py-2.5">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                @hasSection('breadcrumbs')
                    @yield('breadcrumbs')
                @else
                    <x-breadcrumbs />
                @endif

                <div class="hidden sm:flex items-center space-x-2 text-[11px] text-slate-400 dark:text-slate-500 font-medium shrink-0">
                    <i class="fa-regular fa-clock text-slate-400"></i>
                    <span>{{ now()->translatedFormat('l, d M Y') }}</span>
                </div>
            </div>
        </div>

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
        <footer class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 py-4 px-6 text-xs text-slate-500 dark:text-slate-400 flex flex-col sm:flex-row justify-between items-center gap-2">
            <div class="flex items-center space-x-2">
                <span class="font-bold text-slate-800 dark:text-slate-200">AquaFlow Member Portal</span>
                <span>&bull;</span>
                <span>Layanan Pesan Antar Air Minum Higienis</span>
            </div>
            <div class="text-[11px] text-slate-400 dark:text-slate-500">
                Pusat Bantuan WhatsApp: <a href="https://wa.me/{{ $cleanWhatsapp }}" target="_blank" class="font-bold text-emerald-600 dark:text-emerald-400 hover:underline">{{ \App\Models\BusinessSetting::get('company_phone', '0881-0261-46848') }}</a>
            </div>
        </footer>

    </div>

    <!-- Floating WhatsApp Support Button -->
    <a href="https://wa.me/{{ $cleanWhatsapp }}?text=Halo%20Admin%20AquaFlow,%20saya%20mau%20tanya%20pesanan" target="_blank" 
       class="fixed bottom-20 md:bottom-6 right-5 z-40 bg-emerald-500 hover:bg-emerald-600 text-white w-12 h-12 sm:w-14 sm:h-14 rounded-full flex items-center justify-center shadow-lg hover:shadow-xl hover:scale-105 transition duration-200">
        <i class="fa-brands fa-whatsapp text-2xl sm:text-3xl"></i>
    </a>

    <!-- Mobile Bottom Navigation & Toast Feedback -->
    <x-bottom-nav />
    <x-toast />

    <!-- Global Confirmation Modal for Delete/Cancel Actions -->
    <x-confirm-modal />

    <!-- Global Quick Search (Ctrl+K or /) -->
    <x-global-search />

    @stack('scripts')
</body>
</html>
