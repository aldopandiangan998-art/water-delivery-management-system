@extends('layouts.app')

@section('title', 'Daftar Akun Pelanggan')

@section('content')
<div class="min-h-[85vh] flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-cyan-600 to-blue-500 flex items-center justify-center text-white mx-auto shadow-md">
            <i class="fa-solid fa-droplet text-2xl"></i>
        </div>
        <h2 class="mt-4 text-center text-2xl font-bold tracking-tight text-slate-900">
            Daftar Akun Pelanggan Baru
        </h2>
        <p class="mt-2 text-center text-sm text-slate-600">
            Sudah memiliki akun?
            <a href="{{ route('login') }}" class="font-medium text-cyan-600 hover:text-cyan-500">
                Masuk di sini
            </a>
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-6 shadow-sm border border-slate-200 rounded-2xl sm:px-10">
            <form class="space-y-4" method="POST" action="{{ route('register') }}">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700">Nama Lengkap</label>
                    <input id="name" name="name" type="text" required value="{{ old('name') }}"
                           class="mt-1 block w-full px-3.5 py-2.5 border border-slate-300 rounded-xl shadow-xs text-sm focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Alamat Email</label>
                    <input id="email" name="email" type="email" required value="{{ old('email') }}"
                           class="mt-1 block w-full px-3.5 py-2.5 border border-slate-300 rounded-xl shadow-xs text-sm focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-slate-700">Nomor WhatsApp / HP</label>
                    <input id="phone" name="phone" type="text" placeholder="081234567890" required value="{{ old('phone') }}"
                           class="mt-1 block w-full px-3.5 py-2.5 border border-slate-300 rounded-xl shadow-xs text-sm focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                </div>

                <div>
                    <label for="customer_type" class="block text-sm font-medium text-slate-700">Tipe Pelanggan</label>
                    <select id="customer_type" name="customer_type"
                            class="mt-1 block w-full px-3.5 py-2.5 border border-slate-300 rounded-xl shadow-xs text-sm focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 bg-white">
                        <option value="household" {{ old('customer_type') === 'household' ? 'selected' : '' }}>Rumah Tangga / Pribadi</option>
                        <option value="retail_shop" {{ old('customer_type') === 'retail_shop' ? 'selected' : '' }}>Toko Kelontong / Warung</option>
                        <option value="office" {{ old('customer_type') === 'office' ? 'selected' : '' }}>Kantor / Instansi</option>
                        <option value="restaurant" {{ old('customer_type') === 'restaurant' ? 'selected' : '' }}>Restoran / Cafe</option>
                    </select>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Kata Sandi</label>
                    <input id="password" name="password" type="password" required
                           class="mt-1 block w-full px-3.5 py-2.5 border border-slate-300 rounded-xl shadow-xs text-sm focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Konfirmasi Kata Sandi</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required
                           class="mt-1 block w-full px-3.5 py-2.5 border border-slate-300 rounded-xl shadow-xs text-sm focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-semibold text-white bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 transition duration-150">
                        Buat Akun Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
