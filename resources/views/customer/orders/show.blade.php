@extends('layouts.app')

@section('title', 'Detail Pesanan #' . $order->order_number)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    <!-- Header bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center space-x-2">
                <h1 class="text-xl font-black text-slate-900 tracking-tight">Pesanan {{ $order->order_number }}</h1>
                <span class="text-xs font-bold px-2.5 py-0.5 rounded-full border {{ $order->status_badge['color'] }}">
                    {{ $order->status_badge['label'] }}
                </span>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full border {{ $order->payment_badge['color'] }}">
                    {{ $order->payment_badge['label'] }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">Dibuat pada {{ $order->created_at->format('d M Y, H:i') }} WIB</p>
        </div>

        <div class="flex items-center space-x-2">
            @if ($order->delivery && in_array($order->delivery->status, ['assigned', 'in_transit', 'arrived']))
                <a href="{{ route('customer.orders.track', $order) }}" 
                   class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition">
                    <i class="fa-solid fa-map-location-dot mr-1.5"></i> Lacak Kurir Live
                </a>
            @endif

            <a href="{{ route('customer.orders.index') }}" class="px-3 py-2 rounded-xl text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">
                &larr; Kembali
            </a>
        </div>
    </div>

    <!-- WhatsApp Direct Confirmation Banner -->
    <div class="bg-gradient-to-r from-emerald-50 via-teal-50 to-cyan-50 border-2 border-emerald-300 rounded-2xl p-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-start space-x-3.5">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-sm text-2xl">
                <i class="fa-brands fa-whatsapp"></i>
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <h2 class="text-sm sm:text-base font-black text-slate-900">Kirim & Konfirmasi ke WhatsApp Admin</h2>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Rekomendasi Cepat</span>
                </div>
                <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                    Pesanan Anda sudah tersimpan di sistem. Klik tombol di bawah untuk langsung meneruskan rincian pesanan, alamat, dan pin lokasi Anda ke WhatsApp Admin agar pesanan segera disiapkan & diantar!
                </p>
            </div>
        </div>

        <a href="{{ $order->whatsapp_url }}" target="_blank"
           class="w-full sm:w-auto px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md hover:shadow-lg transition flex items-center justify-center space-x-2 shrink-0">
            <i class="fa-brands fa-whatsapp text-lg"></i>
            <span>Kirim ke WhatsApp Admin</span>
            <i class="fa-solid fa-arrow-up-right-from-square text-[10px] ml-1"></i>
        </a>
    </div>

    <!-- Delivery & Driver Status Card -->
    @if ($order->delivery)
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
            <h2 class="font-bold text-sm text-slate-900 uppercase tracking-wider flex items-center">
                <i class="fa-solid fa-truck text-cyan-600 mr-2"></i> Status Pengantaran
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-slate-50 p-4 rounded-xl text-xs">
                <div>
                    <span class="text-slate-400 block">Kurir Pengantar:</span>
                    <span class="font-bold text-slate-800 text-sm">{{ $order->delivery->driver?->name ?? 'Belum Ditugaskan' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Status Pengiriman:</span>
                    <span class="font-bold text-slate-800 text-sm">{{ $order->delivery->status_badge['label'] }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Kontak Kurir:</span>
                    @if ($order->delivery->driver?->phone)
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $order->delivery->driver->phone) }}" target="_blank" class="font-bold text-emerald-600 hover:underline flex items-center mt-0.5">
                            <i class="fa-brands fa-whatsapp mr-1"></i> {{ $order->delivery->driver->phone }}
                        </a>
                    @else
                        <span class="text-slate-400">-</span>
                    @endif
                </div>
            </div>

            <!-- Delivery Proof Photo if uploaded -->
            @if ($order->delivery->photos->isNotEmpty())
                <div class="pt-3 border-t border-slate-100">
                    <h3 class="text-xs font-bold text-slate-700 mb-2">Foto Bukti Serah Terima:</h3>
                    <div class="flex flex-wrap gap-3">
                        @foreach ($order->delivery->photos as $photo)
                            <div class="border border-slate-200 rounded-xl overflow-hidden shadow-2xs">
                                <img src="/storage/{{ $photo->photo_path }}" alt="Bukti Serah Terima" class="h-36 w-36 object-cover hover:scale-105 transition">
                                <div class="p-1.5 text-[10px] text-slate-500 bg-slate-50 text-center">
                                    {{ $photo->created_at->format('H:i, d M Y') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Items Detail Card -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
        <h2 class="font-bold text-sm text-slate-900 uppercase tracking-wider">Rincian Barang</h2>

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
                    <div class="font-bold text-slate-800">
                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                    </div>
                </div>
            @endforeach
        </div>

        <div class="pt-4 border-t border-slate-200 space-y-2 text-xs">
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

    <!-- Destination & Notes -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-3">
        <h2 class="font-bold text-sm text-slate-900 uppercase tracking-wider">Alamat & Catatan</h2>
        <div class="text-xs text-slate-700 space-y-1">
            <div><span class="text-slate-400">Penerima:</span> <span class="font-semibold">{{ $order->recipient_name }} ({{ $order->recipient_phone }})</span></div>
            <div><span class="text-slate-400">Alamat Lengkap:</span> {{ $order->delivery_address_text }}</div>
            @if ($order->delivery_benchmark)
                <div><span class="text-slate-400">Patokan Lokasi:</span> {{ $order->delivery_benchmark }}</div>
            @endif
            @if ($order->customer_notes)
                <div><span class="text-slate-400">Catatan Pelanggan:</span> <span class="italic font-medium text-slate-800">"{{ $order->customer_notes }}"</span></div>
            @endif
        </div>
    </div>

    <!-- Review Section if Completed -->
    @if ($order->status === 'completed')
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
            <h2 class="font-bold text-sm text-slate-900 uppercase tracking-wider flex items-center">
                <i class="fa-solid fa-star text-amber-500 mr-2"></i> Ulasan & Penilaian Pesanan
            </h2>

            @if ($order->review)
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <div class="flex items-center space-x-1 text-amber-400 mb-2">
                        @for ($i = 1; $i <= 5; $i++)
                            <i class="fa-solid fa-star {{ $i <= $order->review->rating ? 'text-amber-400' : 'text-slate-300' }}"></i>
                        @endfor
                        <span class="text-xs font-bold text-slate-700 ml-2">{{ $order->review->rating }} / 5 Bintang</span>
                    </div>
                    <p class="text-sm text-slate-700 italic">"{{ $order->review->comment }}"</p>
                    <div class="text-[10px] text-slate-400 mt-2">Diberikan pada {{ $order->review->created_at->format('d M Y, H:i') }} WIB</div>
                </div>
            @else
                <form method="POST" action="{{ route('customer.orders.review', $order) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Berikan Bintang:</label>
                        <select name="rating" required class="text-sm border border-slate-300 rounded-xl px-3 py-2 bg-white">
                            <option value="5">⭐⭐⭐⭐⭐ (5 - Sangat Memuaskan)</option>
                            <option value="4">⭐⭐⭐⭐ (4 - Memuaskan)</option>
                            <option value="3">⭐⭐⭐ (3 - Cukup)</option>
                            <option value="2">⭐⭐ (2 - Kurang)</option>
                            <option value="1">⭐ (1 - Buruk)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Komentar / Pengalaman Anda:</label>
                        <textarea name="comment" rows="2" placeholder="Tuliskan ulasan Anda mengenai kebersihan air dan keramahan kurir..."
                                  class="w-full text-sm border border-slate-300 rounded-xl p-3 focus:ring-2 focus:ring-cyan-500"></textarea>
                    </div>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold bg-amber-500 hover:bg-amber-600 text-white shadow-xs transition">
                        Kirim Ulasan Saya
                    </button>
                </form>
            @endif
        </div>
    @endif

    <!-- Cancel Order Button if Still Pending -->
    @if (in_array($order->status, ['pending', 'confirmed']))
        <div class="bg-rose-50 border border-rose-200 p-4 rounded-2xl flex items-center justify-between">
            <div>
                <div class="font-bold text-sm text-rose-900">Ingin membatalkan pesanan ini?</div>
                <div class="text-xs text-rose-700">Pesanan hanya dapat dibatalkan sebelum kurir memulai perjalanan.</div>
            </div>
            <form method="POST" action="{{ route('customer.orders.cancel', $order) }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')">
                @csrf
                <input type="hidden" name="cancellation_reason" value="Dibatalkan oleh pelanggan via portal">
                <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                    Batalkan Pesanan
                </button>
            </form>
        </div>
    @endif

</div>
@endsection
