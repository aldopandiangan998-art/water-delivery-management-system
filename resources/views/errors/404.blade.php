@extends('layouts.app')

@section('title', __('404 - Halaman Tidak Ditemukan'))

@section('content')
<div class="min-h-[75vh] flex items-center justify-center px-4 py-16">
    <div class="text-center max-w-md mx-auto space-y-5">
        <div class="w-20 h-20 rounded-3xl bg-cyan-100 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-3xl mx-auto shadow-md ring-8 ring-cyan-50 dark:ring-cyan-950/30">
            <i class="fa-solid fa-compass"></i>
        </div>
        <div class="space-y-2">
            <span class="text-sm font-black text-cyan-600 dark:text-cyan-400 tracking-wider uppercase">404 {{ __('Not Found') }}</span>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Halaman Tidak Ditemukan') }}</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                {{ __('Halaman yang Anda tuju tidak ada, sudah dipindahkan, atau alamat URL yang Anda masukkan salah.') }}
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
