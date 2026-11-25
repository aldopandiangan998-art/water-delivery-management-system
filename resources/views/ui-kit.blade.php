@extends('layouts.app')

@section('title', 'UI Component Kit')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">
    <div>
        <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">AquaFlow UI Component Kit</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Showcase of reusable Blade components, tokens, and responsive patterns.</p>
    </div>

    <!-- 1. Buttons -->
    <x-card title="Buttons (<x-button>)">
        <div class="flex flex-wrap items-center gap-3">
            <x-button variant="primary" icon="fa-solid fa-cart-plus">Primary Button</x-button>
            <x-button variant="secondary" icon="fa-solid fa-filter">Secondary</x-button>
            <x-button variant="ghost" icon="fa-solid fa-arrow-left">Ghost Button</x-button>
            <x-button variant="danger" icon="fa-solid fa-trash-can">Danger</x-button>
            <x-button variant="success" icon="fa-solid fa-check">Success</x-button>
            <x-button size="sm">Small</x-button>
            <x-button size="lg">Large</x-button>
        </div>
    </x-card>

    <!-- 2. Badges & Avatars -->
    <x-card title="Badges & Avatars (<x-badge>, <x-avatar>)">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-2">
                <x-badge variant="primary" dot="true">Pesanan Baru</x-badge>
                <x-badge variant="success" dot="true">Selesai</x-badge>
                <x-badge variant="warning" dot="true">Menunggu</x-badge>
                <x-badge variant="danger" dot="true">Dibatalkan</x-badge>
                <x-badge variant="info">Dalam Antar</x-badge>
            </div>
            <div class="flex items-center gap-3">
                <x-avatar name="Budi Santoso" size="sm" />
                <x-avatar name="Budi Santoso" size="md" />
                <x-avatar name="Budi Santoso" size="lg" />
            </div>
        </div>
    </x-card>

    <!-- 3. Form Inputs & Pickers -->
    <x-card title="Form Controls (<x-input>, <x-select>, <x-textarea>, <x-checkbox>, <x-datepicker>)">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <x-input label="Nama Lengkap" placeholder="Masukkan nama..." icon="fa-solid fa-user" />
            <x-select label="Kategori Produk" placeholder="Pilih Kategori" :options="['1' => 'Air RO', '2' => 'Bio Mineral']" icon="fa-solid fa-boxes-stacked" />
            <x-datepicker label="Pilih Tanggal Pengantaran" name="delivery_date" />
            <div class="flex items-center space-x-3 pt-6">
                <x-button variant="secondary" icon="fa-solid fa-magnifying-glass" @click="$dispatch('open-global-search')">
                    Buka Global Search (Ctrl+K)
                </x-button>
                <div class="flex items-center space-x-2">
                    <span class="text-xs text-slate-500 font-bold">Bell:</span>
                    <x-notification-bell />
                </div>
            </div>
            <div class="md:col-span-2">
                <x-textarea label="Catatan Pengantaran" placeholder="Contoh: Belakang pos satpam..." />
            </div>
            <div class="md:col-span-2">
                <x-checkbox label="Jadikan alamat utama untuk pengantaran" description="Alamat ini akan otomatis terpilih saat Anda memesan air galon." checked="true" />
            </div>
        </div>
    </x-card>

    <!-- 4. Alerts & Toast Trigger -->
    <x-card title="Alerts & Toasts (<x-alert>, <x-toast>)">
        <div class="space-y-3">
            <x-alert variant="success" title="Pesanan Berhasil">Pesanan air minum Anda telah terdaftar dan kurir sedang menuju lokasi.</x-alert>
            <x-alert variant="warning" title="Stok Menipis">Stok galon Le Minerale tersisa 5 unit di gudang.</x-alert>
            <x-alert variant="danger" title="Gagal Memproses">Alamat belum lengkap, silakan pilih titik peta.</x-alert>
            <div class="pt-2 flex gap-2">
                <x-button size="sm" @click="$dispatch('toast', { message: 'Aksi berhasil disimpan!', type: 'success' })">Trigger Success Toast</x-button>
                <x-button size="sm" variant="danger" @click="$dispatch('toast', { message: 'Terjadi kesalahan sistem!', type: 'error' })">Trigger Error Toast</x-button>
            </div>
        </div>
    </x-card>

    <!-- 5. Tables & Skeletons -->
    <x-card title="Tables & Empty State (<x-table>, <x-empty-state>)">
        <x-table :headers="['No', 'Nama Produk', 'Harga', 'Status']">
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                <td class="px-5 py-3 font-bold">#1</td>
                <td class="px-5 py-3">Air Galon RO Murni</td>
                <td class="px-5 py-3 font-bold text-cyan-600">Rp 7.000</td>
                <td class="px-5 py-3"><x-badge variant="success" dot="true">Tersedia</x-badge></td>
            </tr>
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                <td class="px-5 py-3 font-bold">#2</td>
                <td class="px-5 py-3">Galon Aqua Asli 19L</td>
                <td class="px-5 py-3 font-bold text-cyan-600">Rp 22.000</td>
                <td class="px-5 py-3"><x-badge variant="success" dot="true">Tersedia</x-badge></td>
            </tr>
        </x-table>
        <div class="mt-6">
            <x-empty-state icon="fa-solid fa-cart-shopping" title="Keranjang Kosong" description="Belum ada produk air galon yang Anda pilih saat ini." />
        </div>
    </x-card>
</div>

<x-toast />
@endsection
