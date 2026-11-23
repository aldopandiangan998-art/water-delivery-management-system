@extends('layouts.admin')

@section('page_title', 'Ringkasan Bisnis & Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Top KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Today Orders -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-cyan-100 text-cyan-700 flex items-center justify-center text-xl font-bold shrink-0">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Pesanan Hari Ini</span>
                <div class="text-2xl font-black text-slate-900 mt-0.5">{{ $todayOrdersCount }} <span class="text-xs font-normal text-slate-400">order</span></div>
                <div class="text-[11px] text-amber-600 font-bold mt-0.5">{{ $pendingOrdersCount }} perlu dikonfirmasi</div>
            </div>
        </div>

        <!-- Today Revenue -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl font-bold shrink-0">
                <i class="fa-solid fa-money-bill-wave"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Omzet Lunas Hari Ini</span>
                <div class="text-xl font-black text-slate-900 mt-0.5">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Bulan ini: Rp {{ number_format($monthlyRevenue, 0, ',', '.') }}</div>
            </div>
        </div>

        <!-- Active Deliveries -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-xl font-bold shrink-0">
                <i class="fa-solid fa-motorcycle"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Kurir Sedang Antar</span>
                <div class="text-2xl font-black text-slate-900 mt-0.5">{{ $activeDeliveriesCount }} <span class="text-xs font-normal text-slate-400">pengantaran</span></div>
                <a href="{{ route('admin.deliveries.map') }}" class="text-[11px] font-bold text-cyan-600 hover:underline">Lihat di peta &rarr;</a>
            </div>
        </div>

        <!-- Monthly Net Profit -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-xl font-bold shrink-0">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Laba Bersih Bulan Ini</span>
                <div class="text-xl font-black {{ $monthlyNetProfit >= 0 ? 'text-emerald-700' : 'text-rose-700' }} mt-0.5">
                    Rp {{ number_format($monthlyNetProfit, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-slate-400 mt-0.5">Pengeluaran: Rp {{ number_format($monthlyExpenses, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <!-- Low Stock Alert Banner -->
    @if ($lowStockProducts->isNotEmpty())
        <div class="bg-amber-50 border border-amber-200 p-4 rounded-2xl flex items-center justify-between">
            <div class="flex items-center space-x-3 text-amber-800 text-xs">
                <i class="fa-solid fa-triangle-exclamation text-amber-600 text-xl"></i>
                <div>
                    <span class="font-bold text-sm">Peringatan Stok Menipis!</span>
                    <p class="mt-0.5">{{ $lowStockProducts->pluck('name')->join(', ') }} telah mencapai batas minimum stok.</p>
                </div>
            </div>
            <a href="{{ route('admin.products.index') }}" class="px-3.5 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs transition">
                Restok Sekarang
            </a>
        </div>
    @endif

    <!-- Main Grid: Recent Orders & Top Products -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Recent Orders (8 cols) -->
        <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="font-extrabold text-slate-900 text-base">Pesanan Terbaru</h2>
                    <p class="text-xs text-slate-400">Daftar pesanan masuk yang perlu diproses</p>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold text-cyan-600 hover:text-cyan-700">
                    Semua Pesanan &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-left text-xs">
                    <thead class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3">No. Pesanan</th>
                            <th class="px-5 py-3">Pelanggan</th>
                            <th class="px-5 py-3">Total</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Kurir</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($recentOrders as $ord)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-5 py-3 font-bold text-slate-900">
                                    <a href="{{ route('admin.orders.show', $ord) }}" class="hover:text-cyan-600">
                                        {{ $ord->order_number }}
                                    </a>
                                    <div class="text-[10px] text-slate-400 font-normal">{{ $ord->created_at->diffForHumans() }}</div>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="font-semibold text-slate-800">{{ $ord->recipient_name }}</div>
                                    <div class="text-[10px] text-slate-400 truncate max-w-[150px]">{{ $ord->delivery_address_text }}</div>
                                </td>
                                <td class="px-5 py-3 font-bold text-slate-900 whitespace-nowrap">
                                    Rp {{ number_format($ord->total_amount, 0, ',', '.') }}
                                    <div class="text-[10px] font-normal uppercase text-slate-400">{{ $ord->payment_method }} &bull; {{ $ord->payment_status }}</div>
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-full font-bold text-[10px] border {{ $ord->status_badge['color'] }}">
                                        {{ $ord->status_badge['label'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-slate-600 whitespace-nowrap">
                                    {{ $ord->delivery?->driver?->name ?? 'Belum ada' }}
                                </td>
                                <td class="px-5 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.orders.show', $ord) }}" class="text-xs font-bold text-cyan-600 hover:text-cyan-800 bg-cyan-50 px-2.5 py-1 rounded-lg">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                    Belum ada pesanan terbaru.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Products & Quick Links (4 cols) -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Top Selling Products -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-4">
                <h2 class="font-extrabold text-slate-900 text-sm uppercase tracking-wider flex items-center">
                    <i class="fa-solid fa-trophy text-amber-500 mr-2"></i> Produk Terlaris Bulan Ini
                </h2>

                <div class="space-y-3">
                    @forelse ($topProducts as $idx => $tp)
                        <div class="flex items-center justify-between text-xs pb-2 border-b border-slate-100 last:border-0 last:pb-0">
                            <div class="flex items-center space-x-2.5">
                                <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-[10px]">
                                    {{ $idx + 1 }}
                                </span>
                                <span class="font-bold text-slate-800 truncate max-w-[160px]">{{ $tp->product_name }}</span>
                            </div>
                            <div class="text-right">
                                <span class="font-black text-cyan-700">{{ $tp->total_qty }} unit</span>
                                <div class="text-[10px] text-slate-400">Rp {{ number_format($tp->total_sales, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-slate-400 text-xs py-4">Belum ada data penjualan bulan ini.</div>
                    @endforelse
                </div>
            </div>

            <!-- Quick Action Links -->
            <div class="bg-gradient-to-tr from-slate-900 to-slate-800 text-white rounded-2xl p-5 shadow-xs space-y-3">
                <h3 class="font-bold text-sm">Pintasan Cepat Admin</h3>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <a href="{{ route('admin.deliveries.map') }}" class="p-3 bg-slate-800/80 hover:bg-slate-700 rounded-xl text-center transition">
                        <i class="fa-solid fa-map-location-dot text-emerald-400 text-base block mb-1"></i>
                        <span>Peta Kurir</span>
                    </a>
                    <a href="{{ route('admin.products.create') }}" class="p-3 bg-slate-800/80 hover:bg-slate-700 rounded-xl text-center transition">
                        <i class="fa-solid fa-plus text-cyan-400 text-base block mb-1"></i>
                        <span>Tambah Produk</span>
                    </a>
                    <a href="{{ route('admin.finance.index') }}" class="p-3 bg-slate-800/80 hover:bg-slate-700 rounded-xl text-center transition">
                        <i class="fa-solid fa-receipt text-amber-400 text-base block mb-1"></i>
                        <span>Catat Biaya</span>
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="p-3 bg-slate-800/80 hover:bg-slate-700 rounded-xl text-center transition">
                        <i class="fa-solid fa-users text-blue-400 text-base block mb-1"></i>
                        <span>Pengguna</span>
                    </a>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
