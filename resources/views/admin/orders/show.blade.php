@extends('layouts.admin')

@section('page_title', __('Rincian Pesanan #') . $order->order_number)

@section('content')
<div class="space-y-6">

    <!-- Top Action & Info Bar -->
    <x-card class="p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2.5 flex-wrap gap-y-2">
                <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight font-mono">{{ __('Pesanan') }} #{{ $order->order_number }}</h1>
                <x-badge variant="neutral" class="{{ $order->status_badge['color'] }}">
                    {{ __($order->status_badge['label']) }}
                </x-badge>
                <x-badge variant="neutral" class="{{ $order->payment_badge['color'] }}">
                    {{ __($order->payment_badge['label']) }}
                </x-badge>
            </div>
            <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center space-x-2 flex-wrap gap-y-1">
                <span>{{ __('Dipesan pada') }} {{ $order->created_at->format('d M Y, H:i') }} WIB</span>
                <span>&bull;</span>
                <span>{{ __('Metode') }}:</span>
                <x-badge variant="neutral" size="sm" class="{{ $order->payment_method_badge['color'] }}">
                    <i class="fa-solid {{ $order->payment_method_badge['icon'] }} mr-1"></i>
                    {{ __($order->payment_method_badge['label']) }}
                </x-badge>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            <x-button :href="route('admin.orders.index')" variant="secondary" size="sm" icon="fa-solid fa-arrow-left">
                {{ __('Kembali') }}
            </x-button>
        </div>
    </x-card>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left col: Items, Customer, Delivery (8 cols) -->
        <div class="lg:col-span-8 space-y-6">

            <!-- Products Ordered -->
            <x-card class="p-6 space-y-4">
                <h2 class="font-black text-sm text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Rincian Barang Dipesan') }}</h2>

                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($order->items as $item)
                        <div class="py-3 flex justify-between items-center text-sm">
                            <div>
                                <div class="font-bold text-slate-900 dark:text-white">{{ $item->product_name }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ $item->quantity }} {{ $item->unit }} &times; Rp {{ number_format($item->price, 0, ',', '.') }}
                                    @if ($item->is_exchange)
                                        <span class="text-blue-600 dark:text-blue-400 font-semibold ml-1">({{ __('Tukar Galon Kosong') }})</span>
                                    @endif
                                </div>
                            </div>
                            <div class="font-bold text-slate-900 dark:text-white">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="pt-3 border-t border-slate-200 dark:border-slate-800 space-y-1.5 text-xs">
                    <div class="flex justify-between text-slate-600 dark:text-slate-400">
                        <span>{{ __('Subtotal Barang') }}:</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600 dark:text-slate-400">
                        <span>{{ __('Ongkos Kirim') }}:</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Rp {{ number_format($order->delivery_fee, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-base font-black text-slate-900 dark:text-white pt-2 border-t border-slate-100 dark:border-slate-800">
                        <span>{{ __('Total Tagihan') }}:</span>
                        <span class="text-cyan-600 dark:text-cyan-400">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </x-card>

            <!-- Customer & Destination Card -->
            <x-card class="p-6 space-y-3">
                <h2 class="font-black text-sm text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Tujuan Pengantaran & Kontak') }}</h2>

                <div class="text-xs text-slate-700 dark:text-slate-300 space-y-2">
                    <div><span class="text-slate-400 dark:text-slate-500">{{ __('Penerima') }}:</span> <span class="font-bold text-slate-900 dark:text-white">{{ $order->recipient_name }}</span></div>
                    <div><span class="text-slate-400 dark:text-slate-500">{{ __('Nomor Telepon / WA') }}:</span> <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $order->recipient_phone }}</span></div>
                    <div><span class="text-slate-400 dark:text-slate-500">{{ __('Alamat Lengkap') }}:</span> {{ $order->delivery_address_text }}</div>
                    @if ($order->delivery_benchmark)
                        <div><span class="text-slate-400 dark:text-slate-500">{{ __('Patokan Lokasi') }}:</span> {{ $order->delivery_benchmark }}</div>
                    @endif
                    @if ($order->customer_notes)
                        <div class="p-3 bg-slate-50 dark:bg-slate-950/60 border border-slate-100 dark:border-slate-800 rounded-xl text-slate-800 dark:text-slate-200 italic">
                            {{ __('Catatan Pelanggan') }}: "{{ $order->customer_notes }}"
                        </div>
                    @endif
                </div>
            </x-card>

            <!-- Delivery Proof Photos if completed -->
            @if ($order->delivery && $order->delivery->photos->isNotEmpty())
                <x-card class="p-6 space-y-3">
                    <h2 class="font-black text-sm text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Foto Bukti Serah Terima Kurir') }}</h2>
                    <div class="flex flex-wrap gap-4">
                        @foreach ($order->delivery->photos as $ph)
                            <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-2xs">
                                <img src="/storage/{{ $ph->photo_path }}" alt="Bukti Serah Terima" class="h-44 w-44 object-cover hover:scale-105 transition">
                                <div class="p-2 text-[10px] text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-950 text-center">
                                    {{ $ph->created_at->format('H:i, d M Y') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            @endif

            <!-- Order Status Audit Timeline -->
            <x-card class="p-6 space-y-4">
                <h2 class="font-black text-sm text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Riwayat Status (Audit Trail)') }}</h2>

                <div class="space-y-3">
                    @foreach ($order->statusHistories as $hist)
                        <div class="flex items-start space-x-3 text-xs">
                            <div class="w-6 h-6 rounded-full bg-cyan-100 dark:bg-cyan-950 text-cyan-600 dark:text-cyan-400 flex items-center justify-center font-bold text-[10px] mt-0.5 shrink-0">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div>
                                <div class="font-bold text-slate-800 dark:text-slate-200 capitalize">{{ $hist->status }}</div>
                                <div class="text-slate-500 dark:text-slate-400">{{ $hist->notes }}</div>
                                <div class="text-[10px] text-slate-400">{{ $hist->created_at->format('d M Y, H:i:s') }} WIB &bull; {{ __('Oleh') }}: {{ $hist->changer?->name ?? __('Sistem') }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>

        </div>

        <!-- Right col: Control Panel (Status, Delivery, Payment) (4 cols) -->
        <div class="lg:col-span-4 space-y-6">

            <!-- 1. Change Status Control -->
            <x-card class="p-5 space-y-4">
                <h3 class="font-black text-sm text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Ubah Status Pesanan') }}</h3>

                <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="space-y-3">
                    @csrf
                    @method('PUT')

                    <div>
                        @php
                            $statusChangeOptions = [
                                'pending' => __('Menunggu Konfirmasi'),
                                'confirmed' => __('Konfirmasi (Confirmed)'),
                                'processing' => __('Sedang Disiapkan (Processing)'),
                                'ready_for_delivery' => __('Siap Dikirim'),
                                'completed' => __('Selesai (Completed)'),
                                'cancelled' => __('Batalkan (Cancelled)'),
                                'rejected' => __('Tolak (Rejected)'),
                            ];
                        @endphp
                        <x-select 
                            name="status" 
                            :options="$statusChangeOptions" 
                            :selected="$order->status" 
                        />
                    </div>

                    <div>
                        <x-input 
                            name="notes" 
                            placeholder="{{ __('Catatan perubahan status...') }}" 
                        />
                    </div>

                    <x-button type="submit" variant="primary" class="w-full">
                        {{ __('Perbarui Status') }}
                    </x-button>
                </form>
            </x-card>

            <!-- 2. Delivery Control -->
            @if ($order->delivery)
                <x-card class="p-5 space-y-3">
                    <h3 class="font-black text-sm text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Pengantaran Pesanan') }}</h3>

                    <div class="p-3.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-2xl text-xs space-y-2">
                        <div>
                            <div class="font-bold text-emerald-900 dark:text-emerald-300">{{ __('No. Pengantaran') }}:</div>
                            <div class="text-sm font-black text-slate-800 dark:text-slate-100 font-mono">{{ $order->delivery->delivery_number }}</div>
                            <div class="text-slate-500 dark:text-slate-400 mt-1">{{ __('Status') }}: <span class="capitalize font-semibold text-emerald-700 dark:text-emerald-400">{{ __($order->delivery->status_badge['label'] ?? $order->delivery->status) }}</span></div>
                        </div>

                        <x-button :href="route('admin.deliveries.show', $order->delivery)" variant="success" size="sm" class="w-full" icon="fa-solid fa-truck-fast">
                            {{ __('Buka Kontrol Pengantaran & Bukti') }}
                        </x-button>
                    </div>
                </x-card>
            @endif

            <!-- 3. Payment Status Control -->
            <x-card class="p-5 space-y-3">
                <h3 class="font-black text-sm text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Status Pembayaran') }}</h3>

                <form method="POST" action="{{ route('admin.orders.payment', $order) }}" class="space-y-3">
                    @csrf
                    @method('PUT')

                    <div>
                        @php
                            $paymentChangeOptions = [
                                'unpaid' => __('Belum Lunas'),
                                'partially_paid' => __('Bayar Sebagian'),
                                'paid' => __('Lunas (Paid)'),
                                'refunded' => __('Refund'),
                            ];
                        @endphp
                        <x-select 
                            name="payment_status" 
                            :options="$paymentChangeOptions" 
                            :selected="$order->payment_status" 
                        />
                    </div>

                    <div>
                        <x-input 
                            :label="__('Nominal Diterima (Rp)')" 
                            type="number" 
                            name="amount_paid" 
                            :value="$order->total_amount" 
                        />
                    </div>

                    <x-button type="submit" variant="success" class="w-full" icon="fa-solid fa-check">
                        {{ __('Simpan Pembayaran') }}
                    </x-button>
                </form>
            </x-card>

        </div>

    </div>

</div>
@endsection
