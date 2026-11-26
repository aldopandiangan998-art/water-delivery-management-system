<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50 dark:bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'AquaFlow') }} - @yield('title', __('Depot Air Minum & Layanan Antar'))</title>

    <!-- Dark Mode Initial Script to Prevent Flicker -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <!-- Tailwind CSS, Alpine.js, Leaflet bundled via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Heroicons / Fontawesome CDN for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @stack('styles')
</head>
<body class="h-full flex flex-col font-sans text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-950 antialiased selection:bg-cyan-500 selection:text-white">
    <!-- Navbar -->
    @include('layouts.navigation')

    <!-- Flash Messages -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full mt-4">
        @if (session('success'))
            <div class="mb-4 flex items-center p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl shadow-xs">
                <i class="fa-solid fa-circle-check text-emerald-600 text-lg mr-3"></i>
                <div class="text-sm font-medium">{{ session('success') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 flex items-center p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl shadow-xs">
                <i class="fa-solid fa-triangle-exclamation text-rose-600 text-lg mr-3"></i>
                <div class="text-sm font-medium">{{ session('error') }}</div>
            </div>
        @endif

        @if (session('info'))
            <div class="mb-4 flex items-center p-4 bg-sky-50 border border-sky-200 text-sky-800 rounded-xl shadow-xs">
                <i class="fa-solid fa-circle-info text-sky-600 text-lg mr-3"></i>
                <div class="text-sm font-medium">{{ session('info') }}</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl shadow-xs">
                <div class="flex items-center mb-1">
                    <i class="fa-solid fa-circle-xmark text-rose-600 mr-2"></i>
                    <span class="text-sm font-semibold">Terdapat beberapa kesalahan:</span>
                </div>
                <ul class="list-disc list-inside text-sm pl-4 space-y-0.5">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-300 border-t border-slate-800 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Col 1: Brand -->
                <div class="space-y-4">
                    <div class="flex items-center space-x-2">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-cyan-600 to-blue-500 flex items-center justify-center text-white shadow-md">
                            <i class="fa-solid fa-droplet text-xl"></i>
                        </div>
                        <span class="text-xl font-bold text-white tracking-tight">Aqua<span class="text-cyan-400">Flow</span></span>
                    </div>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Layanan pesan antar air minum higienis, murni & berkualitas langsung ke pintu rumah, toko, dan kantor Anda dengan pelacakan pengantaran real-time.
                    </p>
                    <div class="flex space-x-3 text-slate-400">
                        <a href="#" class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center hover:text-cyan-400 hover:bg-slate-700 transition"><i class="fa-brands fa-whatsapp"></i></a>
                        <a href="#" class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center hover:text-cyan-400 hover:bg-slate-700 transition"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center hover:text-cyan-400 hover:bg-slate-700 transition"><i class="fa-brands fa-facebook"></i></a>
                    </div>
                </div>

                <!-- Col 2: Produk -->
                <div>
                    <h4 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Produk Utama</h4>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li><a href="{{ route('home') }}#produk" class="hover:text-cyan-400 transition">Air Galon Isi Ulang RO</a></li>
                        <li><a href="{{ route('home') }}#produk" class="hover:text-cyan-400 transition">Air Galon Bio-Mineral</a></li>
                        <li><a href="{{ route('home') }}#produk" class="hover:text-cyan-400 transition">Galon Aqua Resmi 19L</a></li>
                        <li><a href="{{ route('home') }}#produk" class="hover:text-cyan-400 transition">Galon Le Minerale 15L</a></li>
                        <li><a href="{{ route('home') }}#produk" class="hover:text-cyan-400 transition">Air Dus Gelas & Botol</a></li>
                    </ul>
                </div>

                <!-- Col 3: Area & Layanan -->
                <div>
                    <h4 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Area Layanan</h4>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li><a href="{{ route('public.service-areas') }}" class="hover:text-cyan-400 transition">Kecamatan Sukajadi</a></li>
                        <li><a href="{{ route('public.service-areas') }}" class="hover:text-cyan-400 transition">Gegerkalong & Gerlong Hilir</a></li>
                        <li><a href="{{ route('public.service-areas') }}" class="hover:text-cyan-400 transition">Sarijadi & Blok Perumahan</a></li>
                        <li><a href="{{ route('public.service-areas') }}" class="hover:text-cyan-400 transition">Pasteur & Cipaganti</a></li>
                        <li><a href="{{ route('public.service-areas') }}" class="text-cyan-400 font-medium hover:underline">Lihat Semua Wilayah &rarr;</a></li>
                    </ul>
                </div>

                <!-- Col 4: Kontak & Jam Buka -->
                <div>
                    <h4 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Depot & Kontak</h4>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li class="flex items-start">
                            <i class="fa-solid fa-location-dot mt-1 mr-2 text-cyan-400"></i>
                            <span>{{ \App\Models\BusinessSetting::get('company_address', 'Jl. Raya Gubeng No. 45, Kota Surabaya, Jawa Timur 60281') }}</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fa-solid fa-phone mr-2 text-cyan-400"></i>
                            <span>{{ \App\Models\BusinessSetting::get('company_phone', '0881-0261-46848') }}</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fa-solid fa-clock mr-2 text-cyan-400"></i>
                            <span>Buka: 07:00 - 20:00 WIB</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-slate-800 mt-8 pt-8 flex flex-col sm:flex-row justify-between items-center text-xs text-slate-500">
                <p>&copy; {{ date('Y') }} AquaFlow Delivery Management System. All rights reserved.</p>
                <p class="mt-2 sm:mt-0">Sistem Manajemen Pengantaran Air Minum Terintegrasi.</p>
            </div>
        </div>
    </footer>

    <!-- Floating WhatsApp Button -->
    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', \App\Models\BusinessSetting::get('company_whatsapp', '6283186109775')) }}?text=Halo%20AquaFlow,%20saya%20mau%20pesan%20air%20galon" target="_blank" 
       class="fixed bottom-20 md:bottom-6 right-6 z-40 bg-emerald-500 hover:bg-emerald-600 text-white w-12 h-12 md:w-14 md:h-14 rounded-full flex items-center justify-center shadow-lg hover:shadow-xl hover:scale-105 transition duration-200">
        <i class="fa-brands fa-whatsapp text-2xl md:text-3xl"></i>
    </a>

    <!-- Mobile Bottom Navigation & Toast Feedback -->
    <x-bottom-nav />
    <x-toast />

    <!-- Global Quick Search (Ctrl+K or /) -->
    <x-global-search />

    @stack('scripts')
</body>
</html>
