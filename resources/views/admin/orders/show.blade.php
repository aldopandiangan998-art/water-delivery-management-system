@extends('layouts.admin')

@section('page_title', 'Rincian Pesanan #' . $order->order_number)

@section('content')
<div class="space-y-6">

    <!-- Top Action & Info Bar -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2.5">
                <h1 class="text-xl font-black text-slate-900 tracking-tight font-mono">Pesanan #{{ $order->order_number }}</h1>
                <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $order->status_badge['color'] }}">
                    {{ $order->status_badge['label'] }}
                </span>
                <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $order->payment_badge['color'] }}">
                    {{ $order->payment_badge['label'] }}
                </span>
            </div>
            <div class="text-xs text-slate-500 mt-1 flex items-center space-x-2 flex-wrap gap-y-1">
                <span>Dipesan pada {{ $order->created_at->format('d M Y, H:i') }} WIB</span>
                <span>&bull;</span>
                <span>Metode:</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold border {{ $order->payment_method_badge['color'] }}">
                    <i class="fa-solid {{ $order->payment_method_badge['icon'] }} mr-1"></i>
                    {{ $order->payment_method_badge['label'] }}
                </span>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.orders.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                &larr; Kembali
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left col: Items, Customer, Delivery (8 cols) -->
        <div class="lg:col-span-8 space-y-6">

            <!-- Products Ordered -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                <h2 class="font-bold text-sm text-slate-900 uppercase tracking-wider">Rincian Barang Dipesan</h2>

                <div class="divide-y divide-slate-100">
                    @foreach ($order->items as $item)
                        <div class="py-3 flex justify-between items-center text-sm">
                            <div>
                                <div class="font-bold text-slate-900">{{ $item->product_name }}</div>
                                <div class="text-xs text-slate-500">
                                    {{ $item->quantity }} {{ $item->unit }} &times; Rp {{ number_format($item->price, 0, ',', '.') }}
                                    @if ($item->is_exchange)
                                        <span class="text-blue-600 font-semibold ml-1">(Tukar Galon Kosong)</span>
                                    @endif
                                </div>
                            </div>
                            <div class="font-bold text-slate-900">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="pt-3 border-t border-slate-200 space-y-1.5 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal Barang:</span>
                        <span class="font-semibold text-slate-800">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Ongkos Kirim:</span>
                        <span class="font-semibold text-slate-800">Rp {{ number_format($order->delivery_fee, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-base font-black text-slate-900 pt-2 border-t border-slate-100">
                        <span>Total Tagihan:</span>
                        <span class="text-cyan-700">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Customer & Destination Card -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-3">
                <h2 class="font-bold text-sm text-slate-900 uppercase tracking-wider">Tujuan Pengantaran & Kontak</h2>

                <div class="text-xs text-slate-700 space-y-1.5">
                    <div><span class="text-slate-400">Penerima:</span> <span class="font-bold text-slate-900">{{ $order->recipient_name }}</span></div>
                    <div><span class="text-slate-400">Nomor Telepon / WA:</span> <span class="font-bold text-emerald-600">{{ $order->recipient_phone }}</span></div>
                    <div><span class="text-slate-400">Alamat Lengkap:</span> {{ $order->delivery_address_text }}</div>
                    @if ($order->delivery_benchmark)
                        <div><span class="text-slate-400">Patokan Lokasi:</span> {{ $order->delivery_benchmark }}</div>
                    @endif
                    @if ($order->customer_notes)
                        <div class="p-2.5 bg-slate-50 border border-slate-100 rounded-xl text-slate-800 italic">
                            Catatan Pelanggan: "{{ $order->customer_notes }}"
                        </div>
                    @endif
                </div>
            </div>

            <!-- Delivery Proof Photos if completed -->
            @if ($order->delivery && $order->delivery->photos->isNotEmpty())
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-3">
                    <h2 class="font-bold text-sm text-slate-900 uppercase tracking-wider">Foto Bukti Serah Terima Kurir</h2>
                    <div class="flex flex-wrap gap-4">
                        @foreach ($order->delivery->photos as $ph)
                            <div class="border border-slate-200 rounded-xl overflow-hidden shadow-2xs">
                                <img src="/storage/{{ $ph->photo_path }}" alt="Bukti Serah Terima" class="h-44 w-44 object-cover hover:scale-105 transition">
                                <div class="p-2 text-[10px] text-slate-500 bg-slate-50 text-center">
                                    {{ $ph->created_at->format('H:i, d M Y') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Order Status Audit Timeline -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                <h2 class="font-bold text-sm text-slate-900 uppercase tracking-wider">Riwayat Status (Audit Trail)</h2>

                <div class="space-y-3">
                    @foreach ($order->statusHistories as $hist)
                        <div class="flex items-start space-x-3 text-xs">
                            <div class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-[10px] mt-0.5">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div>
                                <div class="font-bold text-slate-800 capitalize">{{ $hist->status }}</div>
                                <div class="text-slate-500">{{ $hist->notes }}</div>
                                <div class="text-[10px] text-slate-400">{{ $hist->created_at->format('d M Y, H:i:s') }} WIB &bull; Oleh: {{ $hist->changer?->name ?? 'Sistem' }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        <!-- Right col: Control Panel (Status, Driver, Payment) (4 cols) -->
        <div class="lg:col-span-4 space-y-6">

            <!-- 1. Change Status Control -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                <h3 class="font-bold text-sm text-slate-900 uppercase tracking-wider">Ubah Status Pesanan</h3>

                <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="space-y-3">
                    @csrf
                    @method('PUT')

                    <div>
                        <select name="status" class="w-full text-xs font-bold border border-slate-300 rounded-xl p-2.5 bg-white">
                            <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                            <option value="confirmed" {{ $order->status === 'confirmed' ? 'selected' : '' }}>Konfirmasi (Confirmed)</option>
                            <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Sedang Disiapkan (Processing)</option>
                            <option value="ready_for_delivery" {{ $order->status === 'ready_for_delivery' ? 'selected' : '' }}>Siap Dikirim</option>
                            <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Selesai (Completed)</option>
                            <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Batalkan (Cancelled)</option>
                            <option value="rejected" {{ $order->status === 'rejected' ? 'selected' : '' }}>Tolak (Rejected)</option>
                        </select>
                    </div>

                    <div>
                        <input type="text" name="notes" placeholder="Catatan perubahan status..." 
                               class="w-full text-xs border border-slate-300 rounded-xl p-2.5">
                    </div>

                    <button type="submit" class="w-full py-2 px-4 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition">
                        Perbarui Status
                    </button>
                </form>
            </div>

            <!-- 2. Assign Delivery Staff Control -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-3">
                <h3 class="font-bold text-sm text-slate-900 uppercase tracking-wider">Petugas Pengantar</h3>

                @if ($order->delivery && $order->delivery->driver)
                    <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs space-y-2">
                        <div>
                            <div class="font-bold text-emerald-900">Petugas Ditugaskan:</div>
                            <div class="text-sm font-black text-slate-800">{{ $order->delivery->driver->name }}</div>
                            <div class="text-slate-500">Status Pengantaran: <span class="capitalize font-semibold text-emerald-700">{{ $order->delivery->status_badge['label'] ?? $order->delivery->status }}</span></div>
                        </div>

                        <a href="{{ route('admin.deliveries.show', $order->delivery) }}" 
                           class="block text-center py-2 px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-xs shadow-xs transition">
                            <i class="fa-solid fa-truck-fast mr-1"></i> Buka Kontrol Pengantaran & Bukti
                        </a>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.orders.assign-driver', $order) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Ganti / Pilih Petugas Pengantar (Admin)</label>
                        <select name="driver_id" required class="w-full text-xs border border-slate-300 rounded-xl p-2.5 bg-white font-medium">
                            <option value="">Pilih Petugas (Admin)</option>
                            @foreach ($drivers as $dr)
                                <option value="{{ $dr->id }}" {{ ($order->delivery?->driver_id == $dr->id) ? 'selected' : '' }}>
                                    {{ $dr->name }} ({{ $dr->phone ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="w-full py-2 px-4 bg-cyan-600 hover:bg-cyan-700 text-white rounded-xl text-xs font-bold transition">
                        Tugaskan Petugas Ini
                    </button>
                </form>
            </div>

            <!-- 3. QRIS Payment Proof Card for Admin -->
            @if ($order->payment_method === 'qris')
                <div class="bg-white p-5 rounded-2xl border-2 {{ $order->is_qris_confirmed ? 'border-teal-300 bg-teal-50/20' : 'border-slate-200' }} shadow-xs space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-sm text-slate-900 uppercase tracking-wider flex items-center">
                            <i class="fa-solid fa-qrcode text-teal-600 mr-2"></i> Bukti QRIS Pelanggan
                        </h3>
                        @if ($order->payment_status === 'paid')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                Sudah Lunas
                            </span>
                        @elseif ($order->is_qris_confirmed)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 animate-pulse">
                                Perlu Cek Mutasi
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                Belum Konfirmasi
                            </span>
                        @endif
                    </div>

                    @if ($order->is_qris_confirmed)
                        <div class="text-xs space-y-2">
                            @if ($order->latest_payment_proof)
                                <div class="border border-slate-200 rounded-xl overflow-hidden shadow-2xs group relative">
                                    <a href="/storage/{{ $order->latest_payment_proof }}" target="_blank">
                                        <img src="/storage/{{ $order->latest_payment_proof }}" alt="Bukti Transfer QRIS" class="w-full h-48 object-contain bg-slate-900">
                                        <div class="p-2 text-center text-xs font-bold text-teal-700 bg-teal-50 border-t border-teal-100">
                                            <i class="fa-solid fa-up-right-from-square mr-1"></i> Buka Gambar Bukti Ukuran Penuh
                                        </div>
                                    </a>
                                </div>
                            @else
                                <div class="p-3 bg-teal-50 border border-teal-200 rounded-xl text-teal-900 text-xs">
                                    <i class="fa-solid fa-check-double mr-1 text-teal-600"></i> Pelanggan menekan tombol <b>"Saya Sudah Bayar"</b> via web.
                                </div>
                            @endif

                            @if ($order->payments->first() && $order->payments->first()->notes)
                                <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 text-xs">
                                    <span class="font-bold text-slate-500 block text-[10px] uppercase">Catatan / Akun Pengirim:</span>
                                    {{ $order->payments->first()->notes }}
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="text-xs text-slate-500">
                            Pelanggan belum mengonfirmasi pembayaran di portal web. Pesanan belum diteruskan untuk pengantaran.
                        </p>
                    @endif
                </div>
            @endif

            <!-- 4. Payment Status Control -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-3">
                <h3 class="font-bold text-sm text-slate-900 uppercase tracking-wider">Status Pembayaran</h3>

                @if ($order->payment_method === 'qris' && $order->payment_status !== 'paid')
                    <form method="POST" action="{{ route('admin.orders.payment', $order) }}" class="mb-3">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="payment_status" value="paid">
                        <input type="hidden" name="amount_paid" value="{{ $order->total_amount }}">
                        <button type="button" 
                                @click="$dispatch('open-confirm-modal', {
                                    form: $el.closest('form'),
                                    title: 'Konfirmasi Pelunasan QRIS',
                                    message: 'Konfirmasi bahwa pembayaran QRIS sebesar Rp {{ number_format($order->total_amount, 0, ',', '.') }} telah masuk ke rekening / e-wallet Doppnnstore?',
                                    confirmText: 'Ya, Konfirmasi Lunas',
                                    confirmButtonClass: 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/20',
                                    iconClass: 'fa-solid fa-qrcode text-teal-600',
                                    iconBgClass: 'bg-teal-100 ring-8 ring-teal-50'
                                })"
                                class="w-full py-2.5 px-3 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-500 hover:to-emerald-500 text-white rounded-xl text-xs font-black shadow-sm flex items-center justify-center space-x-1.5 transition cursor-pointer">
                            <i class="fa-solid fa-qrcode text-sm"></i>
                            <span>Konfirmasi Lunas (QRIS Diterima)</span>
                        </button>
                    </form>
                @endif

                <form method="POST" action="{{ route('admin.orders.payment', $order) }}" class="space-y-3">
                    @csrf
                    @method('PUT')

                    <div>
                        <select name="payment_status" class="w-full text-xs font-bold border border-slate-300 rounded-xl p-2.5 bg-white">
                            <option value="unpaid" {{ $order->payment_status === 'unpaid' ? 'selected' : '' }}>Belum Lunas</option>
                            <option value="partially_paid" {{ $order->payment_status === 'partially_paid' ? 'selected' : '' }}>Bayar Sebagian</option>
                            <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Lunas (Paid)</option>
                            <option value="refunded" {{ $order->payment_status === 'refunded' ? 'selected' : '' }}>Refund</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] text-slate-500 mb-1">Nominal Diterima (Rp):</label>
                        <input type="number" name="amount_paid" value="{{ $order->total_amount }}" 
                               class="w-full text-xs border border-slate-300 rounded-xl p-2.5 font-bold">
                    </div>

                    <button type="submit" class="w-full py-2 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition">
                        Simpan Pembayaran
                    </button>
                </form>
            </div>

        </div>

    </div>

</div>
@endsection
