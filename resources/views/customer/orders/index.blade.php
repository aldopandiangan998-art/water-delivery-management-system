@extends('layouts.customer')

@section('title', __('Riwayat Pesanan Saya'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6" 
     x-data="{ viewMode: localStorage.getItem('view_mode_customer_orders') || 'list' }"
     x-effect="localStorage.setItem('view_mode_customer_orders', viewMode)">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Riwayat Pesanan') }}</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Daftar seluruh transaksi pemesanan air minum Anda.') }}</p>
        </div>
        <div class="flex items-center space-x-2">
            <x-grid-list-toggle model="viewMode" />
            <x-button :href="route('customer.orders.create')" variant="primary" size="sm" icon="fa-solid fa-plus">
                {{ __('Pesan Air Baru') }}
            </x-button>
        </div>
    </div>

    <!-- Filter status -->
    <div class="flex flex-wrap gap-2 text-xs">
        <a href="{{ route('customer.orders.index') }}" 
           class="px-3.5 py-2 rounded-xl font-bold transition {{ !request('status') ? 'bg-cyan-600 text-white shadow-xs' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800' }}">
            {{ __('Semua') }}
        </a>
        <a href="{{ route('customer.orders.index', ['status' => 'pending']) }}" 
           class="px-3.5 py-2 rounded-xl font-bold transition {{ request('status') === 'pending' ? 'bg-amber-600 text-white shadow-xs' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800' }}">
            {{ __('Menunggu') }}
        </a>
        <a href="{{ route('customer.orders.index', ['status' => 'ready_for_delivery']) }}" 
           class="px-3.5 py-2 rounded-xl font-bold transition {{ request('status') === 'ready_for_delivery' ? 'bg-cyan-600 text-white shadow-xs' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800' }}">
            {{ __('Dikirim') }}
        </a>
        <a href="{{ route('customer.orders.index', ['status' => 'completed']) }}" 
           class="px-3.5 py-2 rounded-xl font-bold transition {{ request('status') === 'completed' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800' }}">
            {{ __('Selesai') }}
        </a>
        <a href="{{ route('customer.orders.index', ['status' => 'cancelled']) }}" 
           class="px-3.5 py-2 rounded-xl font-bold transition {{ request('status') === 'cancelled' ? 'bg-rose-600 text-white shadow-xs' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800' }}">
            {{ __('Dibatalkan') }}
        </a>
    </div>

    <!-- List View -->
    <div x-show="viewMode === 'list'" x-cloak class="space-y-3">
        @forelse ($orders as $order)
            <x-card class="p-5 sm:p-6 hover:shadow-md transition flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-2 flex-1 min-w-0">
                    <div class="flex items-center space-x-2 flex-wrap gap-y-1">
                        <span class="font-black text-base text-slate-900 dark:text-white font-mono">{{ $order->order_number }}</span>
                        <x-badge variant="neutral" class="{{ $order->status_badge['color'] }}">
                            {{ __($order->status_badge['label']) }}
                        </x-badge>
                        <x-badge variant="neutral" class="{{ $order->payment_badge['color'] }}">
                            {{ __($order->payment_badge['label']) }}
                        </x-badge>
                    </div>

                    <div class="text-xs text-slate-500 dark:text-slate-400 flex flex-wrap items-center gap-1.5">
                        <span>{{ $order->created_at->format('d M Y, H:i') }} WIB</span>
                        <span>&bull;</span>
                        <span class="inline-flex items-center text-slate-700 dark:text-slate-300 font-medium">
                            <i class="fa-solid fa-location-dot text-cyan-600 dark:text-cyan-400 mr-1 text-[11px]"></i>
                            {{ __('Tujuan') }}: {{ $order->recipient_name }}
                            @if ($order->delivery_address_text)
                                <span class="text-slate-500 dark:text-slate-400 ml-1">({{ Str::limit($order->delivery_address_text, 45) }})</span>
                            @endif
                        </span>
                    </div>

                    <!-- Items brief -->
                    <div class="text-xs text-slate-700 dark:text-slate-300 font-medium">
                        @foreach ($order->items as $item)
                            <span class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-2 py-0.5 rounded-md mr-1 inline-block my-0.5">
                                {{ $item->quantity }}x {{ $item->product_name }}
                            </span>
                        @endforeach
                    </div>

                    <div class="flex flex-wrap items-center gap-2 text-xs pt-1">
                        <span class="font-bold text-cyan-600 dark:text-cyan-400">{{ __('Total') }}: Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        <x-badge variant="neutral" size="sm" class="{{ $order->payment_method_badge['color'] }}">
                            <i class="fa-solid {{ $order->payment_method_badge['icon'] }} mr-1 text-[10px]"></i>
                            {{ __($order->payment_method_badge['label']) }}
                        </x-badge>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 shrink-0 pt-2 md:pt-0">
                    @if ($order->delivery && in_array($order->delivery->status, ['assigned', 'in_transit', 'arrived']))
                        <x-button :href="route('customer.orders.track', $order)" variant="success" size="sm" icon="fa-solid fa-map-location-dot">
                            {{ __('Lacak Pengantaran') }}
                        </x-button>
                    @endif

                    <x-button :href="route('customer.orders.show', $order)" variant="secondary" size="sm">
                        {{ __('Detail Pesanan') }}
                    </x-button>
                </div>
            </x-card>
        @empty
            <x-card class="p-12 text-center">
                <x-empty-state 
                    icon="fa-regular fa-receipt" 
                    :title="__('Belum Ada Pesanan')" 
                    :description="__('Anda belum memiliki riwayat pesanan dengan filter status ini.')"
                />
            </x-card>
        @endforelse
    </div>

    <!-- Grid View -->
    <div x-show="viewMode === 'grid'" x-cloak class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($orders as $order)
            <x-card class="p-5 sm:p-6 hover:shadow-md transition flex flex-col justify-between space-y-4">
                <div class="space-y-2">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <span class="font-black text-base text-slate-900 dark:text-white font-mono">{{ $order->order_number }}</span>
                        <x-badge variant="neutral" class="{{ $order->status_badge['color'] }}">
                            {{ __($order->status_badge['label']) }}
                        </x-badge>
                    </div>

                    <div class="text-xs text-slate-500 dark:text-slate-400 flex flex-wrap items-center gap-1.5">
                        <span>{{ $order->created_at->format('d M Y, H:i') }} WIB</span>
                        <span>&bull;</span>
                        <span class="inline-flex items-center text-slate-700 dark:text-slate-300 font-medium">
                            <i class="fa-solid fa-location-dot text-cyan-600 dark:text-cyan-400 mr-1 text-[11px]"></i>
                            {{ __('Tujuan') }}: {{ $order->recipient_name }}
                            @if ($order->delivery_address_text)
                                <span class="text-slate-500 dark:text-slate-400 ml-1">({{ Str::limit($order->delivery_address_text, 45) }})</span>
                            @endif
                        </span>
                    </div>

                    <!-- Items brief -->
                    <div class="text-xs text-slate-700 dark:text-slate-300 font-medium">
                        @foreach ($order->items as $item)
                            <span class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-2 py-0.5 rounded-md mr-1 inline-block my-0.5">
                                {{ $item->quantity }}x {{ $item->product_name }}
                            </span>
                        @endforeach
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs pt-1">
                        <span class="font-bold text-cyan-600 dark:text-cyan-400 text-sm">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        <x-badge variant="neutral" size="sm" class="{{ $order->payment_method_badge['color'] }}">
                            <i class="fa-solid {{ $order->payment_method_badge['icon'] }} mr-1 text-[10px]"></i>
                            {{ __($order->payment_method_badge['label']) }}
                        </x-badge>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-2 shrink-0 pt-3 border-t border-slate-100 dark:border-slate-800">
                    @if ($order->status === 'pending')
                        <form id="cancel-order-{{ $order->id }}" method="POST" action="{{ route('customer.orders.cancel', $order) }}" class="inline">
                            @csrf
                            <button type="button" 
                                    @click="$dispatch('open-confirm-modal', {
                                        form: 'cancel-order-{{ $order->id }}',
                                        title: '{{ __('Batalkan Pesanan') }}',
                                        message: '{{ __('Apakah Anda yakin ingin membatalkan pesanan #') }}{{ $order->order_number }}? {{ __('Stok pesanan akan dikembalikan.') }}',
                                        confirmText: '{{ __('Ya, Batalkan Pesanan') }}',
                                        confirmButtonClass: 'bg-rose-600 hover:bg-rose-700 text-white shadow-md shadow-rose-600/20'
                                    })"
                                    class="text-xs font-bold text-rose-600 dark:text-rose-400 hover:underline px-2.5 py-1.5 cursor-pointer flex items-center">
                                <i class="fa-solid fa-ban mr-1.5"></i> {{ __('Batalkan') }}
                            </button>
                        </form>
                    @endif

                    @if ($order->delivery && in_array($order->delivery->status, ['assigned', 'in_transit', 'arrived']))
                        <x-button :href="route('customer.orders.track', $order)" variant="success" size="sm" icon="fa-solid fa-map-location-dot">
                            {{ __('Lacak Pengantaran') }}
                        </x-button>
                    @endif

                    <x-button :href="route('customer.orders.show', $order)" variant="secondary" size="sm">
                        {{ __('Detail Pesanan') }}
                    </x-button>
                </div>
            </x-card>
        @empty
            <div class="col-span-full">
                <x-card class="p-12 text-center">
                    <x-empty-state 
                        icon="fa-regular fa-receipt" 
                        :title="__('Belum Ada Pesanan')" 
                        :description="__('Anda belum memiliki riwayat pesanan dengan filter status ini.')"
                    />
                </x-card>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="pt-2">
        {{ $orders->links() }}
    </div>

</div>
@endsection
