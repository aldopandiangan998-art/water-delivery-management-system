<!-- Notification Bell with Dropdown Menu -->
@php
    $unreadCount = 0;
    $notifications = [];

    if (auth()->check()) {
        if (auth()->user()->role === 'admin') {
            $pendingOrders = \App\Models\Order::where('status', 'pending')->count();
            if ($pendingOrders > 0) {
                $notifications[] = [
                    'icon' => 'fa-solid fa-cart-shopping text-cyan-500',
                    'title' => __(':count pesanan baru perlu konfirmasi', ['count' => $pendingOrders]),
                    'url' => route('admin.orders.index', ['status' => 'pending']),
                    'time' => __('Hari ini'),
                ];
                $unreadCount += $pendingOrders;
            }

            $lowStock = \App\Models\Product::where('tracks_inventory', true)
                ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                ->count();
            if ($lowStock > 0) {
                $notifications[] = [
                    'icon' => 'fa-solid fa-triangle-exclamation text-amber-500',
                    'title' => __(':count produk stok gudang menipis', ['count' => $lowStock]),
                    'url' => route('admin.products.index'),
                    'time' => __('Perlu Restok'),
                ];
                $unreadCount += $lowStock;
            }
        } else {
            $activeDeliveries = \App\Models\Order::where('user_id', auth()->id())
                ->whereIn('status', ['processing', 'ready_for_delivery'])
                ->count();
            if ($activeDeliveries > 0) {
                $notifications[] = [
                    'icon' => 'fa-solid fa-truck-fast text-emerald-500',
                    'title' => __(':count pesanan air Anda sedang dalam pengantaran', ['count' => $activeDeliveries]),
                    'url' => route('customer.orders.index'),
                    'time' => __('Sedang Antar'),
                ];
                $unreadCount += $activeDeliveries;
            }
        }
    }
@endphp

<div x-data="{ open: false }" class="relative">
    <button 
        type="button" 
        @click="open = !open" 
        class="relative w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center transition cursor-pointer"
        aria-label="{{ __('Notifikasi') }}"
    >
        <i class="fa-regular fa-bell text-sm"></i>
        @if ($unreadCount > 0)
            <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-rose-500 text-white text-[9px] font-black flex items-center justify-center animate-pulse">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div 
        x-show="open" 
        x-cloak 
        @click.away="open = false" 
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
        class="absolute right-0 mt-2 w-80 rounded-2xl bg-white dark:bg-slate-900 shadow-2xl border border-slate-100 dark:border-slate-800 py-3 z-50 overflow-hidden"
        style="display: none;"
    >
        <div class="px-4 pb-2 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <span class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Notifikasi') }}</span>
            <span class="text-[10px] font-bold text-cyan-600 dark:text-cyan-400">{{ $unreadCount }} {{ __('baru') }}</span>
        </div>

        <div class="divide-y divide-slate-100 dark:divide-slate-800 max-h-64 overflow-y-auto">
            @forelse ($notifications as $notif)
                <a href="{{ $notif['url'] }}" class="p-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 flex items-start space-x-3 transition block">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-sm shrink-0 mt-0.5">
                        <i class="{{ $notif['icon'] }}"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-slate-800 dark:text-slate-200 line-clamp-2">{{ $notif['title'] }}</p>
                        <span class="text-[10px] text-slate-400 mt-0.5 block">{{ $notif['time'] }}</span>
                    </div>
                </a>
            @empty
                <div class="py-8 text-center text-xs text-slate-400">
                    <i class="fa-regular fa-bell-slash text-2xl mb-2 text-slate-300 dark:text-slate-600 block"></i>
                    {{ __('Tidak ada notifikasi baru.') }}
                </div>
            @endforelse
        </div>
    </div>
</div>
