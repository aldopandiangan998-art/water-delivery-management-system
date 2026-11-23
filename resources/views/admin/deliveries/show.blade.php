@extends('layouts.admin')

@section('page_title', 'Tugas Pengantaran #' . $delivery->delivery_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6" 
     x-data="adminDeliveryExecution({
         deliveryId: {{ $delivery->id }},
         status: '{{ $delivery->status }}',
         locationApiUrl: '{{ route('admin.deliveries.location', $delivery) }}',
         customerLat: {{ $delivery->order->delivery_latitude ?? 'null' }},
         customerLng: {{ $delivery->order->delivery_longitude ?? 'null' }}
     })">

    <!-- Top Action Card -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-1 rounded-full text-xs font-bold border {{ $delivery->status_badge['color'] }}">
                    {{ $delivery->status_badge['label'] }}
                </span>
                <span class="text-xs text-slate-400 font-mono">Order #{{ $delivery->order->order_number }}</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">{{ $delivery->delivery_number }}</h1>
            <p class="text-xs text-slate-500">Petugas Pengantar: <span class="font-bold text-slate-700">{{ $delivery->driver?->name ?? 'Admin' }}</span></p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.orders.show', $delivery->order) }}" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Order
            </a>
            @if ($delivery->order->delivery_latitude && $delivery->order->delivery_longitude)
                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $delivery->order->delivery_latitude }},{{ $delivery->order->delivery_longitude }}"
                   target="_blank"
                   class="px-4 py-2 rounded-xl text-xs font-bold bg-cyan-600 hover:bg-cyan-700 text-white shadow-xs transition flex items-center">
                    <i class="fa-solid fa-diamond-turn-right mr-1.5"></i> Buka Google Maps
                </a>
            @endif
        </div>
    </div>

    <!-- Live GPS Transmitter Notification (when in transit) -->
    @if ($delivery->status === 'in_transit')
        <div class="bg-emerald-50 border border-emerald-200 p-4 rounded-2xl flex items-center justify-between text-xs text-emerald-900">
            <div class="flex items-center space-x-3">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-600"></span>
                </span>
                <div>
                    <span class="font-bold">Pemancar GPS Real-Time Aktif</span>
                    <p class="text-[11px] text-emerald-700 mt-0.5" x-text="gpsStatusText">Mengirim koordinat lokasi ke pelanggan & radar...</p>
                </div>
            </div>
            <span class="text-[10px] bg-white px-2.5 py-1 rounded-lg border border-emerald-200 font-mono text-emerald-800" x-text="'Sent: ' + pingCount + 'x'"></span>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Customer & Destination Info -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-4">
            <h2 class="font-bold text-sm text-slate-900 uppercase tracking-wider flex items-center">
                <i class="fa-solid fa-location-dot text-cyan-600 mr-2"></i> Alamat & Penerima
            </h2>

            <div class="space-y-2 text-xs">
                <div>
                    <span class="text-slate-400 block">Penerima:</span>
                    <span class="font-bold text-slate-800 text-sm">{{ $delivery->order->recipient_name }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Nomor Telepon:</span>
                    <span class="font-bold text-slate-800">{{ $delivery->order->recipient_phone }}</span>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $delivery->order->recipient_phone) }}" target="_blank"
                       class="inline-flex items-center text-emerald-600 font-bold ml-2 hover:underline">
                        <i class="fa-brands fa-whatsapp mr-1"></i> WhatsApp
                    </a>
                </div>
                <div>
                    <span class="text-slate-400 block">Alamat Tujuan:</span>
                    <p class="text-slate-700 font-medium leading-relaxed mt-0.5">{{ $delivery->order->delivery_address_text }}</p>
                </div>
                @if ($delivery->order->address?->benchmark)
                    <div class="p-2.5 bg-amber-50 rounded-xl border border-amber-200 text-amber-900">
                        <span class="font-bold block text-[11px]">Patokan:</span>
                        {{ $delivery->order->address->benchmark }}
                    </div>
                @endif
            </div>

            <!-- Items to Deliver -->
            <div class="pt-3 border-t border-slate-100">
                <h3 class="font-bold text-xs text-slate-700 uppercase mb-2">Item Galon / Air:</h3>
                <ul class="space-y-2 text-xs">
                    @foreach ($delivery->order->items as $item)
                        <li class="flex justify-between items-center bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            <div>
                                <span class="font-bold text-slate-800">{{ $item->product_name }}</span>
                                @if ($item->is_exchange)
                                    <span class="text-[10px] text-cyan-600 font-bold ml-1">(Tukar Galon)</span>
                                @endif
                            </div>
                            <span class="font-bold text-cyan-700 font-mono">{{ $item->quantity }} pcs</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- Action & Delivery Controls -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-5">
            <h2 class="font-bold text-sm text-slate-900 uppercase tracking-wider flex items-center">
                <i class="fa-solid fa-sliders text-indigo-600 mr-2"></i> Kontrol Status Pengantaran
            </h2>

            @if ($delivery->status === 'assigned')
                <div class="p-4 bg-amber-50 rounded-2xl border border-amber-200 text-xs text-amber-900 space-y-3">
                    <p class="font-semibold">Pesanan siap diantar. Klik tombol di bawah untuk memulai perjalanan ke lokasi pelanggan.</p>
                    <form method="POST" action="{{ route('admin.deliveries.start', $delivery) }}">
                        @csrf
                        <button type="submit" class="w-full py-2.5 bg-cyan-600 hover:bg-cyan-700 text-white font-bold rounded-xl shadow-xs transition flex items-center justify-center">
                            <i class="fa-solid fa-play mr-2"></i> Mulai Pengantaran Sekarang
                        </button>
                    </form>
                </div>
            @endif

            @if ($delivery->status === 'in_transit')
                <div class="space-y-3">
                    <form method="POST" action="{{ route('admin.deliveries.arrive', $delivery) }}">
                        @csrf
                        <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-xs transition flex items-center justify-center">
                            <i class="fa-solid fa-location-crosshairs mr-2"></i> Tiba di Lokasi Pelanggan
                        </button>
                    </form>
                </div>
            @endif

            @if (in_array($delivery->status, ['in_transit', 'arrived']))
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-4">
                    <h3 class="font-bold text-xs text-slate-900 uppercase">Selesaikan & Bukti Pengantaran</h3>

                    <form method="POST" action="{{ route('admin.deliveries.complete', $delivery) }}" enctype="multipart/form-data" class="space-y-3">
                        @csrf

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Foto Bukti Serah Terima (Opsional)</label>
                            <input type="file" name="photo" accept="image/*" capture="environment"
                                   class="w-full text-xs border border-slate-300 rounded-xl p-2 bg-white file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-cyan-50 file:text-cyan-700 hover:file:bg-cyan-100">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Galon Kosong Ditarik:</label>
                            <input type="number" name="empty_gallons_returned" value="{{ $delivery->order->empty_gallons_returned }}" min="0"
                                   class="w-full text-xs border border-slate-300 rounded-xl p-2.5 font-bold">
                        </div>

                        @if ($delivery->order->payment_method === 'cod')
                            <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs">
                                <label class="block font-bold text-amber-900 mb-1">Tagihan Tunai (COD): Rp {{ number_format($delivery->order->total_amount, 0, ',', '.') }}</label>
                                <input type="number" name="payment_received" value="{{ $delivery->order->total_amount }}"
                                       class="w-full text-xs border border-slate-300 rounded-xl p-2 bg-white font-bold text-emerald-700">
                            </div>
                        @endif

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan (Opsional):</label>
                            <input type="text" name="driver_notes" placeholder="Contoh: Diterima langsung oleh pelanggan di teras..."
                                   class="w-full text-xs border border-slate-300 rounded-xl p-2.5">
                        </div>

                        <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition flex items-center justify-center">
                            <i class="fa-solid fa-check-double mr-2"></i> Konfirmasi Pesanan Selesai
                        </button>
                    </form>
                </div>
            @endif

            @if ($delivery->status === 'delivered')
                <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-200 text-center space-y-2 text-xs">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto text-lg font-bold">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <div class="font-bold text-emerald-900 text-sm">Pengantaran Telah Selesai</div>
                    <div class="text-slate-500">Selesai pada: {{ $delivery->completed_at?->format('d M Y, H:i') }} WIB</div>
                </div>
            @endif
        </div>
    </div>

</div>

<script>
function adminDeliveryExecution(config) {
    return {
        pingCount: 0,
        gpsStatusText: 'Menginisialisasi GPS...',
        intervalId: null,

        init() {
            if (config.status === 'in_transit') {
                this.startBroadcastingGps();
            }
        },

        startBroadcastingGps() {
            if (!navigator.geolocation) {
                this.gpsStatusText = 'Perangkat tidak mendukung geolokasi.';
                return;
            }

            const sendCoords = (pos) => {
                const payload = {
                    latitude: pos.coords.latitude,
                    longitude: pos.coords.longitude,
                    accuracy: pos.coords.accuracy || 10,
                    speed: pos.coords.speed || 0,
                    heading: pos.coords.heading || 0
                };

                fetch(config.locationApiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(payload)
                })
                .then(r => r.json())
                .then(data => {
                    this.pingCount++;
                    this.gpsStatusText = `Koordinat terkirim (${pos.coords.latitude.toFixed(4)}, ${pos.coords.longitude.toFixed(4)})`;
                })
                .catch(err => {
                    this.gpsStatusText = 'Gagal mengirim koordinat lokasi.';
                });
            };

            navigator.geolocation.getCurrentPosition(sendCoords);
            this.intervalId = setInterval(() => {
                navigator.geolocation.getCurrentPosition(sendCoords);
            }, 8000);
        }
    };
}
</script>
@endsection
