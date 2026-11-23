@extends('layouts.app')

@section('title', 'Dashboard Pelanggan')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    <!-- Welcome & Profile summary -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <span class="text-xs font-bold text-cyan-600 uppercase tracking-wider">Selamat Datang Kembali</span>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ $user->name }}</h1>
            <p class="text-sm text-slate-500 mt-1">
                Tipe Akun: <span class="font-semibold text-slate-700 capitalize">{{ str_replace('_', ' ', $user->profile?->customer_type ?? 'Household') }}</span> &bull; Telp: {{ $user->phone ?? '-' }}
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('customer.orders.create') }}" class="inline-flex items-center px-5 py-2.5 rounded-xl font-bold text-white bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 shadow-sm transition">
                <i class="fa-solid fa-cart-plus mr-2"></i> Pesan Air Sekarang
            </a>
            <a href="{{ route('customer.addresses.index') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl font-semibold text-slate-700 bg-slate-50 hover:bg-slate-100 border border-slate-200 transition text-sm">
                <i class="fa-solid fa-location-dot mr-1.5 text-cyan-600"></i> Alamat Saya
            </a>
        </div>
    </div>

    <!-- Active Delivery Banner (If there is an active order) -->
    @if ($activeOrder)
        <div class="bg-gradient-to-r from-cyan-600 via-blue-600 to-indigo-700 text-white rounded-2xl p-6 shadow-md relative overflow-hidden">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2">
                    <div class="inline-flex items-center px-3 py-1 rounded-full bg-white/20 text-white text-xs font-bold backdrop-blur-xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 mr-2 animate-ping"></span>
                        Pesanan Sedang Aktif: {{ $activeOrder->order_number }}
                    </div>
                    <h3 class="text-xl font-extrabold tracking-tight">
                        Status: {{ $activeOrder->status_badge['label'] }}
                    </h3>
                    <p class="text-cyan-100 text-xs sm:text-sm max-w-xl">
                        Tujuan pengantaran: <span class="font-medium text-white">{{ $activeOrder->delivery_address_text }}</span>
                    </p>
                    @if ($activeOrder->delivery && $activeOrder->delivery->driver)
                        <div class="text-xs text-white/90 pt-1 flex items-center">
                            <i class="fa-solid fa-motorcycle mr-1.5 text-emerald-300"></i> Kurir: <span class="font-bold ml-1">{{ $activeOrder->delivery->driver->name }}</span>
                            @if ($activeOrder->delivery->driver->phone)
                                &bull; <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $activeOrder->delivery->driver->phone) }}" target="_blank" class="ml-1 underline hover:text-emerald-300">Hubungi Kurir</a>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    @if ($activeOrder->delivery)
                        <a href="{{ route('customer.orders.track', $activeOrder) }}" 
                           class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold text-sm shadow-md transition transform hover:scale-105">
                            <i class="fa-solid fa-map-location-dot mr-2"></i> Lacak Kurir Live di Peta
                        </a>
                    @endif
                    <a href="{{ route('customer.orders.show', $activeOrder) }}" 
                       class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-sm backdrop-blur-xs transition">
                        Detail Pesanan
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-cyan-100 text-cyan-700 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-bottle-water"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-500">Saldo Galon Dititipkan</span>
                <div class="text-2xl font-black text-slate-900">{{ $user->profile?->gallon_deposit_balance ?? 0 }} <span class="text-xs font-normal text-slate-400">galon</span></div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-500">Total Transaksi Selesai</span>
                <div class="text-2xl font-black text-slate-900">{{ $completedOrdersCount }} <span class="text-xs font-normal text-slate-400">pesanan</span></div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-map-location-dot"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-500">Alamat Tersimpan</span>
                <div class="text-2xl font-black text-slate-900">{{ $user->addresses->count() }} <span class="text-xs font-normal text-slate-400">lokasi</span></div>
            </div>
        </div>
    </div>

    <!-- Recent Orders Section -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-extrabold text-slate-900 text-lg">Pesanan Terakhir</h2>
            <a href="{{ route('customer.orders.index') }}" class="text-xs font-bold text-cyan-600 hover:text-cyan-700">
                Lihat Semua Pesanan &rarr;
            </a>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse ($recentOrders as $order)
                <div class="p-6 hover:bg-slate-50/60 transition flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center space-x-2">
                            <span class="font-bold text-sm text-slate-900">{{ $order->order_number }}</span>
                            <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full border {{ $order->status_badge['color'] }}">
                                {{ $order->status_badge['label'] }}
                            </span>
                            <span class="text-[11px] font-bold px-2 py-0.5 rounded-full border {{ $order->payment_badge['color'] }}">
                                {{ $order->payment_badge['label'] }}
                            </span>
                        </div>
                        <div class="text-xs text-slate-500">
                            {{ $order->created_at->format('d M Y, H:i') }} WIB &bull; 
                            {{ $order->items->pluck('product_name')->join(', ') }} ({{ $order->items->sum('quantity') }} item)
                        </div>
                        <div class="text-xs font-semibold text-cyan-700">
                            Total: Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        @if ($order->delivery && in_array($order->delivery->status, ['assigned', 'in_transit', 'arrived']))
                            <a href="{{ route('customer.orders.track', $order) }}" class="px-3.5 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition">
                                <i class="fa-solid fa-location-crosshairs mr-1"></i> Lacak
                            </a>
                        @endif
                        <a href="{{ route('customer.orders.show', $order) }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                            Detail
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-400 text-sm">
                    Anda belum pernah membuat pesanan. <a href="{{ route('customer.orders.create') }}" class="text-cyan-600 font-bold underline ml-1">Pesan sekarang!</a>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
