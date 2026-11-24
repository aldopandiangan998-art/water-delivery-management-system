@extends('layouts.customer')

@section('title', 'Profil & Lokasi Toko Nova Jaya')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    @keyframes depotPulse {
        0% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.7); }
        70% { box-shadow: 0 0 0 14px rgba(37, 99, 235, 0); }
        100% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
    }
    .depot-pulse-icon {
        animation: depotPulse 2s infinite;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6"
     x-data="customerStoreMap({
         lat: {{ $settings['store_latitude'] }},
         lng: {{ $settings['store_longitude'] }},
         name: '{{ addslashes($settings['company_name']) }}',
         address: '{{ addslashes($settings['company_address']) }}',
         gmapsUrl: '{{ $settings['google_maps_url'] }}'
     })">

    <!-- Hero Header: Store Identity -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div class="flex items-start sm:items-center space-x-4">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-cyan-600 via-blue-600 to-indigo-700 text-white flex items-center justify-center font-black text-2xl shadow-md shrink-0">
                <i class="fa-solid fa-store"></i>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">{{ $settings['company_name'] }}</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse mr-1.5"></span>
                        Depot Resmi Terverifikasi
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1 max-w-2xl">
                    {{ $settings['company_tagline'] }}
                </p>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 mt-2.5 text-xs text-slate-600">
                    <span class="flex items-center">
                        <i class="fa-solid fa-location-dot text-rose-500 mr-1.5"></i> Bratang Gede, Wonokromo, Surabaya
                    </span>
                    <span class="flex items-center">
                        <i class="fa-regular fa-clock text-blue-600 mr-1.5"></i> {{ $settings['business_hours'] }}
                    </span>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <a href="{{ $settings['google_maps_url'] }}" target="_blank"
               class="px-4 py-2.5 rounded-2xl font-bold text-xs text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 transition flex items-center shadow-2xs">
                <i class="fa-solid fa-map-location-dot mr-2 text-blue-600"></i> Buka di Google Maps
            </a>
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $settings['company_whatsapp']) }}?text=Halo%20Toko%20Nova%20Jaya,%20saya%20pelanggan%20ingin%20bertanya" target="_blank"
               class="px-4 py-2.5 rounded-2xl font-bold text-xs text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition flex items-center shadow-2xs">
                <i class="fa-brands fa-whatsapp mr-2 text-emerald-600 text-sm"></i> WhatsApp Toko
            </a>
            <a href="{{ route('customer.orders.create') }}" 
               class="px-5 py-2.5 rounded-2xl font-extrabold text-xs text-white bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 shadow-md hover:shadow-lg transition flex items-center">
                <i class="fa-solid fa-cart-plus mr-2"></i> Pesan Air Sekarang
            </a>
        </div>
    </div>

    <!-- Main Grid: Map (8 cols) & Store Info (4 cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Interactive Store Location Map (8 cols) -->
        <div class="lg:col-span-8 bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-xs flex flex-col">
            <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900 flex items-center">
                        <i class="fa-solid fa-map-pin text-rose-500 mr-2"></i> Peta Lokasi Toko & Depot
                    </h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                        Titik presisi Toko Nova Jaya berdasarkan koordinat resmi Google Maps Kota Surabaya.
                    </p>
                </div>

                <div class="flex items-center space-x-2 text-xs">
                    <button type="button" @click="recenterMap()"
                            class="px-3 py-1.5 rounded-xl bg-white hover:bg-slate-100 text-slate-700 font-bold border border-slate-200 transition flex items-center shadow-2xs cursor-pointer"
                            title="Pusatkan kembali ke Toko Nova Jaya">
                        <i class="fa-solid fa-location-crosshairs mr-1.5 text-cyan-600"></i> Pusatkan Toko
                    </button>
                    <a :href="gmapsUrl" target="_blank"
                       class="px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold transition flex items-center shadow-2xs"
                       title="Petunjuk arah via Google Maps">
                        <i class="fa-solid fa-diamond-turn-right mr-1.5"></i> Navigasi Rute
                    </a>
                </div>
            </div>

            <!-- Leaflet Map Container -->
            <div class="relative w-full h-[450px]">
                <div id="storeLocationMap" class="w-full h-full z-10"></div>

                <!-- Floating Coordinates Pill on Map -->
                <div class="absolute bottom-4 left-4 z-20 bg-white/95 backdrop-blur-xs px-3.5 py-2 rounded-2xl shadow-lg border border-slate-200 text-xs flex items-center space-x-3">
                    <div>
                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Koordinat GPS</div>
                        <div class="font-mono font-bold text-slate-800 text-[11px]" x-text="lat.toFixed(6) + ', ' + lng.toFixed(6)"></div>
                    </div>
                    <button type="button" @click="copyCoords()" 
                            class="p-1.5 hover:bg-slate-100 rounded-lg text-slate-500 hover:text-slate-800 transition cursor-pointer"
                            title="Salin Koordinat">
                        <i class="fa-solid fa-copy"></i>
                    </button>
                    <span x-show="copied" x-cloak class="text-[10px] text-emerald-600 font-bold">Disalin!</span>
                </div>
            </div>

            <!-- Direct Driving/Visiting Guide Banner -->
            <div class="p-4 bg-slate-50 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-600">
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                    <span>Pelanggan bisa datang langsung untuk beli air galon di tempat atau pesan online untuk diantar.</span>
                </div>
                <a :href="gmapsUrl" target="_blank" class="font-bold text-blue-600 hover:underline flex items-center shrink-0">
                    Buka Google Maps Navigator &rarr;
                </a>
            </div>
        </div>

        <!-- Store Information Sidebar (4 cols) -->
        <div class="lg:col-span-4 space-y-4">

            <!-- Detail Alamat & Patokan Card -->
            <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs space-y-3">
                <div class="flex items-center space-x-2 text-slate-900 font-black text-sm">
                    <div class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-map-location"></i>
                    </div>
                    <h3>Alamat Lengkap Depot</h3>
                </div>

                <div class="text-xs text-slate-600 leading-relaxed bg-slate-50 p-3.5 rounded-2xl border border-slate-100 space-y-2">
                    <p class="font-bold text-slate-800">
                        {{ $settings['company_address'] }}
                    </p>
                    <div class="pt-2 border-t border-slate-200/80 text-[11px] text-slate-500">
                        <strong class="text-slate-700">Patokan Lokasi:</strong> Area Jl. Bratang Gede Gang VI H, Ngagelrejo, Kecamatan Wonokromo, Kota Surabaya (Akses mudah dari arah Jl. Ngagel Jaya Selatan dan Stasiun Wonokromo).
                    </div>
                </div>
            </div>

            <!-- Jam Buka & Layanan Pengantaran Card -->
            <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs space-y-3">
                <div class="flex items-center space-x-2 text-slate-900 font-black text-sm">
                    <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                        <i class="fa-regular fa-clock"></i>
                    </div>
                    <h3>Jam Buka & Operasional</h3>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200">
                        <span class="font-bold">Senin - Minggu (Setiap Hari)</span>
                        <span class="font-mono font-extrabold">07:00 - 20:00 WIB</span>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 text-[11px] text-slate-600 space-y-1">
                        <div class="font-bold text-slate-700 flex items-center">
                            <i class="fa-solid fa-bolt text-amber-500 mr-1.5"></i> Layanan Antar Cepat
                        </div>
                        <p>Pesanan yang masuk pada jam operasional akan langsung diproses dan dikirim kurir lapangan rata-rata 20-40 menit.</p>
                    </div>
                </div>
            </div>

            <!-- Kontak Toko & Pembayaran Card -->
            <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs space-y-3">
                <div class="flex items-center space-x-2 text-slate-900 font-black text-sm">
                    <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-headset"></i>
                    </div>
                    <h3>Kontak & Rekening Resmi</h3>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-500">Telepon / WhatsApp</span>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $settings['company_whatsapp']) }}" target="_blank" class="font-bold text-emerald-700 hover:underline">
                            {{ $settings['company_phone'] }}
                        </a>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-500">Email Bantuan</span>
                        <span class="font-medium text-slate-700">{{ $settings['company_email'] }}</span>
                    </div>

                    <div class="p-3 bg-cyan-50/60 rounded-2xl border border-cyan-100 text-[11px] space-y-1">
                        <div class="text-slate-500 font-bold uppercase tracking-wider text-[10px]">Transfer Bank Resmi</div>
                        <div class="font-bold text-slate-900">{{ $settings['bank_name'] }}: <span class="font-mono text-cyan-800 text-xs">{{ $settings['bank_account_number'] }}</span></div>
                        <div class="text-[10px] text-slate-500">a/n {{ $settings['bank_account_name'] }}</div>
                        <div class="text-[10px] text-emerald-700 font-semibold pt-1">✓ Tersedia opsi Bayar di Tempat (COD)</div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Section: Service Areas (Area Jangkauan Pengiriman di Surabaya) -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 flex items-center">
                    <i class="fa-solid fa-truck-fast text-cyan-600 mr-2"></i> Area Jangkauan Pengantaran Surabaya
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Depot Toko Nova Jaya melayani pengantaran galon ke berbagai wilayah kecamatan di Kota Surabaya.
                </p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-cyan-50 text-cyan-800 border border-cyan-200 self-start sm:self-auto">
                {{ $serviceAreas->count() }} Wilayah Aktif
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
            @forelse ($serviceAreas as $area)
                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/60 hover:bg-cyan-50/50 hover:border-cyan-200 transition space-y-2">
                    <div class="flex items-center justify-between">
                        <h4 class="font-bold text-xs text-slate-900">{{ $area->name }}</h4>
                        <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full {{ $area->delivery_fee == 0 ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-slate-200 text-slate-700' }}">
                            {{ $area->delivery_fee == 0 ? 'GRATIS ONGKIR' : 'Ongkir Rp ' . number_format($area->delivery_fee, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="text-[11px] text-slate-500 space-y-0.5">
                        <div class="flex items-center">
                            <i class="fa-solid fa-city w-4 mr-1 text-slate-400"></i> {{ $area->district }} (Kode Pos: {{ $area->postal_code ?? '-' }})
                        </div>
                        <div class="flex items-center">
                            <i class="fa-regular fa-clock w-4 mr-1 text-slate-400"></i> Estimasi Antar: <span class="font-semibold text-slate-700 ml-1">{{ $area->estimated_delivery_time ?? '30 menit' }}</span>
                        </div>
                        @if ($area->notes)
                            <div class="text-[10px] text-cyan-700 italic pt-1">
                                &bull; {{ $area->notes }}
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-6 text-slate-400 text-xs">
                    Informasi area jangkauan belum diatur.
                </div>
            @endforelse
        </div>
    </div>

</div>

<!-- Leaflet Map Script -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
function customerStoreMap(config) {
    return {
        lat: Number(config.lat),
        lng: Number(config.lng),
        name: config.name,
        address: config.address,
        gmapsUrl: config.gmapsUrl,
        map: null,
        marker: null,
        copied: false,

        init() {
            this.$nextTick(() => {
                this.initMap();
            });
        },

        initMap() {
            if (!window.L) return;

            // Bounding Box Khusus Kota Surabaya
            const surabayaBounds = L.latLngBounds(
                L.latLng(-7.3800, 112.5800),
                L.latLng(-7.1500, 112.8600)
            );

            this.map = L.map('storeLocationMap', {
                maxBounds: surabayaBounds,
                maxBoundsViscosity: 0.9,
                minZoom: 12
            }).setView([this.lat, this.lng], 16);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors • Bratang Gede, Wonokromo, Surabaya'
            }).addTo(this.map);

            // Custom Store Marker with Glow
            const storeIcon = L.divIcon({
                className: 'store-pin',
                html: `<div class="depot-pulse-icon" style="background: linear-gradient(135deg, #0284c7, #2563eb); color: white; border-radius: 50%; width: 46px; height: 46px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(37,99,235,0.5); border: 3px solid white;">
                        <i class="fa-solid fa-store" style="font-size: 20px;"></i>
                       </div>`,
                iconSize: [46, 46],
                iconAnchor: [23, 23]
            });

            this.marker = L.marker([this.lat, this.lng], { icon: storeIcon })
                .addTo(this.map)
                .bindPopup(`
                    <div style="min-width: 220px; font-family: inherit;">
                        <span style="background: #dbeafe; color: #1d4ed8; font-size: 9px; font-weight: 800; padding: 2px 6px; border-radius: 6px; text-transform: uppercase;">Depot Resmi Toko</span>
                        <h4 style="font-size: 13px; font-weight: 800; color: #0f172a; margin-top: 4px; margin-bottom: 2px;">${this.name}</h4>
                        <p style="font-size: 11px; color: #475569; margin: 0; line-height: 1.3;">${this.address}</p>
                        <div style="margin-top: 8px; pt-2; border-top: 1px solid #e2e8f0;">
                            <a href="${this.gmapsUrl}" target="_blank" 
                               style="color: #2563eb; font-weight: 700; font-size: 11px; text-decoration: none; display: flex; align-items: center;">
                                <i class="fa-solid fa-arrow-up-right-from-square" style="margin-right: 4px;"></i> Buka di Google Maps
                            </a>
                        </div>
                    </div>
                `)
                .openPopup();
        },

        recenterMap() {
            if (!this.map) return;
            this.map.flyTo([this.lat, this.lng], 16, { duration: 1.2 });
            if (this.marker) this.marker.openPopup();
        },

        copyCoords() {
            const coordStr = `${this.lat.toFixed(6)}, ${this.lng.toFixed(6)}`;
            navigator.clipboard.writeText(coordStr);
            this.copied = true;
            setTimeout(() => { this.copied = false; }, 2500);
        }
    };
}
</script>
@endsection
