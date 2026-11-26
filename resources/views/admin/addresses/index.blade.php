@extends('layouts.admin')

@section('page_title', __('Kelola Alamat Pelanggan'))

@section('content')
<div class="space-y-6"
     x-data="{ viewMode: localStorage.getItem('view_mode_admin_addresses') || 'list' }"
     x-effect="localStorage.setItem('view_mode_admin_addresses', viewMode)">

    <!-- Top header card -->
    <x-card class="p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Manajemen Alamat Pelanggan') }}</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Kelola buku alamat pelanggan, patokan lokasi rumah, koordinat GPS, dan wilayah pengantaran.') }}</p>
        </div>
        <div class="flex items-center space-x-2">
            <x-grid-list-toggle model="viewMode" />
        </div>
    </x-card>

    <!-- Filters & Search -->
    <x-card class="p-4">
        <form method="GET" action="{{ route('admin.addresses.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            <div class="sm:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="{{ __('Cari penerima, no. telp, jalan, patokan, atau nama pelanggan...') }}"
                       class="w-full border border-slate-200 dark:border-slate-700 rounded-xl p-2.5 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white focus:bg-white focus:ring-2 focus:ring-cyan-500 transition">
            </div>

            <div>
                <select name="user_id" onchange="this.form.submit()" class="w-full border border-slate-200 dark:border-slate-700 rounded-xl p-2.5 bg-white dark:bg-slate-900 text-slate-900 dark:text-white font-medium">
                    <option value="">{{ __('Semua Pelanggan') }}</option>
                    @foreach ($customers as $cust)
                        <option value="{{ $cust->id }}" {{ request('user_id') == $cust->id ? 'selected' : '' }}>
                            {{ $cust->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex space-x-2">
                <select name="service_area_id" onchange="this.form.submit()" class="flex-1 border border-slate-200 dark:border-slate-700 rounded-xl p-2.5 bg-white dark:bg-slate-900 text-slate-900 dark:text-white font-medium">
                    <option value="">{{ __('Semua Wilayah') }}</option>
                    @foreach ($serviceAreas as $area)
                        <option value="{{ $area->id }}" {{ request('service_area_id') == $area->id ? 'selected' : '' }}>
                            {{ $area->name }}
                        </option>
                    @endforeach
                </select>

                <x-button type="submit" variant="primary">
                    {{ __('Filter') }}
                </x-button>
            </div>
        </form>
    </x-card>

    <!-- List View (Table) -->
    <div x-show="viewMode === 'list'" x-cloak>
        <x-card class="p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800 text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5">{{ __('Akun Pelanggan') }}</th>
                            <th class="px-5 py-3.5">{{ __('Penerima & Kontak') }}</th>
                            <th class="px-5 py-3.5">{{ __('Alamat & Patokan') }}</th>
                            <th class="px-5 py-3.5">{{ __('Wilayah Layanan') }}</th>
                            <th class="px-5 py-3.5">{{ __('Koordinat GPS') }}</th>
                            <th class="px-5 py-3.5">{{ __('Status') }}</th>
                            <th class="px-5 py-3.5 text-right">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                        @forelse ($addresses as $addr)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                <td class="px-5 py-3.5">
                                    <div class="font-bold text-slate-900 dark:text-white flex items-center">
                                        <div class="w-6 h-6 rounded-full bg-cyan-100 text-cyan-700 dark:bg-cyan-900 dark:text-cyan-300 font-bold flex items-center justify-center text-[10px] mr-2">
                                            {{ strtoupper(substr($addr->user?->name ?? 'P', 0, 1)) }}
                                        </div>
                                        <span>{{ $addr->user?->name ?? __('Pelanggan Tidak Dikenal') }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5 ml-8">{{ $addr->user?->email }}</div>
                                </td>

                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="flex items-center space-x-1.5">
                                        <span class="font-bold text-slate-900 dark:text-white">{{ $addr->recipient_name }}</span>
                                        <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                            {{ $addr->label }}
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 flex items-center">
                                        <i class="fa-solid fa-phone text-slate-400 mr-1 text-[10px]"></i> {{ $addr->recipient_phone }}
                                    </div>
                                </td>

                                <td class="px-5 py-3.5">
                                    <div class="font-medium text-slate-800 dark:text-slate-200 line-clamp-2 max-w-[240px]">
                                        {{ $addr->address_line }}
                                    </div>
                                    @if ($addr->benchmark)
                                        <div class="text-[11px] text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded-md inline-block mt-1 border border-amber-200 dark:border-amber-800">
                                            <span class="font-bold">{{ __('Patokan') }}:</span> {{ $addr->benchmark }}
                                        </div>
                                    @endif
                                </td>

                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    @if ($addr->serviceArea)
                                        <div class="font-semibold text-slate-900 dark:text-white">{{ $addr->serviceArea->name }}</div>
                                        <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold">
                                            {{ __('Ongkir') }}: Rp {{ number_format($addr->serviceArea->delivery_fee, 0, ',', '.') }}
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic">{{ __('Belum diatur') }}</span>
                                    @endif
                                </td>

                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    @if ($addr->latitude && $addr->longitude)
                                        <div class="font-mono text-[11px] text-slate-600 dark:text-slate-400">
                                            {{ number_format($addr->latitude, 4) }}, {{ number_format($addr->longitude, 4) }}
                                        </div>
                                        <a href="https://maps.google.com/?q={{ $addr->latitude }},{{ $addr->longitude }}" target="_blank"
                                           class="text-[10px] font-bold text-cyan-600 dark:text-cyan-400 hover:underline flex items-center mt-0.5">
                                            <i class="fa-solid fa-arrow-up-right-from-square mr-1 text-[9px]"></i> Google Maps
                                        </a>
                                    @else
                                        <span class="text-slate-400 italic">{{ __('Tanpa Titik GPS') }}</span>
                                    @endif
                                </td>

                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    @if ($addr->is_default)
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-cyan-50 dark:bg-cyan-950/40 text-cyan-800 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800 flex items-center w-fit">
                                            <i class="fa-solid fa-circle-check mr-1"></i> {{ __('Utama') }}
                                        </span>
                                    @else
                                        <form method="POST" action="{{ route('admin.addresses.set-default', $addr) }}">
                                            @csrf
                                            <button type="submit" class="text-[11px] text-slate-500 dark:text-slate-400 hover:text-cyan-600 dark:hover:text-cyan-400 font-medium underline cursor-pointer">
                                                {{ __('Jadikan Utama') }}
                                            </button>
                                        </form>
                                    @endif
                                </td>

                                <td class="px-5 py-3.5 text-right whitespace-nowrap space-x-1.5">
                                    <a href="{{ route('admin.addresses.show', $addr) }}" 
                                       class="text-xs font-semibold px-2.5 py-1 rounded-lg text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 transition">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>

                                    <a href="{{ route('admin.addresses.edit', $addr) }}" 
                                       class="text-xs font-bold px-2.5 py-1 rounded-lg text-cyan-700 dark:text-cyan-400 bg-cyan-50 dark:bg-cyan-950/40 hover:bg-cyan-100 transition">
                                        <i class="fa-solid fa-pen-to-square"></i> {{ __('Edit') }}
                                    </a>

                                    <form id="delete-admin-address-{{ $addr->id }}" method="POST" action="{{ route('admin.addresses.destroy', $addr) }}" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" 
                                                @click="$dispatch('open-confirm-modal', {
                                                    form: 'delete-admin-address-{{ $addr->id }}',
                                                    title: '{{ __('Hapus Alamat Pelanggan') }}',
                                                    message: '{{ __('Apakah Anda yakin ingin menghapus alamat ini?') }}'
                                                })"
                                                class="text-xs font-bold px-2.5 py-1 rounded-lg text-rose-600 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 transition cursor-pointer"
                                                title="{{ __('Hapus Alamat') }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                                    <x-empty-state 
                                        icon="fa-solid fa-map-location-dot" 
                                        :title="__('Tidak Ada Data Alamat')" 
                                        :description="__('Tidak ditemukan alamat pelanggan yang sesuai kriteria filter.')"
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>

    <!-- Grid View (Cards) -->
    <div x-show="viewMode === 'grid'" x-cloak class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($addresses as $addr)
            <x-card class="p-5 flex flex-col justify-between space-y-4 hover:shadow-md transition {{ $addr->is_default ? 'ring-2 ring-cyan-500 border-cyan-500/40' : '' }}">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <div class="w-7 h-7 rounded-full bg-cyan-100 text-cyan-700 dark:bg-cyan-900 dark:text-cyan-300 font-bold flex items-center justify-center text-xs">
                                {{ strtoupper(substr($addr->user?->name ?? 'P', 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-bold text-xs text-slate-900 dark:text-white">{{ $addr->user?->name ?? __('Pelanggan Tidak Dikenal') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $addr->user?->email }}</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                {{ $addr->label }}
                            </span>
                            @if ($addr->is_default)
                                <x-badge variant="info" size="sm">
                                    <i class="fa-solid fa-circle-check mr-1"></i> {{ __('Utama') }}
                                </x-badge>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="font-extrabold text-sm text-slate-900 dark:text-white">{{ $addr->recipient_name }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 flex items-center">
                            <i class="fa-solid fa-phone text-slate-400 mr-1.5 text-[10px]"></i> {{ $addr->recipient_phone }}
                        </div>
                    </div>

                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed line-clamp-2">
                        {{ $addr->address_line }}
                    </p>

                    @if ($addr->benchmark)
                        <div class="text-[11px] text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/40 px-2.5 py-1 rounded-xl border border-amber-200 dark:border-amber-800">
                            <span class="font-bold">{{ __('Patokan') }}:</span> {{ $addr->benchmark }}
                        </div>
                    @endif

                    <div class="flex items-center justify-between text-xs pt-1">
                        <div>
                            @if ($addr->serviceArea)
                                <div class="font-semibold text-cyan-600 dark:text-cyan-400">
                                    <i class="fa-solid fa-location-dot mr-1"></i>{{ $addr->serviceArea->name }}
                                </div>
                                <div class="text-[10px] text-slate-400">
                                    Rp {{ number_format($addr->serviceArea->delivery_fee, 0, ',', '.') }}
                                </div>
                            @else
                                <span class="text-slate-400 italic text-[11px]">{{ __('Tanpa Area') }}</span>
                            @endif
                        </div>

                        <div>
                            @if ($addr->latitude && $addr->longitude)
                                <a href="https://maps.google.com/?q={{ $addr->latitude }},{{ $addr->longitude }}" target="_blank"
                                   class="text-[11px] font-bold text-cyan-600 dark:text-cyan-400 hover:underline flex items-center">
                                    <i class="fa-solid fa-map-pin mr-1 text-[10px]"></i> Maps
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                    <div>
                        @if (!$addr->is_default)
                            <form method="POST" action="{{ route('admin.addresses.set-default', $addr) }}">
                                @csrf
                                <button type="submit" class="font-bold text-cyan-600 dark:text-cyan-400 hover:underline cursor-pointer">
                                    {{ __('Jadikan Utama') }}
                                </button>
                            </form>
                        @endif
                    </div>

                    <div class="flex items-center space-x-1.5">
                        <a href="{{ route('admin.addresses.show', $addr) }}" 
                           class="text-xs font-semibold px-2.5 py-1.5 rounded-lg text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 transition">
                            <i class="fa-solid fa-eye"></i>
                        </a>

                        <a href="{{ route('admin.addresses.edit', $addr) }}" 
                           class="text-xs font-bold px-2.5 py-1.5 rounded-lg text-cyan-700 dark:text-cyan-400 bg-cyan-50 dark:bg-cyan-950/40 hover:bg-cyan-100 transition">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>

                        <form id="delete-admin-address-grid-{{ $addr->id }}" method="POST" action="{{ route('admin.addresses.destroy', $addr) }}" class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="button" 
                                    @click="$dispatch('open-confirm-modal', {
                                        form: 'delete-admin-address-grid-{{ $addr->id }}',
                                        title: '{{ __('Hapus Alamat Pelanggan') }}',
                                        message: '{{ __('Apakah Anda yakin ingin menghapus alamat ini?') }}'
                                    })"
                                    class="text-xs font-bold px-2.5 py-1.5 rounded-lg text-rose-600 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 transition cursor-pointer"
                                    title="{{ __('Hapus Alamat') }}">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </x-card>
        @empty
            <div class="col-span-full">
                <x-card class="p-12 text-center">
                    <x-empty-state 
                        icon="fa-solid fa-map-location-dot" 
                        :title="__('Tidak Ada Data Alamat')" 
                        :description="__('Tidak ditemukan alamat pelanggan yang sesuai kriteria filter.')"
                    />
                </x-card>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800">
        {{ $addresses->links() }}
    </div>

</div>
@endsection
