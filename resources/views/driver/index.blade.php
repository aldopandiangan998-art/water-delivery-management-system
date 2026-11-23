@extends('layouts.driver')

@section('title', 'Tugas Pengantaran')

@section('content')
<div class="space-y-4">

    <!-- Header status card -->
    <div class="bg-gradient-to-r from-emerald-600 to-teal-700 text-white p-5 rounded-2xl shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-emerald-200 uppercase tracking-wider">Status Kurir</span>
                <h1 class="text-xl font-black mt-0.5">Tugas Pengantaran</h1>
            </div>
            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-lg">
                <i class="fa-solid fa-motorcycle"></i>
            </div>
        </div>
        <div class="mt-4 pt-3 border-t border-emerald-500/50 flex justify-between text-xs">
            <div>
                <span class="text-emerald-200">Perlu Diantar:</span>
                <span class="font-bold ml-1 text-white">{{ $activeDeliveries->count() }} tugas</span>
            </div>
            <div>
                <span class="text-emerald-200">Selesai Hari Ini:</span>
                <span class="font-bold ml-1 text-white">{{ $completedDeliveries->total() }}</span>
            </div>
        </div>
    </div>

    <!-- Active Tasks List -->
    <div class="space-y-3">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 px-1">Tugas Aktif (Perlu Dikerjakan)</h2>

        @forelse ($activeDeliveries as $del)
            <div class="bg-white rounded-2xl border-2 {{ $del->status === 'in_transit' ? 'border-amber-400 bg-amber-50/20 shadow-md ring-2 ring-amber-400/20' : 'border-slate-200 shadow-xs' }} p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="font-black text-sm text-slate-900">#{{ $del->order->order_number }}</span>
                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full border {{ $del->status_badge['color'] }}">
                        {{ $del->status_badge['label'] }}
                    </span>
                </div>

                <div>
                    <div class="font-bold text-slate-800 text-sm">{{ $del->order->recipient_name }}</div>
                    <div class="text-xs text-slate-600 mt-0.5 leading-relaxed">
                        <i class="fa-solid fa-location-dot text-rose-500 mr-1"></i> {{ $del->order->delivery_address_text }}
                    </div>
                    @if ($del->order->delivery_benchmark)
                        <div class="text-[11px] text-slate-500 mt-1 italic">
                            Patokan: {{ $del->order->delivery_benchmark }}
                        </div>
                    @endif
                </div>

                <!-- Products Summary -->
                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100 text-xs text-slate-700 space-y-1">
                    @foreach ($del->order->items as $item)
                        <div class="flex justify-between font-medium">
                            <span>{{ $item->quantity }}x {{ $item->product_name }}</span>
                            @if ($item->is_exchange)
                                <span class="text-blue-600 font-semibold">(Tukar Galon)</span>
                            @endif
                        </div>
                    @endforeach
                    <div class="pt-1 border-t border-slate-200 flex justify-between font-bold text-slate-900">
                        <span>Tagihan ({{ strtoupper($del->order->payment_method) }}):</span>
                        <span class="text-cyan-700">Rp {{ number_format($del->order->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Action Button -->
                <a href="{{ route('driver.deliveries.show', $del) }}" 
                   class="w-full py-2.5 px-4 rounded-xl text-center font-bold text-xs text-white bg-slate-900 hover:bg-slate-800 shadow-xs transition flex items-center justify-center">
                    <i class="fa-solid fa-route mr-1.5 text-cyan-400"></i> Buka Rincian & Navigasi
                </a>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-slate-400">
                <i class="fa-solid fa-mug-hot text-3xl mb-2 text-slate-300"></i>
                <p class="text-xs font-semibold">Tidak ada tugas pengantaran aktif saat ini.</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Tugas baru dari admin akan muncul di sini.</p>
            </div>
        @endforelse
    </div>

    <!-- Completed Deliveries List -->
    <div class="space-y-3 pt-2">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 px-1">Riwayat Pengantaran</h2>

        <div class="bg-white rounded-2xl border border-slate-200 divide-y divide-slate-100 shadow-xs">
            @forelse ($completedDeliveries as $comp)
                <div class="p-3.5 flex items-center justify-between text-xs">
                    <div>
                        <div class="font-bold text-slate-800">#{{ $comp->order->order_number }} - {{ $comp->order->recipient_name }}</div>
                        <div class="text-[11px] text-slate-400">{{ $comp->completed_at?->format('H:i, d M Y') ?? $comp->updated_at->format('H:i, d M Y') }}</div>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Selesai
                    </span>
                </div>
            @empty
                <div class="p-6 text-center text-slate-400 text-xs">
                    Belum ada riwayat pengantaran selesai.
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
