@extends('layouts.admin')

@section('page_title', 'Wilayah & Tarif Pengantaran')

@section('content')
<div class="space-y-6" x-data="{ createModal: false, editModal: false, editArea: {} }">

    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight">Wilayah Layanan & Tarif Ongkir</h1>
            <p class="text-xs text-slate-500">Atur jangkauan kelurahan/kecamatan, biaya ongkos kirim, dan minimal order per wilayah.</p>
        </div>
        <button type="button" @click="createModal = true" 
                class="px-4 py-2.5 rounded-xl text-xs font-bold bg-cyan-600 hover:bg-cyan-700 text-white shadow-xs transition flex items-center">
            <i class="fa-solid fa-plus mr-1.5"></i> Tambah Wilayah Baru
        </button>
    </div>

    <!-- Areas Table -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Nama Wilayah</th>
                        <th class="px-5 py-3.5">Kecamatan / Kota</th>
                        <th class="px-5 py-3.5">Tarif Ongkir</th>
                        <th class="px-5 py-3.5">Minimal Order</th>
                        <th class="px-5 py-3.5">Estimasi Antar</th>
                        <th class="px-5 py-3.5">Total Alamat Terdaftar</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($areas as $area)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3.5 font-bold text-slate-900 text-sm">
                                {{ $area->name }}
                                @if ($area->notes)
                                    <div class="text-[10px] text-slate-400 font-normal">{{ $area->notes }}</div>
                                @endif
                            </td>

                            <td class="px-5 py-3.5 font-medium text-slate-600">
                                {{ $area->district ?? '-' }}
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if ($area->delivery_fee == 0)
                                    <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded-full font-bold text-[10px]">
                                        Gratis Ongkir
                                    </span>
                                @else
                                    <span class="font-black text-slate-900">Rp {{ number_format($area->delivery_fee, 0, ',', '.') }}</span>
                                @endif
                            </td>

                            <td class="px-5 py-3.5 font-medium text-slate-800 whitespace-nowrap">
                                Rp {{ number_format($area->min_order_amount, 0, ',', '.') }}
                            </td>

                            <td class="px-5 py-3.5 text-slate-500 whitespace-nowrap">
                                {{ $area->estimated_delivery_time ?? '30-45 menit' }}
                            </td>

                            <td class="px-5 py-3.5 text-slate-600 whitespace-nowrap">
                                <span class="font-bold text-cyan-700">{{ $area->addresses_count }}</span> alamat pelanggan
                            </td>

                            <td class="px-5 py-3.5 text-right whitespace-nowrap space-x-2">
                                <form method="POST" action="{{ route('admin.service-areas.destroy', $area) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" 
                                            @click="$dispatch('open-confirm-modal', {
                                                form: $el.closest('form'),
                                                title: 'Hapus Wilayah Layanan',
                                                message: 'Apakah Anda yakin ingin menghapus wilayah layanan \'{{ addslashes($area->name) }}\'?'
                                            })"
                                            class="text-rose-500 hover:text-rose-700 text-xs font-semibold p-1 cursor-pointer"
                                            title="Hapus Wilayah">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                                Belum ada wilayah layanan yang ditambahkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah Wilayah -->
    <div x-show="createModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4" @click.away="createModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-extrabold text-base text-slate-900">Tambah Wilayah Layanan</h3>
                <button type="button" @click="createModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.service-areas.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Wilayah / Kelurahan</label>
                    <input type="text" name="name" required placeholder="Contoh: Kelurahan Kertajaya"
                           class="w-full text-xs border border-slate-300 rounded-xl p-2.5">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kecamatan / Kota</label>
                    <input type="text" name="district" placeholder="Contoh: Kecamatan Gubeng, Surabaya"
                           class="w-full text-xs border border-slate-300 rounded-xl p-2.5">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tarif Ongkir (Rp)</label>
                        <input type="number" name="delivery_fee" required value="3000" min="0"
                               class="w-full text-xs font-bold border border-slate-300 rounded-xl p-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Minimal Order (Rp)</label>
                        <input type="number" name="min_order_amount" required value="15000" min="0"
                               class="w-full text-xs font-bold border border-slate-300 rounded-xl p-2.5">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Estimasi Waktu Antar</label>
                    <input type="text" name="estimated_delivery_time" value="30-45 menit"
                           class="w-full text-xs border border-slate-300 rounded-xl p-2.5">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                    <input type="text" name="notes" placeholder="Contoh: Rute jalan sempit, antar khusus motor..."
                           class="w-full text-xs border border-slate-300 rounded-xl p-2.5">
                </div>

                <div class="pt-3 flex justify-end space-x-2">
                    <button type="button" @click="createModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-cyan-600 hover:bg-cyan-700 text-white shadow-xs">
                        Simpan Wilayah
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
