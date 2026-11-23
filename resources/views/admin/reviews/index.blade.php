@extends('layouts.admin')

@section('page_title', 'Moderasi Ulasan Pelanggan')

@section('content')
<div class="space-y-6">

    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight">Ulasan & Testimoni Pelanggan</h1>
            <p class="text-xs text-slate-500">Moderasi ulasan yang akan ditampilkan pada halaman depan / beranda publik.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Pelanggan & Tanggal</th>
                        <th class="px-5 py-3.5">No. Pesanan</th>
                        <th class="px-5 py-3.5">Rating Bintang</th>
                        <th class="px-5 py-3.5">Isi Ulasan / Komentar</th>
                        <th class="px-5 py-3.5">Status Tampil</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($reviews as $rev)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-900">{{ $rev->user->name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $rev->created_at->format('d M Y, H:i') }} WIB</div>
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap font-mono font-bold text-cyan-700">
                                <a href="{{ route('admin.orders.show', $rev->order) }}" class="hover:underline">
                                    {{ $rev->order->order_number }}
                                </a>
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <div class="flex text-amber-400 text-xs">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i class="fa-solid fa-star {{ $i <= $rev->rating ? 'text-amber-400' : 'text-slate-200' }}"></i>
                                    @endfor
                                    <span class="text-slate-700 font-bold ml-1.5">({{ $rev->rating }})</span>
                                </div>
                            </td>

                            <td class="px-5 py-3.5 text-slate-800 leading-relaxed italic max-w-sm">
                                "{{ $rev->comment }}"
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full font-bold text-[10px] {{ $rev->is_approved ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $rev->is_approved ? 'Tampil di Web' : 'Disembunyikan' }}
                                </span>
                            </td>

                            <td class="px-5 py-3.5 text-right whitespace-nowrap space-x-2">
                                <form method="POST" action="{{ route('admin.reviews.toggle', $rev) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs font-bold px-2.5 py-1 rounded-lg {{ $rev->is_approved ? 'bg-slate-100 text-slate-700 hover:bg-slate-200' : 'bg-emerald-600 text-white hover:bg-emerald-700' }}">
                                        {{ $rev->is_approved ? 'Sembunyikan' : 'Tampilkan' }}
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.reviews.destroy', $rev) }}" onsubmit="return confirm('Hapus ulasan ini?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-500 hover:text-rose-700 text-xs font-semibold p-1">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                                Belum ada ulasan dari pelanggan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $reviews->links() }}
        </div>
    </div>

</div>
@endsection
