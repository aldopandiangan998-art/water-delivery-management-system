@extends('layouts.customer')

@section('title', __('Dashboard Member'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- Welcome & Member Profile Banner -->
    <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-cyan-600 via-cyan-500 to-blue-600 text-white flex items-center justify-center font-black text-xl shadow-md shrink-0">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Halo') }}, {{ $user->name }}!</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-cyan-50 dark:bg-cyan-950/50 text-cyan-700 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800">
                        {{ str_replace('_', ' ', $user->profile?->customer_type ?? 'Household') }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex flex-wrap items-center gap-x-3 gap-y-1">
                    <span><i class="fa-solid fa-phone text-slate-400 mr-1"></i> {{ $user->phone ?? __('Belum ada no HP') }}</span>
                    <span>&bull;</span>
                    <span><i class="fa-solid fa-calendar-check text-slate-400 mr-1"></i> {{ __('Terdaftar sejak') }} {{ $user->created_at->format('M Y') }}</span>
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('customer.orders.create') }}" 
               class="px-5 py-2.5 rounded-2xl font-extrabold text-xs text-white bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 shadow-md hover:shadow-lg transition flex items-center">
                <i class="fa-solid fa-cart-plus mr-2"></i> {{ __('Pesan Air Sekarang') }}
            </a>
            <a href="{{ route('customer.store-profile') }}" 
               class="px-4 py-2.5 rounded-2xl font-bold text-xs text-blue-800 dark:text-blue-300 bg-blue-50 dark:bg-blue-950/40 hover:bg-blue-100 dark:hover:bg-blue-900/40 border border-blue-200 dark:border-blue-800 transition flex items-center shadow-2xs">
                <i class="fa-solid fa-store mr-1.5 text-blue-600 dark:text-blue-400"></i> {{ __('Lokasi Depot & Maps') }}
            </a>
            <a href="{{ route('customer.addresses.index') }}" 
               class="px-4 py-2.5 rounded-2xl font-bold text-xs text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 transition flex items-center">
                <i class="fa-solid fa-location-dot mr-1.5 text-cyan-600 dark:text-cyan-400"></i> {{ __('Alamat Saya') }}
            </a>
        </div>
    </div>

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

                    @if (in_array($activeOrder->status, ['pending', 'confirmed']))
                        <a href="{{ $activeOrder->whatsapp_url }}" target="_blank" 
                           class="inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs shadow-sm transition">
                            <i class="fa-brands fa-whatsapp text-sm mr-1.5"></i> {{ __('Konfirmasi ke WA') }}
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
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Saldo Galon</span>
                <div class="w-8 h-8 rounded-xl bg-cyan-100 text-cyan-700 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-bottle-water"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900">
                {{ $user->profile?->gallon_deposit_balance ?? 0 }} <span class="text-xs font-normal text-slate-400">galon</span>
            </div>
            <p class="text-[11px] text-slate-500 leading-tight">
                Galon kosong Anda di depot yang siap ditukar.
            </p>
        </div>

        <!-- Metric 2: Pesanan Berjalan -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Pesanan Aktif</span>
                <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-truck-fast"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900">
                {{ $activeOrdersCount }} <span class="text-xs font-normal text-slate-400">pesanan</span>
            </div>
            <p class="text-[11px] text-slate-500 leading-tight">
                Sedang diproses / dalam perjalanan.
            </p>
        </div>

        <!-- Metric 3: Total Pesanan Selesai -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Pesanan Selesai</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-check-double"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900">
                {{ $completedOrdersCount }} <span class="text-xs font-normal text-slate-400">pesanan</span>
            </div>
            <p class="text-[11px] text-slate-500 leading-tight">
                Total transaksi air minum yang sukses diterima.
            </p>
        </div>

        <!-- Metric 4: Alamat Tersimpan -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Buku Alamat</span>
                <div class="w-8 h-8 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-location-dot"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900">
                {{ $user->addresses->count() }} <span class="text-xs font-normal text-slate-400">lokasi</span>
            </div>
            <p class="text-[11px] text-slate-500 leading-tight truncate">
                Utama: <strong class="text-slate-700">{{ $defaultAddress?->label ?? 'Belum ada' }}</strong>
            </p>
        </div>
    </div>

    <!-- Quick Order / Popular Catalog Section -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-extrabold text-slate-900">Pesan Cepat Produk Pilihan</h2>
                <p class="text-xs text-slate-500">Pilih air minum higienis favorit Anda untuk langsung masuk ke keranjang pemesanan.</p>
            </div>
            <a href="{{ route('customer.orders.create') }}" class="text-xs font-bold text-cyan-600 hover:text-cyan-700 flex items-center">
                Lihat Semua &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ($popularProducts as $prod)
                <div class="bg-slate-50 hover:bg-slate-100/70 p-4 rounded-2xl border border-slate-100 flex flex-col justify-between transition group">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] uppercase font-extrabold px-2 py-0.5 rounded-md bg-white border border-slate-200 text-slate-600">
                                {{ $prod->category?->name ?? 'Produk Air' }}
                            </span>
                            @if ($prod->is_returnable_gallon)
                                <span class="text-[10px] font-bold text-cyan-700 bg-cyan-50 px-2 py-0.5 rounded-md">
                                    Tukar Galon
                                </span>
                            @endif
                        </div>

                        <h3 class="font-extrabold text-sm text-slate-900 group-hover:text-cyan-700 transition">
                            {{ $prod->name }}
                        </h3>

                        <div class="text-xs text-slate-500">
                            Rp <span class="text-base font-black text-slate-900">{{ number_format($prod->price, 0, ',', '.') }}</span> / {{ $prod->unit }}
                        </div>
                    </div>

                    <div class="pt-3 mt-3 border-t border-slate-200/60">
                        <a href="{{ route('customer.orders.create') }}" 
                           class="w-full py-2 px-3 rounded-xl bg-white hover:bg-cyan-600 hover:text-white border border-slate-200 text-xs font-bold text-slate-700 transition flex items-center justify-center shadow-2xs">
                            <i class="fa-solid fa-plus mr-1.5 text-xs"></i> Pesan Produk Ini
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Recent Orders & Default Address Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Recent Orders List (2 Cols) -->
        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-xs">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="font-extrabold text-slate-900 text-base">Riwayat Pesanan Terakhir</h2>
                    <p class="text-xs text-slate-400">Pantau transaksi dan status serah terima air galon Anda.</p>
                </div>
                <a href="{{ route('customer.orders.index') }}" class="text-xs font-bold text-cyan-600 hover:text-cyan-700">
                    Lihat Semua &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($recentOrders as $ord)
                    <div class="p-5 hover:bg-slate-50/60 transition flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center space-x-2">
                                <span class="font-black text-sm text-slate-900">#{{ $ord->order_number }}</span>
                                <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full border {{ $ord->status_badge['color'] }}">
                                    {{ $ord->status_badge['label'] }}
                                </span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border {{ $ord->payment_badge['color'] }}">
                                    {{ $ord->payment_badge['label'] }}
                                </span>
                            </div>

                            <div class="text-xs text-slate-500">
                                {{ $ord->created_at->format('d M Y, H:i') }} WIB &bull;
                                {{ $ord->items->pluck('product_name')->join(', ') }}
                            </div>

                            <div class="text-xs font-bold text-cyan-700">
                                Total: Rp {{ number_format($ord->total_amount, 0, ',', '.') }} ({{ strtoupper($ord->payment_method) }})
                            </div>
                        </div>

                        <div class="flex items-center space-x-2">
                            @if ($ord->delivery && in_array($ord->delivery->status, ['assigned', 'in_transit', 'arrived']))
                                <a href="{{ route('customer.orders.track', $ord) }}" 
                                   class="px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition flex items-center shadow-xs">
                                    <i class="fa-solid fa-location-crosshairs mr-1"></i> Lacak
                                </a>
                            @endif

                            @if (in_array($ord->status, ['pending', 'confirmed']))
                                <a href="{{ $ord->whatsapp_url }}" target="_blank"
                                   class="px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition flex items-center">
                                    <i class="fa-brands fa-whatsapp mr-1 text-emerald-600"></i> WA
                                </a>
                            @endif

                            <a href="{{ route('customer.orders.show', $ord) }}" 
                               class="px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                                Detail
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-10 text-center text-slate-400 text-xs">
                        <i class="fa-solid fa-receipt text-3xl mb-2 text-slate-300 block"></i>
                        Belum ada pesanan. <a href="{{ route('customer.orders.create') }}" class="text-cyan-600 font-bold underline ml-1">Pesan galon air sekarang!</a>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right: Default Address Card (1 Col) -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs space-y-4 flex flex-col justify-between">
            <div class="space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="font-extrabold text-slate-900 text-sm flex items-center">
                        <i class="fa-solid fa-location-dot text-cyan-600 mr-2"></i> Alamat Pengantaran Utama
                    </h2>
                    @if ($defaultAddress)
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-cyan-50 text-cyan-700 border border-cyan-200">
                            {{ $defaultAddress->label }}
                        </span>
                    @endif
                </div>

                @if ($defaultAddress)
                    <div class="space-y-2 text-xs">
                        <div>
                            <span class="text-slate-400 block">Penerima:</span>
                            <span class="font-bold text-slate-800 text-sm">{{ $defaultAddress->recipient_name }}</span>
                            <span class="text-slate-500 block">({{ $defaultAddress->recipient_phone }})</span>
                        </div>

                        <div>
                            <span class="text-slate-400 block">Alamat Jalan:</span>
                            <p class="text-slate-700 font-medium leading-relaxed mt-0.5">{{ $defaultAddress->address_line }}</p>
                        </div>

                        @if ($defaultAddress->benchmark)
                            <div class="p-2.5 bg-amber-50 rounded-xl border border-amber-200 text-amber-900 text-[11px]">
                                <span class="font-bold">Patokan:</span> {{ $defaultAddress->benchmark }}
                            </div>
                        @endif

                        @if ($defaultAddress->serviceArea)
                            <div class="text-[11px] text-cyan-700 font-bold">
                                Area: {{ $defaultAddress->serviceArea->name }} (Ongkir: Rp {{ number_format($defaultAddress->serviceArea->delivery_fee, 0, ',', '.') }})
                            </div>
                        @endif
                    </div>
                @else
                    <div class="py-8 text-center text-slate-400 text-xs space-y-2">
                        <i class="fa-solid fa-map-location-dot text-3xl text-slate-300 block"></i>
                        <p>Anda belum menyimpan alamat pengantaran.</p>
                    </div>
                @endif
            </div>

            <div class="pt-3 border-t border-slate-100">
                <a href="{{ route('customer.addresses.index') }}" 
                   class="w-full py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center justify-center">
                    <i class="fa-solid fa-gear mr-1.5"></i> Kelola Seluruh Alamat Saya
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
