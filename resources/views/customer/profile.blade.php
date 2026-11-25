@extends('layouts.customer')

@section('title', 'Profil Pengguna')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Pengaturan Profil</h1>
            <p class="text-sm text-slate-500">Perbarui informasi kontak dan kata sandi akun Anda.</p>
        </div>

        <form method="POST" action="{{ route('customer.profile.update') }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap</label>
                <input type="text" name="name" required value="{{ old('name', $user->name) }}"
                       class="w-full text-sm border border-slate-300 rounded-xl p-3 focus:ring-2 focus:ring-cyan-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Email</label>
                <input type="email" disabled value="{{ $user->email }}"
                       class="w-full text-sm border border-slate-200 bg-slate-50 rounded-xl p-3 text-slate-500 cursor-not-allowed">
                <span class="text-[10px] text-slate-400">Email tidak dapat diubah. Hubungi admin jika butuh perubahan email.</span>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WhatsApp / HP</label>
                <input type="text" name="phone" required value="{{ old('phone', $user->phone) }}"
                       class="w-full text-sm border border-slate-300 rounded-xl p-3 focus:ring-2 focus:ring-cyan-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Pelanggan</label>
                <select name="customer_type" class="w-full text-sm border border-slate-300 rounded-xl p-3 bg-white">
                    <option value="household" {{ ($user->profile?->customer_type ?? '') === 'household' ? 'selected' : '' }}>Rumah Tangga / Pribadi</option>
                    <option value="retail_shop" {{ ($user->profile?->customer_type ?? '') === 'retail_shop' ? 'selected' : '' }}>Toko Kelontong / Warung</option>
                    <option value="office" {{ ($user->profile?->customer_type ?? '') === 'office' ? 'selected' : '' }}>Kantor / Instansi</option>
                    <option value="restaurant" {{ ($user->profile?->customer_type ?? '') === 'restaurant' ? 'selected' : '' }}>Restoran / Cafe</option>
                </select>
            </div>

            <div class="pt-4 border-t border-slate-200">
                <h3 class="font-bold text-sm text-slate-800 mb-3">Ubah Kata Sandi (Opsional)</h3>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Kata Sandi Baru</label>
                        <input type="password" name="new_password"
                               class="w-full text-sm border border-slate-300 rounded-xl p-3 focus:ring-2 focus:ring-cyan-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Konfirmasi Kata Sandi Baru</label>
                        <input type="password" name="new_password_confirmation"
                               class="w-full text-sm border border-slate-300 rounded-xl p-3 focus:ring-2 focus:ring-cyan-500">
                    </div>
                </div>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-xl font-bold text-sm text-white bg-cyan-600 hover:bg-cyan-700 shadow-xs transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    <!-- Alamat Pengantaran Dedicated Card -->
    <div class="mt-6 bg-white rounded-2xl border border-slate-200 p-6 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-start space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-cyan-50 text-cyan-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-map-location-dot"></i>
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <h2 class="text-base font-extrabold text-slate-900">Buku Alamat Pengantaran</h2>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-cyan-50 text-cyan-700 border border-cyan-100">
                        {{ $user->addresses->count() }} Alamat Tersimpan
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Kelola daftar lokasi rumah, kantor, atau toko lengkap dengan koordinat peta GPS agar kurir mengantar tepat ke lokasi Anda.
                </p>

                @php
                    $defaultAddr = $user->addresses->firstWhere('is_default', true) ?? $user->addresses->first();
                @endphp
                @if ($defaultAddr)
                    <div class="mt-3 inline-flex items-center text-xs bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200 text-slate-700">
                        <i class="fa-solid fa-circle-check text-emerald-600 mr-2"></i>
                        <span class="font-bold mr-1">Alamat Utama ({{ $defaultAddr->label }}):</span>
                        <span class="truncate max-w-xs text-slate-500">{{ $defaultAddr->address_line }}</span>
                    </div>
                @else
                    <div class="mt-3 inline-flex items-center text-xs bg-amber-50 px-3 py-1.5 rounded-xl border border-amber-200 text-amber-800">
                        <i class="fa-solid fa-circle-exclamation text-amber-600 mr-1.5"></i>
                        <span>Belum ada alamat pengantaran tersimpan. Tambahkan alamat sekarang.</span>
                    </div>
                @endif
            </div>
        </div>

        <a href="{{ route('customer.addresses.index') }}" 
           class="px-5 py-2.5 rounded-xl text-xs font-bold bg-cyan-600 hover:bg-cyan-700 text-white transition flex items-center justify-center space-x-2 shrink-0 shadow-xs">
            <span>Buka Buku Alamat</span>
            <i class="fa-solid fa-arrow-right text-xs"></i>
        </a>
    </div>
</div>
@endsection
