@extends('layouts.customer')

@section('title', __('Profil Pengguna'))

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    <x-card class="p-6 sm:p-8 space-y-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Pengaturan Profil') }}</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Perbarui informasi kontak dan kata sandi akun Anda.') }}</p>
        </div>

        <form method="POST" action="{{ route('customer.profile.update') }}" class="space-y-5">
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
                    :label="__('Email')" 
                    name="email" 
                    icon="fa-regular fa-envelope"
                    :value="$user->email" 
                    disabled 
                    :helper="__('Email tidak dapat diubah. Hubungi admin jika butuh perubahan email.')"
                />
            </div>

            <x-input 
                :label="__('Nomor WhatsApp / HP')" 
                name="phone" 
                icon="fa-solid fa-phone"
                :value="old('phone', $user->phone)" 
                required 
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
                :selected="old('customer_type', $user->profile?->customer_type ?? 'household')" 
            />

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 space-y-4">
                <h3 class="font-bold text-sm text-slate-800 dark:text-slate-200">{{ __('Ubah Kata Sandi (Opsional)') }}</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-input 
                        :label="__('Kata Sandi Baru')" 
                        type="password" 
                        name="new_password" 
                        icon="fa-solid fa-lock"
                        placeholder="••••••••"
                    />

                    <x-input 
                        :label="__('Konfirmasi Kata Sandi Baru')" 
                        type="password" 
                        name="new_password_confirmation" 
                        icon="fa-solid fa-lock"
                        placeholder="••••••••"
                    />
                </div>
            </div>

            <div class="pt-4 flex justify-end border-t border-slate-100 dark:border-slate-800">
                <x-button type="submit" variant="primary" icon="fa-solid fa-save">
                    {{ __('Simpan Perubahan') }}
                </x-button>
            </div>
        </form>
    </x-card>

    <!-- Alamat Pengantaran Dedicated Card -->
    <x-card class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-start space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-cyan-100 dark:bg-cyan-950 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-map-location-dot"></i>
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <h2 class="text-base font-extrabold text-slate-900 dark:text-white">{{ __('Buku Alamat Pengantaran') }}</h2>
                    <x-badge variant="info" size="sm">
                        {{ $user->addresses->count() }} {{ __('Alamat Tersimpan') }}
                    </x-badge>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                    {{ __('Kelola daftar lokasi rumah, kantor, atau toko lengkap dengan koordinat peta GPS agar pesanan diantar tepat waktu.') }}
                </p>

                @php
                    $defaultAddr = $user->addresses->firstWhere('is_default', true) ?? $user->addresses->first();
                @endphp
                @if ($defaultAddr)
                    <div class="mt-3 inline-flex items-center text-xs bg-slate-50 dark:bg-slate-950/60 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300">
                        <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 mr-2"></i>
                        <span class="font-bold mr-1">{{ __('Alamat Utama') }} ({{ $defaultAddr->label }}):</span>
                        <span class="truncate max-w-xs text-slate-500 dark:text-slate-400">{{ $defaultAddr->address_line }}</span>
                    </div>
                @else
                    <div class="mt-3 inline-flex items-center text-xs bg-amber-50 dark:bg-amber-950/40 px-3 py-1.5 rounded-xl border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300">
                        <i class="fa-solid fa-circle-exclamation text-amber-600 mr-1.5"></i>
                        <span>{{ __('Belum ada alamat pengantaran tersimpan. Tambahkan alamat sekarang.') }}</span>
                    </div>
                @endif
            </div>
        </div>

        <x-button :href="route('customer.addresses.index')" variant="secondary" size="sm" iconRight="fa-solid fa-arrow-right" class="shrink-0">
            {{ __('Buka Buku Alamat') }}
        </x-button>
    </x-card>
</div>
@endsection
