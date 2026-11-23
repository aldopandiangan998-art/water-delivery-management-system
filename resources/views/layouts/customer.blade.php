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
<body class="h-full flex flex-col font-sans text-slate-800 antialiased selection:bg-cyan-500 selection:text-white">

    <!-- Customer Portal Navigation Bar -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Left: Brand & Customer Nav -->
                <div class="flex items-center space-x-6">
                    <a href="{{ route('customer.dashboard') }}" class="flex items-center space-x-2">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-cyan-600 to-blue-500 flex items-center justify-center text-white shadow-xs">
                            <i class="fa-solid fa-droplet text-lg"></i>
                        </div>
                        <span class="text-xl font-bold text-slate-900 tracking-tight">Aqua<span class="text-cyan-600">Flow</span></span>
                        <span class="hidden sm:inline-block ml-1.5 px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-cyan-50 text-cyan-700 border border-cyan-200">
                            Member
                        </span>
                    </a>

                    <!-- Desktop Nav Links -->
                    <div class="hidden md:flex items-center space-x-1 text-xs font-bold">
                        <a href="{{ route('customer.dashboard') }}" 
                           class="px-3.5 py-2 rounded-xl transition flex items-center {{ request()->routeIs('customer.dashboard') ? 'bg-cyan-50 text-cyan-700 font-extrabold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            <i class="fa-solid fa-house mr-1.5 text-sm"></i> Dashboard
                        </a>

                        <a href="{{ route('customer.orders.create') }}" 
                           class="px-3.5 py-2 rounded-xl transition flex items-center {{ request()->routeIs('customer.orders.create') ? 'bg-cyan-600 text-white shadow-xs' : 'text-cyan-600 hover:bg-cyan-50' }}">
                            <i class="fa-solid fa-cart-plus mr-1.5 text-sm"></i> Pesan Air
                        </a>

                        <a href="{{ route('customer.orders.index') }}" 
                           class="px-3.5 py-2 rounded-xl transition flex items-center {{ request()->routeIs('customer.orders.index') || request()->routeIs('customer.orders.show') || request()->routeIs('customer.orders.track') ? 'bg-cyan-50 text-cyan-700 font-extrabold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            <i class="fa-solid fa-box-archive mr-1.5 text-sm"></i> Pesanan Saya
                        </a>

                        <a href="{{ route('customer.addresses.index') }}" 
                           class="px-3.5 py-2 rounded-xl transition flex items-center {{ request()->routeIs('customer.addresses.*') ? 'bg-cyan-50 text-cyan-700 font-extrabold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            <i class="fa-solid fa-location-dot mr-1.5 text-sm"></i> Alamat Pengantaran
                        </a>
                    </div>
                </div>

                <!-- Right: Quick stats & User profile menu -->
                <div class="flex items-center space-x-3">
                    <!-- Quick Gallon Deposit Pill -->
                    <div class="hidden lg:flex items-center px-3 py-1 rounded-xl bg-cyan-50 border border-cyan-200 text-cyan-900 text-xs font-semibold" title="Saldo Galon Kosong Anda di Depot">
                        <i class="fa-solid fa-bottle-water text-cyan-600 mr-1.5"></i>
                        <span>Titipan Galon: <strong class="font-extrabold text-cyan-700">{{ Auth::user()->profile?->gallon_deposit_balance ?? 0 }}</strong></span>
                    </div>

                    <!-- CS WhatsApp link -->
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', \App\Models\BusinessSetting::get('company_whatsapp', '62881026146848')) }}?text=Halo%20Admin%20AquaFlow,%20saya%20pelanggan%20mau%20tanya" 
                       target="_blank"
                       class="hidden sm:inline-flex items-center px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 text-xs font-bold transition">
                        <i class="fa-brands fa-whatsapp text-emerald-600 mr-1.5 text-sm"></i> CS Depot
                    </a>

                    <!-- Profile Dropdown -->
                    <div x-data="{ dropdownOpen: false }" class="relative">
                        <button @click="dropdownOpen = !dropdownOpen" 
                                class="flex items-center space-x-2 text-xs font-bold text-slate-700 hover:text-cyan-600 bg-slate-50 hover:bg-slate-100 px-3 py-1.5 rounded-xl border border-slate-200 transition">
                            <div class="w-7 h-7 rounded-lg bg-gradient-to-tr from-cyan-600 to-blue-500 text-white font-black flex items-center justify-center text-xs">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <span class="max-w-[120px] truncate hidden sm:inline">{{ Auth::user()->name }}</span>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                        </button>

                        <div x-show="dropdownOpen" @click.away="dropdownOpen = false" x-cloak
                             class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50 divide-y divide-slate-100">
                            
                            <div class="px-4 py-2 text-xs">
                                <div class="font-bold text-slate-900">{{ Auth::user()->name }}</div>
                                <div class="text-[11px] text-slate-500 truncate">{{ Auth::user()->email }}</div>
                                <div class="mt-1 text-[10px] font-bold text-cyan-700 uppercase bg-cyan-50 px-2 py-0.5 rounded-md inline-block">
                                    {{ str_replace('_', ' ', Auth::user()->profile?->customer_type ?? 'Household') }}
                                </div>
                            </div>

                            <div class="py-1 text-xs font-medium text-slate-700">
                                <a href="{{ route('customer.dashboard') }}" class="flex items-center px-4 py-2 hover:bg-slate-50">
                                    <i class="fa-solid fa-house w-4 mr-2.5 text-slate-400"></i> Dashboard Utama
                                </a>
                                <a href="{{ route('customer.profile') }}" class="flex items-center px-4 py-2 hover:bg-slate-50">
                                    <i class="fa-solid fa-user-gear w-4 mr-2.5 text-slate-400"></i> Profil & Kata Sandi
                                </a>
                                <a href="{{ route('customer.addresses.index') }}" class="flex items-center px-4 py-2 hover:bg-slate-50">
                                    <i class="fa-solid fa-location-dot w-4 mr-2.5 text-slate-400"></i> Alamat Pengantaran
                                </a>
                                <a href="{{ route('home') }}" target="_blank" class="flex items-center px-4 py-2 text-slate-500 hover:bg-slate-50">
                                    <i class="fa-solid fa-globe w-4 mr-2.5 text-slate-400"></i> Website Publik
                                </a>
                            </div>

                            <div class="pt-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center px-4 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 text-left">
                                        <i class="fa-solid fa-arrow-right-from-bracket w-4 mr-2.5"></i> Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Messages Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full mt-4">
        @if (session('success'))
            <div class="mb-4 flex items-center p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-xs">
                <i class="fa-solid fa-circle-check text-emerald-600 text-lg mr-3"></i>
                <div class="text-xs sm:text-sm font-semibold">{{ session('success') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 flex items-center p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl shadow-xs">
                <i class="fa-solid fa-triangle-exclamation text-rose-600 text-lg mr-3"></i>
                <div class="text-xs sm:text-sm font-semibold">{{ session('error') }}</div>
            </div>
        @endif

        @if (session('info'))
            <div class="mb-4 flex items-center p-4 bg-sky-50 border border-sky-200 text-sky-800 rounded-2xl shadow-xs">
                <i class="fa-solid fa-circle-info text-sky-600 text-lg mr-3"></i>
                <div class="text-xs sm:text-sm font-semibold">{{ session('info') }}</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl shadow-xs">
                <div class="flex items-center mb-1 text-xs sm:text-sm font-bold">
                    <i class="fa-solid fa-circle-xmark text-rose-600 mr-2"></i>
                    <span>Terdapat beberapa kesalahan pengisian:</span>
                </div>
                <ul class="list-disc list-inside text-xs pl-4 space-y-0.5">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Main Content Area -->
    <main class="flex-1 pb-20 md:pb-8">
        @yield('content')
    </main>

    <!-- Sleek Portal Footer (No Giant Marketing Sections) -->
    <footer class="bg-white border-t border-slate-200 py-4 text-slate-500 text-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center gap-2">
            <div class="flex items-center space-x-2">
                <span class="font-bold text-slate-700">AquaFlow Portal Pelanggan</span>
                <span>&bull;</span>
                <span>Depot Air Minum Higienis & Antar Cepat</span>
            </div>
            <div class="text-[11px] text-slate-400">
                Butuh bantuan pesanan? Hubungi WhatsApp Depot: <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', \App\Models\BusinessSetting::get('company_whatsapp', '62881026146848')) }}" target="_blank" class="font-bold text-emerald-600 hover:underline">{{ \App\Models\BusinessSetting::get('company_phone', '0881-0261-46848') }}</a>
            </div>
        </div>
    </footer>

    <!-- Mobile Fixed Bottom Navigation (App-like Bar) -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-slate-200 py-1.5 px-3 flex justify-around text-[10px] font-bold shadow-lg">
        <a href="{{ route('customer.dashboard') }}" class="flex flex-col items-center py-1 {{ request()->routeIs('customer.dashboard') ? 'text-cyan-600' : 'text-slate-500' }}">
            <i class="fa-solid fa-house text-base mb-0.5"></i>
            <span>Beranda</span>
        </a>

        <a href="{{ route('customer.orders.create') }}" class="flex flex-col items-center py-1 {{ request()->routeIs('customer.orders.create') ? 'text-cyan-600' : 'text-slate-500' }}">
            <i class="fa-solid fa-cart-plus text-base mb-0.5"></i>
            <span>Pesan Air</span>
        </a>

        <a href="{{ route('customer.orders.index') }}" class="flex flex-col items-center py-1 {{ request()->routeIs('customer.orders.*') && !request()->routeIs('customer.orders.create') ? 'text-cyan-600' : 'text-slate-500' }}">
            <i class="fa-solid fa-box-archive text-base mb-0.5"></i>
            <span>Pesanan</span>
        </a>

        <a href="{{ route('customer.addresses.index') }}" class="flex flex-col items-center py-1 {{ request()->routeIs('customer.addresses.*') ? 'text-cyan-600' : 'text-slate-500' }}">
            <i class="fa-solid fa-location-dot text-base mb-0.5"></i>
            <span>Alamat</span>
        </a>

        <a href="{{ route('customer.profile') }}" class="flex flex-col items-center py-1 {{ request()->routeIs('customer.profile') ? 'text-cyan-600' : 'text-slate-500' }}">
            <i class="fa-solid fa-user text-base mb-0.5"></i>
            <span>Profil</span>
        </a>
    </div>

    <!-- Floating WhatsApp Support Button -->
    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', \App\Models\BusinessSetting::get('company_whatsapp', '62881026146848')) }}?text=Halo%20Admin%20AquaFlow,%20saya%20mau%20tanya%20pesanan" target="_blank" 
       class="fixed bottom-16 sm:bottom-6 right-5 z-40 bg-emerald-500 hover:bg-emerald-600 text-white w-12 h-12 sm:w-14 sm:h-14 rounded-full flex items-center justify-center shadow-lg hover:shadow-xl hover:scale-105 transition duration-200">
        <i class="fa-brands fa-whatsapp text-2xl sm:text-3xl"></i>
    </a>

    @stack('scripts')
</body>
</html>
