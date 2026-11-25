@extends('layouts.app')

@section('title', __('419 - Sesi Kedaluwarsa'))

@section('content')
<div class="min-h-[75vh] flex items-center justify-center px-4 py-16">
    <div class="text-center max-w-md mx-auto space-y-5">
        <div class="w-20 h-20 rounded-3xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-3xl mx-auto shadow-md ring-8 ring-amber-50 dark:ring-amber-950/30">
            <i class="fa-solid fa-hourglass-end"></i>
        </div>
        <div class="space-y-2">
            <span class="text-sm font-black text-amber-600 dark:text-amber-400 tracking-wider uppercase">419 {{ __('Page Expired') }}</span>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Sesi Telah Kedaluwarsa') }}</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                {{ __('Sesi browser Anda telah berakhir karena tidak ada aktivitas. Silakan muat ulang halaman atau kembali masuk.') }}
            </p>
        </div>
        <div class="pt-2 flex flex-col sm:flex-row justify-center gap-3">
            <x-button href="javascript:window.location.reload()" variant="primary" icon="fa-solid fa-rotate">
                {{ __('Muat Ulang Halaman') }}
            </x-button>
            <x-button href="/" variant="secondary" icon="fa-solid fa-house">
                {{ __('Beranda') }}
            </x-button>
        </div>
    </div>
</div>
@endsection
