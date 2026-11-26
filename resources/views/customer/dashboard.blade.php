@extends('layouts.customer')

@section('title', __('Dashboard Member'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- Welcome & Member Profile Banner -->
    <x-card class="p-6 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center space-x-4">
            <x-avatar :name="$user->name" size="lg" class="shadow-md" />
            <div>
                <div class="flex items-center space-x-2">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Halo') }}, {{ $user->name }}!</h1>
                    <x-badge variant="info">
                        {{ str_replace('_', ' ', $user->profile?->customer_type ?? 'Household') }}
                    </x-badge>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex flex-wrap items-center gap-x-3 gap-y-1">
                    <span><i class="fa-solid fa-phone text-slate-400 mr-1"></i> {{ $user->phone ?? __('Belum ada no HP') }}</span>
                    <span>&bull;</span>
                    <span><i class="fa-solid fa-calendar-check text-slate-400 mr-1"></i> {{ __('Terdaftar sejak') }} {{ $user->created_at->format('M Y') }}</span>
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <x-button :href="route('customer.orders.create')" variant="primary" icon="fa-solid fa-cart-plus">
                {{ __('Pesan Air Sekarang') }}
            </x-button>
            <x-button :href="route('customer.store-profile')" variant="secondary" icon="fa-solid fa-store">
                {{ __('Lokasi Depot & Maps') }}
            </x-button>
            <x-button :href="route('customer.addresses.index')" variant="secondary" icon="fa-solid fa-location-dot">
                {{ __('Alamat Saya') }}
            </x-button>
        </div>
    </x-card>

    <!-- Active Delivery Real-Time Card (If order is active) -->
    @if ($activeOrder)
        <div class="bg-gradient-to-r from-cyan-600 via-blue-600 to-indigo-700 text-white rounded-3xl p-6 shadow-md relative overflow-hidden">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2">
                    <div class="inline-flex items-center px-3 py-1 rounded-full bg-white/20 text-white text-xs font-bold backdrop-blur-xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 mr-2 animate-ping"></span>
                        {{ __('Pesanan Sedang Diproses') }}: {{ $activeOrder->order_number }}
                    </div>
                    <h3 class="text-xl font-extrabold tracking-tight">
                        {{ __('Status') }}: {{ __($activeOrder->status_badge['label']) }}
                    </h3>
                    <p class="text-cyan-100 text-xs sm:text-sm max-w-xl">
                        {{ __('Tujuan pengantaran') }}: <span class="font-medium text-white">{{ $activeOrder->delivery_address_text }}</span>
                    </p>
                    @if ($activeOrder->delivery)
                        <div class="text-xs text-white/90 pt-1 flex items-center">
                            <i class="fa-solid fa-truck-fast mr-1.5 text-emerald-300"></i> {{ __('Status Pengantaran') }}: <span class="font-bold ml-1">{{ __($activeOrder->delivery->status_badge['label'] ?? $activeOrder->delivery->status) }}</span>
                        </div>
                    @endif
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    @if ($activeOrder->delivery && in_array($activeOrder->delivery->status, ['assigned', 'in_transit', 'arrived']))
                        <a href="{{ route('customer.orders.track', $activeOrder) }}" 
                           class="inline-flex items-center justify-center px-6 py-3 rounded-2xl bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold text-xs shadow-md transition transform hover:scale-105">
                            <i class="fa-solid fa-map-location-dot mr-2"></i> {{ __('Lacak Pengantaran di Peta') }}
                        </a>
                    @endif

                    <a href="{{ route('customer.orders.show', $activeOrder) }}" 
                       class="inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs backdrop-blur-xs transition">
                        {{ __('Detail') }}
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Key Metrics Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Metric 1: Saldo Galon -->
        <x-card class="p-5 space-y-2 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ __('Saldo Galon') }}</span>
                <div class="w-8 h-8 rounded-xl bg-cyan-100 dark:bg-cyan-950 text-cyan-700 dark:text-cyan-300 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-bottle-water"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900 dark:text-white">
                {{ $user->profile?->gallon_deposit_balance ?? 0 }} <span class="text-xs font-normal text-slate-400">{{ __('galon') }}</span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight">
                {{ __('Galon kosong Anda di depot yang siap ditukar.') }}
            </p>
        </x-card>

        <!-- Metric 2: Pesanan Berjalan -->
        <x-card class="p-5 space-y-2 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ __('Pesanan Aktif') }}</span>
                <div class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-truck-fast"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900 dark:text-white">
                {{ $activeOrdersCount }} <span class="text-xs font-normal text-slate-400">{{ __('pesanan') }}</span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight">
                {{ __('Sedang diproses / dalam perjalanan.') }}
            </p>
        </x-card>

        <!-- Metric 3: Total Pesanan Selesai -->
        <x-card class="p-5 space-y-2 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ __('Pesanan Selesai') }}</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-check-double"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900 dark:text-white">
                {{ $completedOrdersCount }} <span class="text-xs font-normal text-slate-400">{{ __('pesanan') }}</span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight">
                {{ __('Total transaksi air minum yang sukses diterima.') }}
            </p>
        </x-card>

        <!-- Metric 4: Alamat Tersimpan -->
        <x-card class="p-5 space-y-2 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ __('Buku Alamat') }}</span>
                <div class="w-8 h-8 rounded-xl bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-location-dot"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900 dark:text-white">
                {{ $user->addresses->count() }} <span class="text-xs font-normal text-slate-400">{{ __('lokasi') }}</span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight truncate">
                {{ __('Utama') }}: <strong class="text-slate-700 dark:text-slate-300">{{ $defaultAddress?->label ?? __('Belum ada') }}</strong>
            </p>
        </x-card>
    </div>

    <!-- Popular Catalog Showcase Section -->
    <x-card class="p-6 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-black text-slate-900 dark:text-white tracking-tight">{{ __('Katalog Produk Air Minum') }}</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Daftar produk air minum higienis dan isi ulang resmi Toko Nova Jaya.') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ($popularProducts as $prod)
                <div class="bg-slate-50 dark:bg-slate-950/60 p-4 rounded-2xl border border-slate-100 dark:border-slate-800 flex flex-col justify-between transition group">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] uppercase font-extrabold px-2 py-0.5 rounded-md bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300">
                                {{ $prod->category?->name ?? __('Produk Air') }}
                            </span>
                            @if ($prod->is_returnable_gallon)
                                <x-badge variant="info" size="sm">
                                    {{ __('Tukar Galon') }}
                                </x-badge>
                            @endif
                        </div>

                        <h3 class="font-bold text-sm text-slate-900 dark:text-white">
                            {{ $prod->name }}
                        </h3>

                        <div class="text-xs text-slate-500 dark:text-slate-400">
                            Rp <span class="text-base font-black text-slate-900 dark:text-white">{{ number_format($prod->price, 0, ',', '.') }}</span> / {{ $prod->unit }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-card>

    <!-- Recent Orders & Default Address Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Recent Orders List (2 Cols) -->
        <x-card class="lg:col-span-2 p-0 overflow-hidden">
            <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h2 class="font-black text-slate-900 dark:text-white text-base tracking-tight">{{ __('Riwayat Pesanan Terakhir') }}</h2>
                    <p class="text-xs text-slate-400">{{ __('Pantau transaksi dan status serah terima air galon Anda.') }}</p>
                </div>
                <x-button :href="route('customer.orders.index')" variant="ghost" size="sm" iconRight="fa-solid fa-arrow-right">
                    {{ __('Lihat Semua') }}
                </x-button>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($recentOrders as $ord)
                    <div class="p-5 hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center space-x-2">
                                <span class="font-black text-sm text-slate-900 dark:text-white">#{{ $ord->order_number }}</span>
                                <x-badge variant="neutral" class="{{ $ord->status_badge['color'] }}">
                                    {{ __($ord->status_badge['label']) }}
                                </x-badge>
                                <x-badge variant="neutral" class="{{ $ord->payment_badge['color'] }}">
                                    {{ __($ord->payment_badge['label']) }}
                                </x-badge>
                            </div>

                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $ord->created_at->format('d M Y, H:i') }} WIB &bull;
                                {{ $ord->items->pluck('product_name')->join(', ') }}
                            </div>

                            <div class="text-xs font-bold text-cyan-600 dark:text-cyan-400">
                                {{ __('Total') }}: Rp {{ number_format($ord->total_amount, 0, ',', '.') }} ({{ strtoupper($ord->payment_method) }})
                            </div>
                        </div>

                        <div class="flex items-center space-x-2">
                            @if ($ord->delivery && in_array($ord->delivery->status, ['assigned', 'in_transit', 'arrived']))
                                <x-button :href="route('customer.orders.track', $ord)" variant="success" size="sm" icon="fa-solid fa-location-crosshairs">
                                    {{ __('Lacak') }}
                                </x-button>
                            @endif

                            <x-button :href="route('customer.orders.show', $ord)" variant="secondary" size="sm">
                                {{ __('Detail') }}
                            </x-button>
                        </div>
                    </div>
                @empty
                    <div class="p-10 text-center text-slate-400 text-xs">
                        <i class="fa-solid fa-receipt text-3xl mb-2 text-slate-300 dark:text-slate-600 block"></i>
                        {{ __('Belum ada riwayat pesanan.') }}
                    </div>
                @endforelse
            </div>
        </x-card>

        <!-- Right: Default Address Card (1 Col) -->
        <x-card class="p-5 space-y-4 flex flex-col justify-between">
            <div class="space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h2 class="font-black text-slate-900 dark:text-white text-sm flex items-center">
                        <i class="fa-solid fa-location-dot text-cyan-600 dark:text-cyan-400 mr-2"></i> {{ __('Alamat Pengantaran Utama') }}
                    </h2>
                    @if ($defaultAddress)
                        <x-badge variant="info" size="sm">
                            {{ $defaultAddress->label }}
                        </x-badge>
                    @endif
                </div>

                @if ($defaultAddress)
                    <div class="space-y-2 text-xs">
                        <div>
                            <span class="text-slate-400 dark:text-slate-500 block">{{ __('Penerima') }}:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200 text-sm">{{ $defaultAddress->recipient_name }}</span>
                            <span class="text-slate-500 dark:text-slate-400 block">({{ $defaultAddress->recipient_phone }})</span>
                        </div>

                        <div>
                            <span class="text-slate-400 dark:text-slate-500 block">{{ __('Alamat Jalan') }}:</span>
                            <p class="text-slate-700 dark:text-slate-300 font-medium leading-relaxed mt-0.5">{{ $defaultAddress->address_line }}</p>
                        </div>

                        @if ($defaultAddress->benchmark)
                            <div class="p-2.5 bg-amber-50 dark:bg-amber-950/40 rounded-xl border border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-200 text-[11px]">
                                <span class="font-bold">{{ __('Patokan') }}:</span> {{ $defaultAddress->benchmark }}
                            </div>
                        @endif

                        @if ($defaultAddress->serviceArea)
                            <div class="text-[11px] text-cyan-600 dark:text-cyan-400 font-bold">
                                {{ __('Area') }}: {{ $defaultAddress->serviceArea->name }} ({{ __('Ongkir') }}: Rp {{ number_format($defaultAddress->serviceArea->delivery_fee, 0, ',', '.') }})
                            </div>
                        @endif
                    </div>
                @else
                    <div class="py-8 text-center text-slate-400 text-xs space-y-2">
                        <i class="fa-solid fa-map-location-dot text-3xl text-slate-300 dark:text-slate-600 block"></i>
                        <p>{{ __('Anda belum menyimpan alamat pengantaran.') }}</p>
                    </div>
                @endif
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                <x-button :href="route('customer.addresses.index')" variant="secondary" size="sm" class="w-full" icon="fa-solid fa-gear">
                    {{ __('Kelola Seluruh Alamat Saya') }}
                </x-button>
            </div>
        </x-card>
    </div>

</div>
@endsection
