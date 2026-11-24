@extends('layouts.app')

@section('title', 'Masuk Akun')

@section('content')
<div class="min-h-[80vh] flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-cyan-600 to-blue-500 flex items-center justify-center text-white mx-auto shadow-md">
            <i class="fa-solid fa-droplet text-2xl"></i>
        </div>
        <h2 class="mt-4 text-center text-2xl font-bold tracking-tight text-slate-900">
            Masuk ke Akun Anda
        </h2>
        <p class="mt-2 text-center text-sm text-slate-600">
            Belum memiliki akun?
            <a href="{{ route('register') }}" class="font-medium text-cyan-600 hover:text-cyan-500">
                Daftar sekarang gratis
            </a>
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-6 shadow-sm border border-slate-200 rounded-2xl sm:px-10">
            <form class="space-y-5" method="POST" action="{{ route('login') }}">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Alamat Email</label>
                    <div class="mt-1 relative">
                        <input id="email" name="email" type="email" autocomplete="email" required
                               placeholder="nama@email.com"
                               value="{{ old('email') }}"
                               class="appearance-none block w-full px-3.5 py-2.5 border border-slate-300 rounded-xl shadow-xs placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 text-sm">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Kata Sandi</label>
                    <div class="mt-1 relative">
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                               placeholder="••••••••"
                               class="appearance-none block w-full px-3.5 py-2.5 border border-slate-300 rounded-xl shadow-xs placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 text-sm">
                    </div>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox"
                               class="h-4 w-4 text-cyan-600 focus:ring-cyan-500 border-slate-300 rounded">
                        <label for="remember" class="ml-2 block text-slate-700">Ingat saya</label>
                    </div>
                </div>

                <div>
                    <button type="submit"
                            class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-semibold text-white bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500 transition duration-150">
                        Masuk
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
