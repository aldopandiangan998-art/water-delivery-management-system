@extends('layouts.admin')

@section('page_title', __('Katalog Produk & Stok Galon'))

@section('content')
<div class="space-y-6" 
     x-data="{ adjustModalOpen: false, selectedProduct: null, viewMode: localStorage.getItem('view_mode_admin_products') || 'list' }"
     x-effect="localStorage.setItem('view_mode_admin_products', viewMode)">

    <x-card class="p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Katalog Produk & Manajemen Stok') }}</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Kelola harga air isi ulang, galon baru, air kemasan, dan penyesuaian stok gudang.') }}</p>
        </div>
        <div class="flex items-center space-x-2">
            <x-grid-list-toggle model="viewMode" />
            <x-button :href="route('admin.products.create')" variant="primary" icon="fa-solid fa-plus">
                {{ __('Tambah Produk Baru') }}
            </x-button>
        </div>
    </x-card>

    <!-- List View (Table) -->
    <div x-show="viewMode === 'list'" x-cloak>
        <x-card class="p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800 text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5">{{ __('Nama Produk') }}</th>
                            <th class="px-5 py-3.5">{{ __('Kategori & Satuan') }}</th>
                            <th class="px-5 py-3.5">{{ __('Harga Jual & Modal') }}</th>
                            <th class="px-5 py-3.5">{{ __('Status Galon') }}</th>
                            <th class="px-5 py-3.5">{{ __('Sisa Stok') }}</th>
                            <th class="px-5 py-3.5">{{ __('Status') }}</th>
                            <th class="px-5 py-3.5 text-right">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                        @forelse ($products as $p)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                <td class="px-5 py-3.5">
                                    <div class="font-bold text-slate-900 dark:text-white text-sm">{{ $p->name }}</div>
                                    <div class="text-[11px] text-slate-400 truncate max-w-[220px] mt-0.5">{{ $p->description }}</div>
                                </td>

                                <td class="px-5 py-3.5">
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $p->category?->name ?? __('Tanpa Kategori') }}</span>
                                    <div class="text-[10px] text-slate-400 capitalize mt-0.5">{{ __('Satuan') }}: {{ $p->unit }}</div>
                                </td>

                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="font-black text-cyan-600 dark:text-cyan-400 text-sm">
                                        Rp {{ number_format($p->price, 0, ',', '.') }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-medium mt-0.5">
                                        {{ __('Modal') }}: Rp {{ number_format($p->cost_price, 0, ',', '.') }}
                                    </div>
                                </td>

                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    @if ($p->is_returnable_gallon)
                                        <x-badge variant="info" size="sm">
                                            {{ __('Tukar Galon') }}
                                        </x-badge>
                                        <div class="text-[10px] text-slate-400 mt-1">+ Deposit: Rp {{ number_format($p->deposit_price, 0, ',', '.') }}</div>
                                    @else
                                        <span class="text-slate-400 text-[11px] font-medium">{{ __('Sekali Pakai') }}</span>
                                    @endif
                                </td>

                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    @if ($p->tracks_inventory)
                                        <div class="flex items-center space-x-2">
                                            <span class="font-black text-sm {{ $p->isLowStock() ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white' }}">
                                                {{ $p->stock_quantity }} {{ $p->unit }}
                                            </span>
                                            @if ($p->isLowStock())
                                                <x-badge variant="danger" size="sm">
                                                    {{ __('Menipis!') }}
                                                </x-badge>
                                            @endif
                                        </div>
                                        <button type="button" 
                                                @click="selectedProduct = {{ Js::from($p) }}; adjustModalOpen = true"
                                                class="text-[11px] font-bold text-cyan-600 dark:text-cyan-400 hover:underline mt-1 block cursor-pointer">
                                            + {{ __('Penyesuaian Stok') }}
                                        </button>
                                    @else
                                        <span class="text-slate-400 text-[11px]">{{ __('Tidak Dilacak') }}</span>
                                    @endif
                                </td>

                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <x-badge :variant="$p->is_active ? 'success' : 'neutral'" size="sm">
                                        {{ $p->is_active ? __('Aktif') : __('Nonaktif') }}
                                    </x-badge>
                                </td>

                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <x-button :href="route('admin.products.edit', $p)" variant="secondary" size="sm">
                                        {{ __('Edit') }}
                                    </x-button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                                    <x-empty-state 
                                        icon="fa-solid fa-bottle-water" 
                                        :title="__('Belum Ada Produk')" 
                                        :description="__('Mulai dengan menambahkan produk air galon atau aksesoris depot.')"
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
        @forelse ($products as $p)
            <x-card class="p-5 flex flex-col justify-between space-y-4 hover:shadow-md transition">
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h3 class="font-extrabold text-base text-slate-900 dark:text-white">{{ $p->name }}</h3>
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $p->category?->name ?? __('Tanpa Kategori') }}</span>
                        </div>
                        <x-badge :variant="$p->is_active ? 'success' : 'neutral'" size="sm">
                            {{ $p->is_active ? __('Aktif') : __('Nonaktif') }}
                        </x-badge>
                    </div>

                    @if ($p->description)
                        <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2">{{ $p->description }}</p>
                    @endif

                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-100 dark:border-slate-800 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-slate-500 dark:text-slate-400">{{ __('Harga Jual') }}</span>
                            <span class="font-black text-cyan-600 dark:text-cyan-400 text-base">Rp {{ number_format($p->price, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-slate-400">
                            <span>{{ __('Harga Modal') }}</span>
                            <span>Rp {{ number_format($p->cost_price, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs">
                        <div>
                            <span class="text-slate-400">{{ __('Tipe Galon') }}:</span>
                            @if ($p->is_returnable_gallon)
                                <x-badge variant="info" size="sm" class="ml-1">
                                    {{ __('Tukar Galon') }}
                                </x-badge>
                            @else
                                <span class="text-slate-500 dark:text-slate-400 font-medium ml-1">{{ __('Sekali Pakai') }}</span>
                            @endif
                        </div>
                        @if ($p->is_returnable_gallon)
                            <span class="text-[11px] text-slate-400">+ Dep: Rp {{ number_format($p->deposit_price, 0, ',', '.') }}</span>
                        @endif
                    </div>

                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/40 border border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div>
                            <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">{{ __('Stok Gudang') }}</div>
                            @if ($p->tracks_inventory)
                                <div class="font-black text-sm {{ $p->isLowStock() ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white' }}">
                                    {{ $p->stock_quantity }} {{ $p->unit }}
                                    @if ($p->isLowStock())
                                        <span class="text-[10px] font-bold text-rose-500 ml-1">({{ __('Menipis!') }})</span>
                                    @endif
                                </div>
                            @else
                                <div class="text-xs text-slate-400 italic">{{ __('Tidak Dilacak') }}</div>
                            @endif
                        </div>
                        @if ($p->tracks_inventory)
                            <button type="button" 
                                    @click="selectedProduct = {{ Js::from($p) }}; adjustModalOpen = true"
                                    class="text-xs font-bold text-cyan-600 dark:text-cyan-400 hover:underline cursor-pointer">
                                + {{ __('Penyesuaian') }}
                            </button>
                        @endif
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <form id="delete-product-{{ $p->id }}" method="POST" action="{{ route('admin.products.destroy', $p) }}">
                        @csrf
                        @method('DELETE')
                        <button type="button" 
                                @click="$dispatch('open-confirm-modal', {
                                    form: 'delete-product-{{ $p->id }}',
                                    title: '{{ __('Hapus Produk') }}',
                                    message: '{{ __('Apakah Anda yakin ingin menghapus produk') }} \'{{ addslashes($p->name) }}\'? {{ __('Tindakan ini bersifat permanen.') }}'
                                })"
                                class="text-xs font-bold text-rose-600 dark:text-rose-400 hover:underline p-1.5 cursor-pointer flex items-center">
                            <i class="fa-solid fa-trash mr-1.5"></i> {{ __('Hapus') }}
                        </button>
                    </form>

                    <x-button :href="route('admin.products.edit', $p)" variant="secondary" size="sm" icon="fa-solid fa-pen-to-square">
                        {{ __('Edit Produk') }}
                    </x-button>
                </div>
            </x-card>
        @empty
            <div class="col-span-full">
                <x-card class="p-12 text-center">
                    <x-empty-state 
                        icon="fa-solid fa-bottle-water" 
                        :title="__('Belum Ada Produk')" 
                        :description="__('Mulai dengan menambahkan produk air galon atau aksesoris depot.')"
                    />
                </x-card>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800">
        {{ $products->links() }}
    </div>

    <!-- Modal Penyesuaian Stok Gudang -->
    <div x-show="adjustModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-slate-100 dark:border-slate-800" @click.away="adjustModalOpen = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="font-black text-base text-slate-900 dark:text-white tracking-tight">{{ __('Penyesuaian Stok Gudang') }}</h3>
                <button type="button" @click="adjustModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <template x-if="selectedProduct">
                <form method="POST" :action="'/admin/products/' + selectedProduct.id + '/adjust-stock'" class="space-y-4">
                    @csrf

                    <div class="p-3 bg-slate-50 dark:bg-slate-950/60 rounded-xl border border-slate-100 dark:border-slate-800">
                        <span class="text-xs text-slate-400">{{ __('Produk') }}:</span>
                        <div class="font-bold text-sm text-slate-900 dark:text-white" x-text="selectedProduct.name"></div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            {{ __('Stok Saat Ini') }}: <span class="font-bold text-cyan-600 dark:text-cyan-400" x-text="selectedProduct.stock_quantity + ' ' + selectedProduct.unit"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">{{ __('Jenis Penyesuaian') }}</label>
                        <select name="type" required class="w-full text-xs border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500">
                            <option value="in">{{ __('Tambah Stok Masuk (Restock Gudang)') }}</option>
                            <option value="out">{{ __('Kurangi Stok (Kerusakan / Bocor / Hilang)') }}</option>
                            <option value="adjustment">{{ __('Koreksi Hitung Ulang (Opname Stok)') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">{{ __('Jumlah Unit') }}</label>
                        <input type="number" name="quantity" required min="1" value="10"
                               class="w-full text-xs font-bold border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">{{ __('Alasan / Catatan Penyesuaian') }}</label>
                        <input type="text" name="notes" required placeholder="{{ __('Contoh: Pengiriman supplier 50 galon baru...') }}"
                               class="w-full text-xs border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500">
                    </div>

                    <div class="pt-3 flex justify-end space-x-2">
                        <x-button type="button" variant="ghost" size="sm" @click="adjustModalOpen = false">
                            {{ __('Batal') }}
                        </x-button>
                        <x-button type="submit" variant="primary" size="sm">
                            {{ __('Simpan Perubahan Stok') }}
                        </x-button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
@endsection
