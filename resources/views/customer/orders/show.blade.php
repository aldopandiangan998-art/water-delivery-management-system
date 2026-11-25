@extends('layouts.customer')

@section('title', 'Detail Pesanan #' . $order->order_number)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    <!-- Header bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center space-x-2 flex-wrap gap-y-1">
                <h1 class="text-xl font-black text-slate-900 tracking-tight">Pesanan {{ $order->order_number }}</h1>
                <span class="text-xs font-bold px-2.5 py-0.5 rounded-full border {{ $order->status_badge['color'] }}">
                    {{ $order->status_badge['label'] }}
                </span>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full border {{ $order->payment_badge['color'] }}">
                    {{ $order->payment_badge['label'] }}
                </span>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full border {{ $order->payment_method_badge['color'] }}">
                    <i class="fa-solid {{ $order->payment_method_badge['icon'] }} mr-1"></i>
                    {{ $order->payment_method_badge['label'] }}
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

    <!-- QRIS Payment Box (If QRIS payment method) -->
    @if ($order->payment_method === 'qris')
        @if ($order->payment_status === 'paid')
            <!-- Paid QRIS Banner -->
            <div class="bg-gradient-to-r from-emerald-50 to-teal-50 border-2 border-emerald-300 rounded-3xl p-5 shadow-xs flex items-center justify-between">
                <div class="flex items-center space-x-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-sm text-2xl">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <h2 class="text-sm sm:text-base font-black text-slate-900">Pembayaran QRIS Telah Lunas</h2>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-200">Terverifikasi</span>
                        </div>
                        <p class="text-xs text-slate-600 mt-0.5">
                            Pembayaran QRIS sebesar <b class="text-emerald-700">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</b> telah diverifikasi lunas oleh Admin. Pesanan Anda siap dikirim!
                        </p>
                    </div>
                </div>
            </div>
        @elseif ($order->is_qris_confirmed)
            <!-- QRIS Confirmed - Waiting for Admin Verification -->
            <div class="bg-gradient-to-r from-teal-50 via-cyan-50 to-emerald-50 border-2 border-teal-300 rounded-3xl p-5 shadow-xs space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-start space-x-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-teal-600 text-white flex items-center justify-center shrink-0 shadow-sm text-2xl">
                            <i class="fa-solid fa-receipt"></i>
                        </div>
                        <div>
                            <div class="flex items-center space-x-2">
                                <h2 class="text-sm sm:text-base font-black text-slate-900">Pembayaran QRIS Berhasil Dikonfirmasi!</h2>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 animate-pulse">Menunggu Cek Toko</span>
                            </div>
                            <p class="text-xs text-slate-600 mt-1">
                                Anda telah mengonfirmasi pembayaran sebesar <b class="text-teal-700">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</b>. Pesanan Anda saat ini diteruskan ke WhatsApp Admin untuk disiapkan & diantar.
                            </p>
                        </div>
                    </div>
                </div>

                @if ($order->latest_payment_proof)
                    <div class="pt-3 border-t border-teal-100 flex items-center space-x-3 text-xs text-slate-600">
                        <span class="font-bold text-slate-700">Bukti Transfer:</span>
                        <a href="/storage/{{ $order->latest_payment_proof }}" target="_blank" class="text-teal-700 font-bold hover:underline flex items-center">
                            <i class="fa-solid fa-image mr-1"></i> Lihat Foto Bukti Pembayaran
                        </a>
                    </div>
                @endif
            </div>
        @else
            <!-- Unpaid & Not Confirmed QRIS Box -->
            <div class="bg-white rounded-3xl border-2 border-teal-300 shadow-md overflow-hidden" x-data="{ copied: false, modalOpen: false, previewUrl: null }">
                <!-- Header -->
                <div class="bg-gradient-to-r from-teal-600 via-cyan-600 to-blue-600 p-4 sm:p-5 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-xs flex items-center justify-center text-white text-xl">
                            <i class="fa-solid fa-qrcode"></i>
                        </div>
                        <div>
                            <div class="flex items-center space-x-2">
                                <h2 class="text-base font-black tracking-tight">Pembayaran QRIS Realtime Otomatis</h2>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-400 text-slate-950 animate-pulse">Auto-Detect Realtime</span>
                            </div>
                            <p class="text-xs text-teal-100">
                                @if ($order->tripay_reference)
                                    Ref Gateway: <b>{{ $order->tripay_reference }}</b> &bull;
                                @endif
                                Merchant: <b>Toko Nova Jaya (Doppnnstore)</b>
                            </p>
                        </div>
                    </div>
                    <div class="text-left sm:text-right bg-white/10 px-3 py-1.5 rounded-xl backdrop-blur-xs border border-white/20">
                        <div class="text-[10px] text-teal-100 uppercase tracking-wider font-semibold">Total Tagihan Pas</div>
                        <div class="text-lg font-black text-white">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</div>
                    </div>
                </div>

                <!-- Info banner: Otomatis Terverifikasi via Gateway -->
                <div class="bg-emerald-50 border-b border-emerald-200 px-5 py-2.5 flex items-center justify-between text-xs text-emerald-900 font-medium space-x-2">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-bolt text-emerald-600 text-sm shrink-0"></i>
                        <span><b>Deteksi Otomatis Aktif:</b> Cukup scan barcode dengan m-Banking / E-Wallet Anda. Begitu pembayaran selesai, sistem otomatis mengubah pesanan menjadi <b>LUNAS</b> tanpa perlu admin klik manual.</span>
                    </div>
                </div>

                <!-- Body: QRIS Image & Instructions -->
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                    <!-- Left: QR Code Display -->
                    <div class="flex flex-col items-center justify-center bg-slate-50 p-5 rounded-2xl border border-slate-200">
                        <div class="relative group cursor-pointer" @click="modalOpen = true">
                            <img src="{{ $order->qris_qr_url ?: asset('images/qris.jpg') }}" alt="QRIS Doppnnstore" 
                                 class="w-64 max-w-full h-auto rounded-xl shadow-md border-2 border-white object-contain bg-white p-2">
                            <div class="absolute inset-0 bg-black/30 rounded-xl opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-bold space-x-1">
                                <i class="fa-solid fa-magnifying-glass-plus text-base"></i>
                                <span>Klik untuk Perbesar</span>
                            </div>
                        </div>

                        <!-- Action Buttons below QR -->
                        <div class="mt-4 flex flex-wrap gap-2 justify-center w-full">
                            <a href="{{ $order->qris_qr_url ?: asset('images/qris.jpg') }}" download="QRIS-{{ $order->order_number }}.png"
                               class="px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-200 hover:bg-slate-300 text-slate-800 transition flex items-center">
                                <i class="fa-solid fa-download mr-1.5"></i> Unduh Gambar QRIS
                            </a>
                            <button type="button" @click="navigator.clipboard.writeText('{{ (int)$order->total_amount }}'); copied = true; setTimeout(() => copied = false, 2500)"
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold bg-teal-100 hover:bg-teal-200 text-teal-800 transition flex items-center">
                                <i class="fa-solid fa-copy mr-1.5" x-show="!copied"></i>
                                <i class="fa-solid fa-check mr-1.5 text-emerald-600" x-show="copied"></i>
                                <span x-text="copied ? 'Tersalin!' : 'Salin Nominal Rp {{ number_format($order->total_amount, 0, ',', '.') }}'"></span>
                            </button>
                        </div>

                        <!-- Testing Simulator Button -->
                        <div class="mt-4 w-full pt-3 border-t border-slate-200 text-center">
                            <button type="button"
                                    x-data="{ simulating: false }"
                                    :disabled="simulating"
                                    @click="
                                        simulating = true;
                                        fetch('{{ route('api.webhooks.tripay.simulate', $order) }}', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                            }
                                        })
                                        .then(res => res.json())
                                        .then(data => {
                                            if (data.success) {
                                                window.location.reload();
                                            } else {
                                                alert(data.message || 'Gagal simulasi');
                                                simulating = false;
                                            }
                                        })
                                        .catch(() => {
                                            alert('Terjadi kesalahan koneksi.');
                                            simulating = false;
                                        });
                                    "
                                    class="w-full py-2 px-3 rounded-xl text-[11px] font-black bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white shadow-xs transition flex items-center justify-center space-x-1.5 cursor-pointer">
                                <i class="fa-solid fa-flask" :class="simulating ? 'animate-spin' : ''"></i>
                                <span x-text="simulating ? 'Memvalidasi Pembayaran...' : '⚡ Uji Coba: Simulasi Pembayaran QRIS Lunas (Testing)'"></span>
                            </button>
                            <p class="text-[10px] text-slate-400 mt-1">Gunakan tombol ini saat ingin menguji alur otomatis tanpa transfer uang asli.</p>
                        </div>
                    </div>

                    <!-- Right: Step by Step Instructions & Confirmation Form -->
                    <div class="space-y-4">
                        <h3 class="font-extrabold text-sm text-slate-900 flex items-center">
                            <span class="w-6 h-6 rounded-full bg-teal-600 text-white flex items-center justify-center text-xs mr-2 font-bold">!</span>
                            Cara Bayar & Konfirmasi:
                        </h3>

                        <ol class="space-y-2.5 text-xs text-slate-700">
                            <li class="flex items-start">
                                <span class="font-bold text-teal-700 mr-2 shrink-0">1.</span>
                                <span>Buka <b>m-Banking</b> (BCA, Mandiri, BRI, BNI) atau <b>E-Wallet</b> (GoPay, OVO, Dana, ShopeePay).</span>
                            </li>
                            <li class="flex items-start">
                                <span class="font-bold text-teal-700 mr-2 shrink-0">2.</span>
                                <span>Pilih menu <b>Scan / Bayar QRIS</b>, lalu arahkan ke barcode <b>Doppnnstore</b>.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="font-bold text-teal-700 mr-2 shrink-0">3.</span>
                                <span>Ketik nominal pas: <b class="text-teal-800 bg-teal-50 px-1.5 py-0.5 rounded border border-teal-200">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</b>.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="font-bold text-teal-700 mr-2 shrink-0">4.</span>
                                <span>Setelah transfer berhasil, isi form konfirmasi di bawah ini agar pesanan langsung masuk ke WhatsApp Admin.</span>
                            </li>
                        </ol>

                        <!-- FORM KONFIRMASI PEMBAYARAN QRIS -->
                        <div class="pt-3 border-t border-slate-200">
                            <form method="POST" action="{{ route('customer.orders.confirm-payment', $order) }}" enctype="multipart/form-data" class="space-y-3 bg-teal-50/60 p-4 rounded-2xl border border-teal-200">
                                @csrf
                                <div>
                                    <label class="block text-xs font-bold text-teal-950 mb-1">
                                        Upload Bukti Transfer / Screenshot <span class="text-slate-400 font-normal">(Opsional)</span>
                                    </label>
                                    <input type="file" name="proof_image" accept="image/*"
                                           @change="const file = $event.target.files[0]; if (file) { previewUrl = URL.createObjectURL(file) }"
                                           class="w-full text-xs text-slate-600 file:mr-2.5 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-teal-600 file:text-white hover:file:bg-teal-700 cursor-pointer">
                                    <template x-if="previewUrl">
                                        <div class="mt-2">
                                            <img :src="previewUrl" alt="Preview Bukti" class="h-24 w-auto rounded-lg border border-teal-300 shadow-2xs">
                                        </div>
                                    </template>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-teal-950 mb-1">
                                        Nama Pengirim / Catatan <span class="text-slate-400 font-normal">(Contoh: BCA a.n Budi / GoPay)</span>
                                    </label>
                                    <input type="text" name="notes" placeholder="Nama rekening atau bank/e-wallet Anda..."
                                           class="w-full text-xs border border-teal-300 rounded-xl p-2.5 bg-white focus:ring-2 focus:ring-teal-500">
                                </div>

                                <button type="submit" 
                                        class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-extrabold text-xs shadow-md hover:shadow-lg transition flex items-center justify-center space-x-2">
                                    <i class="fa-solid fa-circle-check text-base"></i>
                                    <span>Saya Sudah Bayar (Konfirmasi & Teruskan ke WA Admin)</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Fullscreen Image Modal -->
                <div x-show="modalOpen" x-cloak 
                     @click="modalOpen = false"
                     class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4 backdrop-blur-xs">
                    <div class="relative max-w-sm w-full bg-white rounded-3xl p-4 shadow-2xl" @click.stop>
                        <button type="button" @click="modalOpen = false" 
                                class="absolute top-3 right-3 text-slate-400 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-full w-8 h-8 flex items-center justify-center">
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                        <h4 class="font-bold text-sm text-slate-900 text-center mb-2">QRIS Resmi Toko (Doppnnstore)</h4>
                        <img src="{{ asset('images/qris.jpg') }}" alt="QRIS Doppnnstore Full" class="w-full h-auto rounded-xl">
                        <div class="mt-3 text-center text-xs font-bold text-teal-700">
                            Nominal Pas: Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif

    <!-- WhatsApp Confirmation Banner:
         - IF COD: Always show!
         - IF QRIS: ONLY show after customer has confirmed or paid! -->
    @if ($order->payment_method === 'cod' || $order->is_qris_confirmed || $order->payment_status === 'paid')
        <div class="bg-gradient-to-r from-emerald-50 via-teal-50 to-cyan-50 border-2 border-emerald-300 rounded-3xl p-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-start space-x-3.5">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-sm text-2xl">
                    <i class="fa-brands fa-whatsapp"></i>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        @if ($order->payment_method === 'cod')
                            <h2 class="text-sm sm:text-base font-black text-slate-900">Pesanan Tunai Siap Diproses</h2>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Bayar di Tempat</span>
                        @else
                            <h2 class="text-sm sm:text-base font-black text-slate-900">Teruskan Pesanan ke WhatsApp Admin</h2>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-100 text-teal-800 border border-teal-200">QRIS Terkonfirmasi</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                        @if ($order->payment_method === 'cod')
                            Pesanan Anda telah tercatat! Klik tombol di bawah untuk meneruskan rincian pesanan ke WhatsApp Admin agar kurir segera menyiapkan pesanan dan jalan ke alamat Anda.
                        @else
                            Pembayaran QRIS telah Anda konfirmasi! Klik tombol di bawah untuk membuka WhatsApp Admin dengan rincian pesanan dan bukti bayar agar kurir segera jalan.
                        @endif
                    </p>
                </div>
            </div>

            <a href="{{ $order->whatsapp_url }}" target="_blank"
               class="w-full sm:w-auto px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md hover:shadow-lg transition flex items-center justify-center space-x-2 shrink-0">
                <i class="fa-brands fa-whatsapp text-lg"></i>
                <span>{{ $order->payment_method === 'cod' ? 'Kirim ke WhatsApp Admin' : 'Kirim Bukti ke WhatsApp Admin' }}</span>
                <i class="fa-solid fa-arrow-up-right-from-square text-[10px] ml-1"></i>
            </a>
        </div>
    @endif

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
            @if ($order->service_fee > 0)
                <div class="flex justify-between text-teal-700 font-semibold">
                    <span>Biaya Layanan QRIS (0.7%):</span>
                    <span>Rp {{ number_format($order->service_fee, 0, ',', '.') }}</span>
                </div>
            @endif
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
            <form method="POST" action="{{ route('customer.orders.cancel', $order) }}">
                @csrf
                <input type="hidden" name="cancellation_reason" value="Dibatalkan oleh pelanggan via portal">
                <button type="button" 
                        @click="$dispatch('open-confirm-modal', {
                            form: $el.closest('form'),
                            title: 'Batalkan Pesanan Ini?',
                            message: 'Apakah Anda yakin ingin membatalkan pesanan #{{ $order->order_number }}? Pesanan yang dibatalkan tidak dapat diproses lagi.',
                            confirmText: 'Ya, Batalkan Pesanan',
                            iconClass: 'fa-solid fa-ban text-rose-600',
                            iconBgClass: 'bg-rose-100 ring-8 ring-rose-50'
                        })"
                        class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-xs transition cursor-pointer">
                    Batalkan Pesanan
                </button>
            </form>
        </div>
    @endif

    @if (session('auto_open_whatsapp'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                setTimeout(function () {
                    window.open("{{ $order->whatsapp_url }}", "_blank");
                }, 700);
            });
        </script>
    @endif

    {{-- Real-time QRIS payment status polling (only for unpaid QRIS orders) --}}
    @if ($order->payment_method === 'qris' && $order->payment_status !== 'paid')
        <script>
            (function () {
                const statusUrl = "{{ route('customer.orders.status-check', $order) }}";
                let pollCount = 0;
                const maxPolls = 300; // max 20 menit (300 x 4 detik)

                function checkPaymentStatus() {
                    if (pollCount >= maxPolls) return;
                    pollCount++;

                    fetch(statusUrl, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.is_paid || data.payment_status === 'paid') {
                            // Payment confirmed by DANA webhook - redirect to updated page
                            window.location.reload();
                        } else {
                            setTimeout(checkPaymentStatus, 4000);
                        }
                    })
                    .catch(() => {
                        // Retry on network error silently
                        setTimeout(checkPaymentStatus, 8000);
                    });
                }

                // Start polling 4 seconds after page load
                setTimeout(checkPaymentStatus, 4000);
            })();
        </script>
    @endif

</div>
@endsection
