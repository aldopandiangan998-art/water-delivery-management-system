@extends('layouts.admin')

@section('page_title', __('Manajemen Pengguna & Peran'))

@section('content')
<div class="space-y-6"
     x-data="{ viewMode: localStorage.getItem('view_mode_admin_users') || 'list' }"
     x-effect="localStorage.setItem('view_mode_admin_users', viewMode)">

    <x-card class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Manajemen Akun & Hak Akses') }}</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Kelola daftar pengguna, tetapkan peran (Admin atau Pelanggan), dan status akun.') }}</p>
        </div>
        <div class="flex items-center space-x-2">
            <x-grid-list-toggle model="viewMode" />
        </div>
    </x-card>

    <!-- List View (Table) -->
    <div x-show="viewMode === 'list'" x-cloak>
        <x-card class="p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800 text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5">{{ __('Nama & Kontak') }}</th>
                            <th class="px-5 py-3.5">{{ __('Email') }}</th>
                            <th class="px-5 py-3.5">{{ __('Peran Akun') }}</th>
                            <th class="px-5 py-3.5">{{ __('Status Akun') }}</th>
                            <th class="px-5 py-3.5">{{ __('Terdaftar Sejak') }}</th>
                            <th class="px-5 py-3.5 text-right">{{ __('Ubah Peran & Akses') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                        @forelse ($users as $u)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                <td class="px-5 py-3.5">
                                    <div class="font-bold text-slate-900 dark:text-white text-sm flex items-center">
                                        <div class="w-7 h-7 rounded-full bg-cyan-100 text-cyan-700 dark:bg-cyan-900 dark:text-cyan-300 font-bold flex items-center justify-center text-xs mr-2">
                                            {{ strtoupper(substr($u->name, 0, 1)) }}
                                        </div>
                                        {{ $u->name }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5 ml-9">{{ $u->phone ?? '-' }}</div>
                                </td>

                                <td class="px-5 py-3.5 font-mono text-slate-600 dark:text-slate-400">
                                    {{ $u->email }}
                                </td>

                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                                        {{ $u->role === 'admin' ? 'bg-indigo-100 dark:bg-indigo-950/40 text-indigo-800 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700' }}">
                                        {{ $u->role === 'admin' ? 'Admin' : 'Pelanggan' }}
                                    </span>
                                </td>

                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $u->is_active ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800' }}">
                                        {{ $u->is_active ? __('Aktif') : __('Dinonaktifkan') }}
                                    </span>
                                </td>

                                <td class="px-5 py-3.5 text-slate-400 whitespace-nowrap">
                                    {{ $u->created_at->format('d M Y') }}
                                </td>

                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end space-x-2">
                                        @if ($u->role === 'customer')
                                            <a href="{{ route('admin.addresses.index', ['user_id' => $u->id]) }}" 
                                               class="text-xs font-semibold px-2 py-1 rounded-lg text-cyan-700 dark:text-cyan-400 bg-cyan-50 dark:bg-cyan-950/40 hover:bg-cyan-100 transition"
                                               title="{{ __('Kelola Alamat Pelanggan') }}">
                                                <i class="fa-solid fa-location-dot mr-1"></i> {{ __('Alamat') }}
                                            </a>
                                        @endif

                                        <!-- Role update dropdown form -->
                                        <form method="POST" action="{{ route('admin.users.role', $u) }}" class="inline-flex items-center">
                                            @csrf
                                            @method('PUT')
                                            <select name="role" onchange="this.form.submit()" class="text-xs border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white rounded-lg p-1 font-medium cursor-pointer">
                                                <option value="customer" {{ $u->role === 'customer' ? 'selected' : '' }}>{{ __('Pelanggan') }}</option>
                                                <option value="admin" {{ $u->role === 'admin' ? 'selected' : '' }}>{{ __('Admin') }}</option>
                                            </select>
                                        </form>

                                        <!-- Toggle Active status -->
                                        @if ($u->id !== Auth::id())
                                            <form method="POST" action="{{ route('admin.users.toggle-active', $u) }}" class="inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="text-xs font-semibold px-2 py-1 rounded-lg {{ $u->is_active ? 'text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40' : 'text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/40' }} cursor-pointer">
                                                    {{ $u->is_active ? __('Nonaktifkan') : __('Aktifkan') }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                                    <x-empty-state 
                                        icon="fa-solid fa-users" 
                                        :title="__('Tidak Ada Data Pengguna')" 
                                        :description="__('Belum ada pengguna terdaftar di dalam sistem.')"
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
        @forelse ($users as $u)
            <x-card class="p-5 flex flex-col justify-between space-y-4 hover:shadow-md transition">
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center space-x-2.5">
                            <div class="w-9 h-9 rounded-full bg-cyan-100 text-cyan-700 dark:bg-cyan-900 dark:text-cyan-300 font-bold flex items-center justify-center text-sm shrink-0">
                                {{ strtoupper(substr($u->name, 0, 1)) }}
                            </div>
                            <div>
                                <h3 class="font-bold text-sm text-slate-900 dark:text-white">{{ $u->name }}</h3>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $u->email }}</div>
                            </div>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider shrink-0
                            {{ $u->role === 'admin' ? 'bg-indigo-100 dark:bg-indigo-950/40 text-indigo-800 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700' }}">
                            {{ $u->role === 'admin' ? 'Admin' : 'Pelanggan' }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs pt-1">
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-100 dark:border-slate-800">
                            <div class="text-[10px] uppercase font-bold text-slate-400">{{ __('No. Telepon') }}</div>
                            <div class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5 truncate">{{ $u->phone ?? '-' }}</div>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-100 dark:border-slate-800">
                            <div class="text-[10px] uppercase font-bold text-slate-400">{{ __('Status') }}</div>
                            <span class="font-bold text-xs {{ $u->is_active ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ $u->is_active ? __('Aktif') : __('Nonaktif') }}
                            </span>
                        </div>
                    </div>

                    <div class="text-[11px] text-slate-400">
                        <i class="fa-regular fa-calendar mr-1"></i>{{ __('Terdaftar') }}: {{ $u->created_at->format('d M Y') }}
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
                    <div>
                        @if ($u->role === 'customer')
                            <a href="{{ route('admin.addresses.index', ['user_id' => $u->id]) }}" 
                               class="text-xs font-semibold px-2.5 py-1.5 rounded-xl text-cyan-700 dark:text-cyan-400 bg-cyan-50 dark:bg-cyan-950/40 hover:bg-cyan-100 transition inline-flex items-center">
                                <i class="fa-solid fa-location-dot mr-1"></i> {{ __('Alamat') }}
                            </a>
                        @endif
                    </div>

                    <div class="flex items-center space-x-2">
                        <!-- Role change -->
                        <form method="POST" action="{{ route('admin.users.role', $u) }}" class="inline-flex items-center">
                            @csrf
                            @method('PUT')
                            <select name="role" onchange="this.form.submit()" class="text-xs border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white rounded-xl p-1.5 font-medium cursor-pointer">
                                <option value="customer" {{ $u->role === 'customer' ? 'selected' : '' }}>{{ __('Pelanggan') }}</option>
                                <option value="admin" {{ $u->role === 'admin' ? 'selected' : '' }}>{{ __('Admin') }}</option>
                            </select>
                        </form>

                        @if ($u->id !== Auth::id())
                            <form method="POST" action="{{ route('admin.users.toggle-active', $u) }}" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="text-xs font-semibold px-2.5 py-1.5 rounded-xl {{ $u->is_active ? 'text-rose-600 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100' : 'text-emerald-600 bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100' }} cursor-pointer">
                                    {{ $u->is_active ? __('Nonaktifkan') : __('Aktifkan') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </x-card>
        @empty
            <div class="col-span-full">
                <x-card class="p-12 text-center">
                    <x-empty-state 
                        icon="fa-solid fa-users" 
                        :title="__('Tidak Ada Data Pengguna')" 
                        :description="__('Belum ada pengguna terdaftar di dalam sistem.')"
                    />
                </x-card>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800">
        {{ $users->links() }}
    </div>

</div>
@endsection
