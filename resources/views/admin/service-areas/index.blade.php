@extends('layouts.admin')

@section('page_title', __('Wilayah & Tarif Pengantaran'))

@section('content')
<div class="space-y-6" 
     x-data="{ createModal: false, editModal: false, editArea: {}, viewMode: localStorage.getItem('view_mode_admin_service_areas') || 'list' }"
     x-effect="localStorage.setItem('view_mode_admin_service_areas', viewMode)">

    <x-card class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Wilayah Layanan & Tarif Ongkir') }}</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Atur jangkauan kelurahan/kecamatan, biaya ongkos kirim, dan minimal order per wilayah.') }}</p>
        </div>
        <div class="flex items-center space-x-2">
            <x-grid-list-toggle model="viewMode" />
            <button type="button" @click="createModal = true" 
                    class="px-4 py-2.5 rounded-xl text-xs font-bold bg-cyan-600 hover:bg-cyan-700 text-white shadow-xs transition flex items-center cursor-pointer">
                <i class="fa-solid fa-plus mr-1.5"></i> {{ __('Tambah Wilayah Baru') }}
            </button>
        </div>
    </x-card>

    <!-- List View (Table) -->
    <div x-show="viewMode === 'list'" x-cloak>
        <x-card class="p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800 text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5">{{ __('Nama Wilayah') }}</th>
                            <th class="px-5 py-3.5">{{ __('Kecamatan / Kota') }}</th>
                            <th class="px-5 py-3.5">{{ __('Tarif Ongkir') }}</th>
                            <th class="px-5 py-3.5">{{ __('Minimal Order') }}</th>
                            <th class="px-5 py-3.5">{{ __('Estimasi Antar') }}</th>
                            <th class="px-5 py-3.5">{{ __('Total Alamat Terdaftar') }}</th>
                            <th class="px-5 py-3.5 text-right">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                        @forelse ($areas as $area)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                <td class="px-5 py-3.5 font-bold text-slate-900 dark:text-white text-sm">
                                    {{ $area->name }}
                                    @if ($area->notes)
                                        <div class="text-[10px] text-slate-400 font-normal mt-0.5">{{ $area->notes }}</div>
                                    @endif
                                </td>

                                <td class="px-5 py-3.5 font-medium text-slate-600 dark:text-slate-400">
                                    {{ $area->district ?? '-' }}
                                </td>

                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    @if ($area->delivery_fee == 0)
                                        <span class="bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 px-2 py-0.5 rounded-full font-bold text-[10px]">
                                            {{ __('Gratis Ongkir') }}
                                        </span>
                                    @else
                                        <span class="font-black text-slate-900 dark:text-white">Rp {{ number_format($area->delivery_fee, 0, ',', '.') }}</span>
                                    @endif
                                </td>

                                <td class="px-5 py-3.5 font-medium text-slate-800 dark:text-slate-200 whitespace-nowrap">
                                    Rp {{ number_format($area->min_order_amount, 0, ',', '.') }}
                                </td>

                                <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                    {{ $area->estimated_delivery_time ?? '30-45 menit' }}
                                </td>

                                <td class="px-5 py-3.5 text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                    <span class="font-bold text-cyan-600 dark:text-cyan-400">{{ $area->addresses_count }}</span> {{ __('alamat pelanggan') }}
                                </td>

                                <td class="px-5 py-3.5 text-right whitespace-nowrap space-x-2">
                                    <form method="POST" action="{{ route('admin.service-areas.destroy', $area) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" 
                                                @click="$dispatch('open-confirm-modal', {
                                                    form: $el.closest('form'),
                                                    title: '{{ __('Hapus Wilayah Layanan') }}',
                                                    message: '{{ __('Apakah Anda yakin ingin menghapus wilayah layanan') }} \'{{ addslashes($area->name) }}\'?'
                                                })"
                                                class="text-rose-500 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300 text-xs font-semibold p-1 cursor-pointer"
                                                title="{{ __('Hapus Wilayah') }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                                    <x-empty-state 
                                        icon="fa-solid fa-map" 
                                        :title="__('Belum Ada Wilayah Layanan')" 
                                        :description="__('Tambahkan wilayah jangkauan pengantaran beserta tarif ongkos kirim.')"
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
        @forelse ($areas as $area)
            <x-card class="p-5 flex flex-col justify-between space-y-4 hover:shadow-md transition">
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h3 class="font-extrabold text-base text-slate-900 dark:text-white">{{ $area->name }}</h3>
                            <span class="text-xs text-slate-500 dark:text-slate-400">{{ $area->district ?? '-' }}</span>
                        </div>
                        @if ($area->delivery_fee == 0)
                            <span class="bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 px-2 py-0.5 rounded-full font-bold text-[10px] shrink-0">
                                {{ __('Gratis Ongkir') }}
                            </span>
                        @else
                            <span class="font-black text-cyan-600 dark:text-cyan-400 text-sm shrink-0">
                                Rp {{ number_format($area->delivery_fee, 0, ',', '.') }}
                            </span>
                        @endif
                    </div>

                    @if ($area->notes)
                        <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 bg-slate-50 dark:bg-slate-950/60 p-2 rounded-lg border border-slate-100 dark:border-slate-800">
                            {{ $area->notes }}
                        </p>
                    @endif

                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-100 dark:border-slate-800">
                            <div class="text-[10px] uppercase font-bold text-slate-400">{{ __('Min. Order') }}</div>
                            <div class="font-bold text-slate-900 dark:text-white mt-0.5">Rp {{ number_format($area->min_order_amount, 0, ',', '.') }}</div>
                        </div>

                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-100 dark:border-slate-800">
                            <div class="text-[10px] uppercase font-bold text-slate-400">{{ __('Estimasi') }}</div>
                            <div class="font-bold text-slate-900 dark:text-white mt-0.5">{{ $area->estimated_delivery_time ?? '30-45 m' }}</div>
                        </div>
                    </div>

                    <div class="text-xs text-slate-500 dark:text-slate-400 flex items-center">
                        <i class="fa-solid fa-users text-cyan-500 mr-1.5"></i>
                        <span><strong class="text-slate-900 dark:text-white">{{ $area->addresses_count }}</strong> {{ __('alamat terdaftar') }}</span>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                    <form method="POST" action="{{ route('admin.service-areas.destroy', $area) }}" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="button" 
                                @click="$dispatch('open-confirm-modal', {
                                    form: $el.closest('form'),
                                    title: '{{ __('Hapus Wilayah Layanan') }}',
                                    message: '{{ __('Apakah Anda yakin ingin menghapus wilayah layanan') }} \'{{ addslashes($area->name) }}\'?'
                                })"
                                class="text-rose-500 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300 text-xs font-semibold px-3 py-1.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 transition cursor-pointer flex items-center gap-1.5">
                            <i class="fa-solid fa-trash text-[11px]"></i> {{ __('Hapus Wilayah') }}
                        </button>
                    </form>
                </div>
            </x-card>
        @empty
            <div class="col-span-full">
                <x-card class="p-12 text-center">
                    <x-empty-state 
                        icon="fa-solid fa-map" 
                        :title="__('Belum Ada Wilayah Layanan')" 
                        :description="__('Tambahkan wilayah jangkauan pengantaran beserta tarif ongkos kirim.')"
                    />
                </x-card>
            </div>
        @endforelse
    </div>

    <!-- Modal Tambah Wilayah -->
    <div x-show="createModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-slate-100 dark:border-slate-800" @click.away="createModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="font-extrabold text-base text-slate-900 dark:text-white">{{ __('Tambah Wilayah Layanan') }}</h3>
                <button type="button" @click="createModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.service-areas.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">{{ __('Nama Wilayah / Kelurahan') }}</label>
                    <input type="text" name="name" required placeholder="{{ __('Contoh: Kelurahan Kertajaya') }}"
                           class="w-full text-xs border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white rounded-xl p-2.5">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">{{ __('Kecamatan / Kota') }}</label>
                    <input type="text" name="district" placeholder="{{ __('Contoh: Kecamatan Gubeng, Surabaya') }}"
                           class="w-full text-xs border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white rounded-xl p-2.5">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">{{ __('Tarif Ongkir (Rp)') }}</label>
                        <input type="number" name="delivery_fee" required value="3000" min="0"
                               class="w-full text-xs font-bold border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white rounded-xl p-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">{{ __('Minimal Order (Rp)') }}</label>
                        <input type="number" name="min_order_amount" required value="15000" min="0"
                               class="w-full text-xs font-bold border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white rounded-xl p-2.5">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">{{ __('Estimasi Waktu Antar') }}</label>
                    <input type="text" name="estimated_delivery_time" value="30-45 menit"
                           class="w-full text-xs border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white rounded-xl p-2.5">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">{{ __('Catatan Tambahan (Opsional)') }}</label>
                    <input type="text" name="notes" placeholder="{{ __('Contoh: Rute jalan sempit, antar khusus motor...') }}"
                           class="w-full text-xs border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white rounded-xl p-2.5">
                </div>

                <div class="pt-3 flex justify-end space-x-2">
                    <button type="button" @click="createModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer">
                        {{ __('Batal') }}
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-cyan-600 hover:bg-cyan-700 text-white shadow-xs cursor-pointer">
                        {{ __('Simpan Wilayah') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
