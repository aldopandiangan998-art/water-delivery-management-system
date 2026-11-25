@extends('layouts.app')

@section('title', __('Masuk Akun'))

@section('content')
<div class="min-h-[80vh] flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-cyan-600 to-blue-600 flex items-center justify-center text-white mx-auto shadow-lg shadow-cyan-600/20 ring-4 ring-cyan-50 dark:ring-cyan-950">
            <i class="fa-solid fa-droplet text-2xl"></i>
        </div>
        <h2 class="mt-5 text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">
            {{ __('Masuk ke Akun Anda') }}
        </h2>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
            {{ __('Belum memiliki akun?') }}
            <a href="{{ route('register') }}" class="font-bold text-cyan-600 hover:text-cyan-500 dark:text-cyan-400 dark:hover:text-cyan-300 transition">
                {{ __('Daftar sekarang gratis') }}
            </a>
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <x-card class="p-6 sm:p-8">
            <form class="space-y-5" method="POST" action="{{ route('login') }}" x-data="{ showPassword: false }">
                @csrf

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

                <div class="w-full space-y-1.5">
                    <label for="password" class="block text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-300">
                        {{ __('Kata Sandi') }} <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-2xs">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <i class="fa-solid fa-lock text-sm"></i>
                        </div>
                        <input 
                            :type="showPassword ? 'text' : 'password'" 
                            name="password" 
                            id="password" 
                            required 
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="block w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 text-xs sm:text-sm transition duration-150 min-h-[44px] pl-10 pr-11 py-2.5 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 focus:outline-hidden"
                        />
                        <button 
                            type="button" 
                            @click="showPassword = !showPassword" 
                            class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition"
                            :aria-label="showPassword ? 'Hide password' : 'Show password'"
                        >
                            <i class="fa-regular" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-xs text-rose-600 dark:text-rose-400 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between text-sm pt-1">
                    <x-checkbox 
                        name="remember" 
                        id="remember" 
                        :label="__('Ingat saya')" 
                    />
                </div>

                <div class="pt-2">
                    <x-button type="submit" variant="primary" class="w-full py-3" icon="fa-solid fa-right-to-bracket">
                        {{ __('Masuk') }}
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
</div>
@endsection
