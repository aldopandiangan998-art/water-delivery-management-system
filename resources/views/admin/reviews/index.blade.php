@extends('layouts.admin')

@section('page_title', __('Moderasi Ulasan Pelanggan'))

@section('content')
<div class="space-y-6"
     x-data="{ viewMode: localStorage.getItem('view_mode_admin_reviews') || 'list' }"
     x-effect="localStorage.setItem('view_mode_admin_reviews', viewMode)">

    <x-card class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Ulasan & Testimoni Pelanggan') }}</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Moderasi ulasan yang akan ditampilkan pada halaman depan / beranda publik.') }}</p>
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
                            <th class="px-5 py-3.5">{{ __('Pelanggan & Tanggal') }}</th>
                            <th class="px-5 py-3.5">{{ __('No. Pesanan') }}</th>
                            <th class="px-5 py-3.5">{{ __('Rating Bintang') }}</th>
                            <th class="px-5 py-3.5">{{ __('Isi Ulasan / Komentar') }}</th>
                            <th class="px-5 py-3.5">{{ __('Status Tampil') }}</th>
                            <th class="px-5 py-3.5 text-right">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                        @forelse ($reviews as $rev)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                <td class="px-5 py-3.5">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $rev->user->name }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $rev->created_at->format('d M Y, H:i') }} WIB</div>
                                </td>

                                <td class="px-5 py-3.5 whitespace-nowrap font-mono font-bold text-cyan-600 dark:text-cyan-400">
                                    <a href="{{ route('admin.orders.show', $rev->order) }}" class="hover:underline">
                                        {{ $rev->order->order_number }}
                                    </a>
                                </td>

                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="flex items-center text-amber-400 text-xs">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i class="fa-solid fa-star {{ $i <= $rev->rating ? 'text-amber-400' : 'text-slate-200 dark:text-slate-700' }}"></i>
                                        @endfor
                                        <span class="text-slate-700 dark:text-slate-300 font-bold ml-1.5">({{ $rev->rating }})</span>
                                    </div>
                                </td>

                                <td class="px-5 py-3.5 text-slate-800 dark:text-slate-200 leading-relaxed italic max-w-sm">
                                    "{{ $rev->comment }}"
                                </td>

                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 rounded-full font-bold text-[10px] {{ $rev->is_approved ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }}">
                                        {{ $rev->is_approved ? __('Tampil di Web') : __('Disembunyikan') }}
                                    </span>
                                </td>

                                <td class="px-5 py-3.5 text-right whitespace-nowrap space-x-2">
                                    <form method="POST" action="{{ route('admin.reviews.toggle', $rev) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs font-bold px-2.5 py-1 rounded-lg {{ $rev->is_approved ? 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' : 'bg-emerald-600 text-white hover:bg-emerald-700' }} cursor-pointer">
                                            {{ $rev->is_approved ? __('Sembunyikan') : __('Tampilkan') }}
                                        </button>
                                    </form>

                                    <form id="delete-review-{{ $rev->id }}" method="POST" action="{{ route('admin.reviews.destroy', $rev) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" 
                                                @click="$dispatch('open-confirm-modal', {
                                                    form: 'delete-review-{{ $rev->id }}',
                                                    title: '{{ __('Hapus Ulasan Pelanggan') }}',
                                                    message: '{{ __('Apakah Anda yakin ingin menghapus ulasan dari pelanggan') }} {{ addslashes($rev->order?->recipient_name ?? 'Pelanggan') }}?'
                                                })"
                                                class="text-rose-500 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300 text-xs font-semibold p-1 cursor-pointer"
                                                title="{{ __('Hapus Ulasan') }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                                    <x-empty-state 
                                        icon="fa-regular fa-star" 
                                        :title="__('Belum Ada Ulasan')" 
                                        :description="__('Ulasan dari pelanggan akan muncul di sini untuk dimoderasi.')"
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
        @forelse ($reviews as $rev)
            <x-card class="p-5 flex flex-col justify-between space-y-4 hover:shadow-md transition">
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h3 class="font-bold text-sm text-slate-900 dark:text-white">{{ $rev->user->name }}</h3>
                            <div class="text-[10px] text-slate-400">{{ $rev->created_at->format('d M Y, H:i') }} WIB</div>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full font-bold text-[10px] shrink-0 {{ $rev->is_approved ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }}">
                            {{ $rev->is_approved ? __('Tampil') : __('Tersembunyi') }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-xs">
                        <div class="flex text-amber-400">
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="fa-solid fa-star {{ $i <= $rev->rating ? 'text-amber-400' : 'text-slate-200 dark:text-slate-700' }}"></i>
                            @endfor
                            <span class="text-slate-700 dark:text-slate-300 font-bold ml-1.5">({{ $rev->rating }})</span>
                        </div>
                        <a href="{{ route('admin.orders.show', $rev->order) }}" class="font-mono text-[11px] font-bold text-cyan-600 dark:text-cyan-400 hover:underline">
                            #{{ $rev->order->order_number }}
                        </a>
                    </div>

                    <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed italic bg-slate-50 dark:bg-slate-950/60 p-3 rounded-xl border border-slate-100 dark:border-slate-800">
                        "{{ $rev->comment }}"
                    </p>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <form method="POST" action="{{ route('admin.reviews.toggle', $rev) }}" class="inline">
                        @csrf
                        <button type="submit" class="text-xs font-bold px-3 py-1.5 rounded-xl {{ $rev->is_approved ? 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' : 'bg-emerald-600 text-white hover:bg-emerald-700' }} cursor-pointer">
                            {{ $rev->is_approved ? __('Sembunyikan') : __('Tampilkan di Web') }}
                        </button>
                    </form>

                    <form id="delete-review-grid-{{ $rev->id }}" method="POST" action="{{ route('admin.reviews.destroy', $rev) }}" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="button" 
                                @click="$dispatch('open-confirm-modal', {
                                    form: 'delete-review-grid-{{ $rev->id }}',
                                    title: '{{ __('Hapus Ulasan Pelanggan') }}',
                                    message: '{{ __('Apakah Anda yakin ingin menghapus ulasan dari pelanggan') }} {{ addslashes($rev->order?->recipient_name ?? 'Pelanggan') }}?'
                                })"
                                class="text-rose-500 hover:text-rose-700 dark:text-rose-400 text-xs font-semibold p-1.5 cursor-pointer"
                                title="{{ __('Hapus Ulasan') }}">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                </div>
            </x-card>
        @empty
            <div class="col-span-full">
                <x-card class="p-12 text-center">
                    <x-empty-state 
                        icon="fa-regular fa-star" 
                        :title="__('Belum Ada Ulasan')" 
                        :description="__('Ulasan dari pelanggan akan muncul di sini untuk dimoderasi.')"
                    />
                </x-card>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800">
        {{ $reviews->links() }}
    </div>

</div>
@endsection
