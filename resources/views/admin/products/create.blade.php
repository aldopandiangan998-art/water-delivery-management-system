@extends('layouts.admin')

@section('page_title', 'Tambah Produk Baru')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-xs space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">Tambah Produk Baru</h1>
                <p class="text-xs text-slate-500">Lengkapi informasi produk air minum atau aksesoris depot.</p>
            </div>
            <a href="{{ route('admin.products.index') }}" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">
                &larr; Kembali
            </a>
        </div>

        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Produk</label>
                <input type="text" name="name" required placeholder="Contoh: Air Galon Isi Ulang RO" value="{{ old('name') }}"
                       class="w-full text-sm border border-slate-300 rounded-xl p-3 focus:ring-2 focus:ring-cyan-500">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Produk</label>
                    <select name="category_id" class="w-full text-sm border border-slate-300 rounded-xl p-3 bg-white">
                        <option value="">Pilih Kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Satuan Produk</label>
                    <input type="text" name="unit" required placeholder="galon, botol, dus, unit" value="{{ old('unit', 'galon') }}"
                           class="w-full text-sm border border-slate-300 rounded-xl p-3 focus:ring-2 focus:ring-cyan-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Harga Jual Satuan (Rp)</label>
                    <input type="number" name="price" required placeholder="Contoh: 7000" value="{{ old('price') }}"
                           class="w-full text-sm border border-slate-300 rounded-xl p-3 font-bold focus:ring-2 focus:ring-cyan-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Harga Modal / HPP (Rp)</label>
                    <input type="number" name="cost_price" placeholder="Contoh: 2000" value="{{ old('cost_price', 0) }}"
                           class="w-full text-sm border border-slate-300 rounded-xl p-3 focus:ring-2 focus:ring-cyan-500">
                </div>
            </div>

            <!-- Returnable Gallon settings -->
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-3">
                <div class="flex items-center text-xs">
                    <input type="checkbox" id="is_returnable_gallon" name="is_returnable_gallon" value="1" class="rounded text-cyan-600">
                    <label for="is_returnable_gallon" class="ml-2 font-bold text-slate-800">
                        Produk ini adalah Air Galon Tukar (Memerlukan Galon Kosong)
                    </label>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">
                        Deposit Galon Baru (Jika pembeli tidak membawa galon kosong):
                    </label>
                    <input type="number" name="deposit_price" placeholder="Contoh: 35000" value="{{ old('deposit_price', 0) }}"
                           class="w-full text-xs border border-slate-300 rounded-xl p-2.5 bg-white">
                </div>
            </div>

            <!-- Stock Settings -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Stok Awal Gudang</label>
                    <input type="number" name="stock_quantity" required value="{{ old('stock_quantity', 50) }}"
                           class="w-full text-sm border border-slate-300 rounded-xl p-3 focus:ring-2 focus:ring-cyan-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Batas Minimum Stok Menipis</label>
                    <input type="number" name="low_stock_threshold" required value="{{ old('low_stock_threshold', 15) }}"
                           class="w-full text-sm border border-slate-300 rounded-xl p-3 focus:ring-2 focus:ring-cyan-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Produk</label>
                <textarea name="description" rows="3" placeholder="Informasi proses filtrasi, kejernihan, dan keunggulan produk..."
                          class="w-full text-sm border border-slate-300 rounded-xl p-3 focus:ring-2 focus:ring-cyan-500">{{ old('description') }}</textarea>
            </div>

            <div class="pt-4 flex justify-end space-x-2">
                <a href="{{ route('admin.products.index') }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl font-bold text-xs text-white bg-cyan-600 hover:bg-cyan-700 shadow-xs transition">
                    Simpan Produk
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
