@extends('layouts.admin')

@section('page_title', 'Kelola Pesanan Air Minum')

@section('content')
<div class="space-y-6">

    <!-- Header & Filter Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">Daftar Seluruh Pesanan</h1>
                <p class="text-xs text-slate-500">Kelola konfirmasi, penugasan kurir, dan pembayaran pesanan pelanggan.</p>
            </div>
            <div class="flex items-center space-x-2 text-xs">
                <a href="{{ route('admin.deliveries.map') }}" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition flex items-center shadow-xs">
                    <i class="fa-solid fa-map-location-dot mr-1.5"></i> Buka Peta Kurir
                </a>
            </div>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('admin.orders.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs pt-2 border-t border-slate-100">
            <div>
                <label class="block text-slate-600 font-bold mb-1">Cari No. Order / Pelanggan</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="ORD-2026... atau Nama" 
                       class="w-full border border-slate-300 rounded-xl p-2.5">
            </div>

            <div>
                <label class="block text-slate-600 font-bold mb-1">Status Pesanan</label>
                <select name="status" class="w-full border border-slate-300 rounded-xl p-2.5 bg-white">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Terkonfirmasi</option>
                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Diproses</option>
                    <option value="ready_for_delivery" {{ request('status') === 'ready_for_delivery' ? 'selected' : '' }}>Siap / Sedang Dikirim</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>

            <div>
                <label class="block text-slate-600 font-bold mb-1">Status Pembayaran</label>
                <select name="payment_status" class="w-full border border-slate-300 rounded-xl p-2.5 bg-white">
                    <option value="">Semua Pembayaran</option>
                    <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Belum Lunas</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Lunas</option>
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl transition">
                    Filter Data
                </button>
                <a href="{{ route('admin.orders.index') }}" class="p-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">No. Pesanan & Waktu</th>
                        <th class="px-5 py-3.5">Penerima & Alamat</th>
                        <th class="px-5 py-3.5">Rincian Barang</th>
                        <th class="px-5 py-3.5">Total & Bayar</th>
                        <th class="px-5 py-3.5">Status Pesanan</th>
                        <th class="px-5 py-3.5">Kurir Antar</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-3.5 font-bold text-slate-900 whitespace-nowrap">
                                <a href="{{ route('admin.orders.show', $order) }}" class="hover:text-cyan-600 font-mono text-sm">
                                    {{ $order->order_number }}
                                </a>
                                <div class="text-[10px] text-slate-400 font-normal mt-0.5">
                                    {{ $order->created_at->format('d M Y, H:i') }} WIB
                                </div>
                            </td>

                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-800">{{ $order->recipient_name }}</div>
                                <div class="text-[11px] text-slate-500 truncate max-w-[200px]">{{ $order->delivery_address_text }}</div>
                                <div class="text-[10px] text-emerald-600 font-semibold">{{ $order->recipient_phone }}</div>
                            </td>

                            <td class="px-5 py-3.5">
                                @foreach ($order->items as $it)
                                    <div class="text-slate-800 font-medium">
                                        {{ $it->quantity }}x {{ $it->product_name }}
                                        @if ($it->is_exchange)
                                            <span class="text-blue-600 text-[10px] font-semibold">(Tukar Galon)</span>
                                        @endif
                                    </div>
                                @endforeach
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <div class="font-black text-slate-900 text-sm">
                                    Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                </div>
                                <div class="flex items-center space-x-1 mt-1">
                                    <span class="text-[10px] font-bold px-1.5 py-0.2 rounded border uppercase {{ $order->payment_badge['color'] }}">
                                        {{ $order->payment_badge['label'] }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 uppercase font-semibold">({{ $order->payment_method }})</span>
                                </div>
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full font-bold text-[11px] border {{ $order->status_badge['color'] }}">
                                    {{ $order->status_badge['label'] }}
                                </span>
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if ($order->delivery && $order->delivery->driver)
                                    <div class="font-semibold text-slate-800">{{ $order->delivery->driver->name }}</div>
                                    <div class="text-[10px] text-slate-400 capitalize">{{ $order->delivery->status }}</div>
                                @else
                                    <!-- Quick Assign Driver form inline -->
                                    <form method="POST" action="{{ route('admin.orders.assign-driver', $order) }}" class="flex items-center space-x-1">
                                        @csrf
                                        <select name="driver_id" required class="text-xs border border-slate-200 rounded-lg p-1 bg-white">
                                            <option value="">Pilih Driver</option>
                                            @foreach ($drivers as $dr)
                                                <option value="{{ $dr->id }}">{{ $dr->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="px-2 py-1 bg-cyan-600 text-white rounded-lg text-xs font-bold hover:bg-cyan-700">
                                            Tugaskan
                                        </button>
                                    </form>
                                @endif
                            </td>

                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <a href="{{ route('admin.orders.show', $order) }}" 
                                   class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                                    Kelola &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                                Tidak ada data pesanan yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $orders->links() }}
        </div>
    </div>

</div>
@endsection
