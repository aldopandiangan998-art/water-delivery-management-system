@extends('layouts.admin')

@section('page_title', __('Tugas Pengantaran'))

@section('content')
<div class="space-y-6"
     x-data="{ viewMode: localStorage.getItem('view_mode_admin_deliveries') || 'list' }"
     x-effect="localStorage.setItem('view_mode_admin_deliveries', viewMode)">

    <x-card class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Tugas Pengantaran') }}</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Kelola pengantaran pesanan, pantau posisi GPS real-time, dan konfirmasi serah terima.') }}</p>
        </div>
        <div class="flex items-center space-x-2 text-xs">
            <x-grid-list-toggle model="viewMode" />
            <a href="{{ route('admin.deliveries.map') }}" class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition flex items-center">
                <i class="fa-solid fa-map-location-dot mr-1.5"></i> {{ __('Buka Radar Peta Real-Time') }}
            </a>
        </div>
    </x-card>

    <!-- List View (Table) -->
    <div x-show="viewMode === 'list'" x-cloak>
        <x-card class="p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800 text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5">{{ __('No. Pengantaran') }}</th>
                            <th class="px-5 py-3.5">{{ __('Tujuan & Penerima') }}</th>
                            <th class="px-5 py-3.5">{{ __('Status Pengantaran') }}</th>
                            <th class="px-5 py-3.5">{{ __('Waktu Mulai / Selesai') }}</th>
                            <th class="px-5 py-3.5">{{ __('Bukti Foto') }}</th>
                            <th class="px-5 py-3.5 text-right">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                        @forelse ($deliveries as $del)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                <td class="px-5 py-3.5 font-bold font-mono text-slate-900 dark:text-white">
                                    {{ $del->delivery_number }}
                                    <div class="text-[10px] text-slate-400 font-normal">Order #{{ $del->order->order_number }}</div>
                                </td>

                                <td class="px-5 py-3.5">
                                    <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $del->order->recipient_name }}</div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate max-w-[200px]">{{ $del->order->delivery_address_text }}</div>
                                </td>

                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-full font-bold text-[11px] border {{ $del->status_badge['color'] }}">
                                        {{ $del->status_badge['label'] }}
                                    </span>
                                </td>

                                <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                    @if ($del->started_at)
                                        <div>{{ __('Mulai') }}: {{ $del->started_at->format('H:i, d M') }}</div>
                                    @endif
                                    @if ($del->completed_at)
                                        <div class="text-emerald-700 dark:text-emerald-400 font-semibold">{{ __('Sampai') }}: {{ $del->completed_at->format('H:i, d M') }}</div>
                                    @endif
                                </td>

                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    @if ($del->photos->isNotEmpty())
                                        <a href="/storage/{{ $del->photos->first()->photo_path }}" target="_blank" class="inline-block border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
                                            <img src="/storage/{{ $del->photos->first()->photo_path }}" class="w-10 h-10 object-cover">
                                        </a>
                                    @else
                                        <span class="text-slate-400 text-[11px]">-</span>
                                    @endif
                                </td>

                                <td class="px-5 py-3.5 text-right whitespace-nowrap space-x-1.5">
                                    <a href="{{ route('admin.deliveries.show', $del) }}" class="text-xs font-bold text-emerald-700 dark:text-emerald-400 hover:text-emerald-900 bg-emerald-50 dark:bg-emerald-950/40 px-2.5 py-1 rounded-lg">
                                        {{ __('Proses Antar') }}
                                    </a>
                                    <a href="{{ route('admin.orders.show', $del->order) }}" class="text-xs font-bold text-cyan-600 dark:text-cyan-400 hover:text-cyan-800 bg-cyan-50 dark:bg-cyan-950/40 px-2.5 py-1 rounded-lg">
                                        {{ __('Detail Order') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                                    <x-empty-state 
                                        icon="fa-solid fa-truck" 
                                        :title="__('Belum Ada Data Pengantaran')" 
                                        :description="__('Tugas pengantaran akan muncul saat ada pesanan yang perlu dikirim.')"
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
        @forelse ($deliveries as $del)
            <x-card class="p-5 flex flex-col justify-between space-y-4 hover:shadow-md transition">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-mono text-sm font-bold text-slate-900 dark:text-white">{{ $del->delivery_number }}</span>
                            <div class="text-[10px] text-slate-400">Order #{{ $del->order->order_number }}</div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full font-bold text-[11px] border {{ $del->status_badge['color'] }}">
                            {{ $del->status_badge['label'] }}
                        </span>
                    </div>

                    <div>
                        <div class="font-extrabold text-sm text-slate-900 dark:text-white">{{ $del->order->recipient_name }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 mt-0.5">{{ $del->order->delivery_address_text }}</div>
                        <div class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold mt-0.5">{{ $del->order->recipient_phone }}</div>
                    </div>

                    <div class="text-xs text-slate-500 dark:text-slate-400 space-y-1 bg-slate-50 dark:bg-slate-950/60 p-2.5 rounded-xl border border-slate-100 dark:border-slate-800">
                        @if ($del->started_at)
                            <div><span class="font-semibold text-slate-700 dark:text-slate-300">{{ __('Mulai') }}:</span> {{ $del->started_at->format('H:i, d M Y') }}</div>
                        @endif
                        @if ($del->completed_at)
                            <div class="text-emerald-700 dark:text-emerald-400 font-semibold"><span class="font-semibold">{{ __('Sampai') }}:</span> {{ $del->completed_at->format('H:i, d M Y') }}</div>
                        @endif
                        @if (!$del->started_at && !$del->completed_at)
                            <span class="text-slate-400 italic">{{ __('Belum dimulai') }}</span>
                        @endif
                    </div>

                    @if ($del->photos->isNotEmpty())
                        <div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('Bukti Foto') }}</div>
                            <div class="flex gap-2">
                                @foreach ($del->photos->take(3) as $photo)
                                    <a href="/storage/{{ $photo->photo_path }}" target="_blank" class="border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
                                        <img src="/storage/{{ $photo->photo_path }}" class="w-12 h-12 object-cover">
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                    <a href="{{ route('admin.orders.show', $del->order) }}" class="text-xs font-bold text-cyan-600 dark:text-cyan-400 hover:text-cyan-800 bg-cyan-50 dark:bg-cyan-950/40 px-3 py-1.5 rounded-xl">
                        {{ __('Detail Order') }}
                    </a>
                    <a href="{{ route('admin.deliveries.show', $del) }}" class="text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 px-3 py-1.5 rounded-xl shadow-xs transition">
                        {{ __('Proses Antar') }}
                    </a>
                </div>
            </x-card>
        @empty
            <div class="col-span-full">
                <x-card class="p-12 text-center">
                    <x-empty-state 
                        icon="fa-solid fa-truck" 
                        :title="__('Belum Ada Data Pengantaran')" 
                        :description="__('Tugas pengantaran akan muncul saat ada pesanan yang perlu dikirim.')"
                    />
                </x-card>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800">
        {{ $deliveries->links() }}
    </div>

</div>
@endsection
