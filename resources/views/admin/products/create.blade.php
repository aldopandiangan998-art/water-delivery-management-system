@extends('layouts.admin')

@section('page_title', __('Tambah Produk Baru'))

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <x-card class="p-6 sm:p-8 space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Tambah Produk Baru') }}</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Lengkapi informasi produk air minum atau aksesoris depot.') }}</p>
            </div>
            <x-button :href="route('admin.products.index')" variant="secondary" size="sm" icon="fa-solid fa-arrow-left">
                {{ __('Kembali') }}
            </x-button>
        </div>

        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <x-input 
                :label="__('Nama Produk')" 
                name="name" 
                placeholder="{{ __('Contoh: Air Galon Isi Ulang RO') }}" 
                :value="old('name')" 
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
                    :selected="old('category_id')" 
                />

                <x-input 
                    :label="__('Satuan Produk')" 
                    name="unit" 
                    placeholder="galon, botol, dus, unit" 
                    :value="old('unit', 'galon')" 
                    required 
                />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-input 
                    :label="__('Harga Jual Satuan (Rp)')" 
                    type="number" 
                    name="price" 
                    placeholder="7000" 
                    :value="old('price')" 
                    required 
                />

                <x-input 
                    :label="__('Harga Modal / HPP (Rp)')" 
                    type="number" 
                    name="cost_price" 
                    placeholder="2000" 
                    :value="old('cost_price', 0)" 
                />
            </div>

            <!-- Returnable Gallon settings -->
            <div class="p-4 bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-2xl space-y-3">
                <x-checkbox 
                    id="is_returnable_gallon" 
                    name="is_returnable_gallon" 
                    value="1" 
                    :checked="old('is_returnable_gallon')"
                    :label="__('Produk ini adalah Air Galon Tukar (Memerlukan Galon Kosong)')" 
                />
                <div>
                    <x-input 
                        :label="__('Deposit Galon Baru (Jika pembeli tidak membawa galon kosong)')" 
                        type="number" 
                        name="deposit_price" 
                        placeholder="35000" 
                        :value="old('deposit_price', 0)" 
                    />
                </div>
            </div>

            <!-- Stock Settings -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-input 
                    :label="__('Stok Awal Gudang')" 
                    type="number" 
                    name="stock_quantity" 
                    :value="old('stock_quantity', 50)" 
                    required 
                />

                <x-input 
                    :label="__('Batas Minimum Stok Menipis')" 
                    type="number" 
                    name="low_stock_threshold" 
                    :value="old('low_stock_threshold', 15)" 
                    required 
                />
            </div>

            <x-textarea 
                :label="__('Deskripsi Produk')" 
                name="description" 
                rows="3" 
                placeholder="{{ __('Informasi proses filtrasi, kejernihan, dan keunggulan produk...') }}"
                :value="old('description')"
            />

            <div class="pt-4 flex justify-end space-x-2 border-t border-slate-100 dark:border-slate-800">
                <x-button :href="route('admin.products.index')" variant="ghost">
                    {{ __('Batal') }}
                </x-button>
                <x-button type="submit" variant="primary" icon="fa-solid fa-save">
                    {{ __('Simpan Produk') }}
                </x-button>
            </div>
        </form>
    </x-card>

</div>
@endsection
