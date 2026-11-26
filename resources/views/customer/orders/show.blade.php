@extends('layouts.customer')

@section('title', __('Detail Pesanan #') . $order->order_number)

@section('breadcrumbs')
    <x-breadcrumbs :items="[
        ['title' => __('Riwayat Pesanan'), 'url' => route('customer.orders.index')],
        ['title' => '#' . $order->order_number]
    ]" />
@endsection

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- Header bar -->
    <x-card class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 flex-wrap gap-y-1">
                <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight font-mono">{{ __('Pesanan') }} {{ $order->order_number }}</h1>
                <x-badge variant="neutral" class="{{ $order->status_badge['color'] }}">
                    {{ __($order->status_badge['label']) }}
                </x-badge>
                <x-badge variant="neutral" class="{{ $order->payment_badge['color'] }}">
                    {{ __($order->payment_badge['label']) }}
                </x-badge>
                <x-badge variant="neutral" class="{{ $order->payment_method_badge['color'] }}">
                    <i class="fa-solid {{ $order->payment_method_badge['icon'] }} mr-1"></i>
                    {{ __($order->payment_method_badge['label']) }}
                </x-badge>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Dibuat pada') }} {{ $order->created_at->format('d M Y, H:i') }} WIB</p>
        </div>

        <div class="flex items-center space-x-2">
            @if ($order->delivery && in_array($order->delivery->status, ['assigned', 'in_transit', 'arrived']))
                <x-button :href="route('customer.orders.track', $order)" variant="success" size="sm" icon="fa-solid fa-map-location-dot">
                    {{ __('Lacak Pengantaran Live') }}
                </x-button>
            @endif

            <x-button :href="route('customer.orders.index')" variant="secondary" size="sm" icon="fa-solid fa-arrow-left">
                {{ __('Kembali') }}
            </x-button>
        </div>
    </x-card>

    <!-- COD Notice Banner -->
    <div class="bg-gradient-to-r from-emerald-50 via-teal-50 to-cyan-50 dark:from-emerald-950/40 dark:via-teal-950/40 dark:to-cyan-950/40 border border-emerald-300 dark:border-emerald-700 rounded-3xl p-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-start space-x-3.5">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-sm text-2xl">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <h2 class="text-sm sm:text-base font-black text-slate-900 dark:text-white">{{ __('Pesanan Tunai Siap Diproses') }}</h2>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-700">{{ __('Bayar di Tempat') }}</span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                    {{ __('Pesanan Anda telah tercatat! Pesanan akan segera disiapkan dan diantar ke alamat Anda.') }}
                </p>
            </div>
        </div>
    </div>

    <!-- Delivery & Status Card -->
    @if ($order->delivery)
        <x-card class="p-6 space-y-4">
            <h2 class="font-black text-sm text-slate-900 dark:text-white uppercase tracking-wider flex items-center">
                <i class="fa-solid fa-truck text-cyan-600 dark:text-cyan-400 mr-2"></i> {{ __('Status Pengantaran') }}
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 dark:bg-slate-950/60 p-4 rounded-2xl text-xs border border-slate-100 dark:border-slate-800">
                <div>
                    <span class="text-slate-400 dark:text-slate-500 block">{{ __('No. Pengantaran') }}:</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200 text-sm font-mono">{{ $order->delivery->delivery_number }}</span>
                </div>
                <div>
                    <span class="text-slate-400 dark:text-slate-500 block">{{ __('Status Pengiriman') }}:</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200 text-sm">{{ __($order->delivery->status_badge['label'] ?? $order->delivery->status) }}</span>
                </div>
            </div>

            <!-- Delivery Proof Photo if uploaded -->
            @if ($order->delivery->photos->isNotEmpty())
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                    <h3 class="text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">{{ __('Foto Bukti Serah Terima') }}:</h3>
                    <div class="flex flex-wrap gap-3">
                        @foreach ($order->delivery->photos as $photo)
                            <div class="border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-2xs">
                                <img src="/storage/{{ $photo->photo_path }}" alt="Bukti Serah Terima" class="h-36 w-36 object-cover hover:scale-105 transition">
                                <div class="p-1.5 text-[10px] text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-950 text-center">
                                    {{ $photo->created_at->format('H:i, d M Y') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </x-card>
    @endif

    <!-- Order Items Breakdown Card -->
    <x-card class="p-6 space-y-4">
        <h2 class="font-black text-sm text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Barang Dipesan') }}</h2>

        <div class="divide-y divide-slate-100 dark:divide-slate-800">
            @foreach ($order->items as $item)
                <div class="py-3 flex justify-between items-center text-sm">
                    <div>
                        <div class="font-bold text-slate-900 dark:text-white">{{ $item->product_name }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">
                            {{ $item->quantity }} {{ $item->unit }} &times; Rp {{ number_format($item->price, 0, ',', '.') }}
                            @if ($item->is_exchange)
                                <span class="text-blue-600 dark:text-blue-400 font-semibold ml-1">({{ __('Tukar Galon') }})</span>
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
                <span>{{ __('Subtotal Produk') }}:</span>
                <span class="font-bold text-slate-800 dark:text-slate-200">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-slate-600 dark:text-slate-400">
                <span>{{ __('Ongkos Kirim') }}:</span>
                <span class="font-bold text-slate-800 dark:text-slate-200">Rp {{ number_format($order->delivery_fee, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-base font-black text-slate-900 dark:text-white pt-2 border-t border-slate-100 dark:border-slate-800">
                <span>{{ __('Total Tagihan') }}:</span>
                <span class="text-cyan-600 dark:text-cyan-400">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
            </div>
        </div>
    </x-card>

    <!-- Delivery Address & Notes -->
    <x-card class="p-6 space-y-3">
        <h2 class="font-black text-sm text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Alamat Pengantaran') }}</h2>

        <div class="text-xs text-slate-700 dark:text-slate-300 space-y-1.5">
            <div><span class="text-slate-400 dark:text-slate-500">{{ __('Penerima') }}:</span> <span class="font-bold text-slate-900 dark:text-white">{{ $order->recipient_name }} ({{ $order->recipient_phone }})</span></div>
            <div><span class="text-slate-400 dark:text-slate-500">{{ __('Alamat') }}:</span> {{ $order->delivery_address_text }}</div>
            @if ($order->delivery_benchmark)
                <div><span class="text-slate-400 dark:text-slate-500">{{ __('Patokan') }}:</span> {{ $order->delivery_benchmark }}</div>
            @endif
            @if ($order->customer_notes)
                <div class="p-3 bg-slate-50 dark:bg-slate-950/60 border border-slate-100 dark:border-slate-800 rounded-xl text-slate-800 dark:text-slate-200 italic mt-2">
                    {{ __('Catatan Khusus') }}: "{{ $order->customer_notes }}"
                </div>
            @endif
        </div>
    </x-card>

</div>
@endsection
