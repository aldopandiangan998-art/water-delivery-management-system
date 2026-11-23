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
                               value="{{ old('email', 'customer@aquaflow.com') }}"
                               class="appearance-none block w-full px-3.5 py-2.5 border border-slate-300 rounded-xl shadow-xs placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 text-sm">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Kata Sandi</label>
                    <div class="mt-1 relative">
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                               value="password"
                               class="appearance-none block w-full px-3.5 py-2.5 border border-slate-300 rounded-xl shadow-xs placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 text-sm">
                    </div>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" checked
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

            <!-- Demo Quick Login Buttons -->
            <div class="mt-6 border-t border-slate-100 pt-6">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider text-center mb-3">
                    Akses Cepat Uji Coba Demo:
                </p>
                <div class="grid grid-cols-3 gap-2 text-xs">
                    <button type="button" onclick="setDemo('customer@aquaflow.com', 'password')" 
                            class="p-2 border border-slate-200 rounded-lg text-center hover:bg-cyan-50 hover:border-cyan-300 transition">
                        <i class="fa-solid fa-user text-cyan-600 block mb-1"></i>
                        <span class="font-medium text-slate-700">Customer</span>
                    </button>
                    <button type="button" onclick="setDemo('driver1@aquaflow.com', 'password')" 
                            class="p-2 border border-slate-200 rounded-lg text-center hover:bg-emerald-50 hover:border-emerald-300 transition">
                        <i class="fa-solid fa-motorcycle text-emerald-600 block mb-1"></i>
                        <span class="font-medium text-slate-700">Driver</span>
                    </button>
                    <button type="button" onclick="setDemo('admin@aquaflow.com', 'password')" 
                            class="p-2 border border-slate-200 rounded-lg text-center hover:bg-indigo-50 hover:border-indigo-300 transition">
                        <i class="fa-solid fa-gauge-high text-indigo-600 block mb-1"></i>
                        <span class="font-medium text-slate-700">Admin</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function setDemo(email, pass) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = pass;
}
</script>
@endsection
