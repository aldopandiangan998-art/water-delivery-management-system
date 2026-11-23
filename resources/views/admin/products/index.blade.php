@extends('layouts.admin')

@section('page_title', 'Katalog Produk & Stok Galon')

@section('content')
<div class="space-y-6" x-data="{ adjustModalOpen: false, selectedProduct: null }">

    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight">Katalog Produk & Manajemen Stok</h1>
            <p class="text-xs text-slate-500">Kelola harga air isi ulang, galon baru, air kemasan, dan penyesuaian stok gudang.</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold bg-cyan-600 hover:bg-cyan-700 text-white shadow-xs transition flex items-center">
            <i class="fa-solid fa-plus mr-1.5"></i> Tambah Produk Baru
        </a>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Nama Produk</th>
                        <th class="px-5 py-3.5">Kategori & Satuan</th>
                        <th class="px-5 py-3.5">Harga Jual & Modal</th>
                        <th class="px-5 py-3.5">Status Galon</th>
                        <th class="px-5 py-3.5">Sisa Stok</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($products as $p)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-900 text-sm">{{ $p->name }}</div>
                                <div class="text-[11px] text-slate-400 truncate max-w-[220px]">{{ $p->description }}</div>
                            </td>

                            <td class="px-5 py-3.5">
                                <span class="font-semibold text-slate-800">{{ $p->category?->name ?? 'Tanpa Kategori' }}</span>
                                <div class="text-[10px] text-slate-400 capitalize">Satuan: {{ $p->unit }}</div>
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <div class="font-black text-cyan-700 text-sm">
                                    Rp {{ number_format($p->price, 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] text-slate-400 font-medium">
                                    Modal: Rp {{ number_format($p->cost_price, 0, ',', '.') }}
                                </div>
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if ($p->is_returnable_gallon)
                                    <span class="bg-blue-50 text-blue-700 border border-blue-200 px-2 py-0.5 rounded-full font-bold text-[10px]">
                                        Tukar Galon
                                    </span>
                                    <div class="text-[10px] text-slate-400 mt-0.5">+ Deposit: Rp {{ number_format($p->deposit_price, 0, ',', '.') }}</div>
                                @else
                                    <span class="text-slate-400 text-[11px] font-medium">Sekali Pakai</span>
                                @endif
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if ($p->tracks_inventory)
                                    <div class="flex items-center space-x-2">
                                        <span class="font-black text-sm {{ $p->isLowStock() ? 'text-rose-600' : 'text-slate-900' }}">
                                            {{ $p->stock_quantity }} {{ $p->unit }}
                                        </span>
                                        @if ($p->isLowStock())
                                            <span class="bg-rose-100 text-rose-800 text-[10px] font-bold px-1.5 py-0.2 rounded">
                                                Menipis!
                                            </span>
                                        @endif
                                    </div>
                                    <button type="button" 
                                            @click="selectedProduct = {{ Js::from($p) }}; adjustModalOpen = true"
                                            class="text-[10px] font-bold text-cyan-600 hover:underline mt-0.5 block">
                                        + Penyesuaian Stok
                                    </button>
                                @else
                                    <span class="text-slate-400 text-[11px]">Tidak Dilacak</span>
                                @endif
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $p->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $p->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>

                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <a href="{{ route('admin.products.edit', $p) }}" class="text-xs font-bold text-slate-700 hover:text-cyan-600 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                                Belum ada produk terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $products->links() }}
        </div>
    </div>

    <!-- Modal Penyesuaian Stok Gudang -->
    <div x-show="adjustModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4" @click.away="adjustModalOpen = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-extrabold text-base text-slate-900">Penyesuaian Stok Gudang</h3>
                <button type="button" @click="adjustModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <template x-if="selectedProduct">
                <form method="POST" :action="'/admin/products/' + selectedProduct.id + '/adjust-stock'" class="space-y-4">
                    @csrf

                    <div>
                        <span class="text-xs text-slate-400">Produk:</span>
                        <div class="font-bold text-sm text-slate-900" x-text="selectedProduct.name"></div>
                        <div class="text-xs text-slate-500 mt-0.5">
                            Stok Saat Ini: <span class="font-bold text-cyan-700" x-text="selectedProduct.stock_quantity + ' ' + selectedProduct.unit"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Penyesuaian</label>
                        <select name="type" required class="w-full text-xs border border-slate-300 rounded-xl p-2.5 bg-white">
                            <option value="in">Tambah Stok Masuk (Restock Gudang)</option>
                            <option value="out">Kurangi Stok (Kerusakan / Bocor / Hilang)</option>
                            <option value="adjustment">Koreksi Hitung Ulang (Opname Stok)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Unit</label>
                        <input type="number" name="quantity" required min="1" value="10"
                               class="w-full text-xs font-bold border border-slate-300 rounded-xl p-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alasan / Catatan Penyesuaian</label>
                        <input type="text" name="notes" required placeholder="Contoh: Pengiriman supplier 50 galon baru..."
                               class="w-full text-xs border border-slate-300 rounded-xl p-2.5">
                    </div>

                    <div class="pt-3 flex justify-end space-x-2">
                        <button type="button" @click="adjustModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-cyan-600 hover:bg-cyan-700 text-white shadow-xs">
                            Simpan Perubahan Stok
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
@endsection
