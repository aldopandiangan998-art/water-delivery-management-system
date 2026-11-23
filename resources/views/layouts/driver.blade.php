<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Driver - {{ config('app.name', 'AquaFlow') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @stack('styles')
</head>
<body class="h-full flex flex-col font-sans text-slate-800 antialiased bg-slate-100">

    <!-- Top bar for driver -->
    <header class="bg-slate-900 text-white px-4 py-3 sticky top-0 z-30 shadow-md">
        <div class="max-w-md mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 rounded-lg bg-emerald-500 flex items-center justify-center text-white">
                    <i class="fa-solid fa-motorcycle text-sm"></i>
                </div>
                <div>
                    <div class="font-bold text-sm tracking-tight leading-none">Aqua<span class="text-cyan-400">Flow</span> Kurir</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">{{ Auth::user()->name }}</div>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                <a href="{{ route('home') }}" class="text-xs bg-slate-800 text-slate-300 hover:text-white px-2.5 py-1 rounded-lg">Web</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-xs bg-rose-600/30 text-rose-300 hover:bg-rose-600 px-2.5 py-1 rounded-lg">Keluar</button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-md w-full mx-auto p-4 pb-20">
        @if (session('success'))
            <div class="mb-3 p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold flex items-center">
                <i class="fa-solid fa-circle-check text-emerald-600 mr-2 text-sm"></i> {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-3 p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs font-semibold flex items-center">
                <i class="fa-solid fa-triangle-exclamation text-rose-600 mr-2 text-sm"></i> {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Bottom Navigation Bar for Mobile -->
    <nav class="fixed bottom-0 inset-x-0 bg-white border-t border-slate-200 py-2 z-30 shadow-lg">
        <div class="max-w-md mx-auto grid grid-cols-2 text-center text-xs">
            <a href="{{ route('driver.dashboard') }}" class="flex flex-col items-center py-1 {{ request()->routeIs('driver.dashboard') ? 'text-emerald-600 font-bold' : 'text-slate-500' }}">
                <i class="fa-solid fa-list-check text-lg mb-1"></i>
                <span>Tugas Antar</span>
            </a>
            <a href="{{ route('customer.dashboard') }}" class="flex flex-col items-center py-1 text-slate-500 hover:text-cyan-600">
                <i class="fa-solid fa-user text-lg mb-1"></i>
                <span>Portal Pelanggan</span>
            </a>
        </div>
    </nav>

    @stack('scripts')
</body>
</html>
