@extends('layouts.admin')

@section('page_title', __('Edit Produk - ') . $product->name)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <x-card class="p-6 sm:p-8 space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Edit Produk') }}</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Perbarui informasi dan harga produk.') }}</p>
            </div>
            <x-button :href="route('admin.products.index')" variant="secondary" size="sm" icon="fa-solid fa-arrow-left">
                {{ __('Kembali') }}
            </x-button>
        </div>

        <form method="POST" action="{{ route('admin.products.update', $product) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <x-input 
                :label="__('Nama Produk')" 
                name="name" 
                :value="old('name', $product->name)" 
                required 
            />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @php
                    $categoryOptions = ['' => __('Pilih Kategori')];
                    foreach ($categories as $cat) {
                        $categoryOptions[$cat->id] = $cat->name;
                    }
                @endphp
                <x-select 
                    :label="__('Kategori Produk')" 
                    name="category_id" 
                    :options="$categoryOptions" 
                    :selected="old('category_id', $product->category_id)" 
                />

                <x-input 
                    :label="__('Satuan Produk')" 
                    name="unit" 
                    :value="old('unit', $product->unit)" 
                    required 
                />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-input 
                    :label="__('Harga Jual Satuan (Rp)')" 
                    type="number" 
                    name="price" 
                    :value="old('price', $product->price)" 
                    required 
                />

                <x-input 
                    :label="__('Harga Modal / HPP (Rp)')" 
                    type="number" 
                    name="cost_price" 
                    :value="old('cost_price', $product->cost_price)" 
                />
            </div>

            <div class="p-4 bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-2xl space-y-3">
                <x-checkbox 
                    id="is_returnable_gallon" 
                    name="is_returnable_gallon" 
                    value="1" 
                    :checked="old('is_returnable_gallon', $product->is_returnable_gallon)"
                    :label="__('Produk ini adalah Air Galon Tukar (Memerlukan Galon Kosong)')" 
                />
                <div>
                    <x-input 
                        :label="__('Deposit Galon Baru (Jika pembeli tidak membawa galon kosong)')" 
                        type="number" 
                        name="deposit_price" 
                        :value="old('deposit_price', $product->deposit_price)" 
                    />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-input 
                    :label="__('Batas Minimum Stok Menipis')" 
                    type="number" 
                    name="low_stock_threshold" 
                    :value="old('low_stock_threshold', $product->low_stock_threshold)" 
                    required 
                />

                @php
                    $activeOptions = [
                        '1' => __('Aktif (Tampil di Katalog)'),
                        '0' => __('Nonaktif (Sembunyikan)'),
                    ];
                @endphp
                <x-select 
                    :label="__('Status Keaktifan')" 
                    name="is_active" 
                    :options="$activeOptions" 
                    :selected="old('is_active', $product->is_active ? '1' : '0')" 
                />
            </div>

            <x-textarea 
                :label="__('Deskripsi Produk')" 
                name="description" 
                rows="3" 
                :value="old('description', $product->description)"
            />

            <div class="pt-4 flex justify-end space-x-2 border-t border-slate-100 dark:border-slate-800">
                <x-button :href="route('admin.products.index')" variant="ghost">
                    {{ __('Batal') }}
                </x-button>
                <x-button type="submit" variant="primary" icon="fa-solid fa-save">
                    {{ __('Simpan Perubahan') }}
                </x-button>
            </div>
        </form>
    </x-card>

</div>
@endsection
