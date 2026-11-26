<!-- Mobile Bottom Navigation (Familiar Everyday-App UX Pattern) -->
@auth
<nav class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t border-slate-200 dark:border-slate-800 px-3 py-2 flex items-center justify-around shadow-2xl safe-area-pb">
    @if (Auth::user()->isAdmin())
        <!-- Admin Navigation Items -->
        <a href="{{ route('admin.dashboard') }}" 
           class="flex flex-col items-center justify-center p-1 text-center transition {{ request()->routeIs('admin.dashboard') ? 'text-cyan-600 dark:text-cyan-400 font-bold' : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white' }}">
            <i class="fa-solid fa-chart-pie text-base"></i>
            <span class="text-[10px] mt-1">{{ __('Dashboard') }}</span>
        </a>
        <a href="{{ route('admin.orders.index') }}" 
           class="flex flex-col items-center justify-center p-1 text-center transition {{ request()->routeIs('admin.orders.*') ? 'text-cyan-600 dark:text-cyan-400 font-bold' : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white' }}">
            <i class="fa-solid fa-boxes-stacked text-base"></i>
            <span class="text-[10px] mt-1">{{ __('Pesanan') }}</span>
        </a>
        <a href="{{ route('admin.deliveries.map') }}" 
           class="flex flex-col items-center justify-center p-1 text-center transition {{ request()->routeIs('admin.deliveries.map') ? 'text-cyan-600 dark:text-cyan-400 font-bold' : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white' }}">
            <i class="fa-solid fa-satellite-dish text-base"></i>
            <span class="text-[10px] mt-1">{{ __('Radar') }}</span>
        </a>
        <a href="{{ route('admin.finance.index') }}" 
           class="flex flex-col items-center justify-center p-1 text-center transition {{ request()->routeIs('admin.finance.*') ? 'text-cyan-600 dark:text-cyan-400 font-bold' : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white' }}">
            <i class="fa-solid fa-receipt text-base"></i>
            <span class="text-[10px] mt-1">{{ __('Keuangan') }}</span>
        </a>
    @else
        <!-- Customer Navigation Items -->
        <a href="{{ route('customer.dashboard') }}" 
           class="flex flex-col items-center justify-center p-1 text-center transition {{ request()->routeIs('customer.dashboard') ? 'text-cyan-600 dark:text-cyan-400 font-bold' : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white' }}">
            <i class="fa-solid fa-house text-base"></i>
            <span class="text-[10px] mt-1">{{ __('Beranda') }}</span>
        </a>
        <a href="{{ route('customer.orders.create') }}" 
           class="flex flex-col items-center justify-center p-1 text-center transition {{ request()->routeIs('customer.orders.create') ? 'text-cyan-600 dark:text-cyan-400 font-bold' : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white' }}">
            <i class="fa-solid fa-cart-plus text-base"></i>
            <span class="text-[10px] mt-1">{{ __('Pesan Air') }}</span>
        </a>
        <a href="{{ route('customer.orders.index') }}" 
           class="flex flex-col items-center justify-center p-1 text-center transition {{ request()->routeIs('customer.orders.index') || request()->routeIs('customer.orders.show') ? 'text-cyan-600 dark:text-cyan-400 font-bold' : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white' }}">
            <i class="fa-solid fa-box-archive text-base"></i>
            <span class="text-[10px] mt-1">{{ __('Pesanan') }}</span>
        </a>
        
        <a href="{{ route('customer.addresses.index') }}" 
           class="flex flex-col items-center justify-center p-1 text-center transition {{ request()->routeIs('customer.addresses.*') ? 'text-cyan-600 dark:text-cyan-400 font-bold' : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white' }}">
            <i class="fa-solid fa-location-dot text-base"></i>
            <span class="text-[10px] mt-1">{{ __('Alamat') }}</span>
        </a>
        <a href="{{ route('customer.store-profile') }}" 
           class="flex flex-col items-center justify-center p-1 text-center transition {{ request()->routeIs('customer.store-profile') ? 'text-cyan-600 dark:text-cyan-400 font-bold' : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white' }}">
            <i class="fa-solid fa-store text-base"></i>
            <span class="text-[10px] mt-1">{{ __('Toko') }}</span>
        </a>
        <a href="{{ route('customer.profile') }}" 
           class="flex flex-col items-center justify-center p-1 text-center transition {{ request()->routeIs('customer.profile') ? 'text-cyan-600 dark:text-cyan-400 font-bold' : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white' }}">
            <i class="fa-solid fa-user text-base"></i>
            <span class="text-[10px] mt-1">{{ __('Profil') }}</span>
        </a>
    @endif
</nav>
@endauth
