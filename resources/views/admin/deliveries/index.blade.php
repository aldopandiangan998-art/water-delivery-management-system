@extends('layouts.admin')

@section('page_title', 'Tugas Pengantaran & Kurir')

@section('content')
<div class="space-y-6">

    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight">Tugas Pengantaran Kurir</h1>
            <p class="text-xs text-slate-500">Monitor status kurir lapangan, rute, dan bukti serah terima foto.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.deliveries.map') }}" class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition flex items-center">
                <i class="fa-solid fa-map-location-dot mr-1.5"></i> Buka Radar Peta Real-Time
            </a>
        </div>
    </div>

    <!-- Deliveries Table -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">No. Pengantaran</th>
                        <th class="px-5 py-3.5">Kurir</th>
                        <th class="px-5 py-3.5">Tujuan & Penerima</th>
                        <th class="px-5 py-3.5">Status Pengantaran</th>
                        <th class="px-5 py-3.5">Waktu Mulai / Selesai</th>
                        <th class="px-5 py-3.5">Bukti Foto</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($deliveries as $del)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3.5 font-bold font-mono text-slate-900">
                                {{ $del->delivery_number }}
                                <div class="text-[10px] text-slate-400 font-normal">Order #{{ $del->order->order_number }}</div>
                            </td>

                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-900">{{ $del->driver?->name ?? 'Belum Ditugaskan' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $del->driver?->phone ?? '-' }}</div>
                            </td>

                            <td class="px-5 py-3.5">
                                <div class="font-semibold text-slate-800">{{ $del->order->recipient_name }}</div>
                                <div class="text-[11px] text-slate-500 truncate max-w-[200px]">{{ $del->order->delivery_address_text }}</div>
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full font-bold text-[11px] border {{ $del->status_badge['color'] }}">
                                    {{ $del->status_badge['label'] }}
                                </span>
                            </td>

                            <td class="px-5 py-3.5 text-slate-500 whitespace-nowrap">
                                @if ($del->started_at)
                                    <div>Mulai: {{ $del->started_at->format('H:i, d M') }}</div>
                                @endif
                                @if ($del->completed_at)
                                    <div class="text-emerald-700 font-semibold">Sampai: {{ $del->completed_at->format('H:i, d M') }}</div>
                                @endif
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if ($del->photos->isNotEmpty())
                                    <a href="/storage/{{ $del->photos->first()->photo_path }}" target="_blank" class="inline-block border border-slate-200 rounded-lg overflow-hidden">
                                        <img src="/storage/{{ $del->photos->first()->photo_path }}" class="w-10 h-10 object-cover">
                                    </a>
                                @else
                                    <span class="text-slate-400 text-[11px]">-</span>
                                @endif
                            </td>

                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <a href="{{ route('admin.orders.show', $del->order) }}" class="text-xs font-bold text-cyan-600 hover:text-cyan-800 bg-cyan-50 px-2.5 py-1 rounded-lg">
                                    Lihat Order
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                                Belum ada data pengantaran.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $deliveries->links() }}
        </div>
    </div>

</div>
@endsection
