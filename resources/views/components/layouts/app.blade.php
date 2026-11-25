@props([
    'title' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50 dark:bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title . ' - ' : '' }}{{ config('app.name', 'AquaFlow') }}</title>

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

    <!-- FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @stack('styles')
</head>
<body class="h-full flex flex-col font-sans text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-950 antialiased selection:bg-cyan-500 selection:text-white">
    <!-- Navbar -->
    @include('layouts.navigation')

    <!-- Flash Messages via Toast -->
    <x-toast />

    <!-- Main Content -->
    <main class="flex-1 pb-16 md:pb-0">
        {{ $slot }}
    </main>

    <!-- Mobile Bottom Navigation -->
    <x-bottom-nav />

    @stack('scripts')
</body>
</html>
