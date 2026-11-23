@extends('layouts.app')

@section('title', 'Riwayat Pesanan Saya')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Riwayat Pesanan</h1>
            <p class="text-sm text-slate-500">Daftar seluruh transaksi pemesanan air minum Anda.</p>
        </div>
        <a href="{{ route('customer.orders.create') }}" class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-bold text-white bg-cyan-600 hover:bg-cyan-700 shadow-xs transition">
            <i class="fa-solid fa-cart-plus mr-2"></i> Buat Pesanan Baru
        </a>
    </div>

    <!-- Filter status -->
    <div class="flex flex-wrap gap-2 text-xs">
        <a href="{{ route('customer.orders.index') }}" 
           class="px-3.5 py-1.5 rounded-xl font-bold transition {{ !request('status') ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            Semua
        </a>
        <a href="{{ route('customer.orders.index', ['status' => 'pending']) }}" 
           class="px-3.5 py-1.5 rounded-xl font-bold transition {{ request('status') === 'pending' ? 'bg-amber-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            Menunggu
        </a>
        <a href="{{ route('customer.orders.index', ['status' => 'ready_for_delivery']) }}" 
           class="px-3.5 py-1.5 rounded-xl font-bold transition {{ request('status') === 'ready_for_delivery' ? 'bg-cyan-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            Dikirim
        </a>
        <a href="{{ route('customer.orders.index', ['status' => 'completed']) }}" 
           class="px-3.5 py-1.5 rounded-xl font-bold transition {{ request('status') === 'completed' ? 'bg-emerald-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            Selesai
        </a>
        <a href="{{ route('customer.orders.index', ['status' => 'cancelled']) }}" 
           class="px-3.5 py-1.5 rounded-xl font-bold transition {{ request('status') === 'cancelled' ? 'bg-rose-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            Dibatalkan
        </a>
    </div>

    <!-- Orders List -->
    <div class="space-y-4">
        @forelse ($orders as $order)
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs hover:shadow-sm transition flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-2">
                    <div class="flex items-center space-x-2">
                        <span class="font-extrabold text-base text-slate-900">{{ $order->order_number }}</span>
                        <span class="text-xs font-bold px-2.5 py-0.5 rounded-full border {{ $order->status_badge['color'] }}">
                            {{ $order->status_badge['label'] }}
                        </span>
                        <span class="text-xs font-bold px-2 py-0.5 rounded-full border {{ $order->payment_badge['color'] }}">
                            {{ $order->payment_badge['label'] }}
                        </span>
                    </div>

                    <div class="text-xs text-slate-500">
                        {{ $order->created_at->format('d M Y, H:i') }} WIB &bull; 
                        Tujuan: <span class="text-slate-700 font-medium">{{ $order->recipient_name }}</span>
                    </div>

                    <!-- Items brief -->
                    <div class="text-xs text-slate-700 font-medium">
                        @foreach ($order->items as $item)
                            <span class="bg-slate-50 border border-slate-200 px-2 py-0.5 rounded-md mr-1 inline-block my-0.5">
                                {{ $item->quantity }}x {{ $item->product_name }}
                            </span>
                        @endforeach
                    </div>

                    <div class="text-xs font-bold text-cyan-700">
                        Total Pembayaran: Rp {{ number_format($order->total_amount, 0, ',', '.') }} ({{ strtoupper($order->payment_method) }})
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    @if (in_array($order->status, ['pending', 'confirmed']))
                        <a href="{{ $order->whatsapp_url }}" target="_blank"
                           class="px-3 py-2 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition flex items-center">
                            <i class="fa-brands fa-whatsapp mr-1 text-sm text-emerald-600"></i> Kirim WA
                        </a>
                    @endif

                    @if ($order->delivery && in_array($order->delivery->status, ['assigned', 'in_transit', 'arrived']))
                        <a href="{{ route('customer.orders.track', $order) }}" 
                           class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-700 shadow-xs transition flex items-center">
                            <i class="fa-solid fa-map-location-dot mr-1.5"></i> Lacak Kurir
                        </a>
                    @endif

                    <a href="{{ route('customer.orders.show', $order) }}" 
                       class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                        Detail Pesanan
                    </a>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-400">
                <i class="fa-solid fa-boxes-stacked text-4xl mb-3 text-slate-300"></i>
                <p class="text-sm font-semibold">Tidak ada pesanan yang sesuai filter.</p>
            </div>
        @endforelse

        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection
