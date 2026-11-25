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

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @stack('styles')
</head>
<body class="h-full flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8 font-sans bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 antialiased">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <a href="{{ route('home') }}" class="flex flex-col items-center justify-center space-y-2 group">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-cyan-600 to-blue-500 flex items-center justify-center text-white shadow-md group-hover:scale-105 transition">
                <i class="fa-solid fa-droplet text-2xl"></i>
            </div>
            <span class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Aqua<span class="text-cyan-600 dark:text-cyan-400">Flow</span></span>
        </a>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white dark:bg-slate-900 py-8 px-6 shadow-sm border border-slate-200 dark:border-slate-800 rounded-3xl sm:px-10">
            {{ $slot }}
        </div>
    </div>

    <!-- Theme & Language Switcher at Bottom -->
    <div class="mt-6 flex justify-center">
        @include('partials.theme-lang-controls')
    </div>

    <x-toast />
    @stack('scripts')
</body>
</html>
