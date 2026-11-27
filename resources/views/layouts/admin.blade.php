@php
    $user = Auth::user();

    // Admin Theme Configuration
    $theme = [
        'role_title'         => 'ADMIN',
        'role_icon'          => 'fa-shield-halved text-cyan-400',
        
        // Sidebar Main Background & Borders
        'sidebar_bg_style'   => 'linear-gradient(180deg, #0a2142 0%, #06162d 45%, #030d1c 100%)',
        'sidebar_border'     => 'border-sky-800/40',
        'sidebar_header_style'=> 'background-color: rgba(8, 29, 58, 0.95);',
        'sidebar_footer_style'=> 'background-color: rgba(5, 20, 41, 0.95);',
        'sidebar_scrollbar'  => 'rgba(56, 189, 248, 0.45)',
        'sidebar_thumb'      => 'rgba(56, 189, 248, 0.35)',
        'sidebar_thumb_hover'=> 'rgba(56, 189, 248, 0.65)',
        
        // Brand & Header Badges
        'role_badge_bg'      => 'bg-blue-900/80 text-cyan-200 border-cyan-600/60 shadow-xs',
        'brand_gradient'     => 'from-blue-600 via-sky-500 to-cyan-500 shadow-blue-950/70',
        'accent_text'        => 'text-cyan-400',
        'accent_border'      => 'border-cyan-500',
        
        // Navigation Links
        'nav_active'         => 'bg-gradient-to-r from-blue-600 to-cyan-600 text-white font-bold shadow-md shadow-blue-950/60',
        'nav_inactive'       => 'text-sky-200/75 hover:text-white hover:bg-sky-900/40',
        'indicator_active'   => 'bg-cyan-300',
        'icon_box_active'    => 'bg-white/20 text-white shadow-xs',
        'icon_box_inactive'  => 'bg-sky-950/70 text-sky-300 group-hover:text-cyan-300 group-hover:bg-sky-900/60 group-hover:border-sky-700/50',
        
        // Group Titles & Dividers
        'group_title_text'   => 'text-sky-300/90',
        'group_divider'      => 'bg-sky-800/40',
        
        // Profile Bottom & Dropup Menu
        'avatar_gradient'    => 'from-blue-600 via-sky-600 to-cyan-500 ring-2 ring-cyan-500/40',
        'profile_role_badge' => 'bg-cyan-500/20 text-cyan-200 border-cyan-500/40',
        'profile_btn_hover'  => 'hover:bg-sky-900/35 hover:border-sky-800/40',
        'dropup_bg'          => 'bg-[#071933] border-sky-700/60',
        'dropup_summary_bg'  => 'bg-[#040f20]/90 border-sky-800/50',
        'dropup_item_hover'  => 'hover:bg-sky-900/40',
        
        // Topbar Controls
        'toggle_btn_hover'   => 'hover:text-cyan-600 hover:bg-cyan-50 hover:border-cyan-200',
    ];

    $pendingOrdersBadge = \App\Models\Order::where('status', 'pending')->count();
    $activeDeliveriesBadge = \App\Models\Delivery::whereIn('status', ['assigned', 'in_transit', 'arrived'])->count();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100 dark:bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'AquaFlow') }} - @yield('page_title', __('Dashboard'))</title>

    <!-- Vite CSS & JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Prevent Dark Mode Flash -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    @stack('styles')

    <style>
        /* Sleek custom scrollbar for sidebar navigation */
        .custom-sidebar-scroll {
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: {{ $theme['sidebar_scrollbar'] }} transparent;
        }
        .custom-sidebar-scroll::-webkit-scrollbar {
            width: 6px;
        }
        .custom-sidebar-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-sidebar-scroll::-webkit-scrollbar-thumb {
            background-color: {{ $theme['sidebar_thumb'] }};
            border-radius: 9999px;
        }
        .custom-sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background-color: {{ $theme['sidebar_thumb_hover'] }};
        }
    </style>
</head>
<body class="min-h-screen bg-slate-100 dark:bg-slate-950 text-slate-800 dark:text-slate-100 antialiased font-sans flex flex-col" x-data="adminLayoutData()">

    <!-- Mobile Backdrop Overlay -->
    <div x-show="sidebarOpen" 
         @click="sidebarOpen = false" 
         x-cloak
         class="md:hidden fixed inset-0 z-30 bg-slate-950/60 backdrop-blur-xs transition-opacity duration-300">
    </div>

    <!-- DEDICATED LEFT SIDEBAR NAVIGATION (Non-blocking & Independently Scrollable) -->
    <aside class="fixed inset-y-0 left-0 z-40 h-screen max-h-screen text-slate-200 flex flex-col border-r {{ $theme['sidebar_border'] }} shadow-2xl transition-all duration-300 ease-in-out"
           style="background: {{ $theme['sidebar_bg_style'] }};"
           :class="sidebarOpen ? 'w-64 translate-x-0' : '-translate-x-full md:translate-x-0 md:w-16'">

        <!-- Sidebar Top: Brand & Depot Status -->
        <div class="h-16 flex items-center border-b {{ $theme['sidebar_border'] }} shrink-0 px-3 transition-all duration-300"
             :class="sidebarOpen ? 'justify-between' : 'justify-center'"
             style="{{ $theme['sidebar_header_style'] }}">
            <a href="{{ route('admin.dashboard') }}" 
               x-show="sidebarOpen"
               class="flex items-center space-x-2.5 overflow-hidden group">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr {{ $theme['brand_gradient'] }} flex items-center justify-center text-white shadow-md shrink-0 group-hover:scale-105 transition-transform duration-200">
                    <i class="fa-solid fa-droplet text-base"></i>
                </div>
                <div class="overflow-hidden">
                    <div class="text-sm font-black text-white tracking-tight flex items-center whitespace-nowrap">
                        <span>Aqua<span class="{{ $theme['accent_text'] }}">Flow</span></span>
                    </div>
                </div>
            </a>

            <!-- Toggle Button (Di dalam tampilan sidebar navigasi) -->
            <button type="button" @click="sidebarOpen = !sidebarOpen" 
                    class="text-slate-300 hover:text-white p-2 rounded-xl hover:bg-white/10 transition cursor-pointer flex items-center justify-center shrink-0" 
                    :title="sidebarOpen ? '{{ __('Tutup / Perkecil Navigasi') }}' : '{{ __('Buka / Perlebar Navigasi') }}'">
                <i class="fa-solid fa-bars-staggered text-base"></i>
            </button>
        </div>

        <!-- Sidebar Navigation Menu Links (Independently Scrollable with Role Theme) -->
        <nav class="flex-1 min-h-0 py-4 space-y-5 overflow-y-auto overscroll-contain custom-sidebar-scroll text-xs font-semibold transition-all duration-300"
             :class="sidebarOpen ? 'px-3' : 'px-2'">

            <!-- Group 1: Operasional Utama -->
            <div class="space-y-1">
                <div class="pb-1 flex items-center" :class="sidebarOpen ? 'px-3 justify-between' : 'justify-center'">
                    <span x-show="sidebarOpen" class="text-[10px] font-extrabold uppercase tracking-widest {{ $theme['group_title_text'] }} whitespace-nowrap">{{ __('Operasional Utama') }}</span>
                    <span class="h-px {{ $theme['group_divider'] }}" :class="sidebarOpen ? 'w-8' : 'w-6 my-1'"></span>
                </div>

                <!-- Dashboard -->
                @php $isActive = request()->routeIs('admin.dashboard'); @endphp
                <a href="{{ route('admin.dashboard') }}" 
                   class="group relative flex items-center rounded-xl transition-all duration-200 {{ $isActive ? $theme['nav_active'] : $theme['nav_inactive'] }}"
                   :class="sidebarOpen ? 'px-3 py-2 justify-start' : 'px-1.5 py-2 justify-center'"
                   title="{{ __('Ringkasan Bisnis') }}">
                    @if ($isActive)
                        <span class="absolute left-0 top-2 bottom-2 w-1 {{ $theme['indicator_active'] }} rounded-r-full"></span>
                    @endif
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 shrink-0 {{ $isActive ? $theme['icon_box_active'] : $theme['icon_box_inactive'] }} border border-transparent"
                         :class="sidebarOpen ? 'mr-2.5' : 'mr-0'">
                        <i class="fa-solid fa-chart-pie text-sm"></i>
                    </div>
                    <span x-show="sidebarOpen" class="truncate whitespace-nowrap">{{ __('Ringkasan Bisnis') }}</span>
                </a>

                <!-- Orders -->
                @php $isActive = request()->routeIs('admin.orders.*'); @endphp
                <a href="{{ route('admin.orders.index') }}" 
                   class="group relative flex items-center rounded-xl transition-all duration-200 {{ $isActive ? $theme['nav_active'] : $theme['nav_inactive'] }}"
                   :class="sidebarOpen ? 'px-3 py-2 justify-between' : 'px-1.5 py-2 justify-center'"
                   title="{{ __('Kelola Pesanan') }}">
                    @if ($isActive)
                        <span class="absolute left-0 top-2 bottom-2 w-1 {{ $theme['indicator_active'] }} rounded-r-full"></span>
                    @endif
                    <div class="flex items-center min-w-0">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 shrink-0 relative {{ $isActive ? $theme['icon_box_active'] : $theme['icon_box_inactive'] }} border border-transparent"
                             :class="sidebarOpen ? 'mr-2.5' : 'mr-0'">
                            <i class="fa-solid fa-boxes-stacked text-sm"></i>
                            <span x-show="!sidebarOpen && pendingCount > 0" class="absolute -top-1 -right-1 w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse border border-slate-900"></span>
                        </div>
                        <span x-show="sidebarOpen" class="truncate whitespace-nowrap">{{ __('Kelola Pesanan') }}</span>
                    </div>
                    <span x-show="sidebarOpen && pendingCount > 0" class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-500 text-white shadow-xs animate-pulse" x-text="pendingCount + ' {{ __('Baru') }}'">
                    </span>
                </a>

                <!-- Deliveries -->
                @php $isActive = request()->routeIs('admin.deliveries.index') || request()->routeIs('admin.deliveries.show'); @endphp
                <a href="{{ route('admin.deliveries.index') }}" 
                   class="group relative flex items-center rounded-xl transition-all duration-200 {{ $isActive ? $theme['nav_active'] : $theme['nav_inactive'] }}"
                   :class="sidebarOpen ? 'px-3 py-2 justify-between' : 'px-1.5 py-2 justify-center'"
                   title="{{ __('Tugas Pengantaran') }}">
                    @if ($isActive)
                        <span class="absolute left-0 top-2 bottom-2 w-1 {{ $theme['indicator_active'] }} rounded-r-full"></span>
                    @endif
                    <div class="flex items-center min-w-0">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 shrink-0 relative {{ $isActive ? $theme['icon_box_active'] : $theme['icon_box_inactive'] }} border border-transparent"
                             :class="sidebarOpen ? 'mr-2.5' : 'mr-0'">
                            <i class="fa-solid fa-truck-fast text-sm"></i>
                            @if ($activeDeliveriesBadge > 0)
                                <span x-show="!sidebarOpen" class="absolute -top-1 -right-1 w-2.5 h-2.5 rounded-full bg-emerald-500 border border-slate-900"></span>
                            @endif
                        </div>
                        <span x-show="sidebarOpen" class="truncate whitespace-nowrap">{{ __('Tugas Pengantaran') }}</span>
                    </div>
                    @if ($activeDeliveriesBadge > 0)
                        <span x-show="sidebarOpen" class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-500 text-white shadow-xs">
                            {{ $activeDeliveriesBadge }}
                        </span>
                    @endif
                </a>

                <!-- Live Radar Map -->
                @php $isActive = request()->routeIs('admin.deliveries.map'); @endphp
                <a href="{{ route('admin.deliveries.map') }}" 
                   class="group relative flex items-center rounded-xl transition-all duration-200 {{ $isActive ? $theme['nav_active'] : $theme['nav_inactive'] }}"
                   :class="sidebarOpen ? 'px-3 py-2 justify-between' : 'px-1.5 py-2 justify-center'"
                   title="{{ __('Peta Radar Real-Time') }}">
                    @if ($isActive)
                        <span class="absolute left-0 top-2 bottom-2 w-1 {{ $theme['indicator_active'] }} rounded-r-full"></span>
                    @endif
                    <div class="flex items-center min-w-0">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 shrink-0 relative {{ $isActive ? $theme['icon_box_active'] : $theme['icon_box_inactive'] }} border border-transparent"
                             :class="sidebarOpen ? 'mr-2.5' : 'mr-0'">
                            <i class="fa-solid fa-satellite-dish text-sm"></i>
                            <span x-show="!sidebarOpen" class="absolute -top-0.5 -right-0.5 w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                        </div>
                        <span x-show="sidebarOpen" class="truncate whitespace-nowrap">{{ __('Peta Radar Real-Time') }}</span>
                    </div>
                    <span x-show="sidebarOpen" class="relative flex h-2 w-2 mr-1">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                </a>

                <!-- Customer Addresses Management -->
                @php $isActive = request()->routeIs('admin.addresses.*'); @endphp
                <a href="{{ route('admin.addresses.index') }}" 
                   class="group relative flex items-center rounded-xl transition-all duration-200 {{ $isActive ? $theme['nav_active'] : $theme['nav_inactive'] }}"
                   :class="sidebarOpen ? 'px-3 py-2 justify-start' : 'px-1.5 py-2 justify-center'"
                   title="{{ __('Alamat Pelanggan') }}">
                    @if ($isActive)
                        <span class="absolute left-0 top-2 bottom-2 w-1 {{ $theme['indicator_active'] }} rounded-r-full"></span>
                    @endif
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 shrink-0 {{ $isActive ? $theme['icon_box_active'] : $theme['icon_box_inactive'] }} border border-transparent"
                         :class="sidebarOpen ? 'mr-2.5' : 'mr-0'">
                        <i class="fa-solid fa-map-location-dot text-sm"></i>
                    </div>
                    <span x-show="sidebarOpen" class="truncate whitespace-nowrap">{{ __('Alamat Pelanggan') }}</span>
                </a>
            </div>

            <!-- Group 2: Produk & Layanan Wilayah -->
            <div class="space-y-1">
                <div class="pb-1 flex items-center" :class="sidebarOpen ? 'px-3 justify-between' : 'justify-center'">
                    <span x-show="sidebarOpen" class="text-[10px] font-extrabold uppercase tracking-widest {{ $theme['group_title_text'] }} whitespace-nowrap">{{ __('Katalog & Logistik') }}</span>
                    <span class="h-px {{ $theme['group_divider'] }}" :class="sidebarOpen ? 'w-8' : 'w-6 my-1'"></span>
                </div>

                <!-- Products -->
                @php $isActive = request()->routeIs('admin.products.*'); @endphp
                <a href="{{ route('admin.products.index') }}" 
                   class="group relative flex items-center rounded-xl transition-all duration-200 {{ $isActive ? $theme['nav_active'] : $theme['nav_inactive'] }}"
                   :class="sidebarOpen ? 'px-3 py-2 justify-start' : 'px-1.5 py-2 justify-center'"
                   title="{{ __('Produk & Stok Galon') }}">
                    @if ($isActive)
                        <span class="absolute left-0 top-2 bottom-2 w-1 {{ $theme['indicator_active'] }} rounded-r-full"></span>
                    @endif
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 shrink-0 {{ $isActive ? $theme['icon_box_active'] : $theme['icon_box_inactive'] }} border border-transparent"
                         :class="sidebarOpen ? 'mr-2.5' : 'mr-0'">
                        <i class="fa-solid fa-bottle-water text-sm"></i>
                    </div>
                    <span x-show="sidebarOpen" class="truncate whitespace-nowrap">{{ __('Produk & Stok Galon') }}</span>
                </a>

                <!-- Service Areas -->
                @php $isActive = request()->routeIs('admin.service-areas.*'); @endphp
                <a href="{{ route('admin.service-areas.index') }}" 
                   class="group relative flex items-center rounded-xl transition-all duration-200 {{ $isActive ? $theme['nav_active'] : $theme['nav_inactive'] }}"
                   :class="sidebarOpen ? 'px-3 py-2 justify-start' : 'px-1.5 py-2 justify-center'"
                   title="{{ __('Wilayah & Ongkir') }}">
                    @if ($isActive)
                        <span class="absolute left-0 top-2 bottom-2 w-1 {{ $theme['indicator_active'] }} rounded-r-full"></span>
                    @endif
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 shrink-0 {{ $isActive ? $theme['icon_box_active'] : $theme['icon_box_inactive'] }} border border-transparent"
                         :class="sidebarOpen ? 'mr-2.5' : 'mr-0'">
                        <i class="fa-solid fa-signs-post text-sm"></i>
                    </div>
                    <span x-show="sidebarOpen" class="truncate whitespace-nowrap">{{ __('Wilayah & Ongkir') }}</span>
                </a>
            </div>

            <!-- Group 3: Finansial & Reputasi -->
            <div class="space-y-1">
                <div class="pb-1 flex items-center" :class="sidebarOpen ? 'px-3 justify-between' : 'justify-center'">
                    <span x-show="sidebarOpen" class="text-[10px] font-extrabold uppercase tracking-widest {{ $theme['group_title_text'] }} whitespace-nowrap">{{ __('Keuangan & Reputasi') }}</span>
                    <span class="h-px {{ $theme['group_divider'] }}" :class="sidebarOpen ? 'w-8' : 'w-6 my-1'"></span>
                </div>

                <!-- Finance -->
                @php $isActive = request()->routeIs('admin.finance.*'); @endphp
                <a href="{{ route('admin.finance.index') }}" 
                   class="group relative flex items-center rounded-xl transition-all duration-200 {{ $isActive ? $theme['nav_active'] : $theme['nav_inactive'] }}"
                   :class="sidebarOpen ? 'px-3 py-2 justify-start' : 'px-1.5 py-2 justify-center'"
                   title="{{ __('Keuangan & Beban') }}">
                    @if ($isActive)
                        <span class="absolute left-0 top-2 bottom-2 w-1 {{ $theme['indicator_active'] }} rounded-r-full"></span>
                    @endif
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 shrink-0 {{ $isActive ? $theme['icon_box_active'] : $theme['icon_box_inactive'] }} border border-transparent"
                         :class="sidebarOpen ? 'mr-2.5' : 'mr-0'">
                        <i class="fa-solid fa-wallet text-sm"></i>
                    </div>
                    <span x-show="sidebarOpen" class="truncate whitespace-nowrap">{{ __('Keuangan & Beban') }}</span>
                </a>

                <!-- Reviews -->
                @php $isActive = request()->routeIs('admin.reviews.*'); @endphp
                <a href="{{ route('admin.reviews.index') }}" 
                   class="group relative flex items-center rounded-xl transition-all duration-200 {{ $isActive ? $theme['nav_active'] : $theme['nav_inactive'] }}"
                   :class="sidebarOpen ? 'px-3 py-2 justify-start' : 'px-1.5 py-2 justify-center'"
                   title="{{ __('Ulasan Pelanggan') }}">
                    @if ($isActive)
                        <span class="absolute left-0 top-2 bottom-2 w-1 {{ $theme['indicator_active'] }} rounded-r-full"></span>
                    @endif
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 shrink-0 {{ $isActive ? $theme['icon_box_active'] : $theme['icon_box_inactive'] }} border border-transparent"
                         :class="sidebarOpen ? 'mr-2.5' : 'mr-0'">
                        <i class="fa-solid fa-star text-sm"></i>
                    </div>
                    <span x-show="sidebarOpen" class="truncate whitespace-nowrap">{{ __('Ulasan Pelanggan') }}</span>
                </a>
            </div>

            <!-- Group 4: Sistem & Pengguna -->
            <div class="space-y-1">
                <div class="pb-1 flex items-center" :class="sidebarOpen ? 'px-3 justify-between' : 'justify-center'">
                    <span x-show="sidebarOpen" class="text-[10px] font-extrabold uppercase tracking-widest {{ $theme['group_title_text'] }} whitespace-nowrap">{{ __('Sistem & Akses') }}</span>
                    <span class="h-px {{ $theme['group_divider'] }}" :class="sidebarOpen ? 'w-8' : 'w-6 my-1'"></span>
                </div>

                <!-- Users -->
                @php $isActive = request()->routeIs('admin.users.*'); @endphp
                <a href="{{ route('admin.users.index') }}" 
                   class="group relative flex items-center rounded-xl transition-all duration-200 {{ $isActive ? $theme['nav_active'] : $theme['nav_inactive'] }}"
                   :class="sidebarOpen ? 'px-3 py-2 justify-start' : 'px-1.5 py-2 justify-center'"
                   title="{{ __('Manajemen Pengguna') }}">
                    @if ($isActive)
                        <span class="absolute left-0 top-2 bottom-2 w-1 {{ $theme['indicator_active'] }} rounded-r-full"></span>
                    @endif
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 shrink-0 {{ $isActive ? $theme['icon_box_active'] : $theme['icon_box_inactive'] }} border border-transparent"
                         :class="sidebarOpen ? 'mr-2.5' : 'mr-0'">
                        <i class="fa-solid fa-users-gear text-sm"></i>
                    </div>
                    <span x-show="sidebarOpen" class="truncate whitespace-nowrap">{{ __('Manajemen Pengguna') }}</span>
                </a>

                <!-- View Public Web -->
                <a href="{{ route('home') }}" target="_blank"
                   class="group flex items-center rounded-xl {{ $theme['nav_inactive'] }} transition-all duration-200"
                   :class="sidebarOpen ? 'px-3 py-2 justify-start' : 'px-1.5 py-2 justify-center'"
                   title="{{ __('Buka Web Publik') }}">
                    <div class="w-8 h-8 rounded-lg {{ $theme['icon_box_inactive'] }} border border-transparent flex items-center justify-center transition-all duration-200 shrink-0"
                         :class="sidebarOpen ? 'mr-2.5' : 'mr-0'">
                        <i class="fa-solid fa-arrow-up-right-from-square text-sm"></i>
                    </div>
                    <span x-show="sidebarOpen" class="truncate whitespace-nowrap">{{ __('Buka Web Publik') }}</span>
                </a>
            </div>

        </nav>

        <!-- Sidebar Bottom: Admin Profile & Dropup Menu with Logout -->
        <div x-data="{ userMenuOpen: false }" 
             class="relative border-t {{ $theme['sidebar_border'] }} shrink-0 transition-all duration-300"
             :class="sidebarOpen ? 'p-3' : 'p-2 flex justify-center'"
             style="{{ $theme['sidebar_footer_style'] }}">
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
                 class="absolute bottom-full mb-2 {{ $theme['dropup_bg'] }} border rounded-2xl shadow-2xl p-2 z-50 overflow-hidden"
                 :class="sidebarOpen ? 'left-2 right-2' : 'left-2 w-60'"
                 style="display: none;">
                
                <!-- Account Summary -->
                <div class="px-3 py-2.5 {{ $theme['dropup_summary_bg'] }} rounded-xl mb-1.5 border">
                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">{{ __('Sedang Masuk Sebagai') }}</p>
                    <p class="text-xs font-bold text-white truncate mt-0.5">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] text-slate-400 truncate">{{ Auth::user()->email }}</p>
                </div>

                <!-- Profile Menu Option -->
                <a href="{{ route('admin.profile') }}" 
                   class="w-full flex items-center px-3 py-2 rounded-xl text-xs font-bold text-sky-200 hover:text-white hover:bg-sky-900/40 transition group cursor-pointer mb-1">
                    <i class="fa-solid fa-user-gear w-5 text-cyan-400 group-hover:text-white text-center mr-2"></i>
                    <span>{{ __('Profil Akun') }}</span>
                </a>

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
                    class="flex items-center p-1.5 rounded-xl {{ $theme['profile_btn_hover'] }} transition text-left group cursor-pointer border border-transparent"
                    :class="sidebarOpen ? 'w-full justify-between' : 'justify-center'">
                <div class="flex items-center overflow-hidden" :class="sidebarOpen ? 'space-x-2.5' : ''">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr {{ $theme['avatar_gradient'] }} text-white font-black flex items-center justify-center text-xs shrink-0 shadow-sm group-hover:scale-105 transition-transform duration-200">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="overflow-hidden" x-show="sidebarOpen">
                        <div class="text-xs font-bold text-white truncate group-hover:{{ $theme['accent_text'] }} transition">
                            {{ Auth::user()->name }}
                        </div>
                        <div class="text-[10px] text-slate-400 truncate mt-0.5 whitespace-nowrap">
                            {{ Auth::user()->email }}
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
                <button type="button" 
                        @click="sidebarOpen = !sidebarOpen" 
                        class="md:hidden p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700 transition cursor-pointer"
                        title="{{ __('Buka / Tutup Navigasi') }}">
                    <i class="fa-solid fa-bars-staggered text-base"></i>
                </button>

                <h1 class="text-base sm:text-lg font-black text-slate-900 dark:text-white tracking-tight flex items-center">
                    @yield('page_title', __('Dashboard'))
                </h1>
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

                <!-- Theme (Dark/Light) & Language Controls -->
                @include('partials.theme-lang-controls')
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

    <!-- Mobile Bottom Navigation & Toast Feedback -->
    <x-bottom-nav />
    <x-toast />

    <!-- Global Confirmation Modal for Delete/Cancel Actions -->
    <x-confirm-modal />

    <!-- Global Quick Search (Ctrl+K or /) -->
    <x-global-search />

    <!-- Real-time Visual Order Notification Toast Banner (No Sound) -->
    <div x-show="newOrderToast !== null" 
         x-cloak
         x-transition:enter="transform ease-out duration-300 transition"
         x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-4"
         x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed top-20 right-4 sm:right-6 z-50 max-w-sm w-full bg-white dark:bg-slate-900 border-2 border-rose-500 rounded-2xl shadow-2xl p-4 overflow-hidden"
         style="display: none;">
        <div class="flex items-start gap-3">
            <div class="relative shrink-0">
                <div class="w-10 h-10 rounded-xl bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-bell"></i>
                </div>
                <span class="absolute -top-1 -right-1 flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                </span>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-black uppercase tracking-wider text-rose-600 dark:text-rose-400 flex items-center gap-1.5">
                        <span class="inline-block w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                        {{ __('Pesanan Baru Masuk!') }}
                    </span>
                    <button type="button" @click="newOrderToast = null" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs p-1 cursor-pointer">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <p class="text-xs font-bold text-slate-900 dark:text-white truncate mt-1">
                    <span x-text="newOrderToast?.recipient_name"></span>
                    <span class="text-slate-400 font-mono text-[11px]" x-text="'(#' + (newOrderToast?.order_number || '') + ')'"></span>
                </p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                    {{ __('Total') }}: <strong class="text-slate-800 dark:text-slate-200" x-text="'Rp ' + (newOrderToast?.total_amount || '0')"></strong> &bull; <span x-text="newOrderToast?.time_ago"></span>
                </p>
                <div class="mt-2.5 flex items-center gap-2">
                    <a :href="newOrderToast?.url || '{{ route('admin.orders.index') }}'" 
                       class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition shadow-xs flex items-center gap-1.5">
                        <i class="fa-solid fa-arrow-right text-[10px]"></i> {{ __('Lihat Pesanan') }}
                    </a>
                    <button type="button" @click="newOrderToast = null" class="px-2.5 py-1.5 text-xs font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400 cursor-pointer">
                        {{ __('Tutup') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function adminLayoutData() {
            return {
                sidebarOpen: (window.innerWidth >= 768),
                pendingCount: {{ $pendingOrdersBadge ?? 0 }},
                newOrderToast: null,
                pollTimer: null,
                toastTimer: null,

                init() {
                    this.pollTimer = setInterval(() => {
                        this.checkPendingOrders();
                    }, 15000);
                },

                async checkPendingOrders() {
                    try {
                        const res = await fetch("{{ route('admin.orders.pending-count') }}", {
                            headers: { 'Accept': 'application/json' }
                        });
                        if (!res.ok) return;
                        const data = await res.json();

                        if (typeof data.count === 'number') {
                            if (data.count > this.pendingCount && data.latest) {
                                this.showToast(data.latest);
                            }
                            this.pendingCount = data.count;
                        }
                    } catch (e) {
                        // ignore network glitches
                    }
                },

                showToast(latest) {
                    this.newOrderToast = latest;
                    if (this.toastTimer) clearTimeout(this.toastTimer);
                    this.toastTimer = setTimeout(() => {
                        this.newOrderToast = null;
                    }, 8000);
                }
            };
        }
    </script>

    @stack('scripts')
</body>
</html>
