@extends('layouts.app')

@section('title', __('Daftar Akun Pelanggan'))

@section('content')
<div class="min-h-[85vh] flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-cyan-600 to-blue-600 flex items-center justify-center text-white mx-auto shadow-lg shadow-cyan-600/20 ring-4 ring-cyan-50 dark:ring-cyan-950">
            <i class="fa-solid fa-droplet text-2xl"></i>
        </div>
        <h2 class="mt-5 text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">
            {{ __('Daftar Akun Pelanggan Baru') }}
        </h2>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
            {{ __('Sudah memiliki akun?') }}
            <a href="{{ route('login') }}" class="font-bold text-cyan-600 hover:text-cyan-500 dark:text-cyan-400 dark:hover:text-cyan-300 transition">
                {{ __('Masuk di sini') }}
            </a>
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <x-card class="p-6 sm:p-8">
            <form class="space-y-4" method="POST" action="{{ route('register') }}" x-data="{ showPass: false, showConfirm: false }">
                @csrf

                <x-input 
                    :label="__('Nama Lengkap')" 
                    name="name" 
                    type="text" 
                    icon="fa-regular fa-user" 
                    placeholder="Budi Santoso" 
                    :value="old('name')" 
                    required 
                    autocomplete="name"
                />

                <x-input 
                    :label="__('Alamat Email')" 
                    name="email" 
                    type="email" 
                    icon="fa-regular fa-envelope" 
                    placeholder="nama@email.com" 
                    :value="old('email')" 
                    required 
                    autocomplete="email"
                />

                <x-input 
                    :label="__('Nomor WhatsApp / HP')" 
                    name="phone" 
                    type="tel" 
                    icon="fa-solid fa-phone" 
                    placeholder="081234567890" 
                    :value="old('phone')" 
                    required 
                    autocomplete="tel"
                />

                @php
                    $customerTypeOptions = [
                        'household' => __('Rumah Tangga / Pribadi'),
                        'retail_shop' => __('Toko Kelontong / Warung'),
                        'office' => __('Kantor / Instansi'),
                        'restaurant' => __('Restoran / Cafe'),
                    ];
                @endphp
                <x-select 
                    :label="__('Tipe Pelanggan')" 
                    name="customer_type" 
                    icon="fa-solid fa-shapes" 
                    :options="$customerTypeOptions" 
                    :selected="old('customer_type', 'household')" 
                    required
                />

                <div class="w-full space-y-1.5">
                    <label for="password" class="block text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-300">
                        {{ __('Kata Sandi') }} <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-2xs">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <i class="fa-solid fa-lock text-sm"></i>
                        </div>
                        <input 
                            :type="showPass ? 'text' : 'password'" 
                            name="password" 
                            id="password" 
                            required 
                            placeholder="••••••••"
                            class="block w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 text-xs sm:text-sm transition duration-150 min-h-[44px] pl-10 pr-11 py-2.5 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 focus:outline-hidden"
                        />
                        <button 
                            type="button" 
                            @click="showPass = !showPass" 
                            class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition"
                            :aria-label="showPass ? 'Hide password' : 'Show password'"
                        >
                            <i class="fa-regular" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-xs text-rose-600 dark:text-rose-400 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="w-full space-y-1.5">
                    <label for="password_confirmation" class="block text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-300">
                        {{ __('Konfirmasi Kata Sandi') }} <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-2xs">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <i class="fa-solid fa-lock text-sm"></i>
                        </div>
                        <input 
                            :type="showConfirm ? 'text' : 'password'" 
                            name="password_confirmation" 
                            id="password_confirmation" 
                            required 
                            placeholder="••••••••"
                            class="block w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 text-xs sm:text-sm transition duration-150 min-h-[44px] pl-10 pr-11 py-2.5 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 focus:outline-hidden"
                        />
                        <button 
                            type="button" 
                            @click="showConfirm = !showConfirm" 
                            class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition"
                            :aria-label="showConfirm ? 'Hide confirmation password' : 'Show confirmation password'"
                        >
                            <i class="fa-regular" :class="showConfirm ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>

                <div class="pt-3">
                    <x-button type="submit" variant="primary" class="w-full py-3" icon="fa-solid fa-user-plus">
                        {{ __('Buat Akun Sekarang') }}
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
</div>
@endsection
