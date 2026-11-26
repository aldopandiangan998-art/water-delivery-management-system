@extends('layouts.admin')

@section('page_title', __('Kelola Pesanan Air Minum'))

@section('content')
<div class="space-y-6" 
     x-data="{ viewMode: localStorage.getItem('view_mode_admin_orders') || 'list' }"
     x-effect="localStorage.setItem('view_mode_admin_orders', viewMode)">

    <!-- Header & Filter Bar -->
    <x-card class="p-5 sm:p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Daftar Seluruh Pesanan') }}</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Kelola konfirmasi, pengantaran, dan status pembayaran pesanan pelanggan.') }}</p>
            </div>
            <div class="flex items-center space-x-2 text-xs">
                <x-grid-list-toggle model="viewMode" />
                <x-button :href="route('admin.deliveries.map')" variant="secondary" size="sm" icon="fa-solid fa-map-location-dot">
                    {{ __('Buka Peta Pengantaran') }}
                </x-button>
            </div>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('admin.orders.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs pt-3 border-t border-slate-100 dark:border-slate-800">
            <div>
                <x-input 
                    :label="__('Cari No. Order / Pelanggan')" 
                    name="search" 
                    icon="fa-solid fa-magnifying-glass"
                    placeholder="ORD-2026... atau Nama" 
                    :value="request('search')" 
                />
            </div>

            <div>
                @php
                    $statusOptions = [
                        '' => __('Semua Status'),
                        'pending' => __('Menunggu Konfirmasi'),
                        'confirmed' => __('Terkonfirmasi'),
                        'processing' => __('Diproses'),
                        'ready_for_delivery' => __('Siap / Sedang Dikirim'),
                        'completed' => __('Selesai'),
                        'cancelled' => __('Dibatalkan'),
                    ];
                @endphp
                <x-select 
                    :label="__('Status Pesanan')" 
                    name="status" 
                    :options="$statusOptions" 
                    :selected="request('status')" 
                />
            </div>

            <div>
                @php
                    $paymentOptions = [
                        '' => __('Semua Pembayaran'),
                        'unpaid' => __('Belum Lunas'),
                        'paid' => __('Lunas'),
                    ];
                @endphp
                <x-select 
                    :label="__('Status Pembayaran')" 
                    name="payment_status" 
                    :options="$paymentOptions" 
                    :selected="request('payment_status')" 
                />
            </div>

            <div class="flex items-end space-x-2 pt-1">
                <x-button type="submit" variant="primary" class="w-full" icon="fa-solid fa-filter">
                    {{ __('Filter Data') }}
                </x-button>
                <x-button :href="route('admin.orders.index')" variant="secondary" class="shrink-0" title="{{ __('Reset Filter') }}">
                    <i class="fa-solid fa-rotate-left"></i>
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
                            <th class="px-5 py-3.5">{{ __('No. Pesanan & Waktu') }}</th>
                            <th class="px-5 py-3.5">{{ __('Penerima & Alamat') }}</th>
                            <th class="px-5 py-3.5">{{ __('Rincian Barang') }}</th>
                            <th class="px-5 py-3.5">{{ __('Total & Bayar') }}</th>
                            <th class="px-5 py-3.5">{{ __('Status Pesanan') }}</th>
                            <th class="px-5 py-3.5">{{ __('Pengantaran') }}</th>
                            <th class="px-5 py-3.5 text-right">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                        @forelse ($orders as $order)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                <td class="px-5 py-3.5 font-bold text-slate-900 dark:text-white whitespace-nowrap">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="hover:text-cyan-600 dark:hover:text-cyan-400 font-mono text-sm transition">
                                        {{ $order->order_number }}
                                    </a>
                                    <div class="text-[10px] text-slate-400 font-normal mt-0.5">
                                        {{ $order->created_at->format('d M Y, H:i') }} WIB
                                    </div>
                                </td>

                                <td class="px-5 py-3.5">
                                    <div class="font-bold text-slate-800 dark:text-slate-200">{{ $order->recipient_name }}</div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate max-w-[200px]">{{ $order->delivery_address_text }}</div>
                                    <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold">{{ $order->recipient_phone }}</div>
                                </td>

                                <td class="px-5 py-3.5">
                                    @foreach ($order->items as $it)
                                        <div class="text-slate-800 dark:text-slate-200 font-medium">
                                            {{ $it->quantity }}x {{ $it->product_name }}
                                            @if ($it->is_exchange)
                                                <span class="text-blue-600 dark:text-blue-400 text-[10px] font-semibold">({{ __('Tukar Galon') }})</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </td>

                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="font-black text-slate-900 dark:text-white text-sm">
                                        Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                    </div>
                                    <div class="flex items-center space-x-1.5 mt-1 flex-wrap gap-y-1">
                                        <x-badge variant="neutral" size="sm" class="{{ $order->payment_badge['color'] }}">
                                            {{ __($order->payment_badge['label']) }}
                                        </x-badge>
                                        <x-badge variant="neutral" size="sm" class="{{ $order->payment_method_badge['color'] }}">
                                            <i class="fa-solid {{ $order->payment_method_badge['icon'] }} mr-0.5 text-[9px]"></i>
                                            {{ __($order->payment_method_badge['label']) }}
                                        </x-badge>
                                    </div>
                                </td>

                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <x-badge variant="neutral" class="{{ $order->status_badge['color'] }}">
                                        {{ __($order->status_badge['label']) }}
                                    </x-badge>
                                </td>

                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    @if ($order->delivery)
                                        <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $order->delivery->delivery_number }}</div>
                                        <div class="text-[10px] text-slate-500 dark:text-slate-400 capitalize">{{ __($order->delivery->status_badge['label'] ?? $order->delivery->status) }}</div>
                                    @else
                                        <span class="text-xs text-slate-400 italic">{{ __('Belum dibuat') }}</span>
                                    @endif
                                </td>

                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <x-button :href="route('admin.orders.show', $order)" variant="secondary" size="sm" iconRight="fa-solid fa-arrow-right">
                                        {{ __('Kelola') }}
                                    </x-button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                                    <x-empty-state 
                                        icon="fa-regular fa-clipboard" 
                                        :title="__('Tidak Ada Data Pesanan')" 
                                        :description="__('Tidak ditemukan pesanan dengan kriteria filter saat ini.')"
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
        @forelse ($orders as $order)
            <x-card class="p-5 flex flex-col justify-between space-y-4 hover:shadow-md transition">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <a href="{{ route('admin.orders.show', $order) }}" class="font-mono text-sm font-bold text-cyan-600 dark:text-cyan-400 hover:underline">
                            {{ $order->order_number }}
                        </a>
                        <x-badge variant="neutral" size="sm" class="{{ $order->status_badge['color'] }}">
                            {{ __($order->status_badge['label']) }}
                        </x-badge>
                    </div>

                    <div class="text-[11px] text-slate-400">
                        <i class="fa-regular fa-clock mr-1"></i>{{ $order->created_at->format('d M Y, H:i') }} WIB
                    </div>

                    <div>
                        <div class="font-extrabold text-sm text-slate-900 dark:text-white">{{ $order->recipient_name }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 mt-0.5">{{ $order->delivery_address_text }}</div>
                        <div class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold mt-0.5">{{ $order->recipient_phone }}</div>
                    </div>

                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-100 dark:border-slate-800 space-y-1 text-xs">
                        @foreach ($order->items as $it)
                            <div class="text-slate-700 dark:text-slate-300 font-medium flex justify-between">
                                <span>{{ $it->quantity }}x {{ $it->product_name }}</span>
                                @if ($it->is_exchange)
                                    <span class="text-blue-600 dark:text-blue-400 text-[10px] font-semibold">({{ __('Tukar Galon') }})</span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <div>
                            <div class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">{{ __('Total Tagihan') }}</div>
                            <div class="font-black text-slate-900 dark:text-white text-base">
                                Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            <x-badge variant="neutral" size="sm" class="{{ $order->payment_badge['color'] }}">
                                {{ __($order->payment_badge['label']) }}
                            </x-badge>
                            <x-badge variant="neutral" size="sm" class="{{ $order->payment_method_badge['color'] }}">
                                <i class="fa-solid {{ $order->payment_method_badge['icon'] }} mr-0.5 text-[9px]"></i>
                                {{ __($order->payment_method_badge['label']) }}
                            </x-badge>
                        </div>
                    </div>

                    @if ($order->delivery)
                        <div class="text-xs bg-cyan-50/60 dark:bg-cyan-950/20 p-2 rounded-lg border border-cyan-100 dark:border-cyan-900/40 flex items-center justify-between">
                            <span class="font-semibold text-slate-700 dark:text-slate-300"><i class="fa-solid fa-truck text-cyan-500 mr-1.5"></i>{{ $order->delivery->delivery_number }}</span>
                            <span class="text-[10px] font-bold text-cyan-600 dark:text-cyan-400 capitalize">{{ __($order->delivery->status_badge['label'] ?? $order->delivery->status) }}</span>
                        </div>
                    @endif
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                    <x-button :href="route('admin.orders.show', $order)" variant="secondary" size="sm" iconRight="fa-solid fa-arrow-right">
                        {{ __('Kelola Pesanan') }}
                    </x-button>
                </div>
            </x-card>
        @empty
            <div class="col-span-full">
                <x-card class="p-12 text-center">
                    <x-empty-state 
                        icon="fa-regular fa-clipboard" 
                        :title="__('Tidak Ada Data Pesanan')" 
                        :description="__('Tidak ditemukan pesanan dengan kriteria filter saat ini.')"
                    />
                </x-card>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800">
        {{ $orders->links() }}
    </div>

</div>
@endsection
