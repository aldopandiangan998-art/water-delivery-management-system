@extends('layouts.driver')

@section('title', 'Tugas #' . $delivery->order->order_number)

@section('content')
<div class="space-y-4" 
     x-data="driverDelivery({
         deliveryId: {{ $delivery->id }},
         status: '{{ $delivery->status }}',
         customerLat: {{ (float) $delivery->order->delivery_latitude }},
         customerLng: {{ (float) $delivery->order->delivery_longitude }},
         customerAddress: '{{ addslashes($delivery->order->delivery_address_text) }}',
         locationApiUrl: '{{ route('driver.deliveries.location', $delivery) }}',
     })">

    <!-- Top Status & GPS broadcaster badge -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase">Pesanan #{{ $delivery->order->order_number }}</span>
                <h1 class="text-base font-black text-slate-900">{{ $delivery->order->recipient_name }}</h1>
            </div>
            <span class="text-xs font-bold px-2.5 py-0.5 rounded-full border {{ $delivery->status_badge['color'] }}">
                {{ $delivery->status_badge['label'] }}
            </span>
        </div>

        <!-- Live GPS Tracker Status Pill -->
        <div x-show="status === 'in_transit'" class="p-2.5 bg-emerald-50 border border-emerald-200 rounded-xl text-xs flex items-center justify-between">
            <div class="flex items-center text-emerald-800 font-semibold">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mr-2 animate-ping"></span>
                <span x-text="gpsStatusText">Mengaktifkan GPS kurir...</span>
            </div>
            <span class="text-[10px] text-emerald-600 font-mono" x-text="lastCoordinates"></span>
        </div>
    </div>

    <!-- Quick Navigation & Contact Buttons -->
    <div class="grid grid-cols-2 gap-2">
        <!-- Direct Google Maps Navigation -->
        <a :href="'https://www.google.com/maps/dir/?api=1&destination=' + customerLat + ',' + customerLng" 
           target="_blank"
           class="py-3 px-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold text-center flex items-center justify-center shadow-xs">
            <i class="fa-solid fa-diamond-turn-right mr-1.5 text-base text-yellow-300"></i> Buka Google Maps
        </a>

        <!-- Call / WhatsApp Customer -->
        @if ($delivery->order->recipient_phone)
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $delivery->order->recipient_phone) }}?text=Halo%20{{ urlencode($delivery->order->recipient_name) }},%20saya%20kurir%20AquaFlow%20sedang%20mengantar%20air%20galon%20pesanan%20Anda."
               target="_blank"
               class="py-3 px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold text-center flex items-center justify-center shadow-xs">
                <i class="fa-brands fa-whatsapp mr-1.5 text-base"></i> Hubungi Pembeli
            </a>
        @endif
    </div>

    <!-- Map Preview -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div id="driverMap" class="w-full h-56 z-10"></div>
        <div class="p-3 text-xs text-slate-600 bg-slate-50 border-t border-slate-100 flex items-start space-x-2">
            <i class="fa-solid fa-location-dot text-rose-500 mt-0.5"></i>
            <div>
                <span class="font-bold text-slate-800">Alamat:</span> {{ $delivery->order->delivery_address_text }}
                @if ($delivery->order->delivery_benchmark)
                    <div class="text-[11px] text-slate-500 italic mt-0.5">Patokan: {{ $delivery->order->delivery_benchmark }}</div>
                @endif
            </div>
        </div>
    </div>

    <!-- Items & Payment info -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs space-y-2 text-xs">
        <div class="font-bold text-slate-800 uppercase tracking-wider text-[11px] pb-1 border-b border-slate-100">
            Daftar Galon Diantar
        </div>
        @foreach ($delivery->order->items as $item)
            <div class="flex justify-between py-1">
                <span class="font-medium text-slate-800">{{ $item->quantity }}x {{ $item->product_name }}</span>
                @if ($item->is_exchange)
                    <span class="text-blue-600 font-semibold">(Tukar {{ $item->quantity }} Galon Kosong)</span>
                @endif
            </div>
        @endforeach

        <div class="pt-2 border-t border-slate-100 flex justify-between font-bold text-sm text-slate-900">
            <span>Total Tagihan ({{ strtoupper($delivery->order->payment_method) }}):</span>
            <span class="text-cyan-700">Rp {{ number_format($delivery->order->total_amount, 0, ',', '.') }}</span>
        </div>
    </div>

    <!-- ACTION BUTTONS ACCORDING TO CURRENT STATUS -->
    @if ($delivery->status === 'assigned')
        <!-- 1. Start Delivery -->
        <form method="POST" action="{{ route('driver.deliveries.start', $delivery) }}">
            @csrf
            <button type="submit" 
                    class="w-full py-4 px-4 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white rounded-2xl font-black text-sm shadow-md transition flex items-center justify-center">
                <i class="fa-solid fa-play mr-2"></i> Mulai Pengantaran (In Transit)
            </button>
        </form>
    @elseif ($delivery->status === 'in_transit')
        <!-- 2. Arrived at Destination -->
        <form method="POST" action="{{ route('driver.deliveries.arrive', $delivery) }}">
            @csrf
            <button type="submit" 
                    class="w-full py-4 px-4 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white rounded-2xl font-black text-sm shadow-md transition flex items-center justify-center">
                <i class="fa-solid fa-location-dot mr-2"></i> Saya Sudah Tiba di Lokasi (Arrived)
            </button>
        </form>
    @elseif ($delivery->status === 'arrived')
        <!-- 3. Complete Delivery Form -->
        <div class="bg-white p-5 rounded-2xl border-2 border-emerald-500 shadow-md space-y-4">
            <h3 class="font-extrabold text-sm text-slate-900 flex items-center">
                <i class="fa-solid fa-circle-check text-emerald-600 mr-2 text-base"></i> Konfirmasi Selesai Antar
            </h3>

            <form method="POST" action="{{ route('driver.deliveries.complete', $delivery) }}" enctype="multipart/form-data" class="space-y-3">
                @csrf

                <!-- Delivery Proof Photo -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        <i class="fa-solid fa-camera mr-1 text-cyan-600"></i> Ambil Foto Bukti Serah Terima (Wajib):
                    </label>
                    <input type="file" name="photo" accept="image/*" capture="environment" required
                           class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-cyan-50 file:text-cyan-700 hover:file:bg-cyan-100">
                </div>

                <!-- Empty Gallons Returned -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Jumlah Galon Kosong yang Diterima Kembali:
                    </label>
                    <input type="number" name="empty_gallons_returned" value="{{ $delivery->order->empty_gallons_returned }}" min="0" max="50"
                           class="w-full text-sm font-bold border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-emerald-500">
                </div>

                <!-- COD Payment Confirmation -->
                @if ($delivery->order->payment_method === 'cod')
                    <div class="bg-amber-50 p-3 rounded-xl border border-amber-200">
                        <label class="block text-xs font-bold text-amber-900 mb-1">
                            Uang Tunai COD Diterima (Rp):
                        </label>
                        <input type="number" name="payment_received" value="{{ $delivery->order->total_amount }}" required
                               class="w-full text-sm font-bold border border-amber-300 rounded-xl p-2.5 bg-white text-slate-900">
                    </div>
                @endif

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Catatan Pengantaran (Opsional):</label>
                    <input type="text" name="driver_notes" placeholder="Contoh: Diterima oleh istri pelanggan"
                           class="w-full text-xs border border-slate-300 rounded-xl p-2.5">
                </div>

                <button type="submit" 
                        class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-black text-sm shadow-md transition flex items-center justify-center">
                    <i class="fa-solid fa-check-double mr-2"></i> Serahkan & Selesaikan Tugas
                </button>
            </form>
        </div>
    @elseif ($delivery->status === 'delivered')
        <div class="bg-emerald-50 border border-emerald-200 p-4 rounded-2xl text-center space-y-1">
            <div class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center mx-auto text-lg">
                <i class="fa-solid fa-check"></i>
            </div>
            <h4 class="font-bold text-sm text-emerald-900">Pengantaran Ini Sudah Selesai</h4>
            <p class="text-xs text-emerald-700">Terima kasih atas kerja keras Anda mengantar air minum tepat waktu!</p>
        </div>
    @endif

</div>

<script>
function driverDelivery(config) {
    return {
        deliveryId: config.deliveryId,
        status: config.status,
        customerLat: config.customerLat,
        customerLng: config.customerLng,
        customerAddress: config.customerAddress,
        locationApiUrl: config.locationApiUrl,
        map: null,
        watchId: null,
        gpsStatusText: 'GPS Aktif',
        lastCoordinates: '',

        init() {
            this.$nextTick(() => {
                this.initMap();
                if (this.status === 'in_transit') {
                    this.startGpsBroadcast();
                }
            });
        },

        initMap() {
            if (!window.L) return;

            this.map = L.map('driverMap').setView([this.customerLat, this.customerLng], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(this.map);

            L.marker([this.customerLat, this.customerLng])
                .addTo(this.map)
                .bindPopup(`<b>Alamat Pembeli</b><br>${this.customerAddress}`)
                .openPopup();
        },

        startGpsBroadcast() {
            if (!navigator.geolocation) {
                this.gpsStatusText = 'Browser tidak mendukung GPS';
                return;
            }

            this.gpsStatusText = '🟢 Mengirim Lokasi ke Pelanggan...';

            // Send immediate position
            this.sendCurrentPosition();

            // Setup continuous watcher
            this.watchId = navigator.geolocation.watchPosition(
                (pos) => {
                    this.postCoordinates(pos.coords);
                },
                (err) => {
                    console.warn('GPS error:', err);
                    this.gpsStatusText = 'Sinyal GPS lemah / belum diizinkan';
                },
                {
                    enableHighAccuracy: true,
                    maximumAge: 10000,
                    timeout: 10000
                }
            );
        },

        sendCurrentPosition() {
            navigator.geolocation.getCurrentPosition((pos) => {
                this.postCoordinates(pos.coords);
            });
        },

        async postCoordinates(coords) {
            this.lastCoordinates = `${coords.latitude.toFixed(4)}, ${coords.longitude.toFixed(4)}`;
            try {
                await fetch(this.locationApiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        latitude: coords.latitude,
                        longitude: coords.longitude,
                        accuracy: coords.accuracy,
                        heading: coords.heading,
                        speed: coords.speed
                    })
                });
            } catch (e) {
                console.error('Failed to post driver location:', e);
            }
        }
    };
}
</script>
@endsection
