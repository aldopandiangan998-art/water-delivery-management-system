@extends('layouts.admin')

@section('page_title', __('Ringkasan Bisnis & Dashboard'))

@section('content')
<div class="space-y-6">

    <!-- Top KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
        <!-- Today Orders -->
        <x-card class="p-5 flex items-center space-x-4 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-2xl bg-cyan-100 dark:bg-cyan-950 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-xl font-bold shrink-0">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Pesanan Hari Ini') }}</span>
                <div class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">{{ $todayOrdersCount }} <span class="text-xs font-normal text-slate-400">{{ __('order') }}</span></div>
                <div class="text-[11px] text-amber-600 dark:text-amber-400 font-bold mt-0.5">{{ $pendingOrdersCount }} {{ __('perlu dikonfirmasi') }}</div>
            </div>
        </x-card>

        <!-- Today Revenue -->
        <x-card class="p-5 flex items-center space-x-4 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl font-bold shrink-0">
                <i class="fa-solid fa-money-bill-wave"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Omzet Lunas Hari Ini') }}</span>
                <div class="text-xl font-black text-slate-900 dark:text-white mt-0.5">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">{{ __('Bulan ini') }}: Rp {{ number_format($monthlyRevenue, 0, ',', '.') }}</div>
            </div>
        </x-card>

        <!-- Monthly Net Profit -->
        <x-card class="p-5 flex items-center space-x-4 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-2xl bg-indigo-100 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl font-bold shrink-0">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Laba Bersih Bulan Ini') }}</span>
                <div class="text-xl font-black {{ $monthlyNetProfit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} mt-0.5">
                    Rp {{ number_format($monthlyNetProfit, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-slate-400 mt-0.5">{{ __('Pengeluaran') }}: Rp {{ number_format($monthlyExpenses, 0, ',', '.') }}</div>
            </div>
        </x-card>
    </div>

    <!-- Low Stock Alert Banner -->
    @if ($lowStockProducts->isNotEmpty())
        <div class="bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 p-4 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
            <div class="flex items-start sm:items-center space-x-3 text-amber-800 dark:text-amber-200 text-xs">
                <i class="fa-solid fa-triangle-exclamation text-amber-600 text-xl shrink-0 mt-0.5 sm:mt-0"></i>
                <div>
                    <span class="font-bold text-sm">{{ __('Peringatan Stok Menipis!') }}</span>
                    <p class="mt-0.5 text-amber-700 dark:text-amber-300">{{ $lowStockProducts->pluck('name')->join(', ') }} {{ __('telah mencapai batas minimum stok.') }}</p>
                </div>
            </div>
            <x-button :href="route('admin.products.index')" variant="secondary" size="sm" class="shrink-0">
                {{ __('Restok Sekarang') }}
            </x-button>
        </div>
    @endif

    <!-- Business Performance Chart Section -->
    <x-card class="p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="font-black text-slate-900 dark:text-white text-base tracking-tight flex items-center">
                    <i class="fa-solid fa-chart-column text-cyan-500 mr-2"></i>
                    {{ __('Performa Keuangan & Penjualan Bulan Ini') }}
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">{{ __('Komparasi omzet lunas, biaya operasional, dan produk terlaris') }}</p>
            </div>
            <div class="flex items-center space-x-2 text-xs">
                <span class="inline-flex items-center px-2 py-1 rounded-md bg-cyan-50 dark:bg-cyan-950/60 text-cyan-700 dark:text-cyan-300 font-bold border border-cyan-200 dark:border-cyan-800">
                    <i class="fa-regular fa-calendar mr-1"></i> {{ now()->translatedFormat('F Y') }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 pt-5">
            <!-- Left Chart: Financial Ratio (Doughnut / Bar) -->
            <div class="lg:col-span-5 flex flex-col justify-between">
                <div class="relative h-56 sm:h-64 flex items-center justify-center">
                    <canvas id="financeChart"></canvas>
                </div>
                <div class="grid grid-cols-3 gap-2 pt-3 text-center text-xs">
                    <div class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/40">
                        <span class="text-[10px] uppercase font-bold text-emerald-700 dark:text-emerald-300 block">{{ __('Omzet') }}</span>
                        <span class="font-extrabold text-emerald-900 dark:text-emerald-200 text-xs">Rp {{ number_format($monthlyRevenue, 0, ',', '.') }}</span>
                    </div>
                    <div class="p-2 rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-900/40">
                        <span class="text-[10px] uppercase font-bold text-rose-700 dark:text-rose-300 block">{{ __('Biaya') }}</span>
                        <span class="font-extrabold text-rose-900 dark:text-rose-200 text-xs">Rp {{ number_format($monthlyExpenses, 0, ',', '.') }}</span>
                    </div>
                    <div class="p-2 rounded-xl bg-cyan-50 dark:bg-cyan-950/30 border border-cyan-100 dark:border-cyan-900/40">
                        <span class="text-[10px] uppercase font-bold text-cyan-700 dark:text-cyan-300 block">{{ __('Laba') }}</span>
                        <span class="font-extrabold text-cyan-900 dark:text-cyan-200 text-xs">Rp {{ number_format($monthlyNetProfit, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Right Chart: Top Selling Products Bar Chart -->
            <div class="lg:col-span-7">
                <div class="relative h-64 sm:h-72">
                    <canvas id="productsChart"></canvas>
                </div>
            </div>
        </div>
    </x-card>

    <!-- Main Grid: Recent Orders & Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Recent Orders (8 cols) -->
        <x-card class="lg:col-span-8 p-0 overflow-hidden">
            <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h2 class="font-black text-slate-900 dark:text-white text-base tracking-tight">{{ __('Pesanan Terbaru') }}</h2>
                    <p class="text-xs text-slate-400 mt-0.5">{{ __('Daftar pesanan masuk yang perlu diproses') }}</p>
                </div>
                <x-button :href="route('admin.orders.index')" variant="ghost" size="sm" iconRight="fa-solid fa-arrow-right">
                    {{ __('Semua Pesanan') }}
                </x-button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800 text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-950 text-slate-400 font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3">{{ __('No. Pesanan') }}</th>
                            <th class="px-5 py-3">{{ __('Pelanggan') }}</th>
                            <th class="px-5 py-3">{{ __('Total') }}</th>
                            <th class="px-5 py-3">{{ __('Status') }}</th>
                            <th class="px-5 py-3 text-right">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                        @forelse ($recentOrders as $ord)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                <td class="px-5 py-3.5 font-bold text-slate-900 dark:text-white">
                                    <a href="{{ route('admin.orders.show', $ord) }}" class="hover:text-cyan-600 transition">
                                        {{ $ord->order_number }}
                                    </a>
                                    <div class="text-[10px] text-slate-400 font-normal mt-0.5">{{ $ord->created_at->diffForHumans() }}</div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $ord->recipient_name }}</div>
                                    <div class="text-[10px] text-slate-400 truncate max-w-[150px]">{{ $ord->delivery_address_text }}</div>
                                </td>
                                <td class="px-5 py-3.5 font-bold text-slate-900 dark:text-white whitespace-nowrap">
                                    Rp {{ number_format($ord->total_amount, 0, ',', '.') }}
                                    <div class="text-[10px] font-normal uppercase text-slate-400 mt-0.5">{{ $ord->payment_method }} &bull; {{ $ord->payment_status }}</div>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <x-badge variant="neutral" class="{{ $ord->status_badge['color'] }}">
                                        {{ __($ord->status_badge['label']) }}
                                    </x-badge>
                                </td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <x-button :href="route('admin.orders.show', $ord)" variant="secondary" size="sm">
                                        {{ __('Detail') }}
                                    </x-button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-slate-400">
                                    {{ __('Belum ada pesanan terbaru.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        <!-- Top Products & Quick Links (4 cols) -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Top Selling Products -->
            <x-card class="p-5 space-y-4">
                <h2 class="font-black text-slate-900 dark:text-white text-sm uppercase tracking-wider flex items-center">
                    <i class="fa-solid fa-trophy text-amber-500 mr-2"></i> {{ __('Produk Terlaris Bulan Ini') }}
                </h2>

                <div class="space-y-3">
                    @forelse ($topProducts as $idx => $tp)
                        <div class="flex items-center justify-between text-xs pb-2.5 border-b border-slate-100 dark:border-slate-800 last:border-0 last:pb-0">
                            <div class="flex items-center space-x-2.5">
                                <span class="w-6 h-6 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold flex items-center justify-center text-[11px] shrink-0">
                                    {{ $idx + 1 }}
                                </span>
                                <span class="font-bold text-slate-800 dark:text-slate-200 truncate max-w-[150px]">{{ $tp->product_name }}</span>
                            </div>
                            <div class="text-right">
                                <span class="font-black text-cyan-600 dark:text-cyan-400">{{ $tp->total_qty }} {{ __('unit') }}</span>
                                <div class="text-[10px] text-slate-400">Rp {{ number_format($tp->total_sales, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-slate-400 text-xs py-4">{{ __('Belum ada data penjualan bulan ini.') }}</div>
                    @endforelse
                </div>
            </x-card>

            <!-- Quick Action Links -->
            <div class="bg-gradient-to-tr from-slate-900 via-slate-800 to-cyan-950 text-white rounded-2xl p-5 shadow-md space-y-3 border border-slate-800">
                <h3 class="font-bold text-sm">{{ __('Pintasan Cepat') }}</h3>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <a href="{{ route('admin.deliveries.map') }}" class="p-3 bg-white/10 hover:bg-white/20 rounded-xl text-center transition backdrop-blur-xs">
                        <i class="fa-solid fa-map-location-dot text-emerald-400 text-base block mb-1"></i>
                        <span>{{ __('Peta Pengantaran') }}</span>
                    </a>
                    <a href="{{ route('admin.products.create') }}" class="p-3 bg-white/10 hover:bg-white/20 rounded-xl text-center transition backdrop-blur-xs">
                        <i class="fa-solid fa-plus text-cyan-400 text-base block mb-1"></i>
                        <span>{{ __('Tambah Produk') }}</span>
                    </a>
                    <a href="{{ route('admin.finance.index') }}" class="p-3 bg-white/10 hover:bg-white/20 rounded-xl text-center transition backdrop-blur-xs">
                        <i class="fa-solid fa-receipt text-amber-400 text-base block mb-1"></i>
                        <span>{{ __('Catat Biaya') }}</span>
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="p-3 bg-white/10 hover:bg-white/20 rounded-xl text-center transition backdrop-blur-xs">
                        <i class="fa-solid fa-users text-blue-400 text-base block mb-1"></i>
                        <span>{{ __('Pengguna') }}</span>
                    </a>
                </div>
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Chart === 'undefined') return;

        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#cbd5e1' : '#64748b';
        const gridColor = isDark ? 'rgba(51, 65, 85, 0.4)' : 'rgba(226, 232, 240, 0.8)';

        // 1. Finance Doughnut Chart
        const financeCtx = document.getElementById('financeChart');
        if (financeCtx) {
            new Chart(financeCtx, {
                type: 'doughnut',
                data: {
                    labels: ['{{ __("Omzet Lunas") }}', '{{ __("Biaya Operasional") }}', '{{ __("Laba Bersih") }}'],
                    datasets: [{
                        data: [
                            {{ max(0, $monthlyRevenue) }},
                            {{ max(0, $monthlyExpenses) }},
                            {{ max(0, $monthlyNetProfit) }}
                        ],
                        backgroundColor: ['#10b981', '#f43f5e', '#0891b2'],
                        borderWidth: 2,
                        borderColor: isDark ? '#0f172a' : '#ffffff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: textColor,
                                font: { size: 11, weight: 'bold' },
                                boxWidth: 12
                            }
                        }
                    },
                    cutout: '68%'
                }
            });
        }

        // 2. Products Bar Chart
        const productsCtx = document.getElementById('productsChart');
        if (productsCtx) {
            new Chart(productsCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($topProducts->pluck('product_name')) !!},
                    datasets: [{
                        label: '{{ __("Unit Terjual") }}',
                        data: {!! json_encode($topProducts->pluck('total_qty')) !!},
                        backgroundColor: '#0891b2',
                        borderRadius: 8,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            ticks: { color: textColor, font: { size: 10 } },
                            grid: { display: false }
                        },
                        y: {
                            ticks: { color: textColor, font: { size: 10 } },
                            grid: { color: gridColor },
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
@endsection
