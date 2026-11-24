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

    <style>
        /* Sleek custom scrollbar for sidebar navigation */
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
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased font-sans flex flex-col" x-data="{ sidebarOpen: (window.innerWidth >= 768) }">

    @php
        $user = Auth::user();
        $isSuperAdmin = $user && $user->role === 'super_admin';

        // Dynamic Role Primary Color & Theme Configuration
        $theme = [
            'is_super_admin'     => $isSuperAdmin,
            'role_title'         => $isSuperAdmin ? 'SUPER ADMIN' : 'ADMIN',
            'role_icon'          => $isSuperAdmin ? 'fa-crown text-amber-400' : 'fa-shield-halved text-cyan-400',
            'role_badge_bg'      => $isSuperAdmin 
                ? 'bg-violet-950/90 text-violet-300 border-violet-700/60 shadow-xs' 
                : 'bg-blue-950/90 text-cyan-300 border-cyan-700/60 shadow-xs',
            'brand_gradient'     => $isSuperAdmin
                ? 'from-violet-600 via-indigo-600 to-purple-700 shadow-violet-900/50'
                : 'from-blue-600 via-sky-500 to-cyan-500 shadow-blue-900/50',
            'accent_text'        => $isSuperAdmin ? 'text-violet-400' : 'text-cyan-400',
            'accent_border'      => $isSuperAdmin ? 'border-violet-500' : 'border-cyan-500',
            'nav_active'         => $isSuperAdmin
                ? 'bg-gradient-to-r from-violet-600 to-indigo-600 text-white font-bold shadow-md shadow-violet-950/50'
                : 'bg-gradient-to-r from-blue-600 to-cyan-600 text-white font-bold shadow-md shadow-blue-950/50',
            'nav_inactive'       => 'text-slate-400 hover:text-white hover:bg-slate-800/70',
            'indicator_active'   => $isSuperAdmin ? 'bg-amber-400' : 'bg-cyan-300',
            'icon_box_active'    => 'bg-white/20 text-white shadow-xs',
            'icon_box_inactive'  => $isSuperAdmin 
                ? 'bg-slate-900/90 text-slate-400 group-hover:text-violet-400 group-hover:bg-violet-950/40 group-hover:border-violet-800/40' 
                : 'bg-slate-900/90 text-slate-400 group-hover:text-cyan-400 group-hover:bg-cyan-950/40 group-hover:border-cyan-800/40',
            'avatar_gradient'    => $isSuperAdmin
                ? 'from-violet-600 via-purple-600 to-amber-500 ring-2 ring-violet-500/40'
                : 'from-blue-600 via-sky-600 to-cyan-500 ring-2 ring-cyan-500/40',
            'profile_role_badge' => $isSuperAdmin
                ? 'bg-violet-500/15 text-violet-300 border-violet-500/30'
                : 'bg-cyan-500/15 text-cyan-300 border-cyan-500/30',
            'toggle_btn_hover'   => $isSuperAdmin
                ? 'hover:text-violet-600 hover:bg-violet-50 hover:border-violet-200'
                : 'hover:text-cyan-600 hover:bg-cyan-50 hover:border-cyan-200',
        ];

        $pendingOrdersBadge = \App\Models\Order::where('status', 'pending')->count();
        $activeDeliveriesBadge = \App\Models\Delivery::whereIn('status', ['assigned', 'in_transit', 'arrived'])->count();
    @endphp

    <!-- DEDICATED LEFT SIDEBAR NAVIGATION (Non-blocking & Independently Scrollable) -->
    <aside class="fixed inset-y-0 left-0 z-40 w-64 h-screen max-h-screen bg-slate-950 text-slate-300 flex flex-col border-r border-slate-800/80 shadow-2xl transition-all duration-300 ease-in-out"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

        <!-- Sidebar Top: Brand & Depot Status -->
        <div class="h-16 flex items-center justify-between px-4 bg-slate-950 border-b border-slate-800/90 shrink-0">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 overflow-hidden group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr {{ $theme['brand_gradient'] }} flex items-center justify-center text-white shadow-md shrink-0 group-hover:scale-105 transition-transform duration-200">
                    <i class="fa-solid fa-droplet text-lg"></i>
                </div>
                <div class="overflow-hidden">
                    <div class="text-base font-black text-white tracking-tight flex items-center">
                        <span>Aqua<span class="{{ $theme['accent_text'] }}">Flow</span></span>
                        <span class="ml-2 px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider {{ $theme['role_badge_bg'] }} border flex items-center">
                            <i class="fa-solid {{ $theme['role_icon'] }} mr-1 text-[8px]"></i>
                            {{ $theme['role_title'] }}
                        </span>
                    </div>
                    <span class="text-[10px] flex items-center font-semibold text-emerald-400 mt-0.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse mr-1.5 shrink-0"></span>
                        Depot Online • Siap Antar
                    </span>
                </div>
            </a>

            <!-- Mobile Close Button -->
            <button type="button" @click="sidebarOpen = false" 
                    class="md:hidden text-slate-400 hover:text-white p-1.5 rounded-lg hover:bg-slate-850 transition" 
                    title="Tutup Navigasi">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Sidebar Navigation Menu Links (Independently Scrollable with Role Theme) -->
        <nav class="flex-1 min-h-0 px-3 py-4 space-y-6 overflow-y-auto overscroll-contain custom-sidebar-scroll text-xs font-semibold">

            <!-- Group 1: Operasional Utama -->
            <div class="space-y-1">
                <div class="px-3 pb-1 text-[10px] font-extrabold uppercase tracking-widest text-slate-500 flex items-center justify-between">
                    <span>Operasional Utama</span>
                    <span class="w-8 h-px bg-slate-800/80"></span>
                </div>

                <!-- Dashboard -->
                @php $isActive = request()->routeIs('admin.dashboard'); @endphp
                <a href="{{ route('admin.dashboard') }}" 
                   class="group relative flex items-center px-3 py-2 rounded-xl transition-all duration-200 {{ $isActive ? $theme['nav_active'] : $theme['nav_inactive'] }}">
                    @if ($isActive)
                        <span class="absolute left-0 top-2 bottom-2 w-1 {{ $theme['indicator_active'] }} rounded-r-full"></span>
                    @endif
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-2.5 transition-all duration-200 {{ $isActive ? $theme['icon_box_active'] : $theme['icon_box_inactive'] }} border border-transparent">
                        <i class="fa-solid fa-chart-pie text-sm"></i>
                    </div>
                    <span class="truncate">Ringkasan Bisnis</span>
                </a>

                <!-- Orders -->
                @php $isActive = request()->routeIs('admin.orders.*'); @endphp
                <a href="{{ route('admin.orders.index') }}" 
                   class="group relative flex items-center justify-between px-3 py-2 rounded-xl transition-all duration-200 {{ $isActive ? $theme['nav_active'] : $theme['nav_inactive'] }}">
                    @if ($isActive)
                        <span class="absolute left-0 top-2 bottom-2 w-1 {{ $theme['indicator_active'] }} rounded-r-full"></span>
                    @endif
                    <div class="flex items-center min-w-0">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-2.5 transition-all duration-200 {{ $isActive ? $theme['icon_box_active'] : $theme['icon_box_inactive'] }} border border-transparent">
                            <i class="fa-solid fa-boxes-stacked text-sm"></i>
                        </div>
                        <span class="truncate">Kelola Pesanan</span>
                    </div>
                    @if ($pendingOrdersBadge > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-500 text-slate-950 shadow-xs animate-pulse">
                            {{ $pendingOrdersBadge }} Baru
                        </span>
                    @endif
                </a>

                <!-- Deliveries -->
                @php $isActive = request()->routeIs('admin.deliveries.index') || request()->routeIs('admin.deliveries.show'); @endphp
                <a href="{{ route('admin.deliveries.index') }}" 
                   class="group relative flex items-center justify-between px-3 py-2 rounded-xl transition-all duration-200 {{ $isActive ? $theme['nav_active'] : $theme['nav_inactive'] }}">
                    @if ($isActive)
                        <span class="absolute left-0 top-2 bottom-2 w-1 {{ $theme['indicator_active'] }} rounded-r-full"></span>
                    @endif
                    <div class="flex items-center min-w-0">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-2.5 transition-all duration-200 {{ $isActive ? $theme['icon_box_active'] : $theme['icon_box_inactive'] }} border border-transparent">
                            <i class="fa-solid fa-truck-fast text-sm"></i>
                        </div>
                        <span class="truncate">Tugas Pengantaran</span>
                    </div>
                    @if ($activeDeliveriesBadge > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-500 text-white shadow-xs">
                            {{ $activeDeliveriesBadge }}
                        </span>
                    @endif
                </a>

                <!-- Live Radar Map -->
                @php $isActive = request()->routeIs('admin.deliveries.map'); @endphp
                <a href="{{ route('admin.deliveries.map') }}" 
                   class="group relative flex items-center justify-between px-3 py-2 rounded-xl transition-all duration-200 {{ $isActive ? $theme['nav_active'] : $theme['nav_inactive'] }}">
                    @if ($isActive)
                        <span class="absolute left-0 top-2 bottom-2 w-1 {{ $theme['indicator_active'] }} rounded-r-full"></span>
                    @endif
                    <div class="flex items-center min-w-0">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-2.5 transition-all duration-200 {{ $isActive ? $theme['icon_box_active'] : $theme['icon_box_inactive'] }} border border-transparent">
                            <i class="fa-solid fa-satellite-dish text-sm"></i>
                        </div>
                        <span class="truncate">Peta Radar Real-Time</span>
                    </div>
                    <span class="relative flex h-2 w-2 mr-1">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                </a>

                <!-- Customer Addresses Management -->
                @php $isActive = request()->routeIs('admin.addresses.*'); @endphp
                <a href="{{ route('admin.addresses.index') }}" 
                   class="group relative flex items-center px-3 py-2 rounded-xl transition-all duration-200 {{ $isActive ? $theme['nav_active'] : $theme['nav_inactive'] }}">
                    @if ($isActive)
                        <span class="absolute left-0 top-2 bottom-2 w-1 {{ $theme['indicator_active'] }} rounded-r-full"></span>
                    @endif
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-2.5 transition-all duration-200 {{ $isActive ? $theme['icon_box_active'] : $theme['icon_box_inactive'] }} border border-transparent">
                        <i class="fa-solid fa-map-location-dot text-sm"></i>
                    </div>
                    <span class="truncate">Alamat Pelanggan</span>
                </a>
            </div>

            <!-- Group 2: Produk & Layanan Wilayah -->
            <div class="space-y-1">
                <div class="px-3 pb-1 text-[10px] font-extrabold uppercase tracking-widest text-slate-500 flex items-center justify-between">
                    <span>Katalog & Logistik</span>
                    <span class="w-8 h-px bg-slate-800/80"></span>
                </div>

                <!-- Products -->
                @php $isActive = request()->routeIs('admin.products.*'); @endphp
                <a href="{{ route('admin.products.index') }}" 
                   class="group relative flex items-center px-3 py-2 rounded-xl transition-all duration-200 {{ $isActive ? $theme['nav_active'] : $theme['nav_inactive'] }}">
                    @if ($isActive)
                        <span class="absolute left-0 top-2 bottom-2 w-1 {{ $theme['indicator_active'] }} rounded-r-full"></span>
                    @endif
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-2.5 transition-all duration-200 {{ $isActive ? $theme['icon_box_active'] : $theme['icon_box_inactive'] }} border border-transparent">
                        <i class="fa-solid fa-bottle-water text-sm"></i>
                    </div>
                    <span class="truncate">Produk & Stok Galon</span>
                </a>

                <!-- Service Areas -->
                @php $isActive = request()->routeIs('admin.service-areas.*'); @endphp
                <a href="{{ route('admin.service-areas.index') }}" 
                   class="group relative flex items-center px-3 py-2 rounded-xl transition-all duration-200 {{ $isActive ? $theme['nav_active'] : $theme['nav_inactive'] }}">
                    @if ($isActive)
                        <span class="absolute left-0 top-2 bottom-2 w-1 {{ $theme['indicator_active'] }} rounded-r-full"></span>
                    @endif
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-2.5 transition-all duration-200 {{ $isActive ? $theme['icon_box_active'] : $theme['icon_box_inactive'] }} border border-transparent">
                        <i class="fa-solid fa-signs-post text-sm"></i>
                    </div>
                    <span class="truncate">Wilayah & Ongkir</span>
                </a>
            </div>

            <!-- Group 3: Finansial & Reputasi -->
            <div class="space-y-1">
                <div class="px-3 pb-1 text-[10px] font-extrabold uppercase tracking-widest text-slate-500 flex items-center justify-between">
                    <span>Keuangan & Reputasi</span>
                    <span class="w-8 h-px bg-slate-800/80"></span>
                </div>

                <!-- Finance -->
                @php $isActive = request()->routeIs('admin.finance.*'); @endphp
                <a href="{{ route('admin.finance.index') }}" 
                   class="group relative flex items-center px-3 py-2 rounded-xl transition-all duration-200 {{ $isActive ? $theme['nav_active'] : $theme['nav_inactive'] }}">
                    @if ($isActive)
                        <span class="absolute left-0 top-2 bottom-2 w-1 {{ $theme['indicator_active'] }} rounded-r-full"></span>
                    @endif
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-2.5 transition-all duration-200 {{ $isActive ? $theme['icon_box_active'] : $theme['icon_box_inactive'] }} border border-transparent">
                        <i class="fa-solid fa-wallet text-sm"></i>
                    </div>
                    <span class="truncate">Keuangan & Beban</span>
                </a>

                <!-- Reviews -->
                @php $isActive = request()->routeIs('admin.reviews.*'); @endphp
                <a href="{{ route('admin.reviews.index') }}" 
                   class="group relative flex items-center px-3 py-2 rounded-xl transition-all duration-200 {{ $isActive ? $theme['nav_active'] : $theme['nav_inactive'] }}">
                    @if ($isActive)
                        <span class="absolute left-0 top-2 bottom-2 w-1 {{ $theme['indicator_active'] }} rounded-r-full"></span>
                    @endif
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-2.5 transition-all duration-200 {{ $isActive ? $theme['icon_box_active'] : $theme['icon_box_inactive'] }} border border-transparent">
                        <i class="fa-solid fa-star text-sm"></i>
                    </div>
                    <span class="truncate">Ulasan Pelanggan</span>
                </a>
            </div>

            <!-- Group 4: Sistem & Pengguna -->
            <div class="space-y-1">
                <div class="px-3 pb-1 text-[10px] font-extrabold uppercase tracking-widest text-slate-500 flex items-center justify-between">
                    <span>Sistem & Akses</span>
                    <span class="w-8 h-px bg-slate-800/80"></span>
                </div>

                <!-- Users -->
                @php $isActive = request()->routeIs('admin.users.*'); @endphp
                <a href="{{ route('admin.users.index') }}" 
                   class="group relative flex items-center justify-between px-3 py-2 rounded-xl transition-all duration-200 {{ $isActive ? $theme['nav_active'] : $theme['nav_inactive'] }}">
                    @if ($isActive)
                        <span class="absolute left-0 top-2 bottom-2 w-1 {{ $theme['indicator_active'] }} rounded-r-full"></span>
                    @endif
                    <div class="flex items-center min-w-0">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-2.5 transition-all duration-200 {{ $isActive ? $theme['icon_box_active'] : $theme['icon_box_inactive'] }} border border-transparent">
                            <i class="fa-solid fa-users-gear text-sm"></i>
                        </div>
                        <span class="truncate">Manajemen Pengguna</span>
                    </div>
                    @if ($isSuperAdmin)
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-amber-400/15 text-amber-300 border border-amber-400/30">
                            Akses Penuh
                        </span>
                    @endif
                </a>

                <!-- View Public Web -->
                <a href="{{ route('home') }}" target="_blank"
                   class="group flex items-center px-3 py-2 rounded-xl text-slate-400 hover:bg-slate-800/70 hover:text-white transition-all duration-200">
                    <div class="w-8 h-8 rounded-lg bg-slate-900/90 text-slate-400 group-hover:text-cyan-400 group-hover:bg-cyan-950/40 group-hover:border-cyan-800/40 border border-transparent flex items-center justify-center mr-2.5 transition-all duration-200">
                        <i class="fa-solid fa-arrow-up-right-from-square text-sm"></i>
                    </div>
                    <span class="truncate">Buka Web Publik</span>
                </a>
            </div>

        </nav>

        <!-- Sidebar Bottom: Admin Profile & Dropup Menu with Logout -->
        <div x-data="{ userMenuOpen: false }" class="relative p-3 bg-slate-950 border-t border-slate-800/90 shrink-0">
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
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $theme['profile_role_badge'] }} border">
                            <i class="fa-solid {{ $theme['role_icon'] }} mr-1.5 text-[9px]"></i>
                            {{ $theme['role_title'] }}
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
                    class="w-full flex items-center justify-between p-1.5 rounded-xl hover:bg-slate-900 transition text-left group cursor-pointer border border-transparent hover:border-slate-800">
                <div class="flex items-center space-x-2.5 overflow-hidden">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr {{ $theme['avatar_gradient'] }} text-white font-black flex items-center justify-center text-xs shrink-0 shadow-sm group-hover:scale-105 transition-transform duration-200">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-xs font-bold text-white truncate group-hover:{{ $theme['accent_text'] }} transition">
                            {{ Auth::user()->name }}
                        </div>
                        <div class="text-[10px] {{ $theme['accent_text'] }} uppercase font-bold tracking-wider flex items-center mt-0.5">
                            <i class="fa-solid {{ $theme['role_icon'] }} mr-1 text-[8px]"></i>
                            <span>{{ $theme['role_title'] }}</span>
                        </div>
                    </div>
                </div>

                <div class="text-slate-400 group-hover:text-white transition p-1 shrink-0">
                    <i class="fa-solid fa-chevron-up text-xs transition-transform duration-200" :class="{'rotate-180': userMenuOpen}"></i>
                </div>
            </button>
        </div>

    </aside>

    <!-- MAIN RIGHT CONTENT WRAPPER (Smooth Responsive Transition, Non-blocking) -->
    <div class="flex-1 flex flex-col min-w-0 transition-all duration-300 ease-in-out"
         :class="sidebarOpen ? 'md:pl-64' : 'md:pl-0'">

        <!-- Top Header Bar -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-30 shadow-2xs">
            <div class="flex items-center space-x-3">
                <!-- Sidebar Toggle Button (Non-blocking, Works on Desktop & Mobile) -->
                <button type="button" 
                        @click="sidebarOpen = !sidebarOpen" 
                        class="p-2.5 rounded-xl text-slate-600 {{ $theme['toggle_btn_hover'] }} border border-slate-200 transition-all duration-200 flex items-center justify-center cursor-pointer shadow-2xs"
                        title="Buka / Tutup Navigasi Sidebar">
                    <i class="fa-solid fa-bars-staggered text-base"></i>
                </button>

                <div>
                    <h1 class="text-base sm:text-lg font-black text-slate-900 tracking-tight flex items-center">
                        @yield('page_title', 'Admin Panel')
                    </h1>
                </div>
            </div>

            <!-- Header Right Shortcut Controls -->
            <div class="flex items-center space-x-2 sm:space-x-3">
                <!-- Role Badge in Header -->
                <div class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold {{ $theme['role_badge_bg'] }} border">
                    <i class="fa-solid {{ $theme['role_icon'] }} mr-1.5 text-xs"></i>
                    <span>{{ $theme['role_title'] }}</span>
                </div>

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
