@extends('layouts.app')

@section('title', __('403 - Akses Ditolak'))

@section('content')
<div class="min-h-[75vh] flex items-center justify-center px-4 py-16">
    <div class="text-center max-w-md mx-auto space-y-5">
        <div class="w-20 h-20 rounded-3xl bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-3xl mx-auto shadow-md ring-8 ring-rose-50 dark:ring-rose-950/30">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div class="space-y-2">
            <span class="text-sm font-black text-rose-600 dark:text-rose-400 tracking-wider uppercase">403 {{ __('Forbidden') }}</span>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Akses Ditolak') }}</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                {{ __('Anda tidak memiliki izin untuk mengakses halaman ini. Pastikan Anda telah masuk dengan peran pengguna yang sesuai.') }}
            </p>
        </div>
        <div class="pt-2 flex flex-col sm:flex-row justify-center gap-3">
            <x-button href="/" variant="primary" icon="fa-solid fa-house">
                {{ __('Kembali ke Beranda') }}
            </x-button>
        </div>
    </div>
</div>
@endsection
