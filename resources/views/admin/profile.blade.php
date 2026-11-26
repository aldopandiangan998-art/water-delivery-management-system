@extends('layouts.admin')

@section('page_title', __('Profil Akun'))

@section('breadcrumbs')
    <x-breadcrumbs :items="[
        ['title' => __('Profil Akun')]
    ]" />
@endsection

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Flash message alert -->
    @if (session('success'))
        <x-alert variant="success" :title="__('Berhasil')">
            {{ session('success') }}
        </x-alert>
    @endif

    <x-card class="p-6 sm:p-8 space-y-6">
        <div class="flex items-center space-x-4 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-cyan-600 to-blue-600 text-white font-black flex items-center justify-center text-xl shadow-md shrink-0">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Pengaturan Profil Akun') }}</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Perbarui informasi nama, kontak, dan kata sandi login Anda.') }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-5">
            @csrf
            @method('PUT')

            <x-input 
                :label="__('Nama Lengkap')" 
                name="name" 
                icon="fa-regular fa-user"
                :value="old('name', $user->name)" 
                required 
            />

            <div>
                <x-input 
                    :label="__('Alamat Email')" 
                    name="email" 
                    icon="fa-regular fa-envelope"
                    :value="$user->email" 
                    disabled 
                    :helper="__('Alamat email digunakan untuk autentikasi sistem dan tidak dapat diubah secara langsung.')"
                />
            </div>

            <x-input 
                :label="__('Nomor WhatsApp / Telepon')" 
                name="phone" 
                icon="fa-solid fa-phone"
                :value="old('phone', $user->phone)" 
                placeholder="08123456789"
            />

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 space-y-4">
                <div>
                    <h3 class="font-bold text-sm text-slate-800 dark:text-slate-200">{{ __('Ubah Kata Sandi (Opsional)') }}</h3>
                    <p class="text-xs text-slate-400 mt-0.5">{{ __('Kosongkan jika Anda tidak ingin mengubah kata sandi saat ini.') }}</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-input 
                        :label="__('Kata Sandi Baru')" 
                        type="password" 
                        name="new_password" 
                        icon="fa-solid fa-lock"
                        placeholder="Minimal 8 karakter"
                    />

                    <x-input 
                        :label="__('Konfirmasi Kata Sandi Baru')" 
                        type="password" 
                        name="new_password_confirmation" 
                        icon="fa-solid fa-lock"
                        placeholder="Ulangi kata sandi baru"
                    />
                </div>
            </div>

            <div class="pt-4 flex justify-end border-t border-slate-100 dark:border-slate-800">
                <x-button type="submit" variant="primary" icon="fa-solid fa-floppy-disk">
                    {{ __('Simpan Perubahan') }}
                </x-button>
            </div>
        </form>
    </x-card>

</div>
@endsection
