@props([
    'items' => [],
])

@php
    $currentRoute = request()->route() ? request()->route()->getName() : '';
    $user = auth()->user();
    
    // Determine if customer context or admin context
    $isCustomer = str_starts_with($currentRoute, 'customer.') || ($user && $user->role === 'customer');

    if ($isCustomer) {
        $computedItems = [
            [
                'title' => __('Beranda Member'),
                'url' => route('customer.dashboard'),
                'icon' => 'fa-solid fa-house',
            ]
        ];
    } else {
        $computedItems = [
            [
                'title' => __('Admin'),
                'url' => route('admin.dashboard'),
                'icon' => 'fa-solid fa-shield-halved',
            ]
        ];
    }

    // If explicit items were passed as props, use them
    if (!empty($items)) {
        foreach ($items as $item) {
            $computedItems[] = $item;
        }
    } else {
        // Auto-detect based on current route
        switch ($currentRoute) {
            // ADMIN ROUTES
            case 'admin.dashboard':
                $computedItems[] = ['title' => __('Dashboard')];
                break;

            case 'admin.orders.index':
                $computedItems[] = ['title' => __('Kelola Pesanan Air')];
                break;

            case 'admin.orders.show':
                $computedItems[] = ['title' => __('Pesanan Air'), 'url' => route('admin.orders.index')];
                $computedItems[] = ['title' => __('Rincian Pesanan')];
                break;

            case 'admin.products.index':
                $computedItems[] = ['title' => __('Katalog Produk & Stok')];
                break;

            case 'admin.products.create':
                $computedItems[] = ['title' => __('Katalog Produk'), 'url' => route('admin.products.index')];
                $computedItems[] = ['title' => __('Tambah Produk Baru')];
                break;

            case 'admin.products.edit':
                $computedItems[] = ['title' => __('Katalog Produk'), 'url' => route('admin.products.index')];
                $computedItems[] = ['title' => __('Edit Produk')];
                break;

            case 'admin.products.show':
                $computedItems[] = ['title' => __('Katalog Produk'), 'url' => route('admin.products.index')];
                $computedItems[] = ['title' => __('Detail Produk')];
                break;

            case 'admin.deliveries.index':
                $computedItems[] = ['title' => __('Tugas Pengantaran')];
                break;

            case 'admin.deliveries.map':
                $computedItems[] = ['title' => __('Pengantaran'), 'url' => route('admin.deliveries.index')];
                $computedItems[] = ['title' => __('Peta Pengantaran & GPS')];
                break;

            case 'admin.deliveries.show':
                $computedItems[] = ['title' => __('Pengantaran'), 'url' => route('admin.deliveries.index')];
                $computedItems[] = ['title' => __('Detail Pengantaran')];
                break;

            case 'admin.finance.index':
                $computedItems[] = ['title' => __('Laporan Keuangan & Biaya')];
                break;

            case 'admin.users.index':
                $computedItems[] = ['title' => __('Manajemen Pengguna')];
                break;

            case 'admin.service-areas.index':
                $computedItems[] = ['title' => __('Area Layanan Pengantaran')];
                break;

            case 'admin.service-areas.create':
                $computedItems[] = ['title' => __('Area Layanan'), 'url' => route('admin.service-areas.index')];
                $computedItems[] = ['title' => __('Tambah Area Baru')];
                break;

            case 'admin.service-areas.edit':
                $computedItems[] = ['title' => __('Area Layanan'), 'url' => route('admin.service-areas.index')];
                $computedItems[] = ['title' => __('Edit Area Layanan')];
                break;

            case 'admin.reviews.index':
                $computedItems[] = ['title' => __('Ulasan & Rating')];
                break;

            case 'admin.addresses.index':
                $computedItems[] = ['title' => __('Buku Alamat Pelanggan')];
                break;

            // CUSTOMER ROUTES
            case 'customer.dashboard':
                $computedItems[] = ['title' => __('Dashboard')];
                break;

            case 'customer.orders.index':
                $computedItems[] = ['title' => __('Riwayat Pesanan Saya')];
                break;

            case 'customer.orders.show':
                $computedItems[] = ['title' => __('Riwayat Pesanan'), 'url' => route('customer.orders.index')];
                $computedItems[] = ['title' => __('Rincian Pesanan')];
                break;

            case 'customer.orders.track':
                $computedItems[] = ['title' => __('Riwayat Pesanan'), 'url' => route('customer.orders.index')];
                $computedItems[] = ['title' => __('Lacak Pengantaran Live')];
                break;

            case 'customer.addresses.index':
                $computedItems[] = ['title' => __('Buku Alamat Pengantaran')];
                break;

            case 'customer.profile':
                $computedItems[] = ['title' => __('Pengaturan Profil & Akun')];
                break;

            case 'customer.store-profile':
                $computedItems[] = ['title' => __('Profil & Lokasi Toko')];
                break;

            default:
                // General fallback parser based on URI segments
                $segments = request()->segments();
                if (!empty($segments) && in_array($segments[0], ['admin', 'customer'])) {
                    array_shift($segments);
                }
                foreach ($segments as $index => $seg) {
                    if (is_numeric($seg)) {
                        $computedItems[] = ['title' => '#' . $seg];
                    } else {
                        $computedItems[] = ['title' => ucwords(str_replace(['-', '_'], ' ', $seg))];
                    }
                }
                break;
        }
    }
@endphp

<nav aria-label="{{ __('Breadcrumb') }}" class="flex items-center overflow-x-auto py-1">
    <ol class="inline-flex items-center space-x-1.5 sm:space-x-2 text-xs">
        @foreach ($computedItems as $index => $item)
            @php
                $isLast = $loop->last;
                $hasUrl = !empty($item['url']) && !$isLast;
            @endphp

            <li class="inline-flex items-center">
                @if ($index > 0)
                    <i class="fa-solid fa-chevron-right text-[9px] text-slate-400 dark:text-slate-600 mx-1.5 shrink-0"></i>
                @endif

                @if ($hasUrl)
                    <a href="{{ $item['url'] }}" 
                       class="inline-flex items-center font-semibold text-slate-600 dark:text-slate-400 hover:text-cyan-600 dark:hover:text-cyan-400 transition-colors duration-150">
                        @if (!empty($item['icon']))
                            <i class="{{ $item['icon'] }} mr-1.5 text-xs text-cyan-600 dark:text-cyan-400"></i>
                        @endif
                        <span class="truncate max-w-[150px] sm:max-w-none">{{ $item['title'] }}</span>
                    </a>
                @else
                    <span class="inline-flex items-center font-bold text-slate-900 dark:text-white" aria-current="page">
                        @if (!empty($item['icon']) && $index === 0)
                            <i class="{{ $item['icon'] }} mr-1.5 text-xs text-cyan-600 dark:text-cyan-400"></i>
                        @endif
                        <span class="truncate max-w-[180px] sm:max-w-none">{{ $item['title'] }}</span>
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
