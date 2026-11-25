@extends('layouts.admin')

@section('page_title', 'Keuangan & Laporan Laba Rugi')

@section('content')
<div class="space-y-6" x-data="{ expenseModalOpen: false }">

    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight">Laporan Keuangan & Arus Kas</h1>
            <p class="text-xs text-slate-500">Pencatatan pendapatan riil lunas, piutang pesanan, dan biaya operasional depot.</p>
        </div>

        <div class="flex items-center space-x-2">
            <!-- Period filter buttons -->
            <div class="flex bg-slate-100 p-1 rounded-xl text-xs font-semibold">
                <a href="{{ route('admin.finance.index', ['period' => 'today']) }}" class="px-3 py-1.5 rounded-lg {{ $period === 'today' ? 'bg-white shadow-xs text-cyan-700 font-bold' : 'text-slate-600' }}">Hari Ini</a>
                <a href="{{ route('admin.finance.index', ['period' => 'this_week']) }}" class="px-3 py-1.5 rounded-lg {{ $period === 'this_week' ? 'bg-white shadow-xs text-cyan-700 font-bold' : 'text-slate-600' }}">Minggu Ini</a>
                <a href="{{ route('admin.finance.index', ['period' => 'this_month']) }}" class="px-3 py-1.5 rounded-lg {{ $period === 'this_month' ? 'bg-white shadow-xs text-cyan-700 font-bold' : 'text-slate-600' }}">Bulan Ini</a>
                <a href="{{ route('admin.finance.index', ['period' => 'this_year']) }}" class="px-3 py-1.5 rounded-lg {{ $period === 'this_year' ? 'bg-white shadow-xs text-cyan-700 font-bold' : 'text-slate-600' }}">Tahun Ini</a>
            </div>

            <button type="button" @click="expenseModalOpen = true" 
                    class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center">
                <i class="fa-solid fa-plus mr-1.5"></i> Catat Pengeluaran
            </button>
        </div>
    </div>

    <!-- P&L Financial Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pendapatan Lunas</span>
            <div class="text-2xl font-black text-emerald-600 mt-1">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
            <div class="text-[10px] text-slate-400 mt-1">Uang pembayaran masuk</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Piutang Belum Lunas</span>
            <div class="text-2xl font-black text-amber-600 mt-1">Rp {{ number_format($unpaidAmount, 0, ',', '.') }}</div>
            <div class="text-[10px] text-slate-400 mt-1">Pesanan belum dibayar</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Biaya Operasional</span>
            <div class="text-2xl font-black text-rose-600 mt-1">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</div>
            <div class="text-[10px] text-slate-400 mt-1">BBM, servis, gaji, dll</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Estimasi Laba Bersih</span>
            <div class="text-2xl font-black {{ $netProfit >= 0 ? 'text-cyan-700' : 'text-rose-700' }} mt-1">
                Rp {{ number_format($netProfit, 0, ',', '.') }}
            </div>
            <div class="text-[10px] text-slate-400 mt-1">Pendapatan - Pengeluaran</div>
        </div>
    </div>

    <!-- Expenses & Payments Two Columns -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Operational Expenses Table (7 cols) -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-slate-900 text-sm uppercase tracking-wider">Catatan Pengeluaran Operasional</h2>
                    <p class="text-xs text-slate-400">Biaya bensin motor, pemeliharaan, pembelian tutup/tisu</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-left text-xs">
                    <thead class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3">Tanggal & No</th>
                            <th class="px-5 py-3">Kategori</th>
                            <th class="px-5 py-3">Keterangan</th>
                            <th class="px-5 py-3">Jumlah (Rp)</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($expenses as $exp)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-5 py-3 font-semibold text-slate-800 whitespace-nowrap">
                                    {{ $exp->date->format('d M Y') }}
                                    <div class="text-[10px] font-mono text-slate-400 font-normal">{{ $exp->expense_number }}</div>
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                        {{ $exp->category }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="font-bold text-slate-900">{{ $exp->title }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $exp->notes ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-3 font-black text-rose-600 whitespace-nowrap">
                                    Rp {{ number_format($exp->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3 text-right whitespace-nowrap">
                                    <form method="POST" action="{{ route('admin.finance.destroy-expense', $exp) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" 
                                                @click="$dispatch('open-confirm-modal', {
                                                    form: $el.closest('form'),
                                                    title: 'Hapus Catatan Pengeluaran',
                                                    message: 'Apakah Anda yakin ingin menghapus pengeluaran \'{{ addslashes($exp->title) }}\' sebesar Rp {{ number_format($exp->amount, 0, ',', '.') }}?'
                                                })"
                                                class="text-rose-500 hover:text-rose-700 cursor-pointer p-1"
                                                title="Hapus Pengeluaran">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-slate-400">
                                    Tidak ada catatan pengeluaran pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-t border-slate-100">
                {{ $expenses->links() }}
            </div>
        </div>

        <!-- Recent Payments Received (5 cols) -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
            <div class="p-5 border-b border-slate-100">
                <h2 class="font-bold text-slate-900 text-sm uppercase tracking-wider">Pembayaran Terverifikasi</h2>
                <p class="text-xs text-slate-400">Daftar uang masuk yang telah lunas</p>
            </div>

            <div class="divide-y divide-slate-100 text-xs">
                @forelse ($payments as $pay)
                    <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition">
                        <div>
                            <div class="font-bold text-slate-900">#{{ $pay->order?->order_number }}</div>
                            <div class="text-[11px] text-slate-500">
                                Diterima via <span class="uppercase font-semibold">{{ $pay->payment_method }}</span> &bull; 
                                {{ $pay->paid_at?->format('H:i, d M') ?? $pay->created_at->format('H:i, d M') }}
                            </div>
                            <div class="text-[10px] text-slate-400">Penerima: {{ $pay->receiver?->name ?? 'Kasir / Kurir' }}</div>
                        </div>
                        <div class="text-right">
                            <span class="font-black text-emerald-600 text-sm">
                                Rp {{ number_format($pay->amount, 0, ',', '.') }}
                            </span>
                            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded block mt-0.5">Lunas</span>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-400">
                        Belum ada data pembayaran lunas pada periode ini.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Modal Catat Biaya Operasional -->
    <div x-show="expenseModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4" @click.away="expenseModalOpen = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-extrabold text-base text-slate-900">Catat Pengeluaran Operasional</h3>
                <button type="button" @click="expenseModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.finance.store-expense') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Pengeluaran</label>
                    <select name="category" required class="w-full text-xs border border-slate-300 rounded-xl p-2.5 bg-white">
                        <option value="fuel">BBM & Transportasi (Bensin Motor Kurir)</option>
                        <option value="stock">Pembelian Perlengkapan (Tutup Galon, Tisu, Segel)</option>
                        <option value="vehicle_maintenance">Perawatan Motor & Servis Berkala</option>
                        <option value="salary">Gaji & Upah Kurir / Karyawan</option>
                        <option value="utilities">Listrik, Air & Biaya Utilitas Depot</option>
                        <option value="other">Pengeluaran Lain-lain</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Judul / Keperluan</label>
                    <input type="text" name="title" required placeholder="Contoh: Bensin Motor Beat Kurir Asep..."
                           class="w-full text-xs border border-slate-300 rounded-xl p-2.5">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nominal Biaya (Rp)</label>
                        <input type="number" name="amount" required min="1000" placeholder="Contoh: 50000"
                               class="w-full text-xs font-bold border border-slate-300 rounded-xl p-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal</label>
                        <input type="date" name="date" required value="{{ date('Y-m-d') }}"
                               class="w-full text-xs border border-slate-300 rounded-xl p-2.5">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                    <input type="text" name="notes" placeholder="Nomor plat motor, toko pembelian, dll..."
                           class="w-full text-xs border border-slate-300 rounded-xl p-2.5">
                </div>

                <div class="pt-3 flex justify-end space-x-2">
                    <button type="button" @click="expenseModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white shadow-xs">
                        Simpan Pengeluaran
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
