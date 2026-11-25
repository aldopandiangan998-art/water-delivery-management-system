@extends('layouts.customer')

@section('title', 'Lacak Pengantaran Live - #' . $order->order_number)

@section('breadcrumbs')
    <x-breadcrumbs :items="[
        ['title' => __('Riwayat Pesanan'), 'url' => route('customer.orders.index')],
        ['title' => __('Lacak Kurir #') . $order->order_number]
    ]" />
@endsection

@push('styles')
<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<style>
    #liveMap {
        height: 550px !important;
        min-height: 550px !important;
        width: 100% !important;
        z-index: 10;
    }
    .leaflet-pane {
        z-index: 10 !important;
    }
    .leaflet-bottom, .leaflet-top {
        z-index: 20 !important;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6" 
     x-data="liveTracking({{ Js::from([
         'orderNumber' => $order->order_number,
         'customerLat' => (float) $order->delivery_latitude,
         'customerLng' => (float) $order->delivery_longitude,
         'driverLat' => (float) ($order->delivery?->latestLocation?->latitude ?? $order->delivery_latitude + 0.003),
         'driverLng' => (float) ($order->delivery?->latestLocation?->longitude ?? $order->delivery_longitude + 0.003),
         'driverName' => 'Petugas Depot',
         'driverPhone' => '',
         'apiEndpoint' => route('customer.orders.api-location', $order),
     ]) }})">

    <!-- Top Status Banner -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="w-3 h-3 rounded-full bg-emerald-500 animate-ping"></span>
                <h1 class="text-lg font-black text-slate-900 tracking-tight">Pelacakan Langsung (Live GPS)</h1>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-cyan-100 text-cyan-800">
                    {{ $order->order_number }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Tujuan: <span class="font-medium text-slate-700">{{ $order->delivery_address_text }}</span>
            </p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('customer.orders.show', $order) }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                Rincian Pesanan
            </a>
        </div>
    </div>

    <!-- Stale GPS Warning Banner -->
    <div x-show="isStale" x-cloak class="p-3.5 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-xs font-semibold flex items-center">
        <i class="fa-solid fa-triangle-exclamation text-amber-600 mr-2 text-base"></i>
        <span>Peringatan: Sinyal GPS pengantaran terakhir diperbarui beberapa menit lalu (kemungkinan sinyal sedang lemah).</span>
    </div>

    <!-- Map Container -->
    <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-md relative" style="height: 550px; min-height: 550px;">
        <div id="liveMap" class="w-full z-10" style="height: 550px; min-height: 550px; width: 100%;"></div>

        <!-- Floating Info Overlay on Map -->
        <div class="absolute bottom-5 left-5 z-20 bg-white/95 backdrop-blur-xs p-4 rounded-2xl shadow-lg border border-slate-200 max-w-xs text-xs space-y-1.5">
            <div class="font-bold text-slate-900 flex items-center">
                <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2 animate-pulse"></span>
                Status: <span class="ml-1 text-cyan-700" x-text="deliveryStatusLabel"></span>
            </div>
            <div class="text-slate-500" x-show="lastUpdated">
                Pembaruan Terakhir: <span class="font-semibold text-slate-700" x-text="lastUpdated"></span>
            </div>
            <div class="text-[11px] text-slate-400">
                Data lokasi diperbarui otomatis setiap 4 detik.
            </div>
        </div>
    </div>
</div>

<script>
function liveTracking(config) {
    return {
        orderNumber: config.orderNumber,
        customerLat: config.customerLat,
        customerLng: config.customerLng,
        driverLat: config.driverLat,
        driverLng: config.driverLng,
        driverName: config.driverName,
        driverPhone: config.driverPhone,
        apiEndpoint: config.apiEndpoint,
        deliveryStatusLabel: 'Dalam Perjalanan',
        lastUpdated: 'Baru saja',
        isStale: false,
        map: null,
        driverMarker: null,
        customerMarker: null,
        pollInterval: null,

        init() {
            this.$nextTick(() => {
                const initWhenReady = () => {
                    if (window.L && typeof window.L.map === 'function') {
                        this.initMap();
                        this.startPolling();
                    } else {
                        setTimeout(initWhenReady, 100);
                    }
                };
                initWhenReady();
            });
        },

        initMap() {
            if (!window.L || this.map) return;

            const mapDiv = document.getElementById('liveMap');
            if (!mapDiv) return;

            // Initialize Leaflet map (Area Kota Surabaya - Toko Nova Jaya Bratang Gede)
            const centerLat = this.customerLat || -7.3005;
            const centerLng = this.customerLng || 112.7525;

            const surabayaBounds = L.latLngBounds(
                L.latLng(-7.3800, 112.5800),
                L.latLng(-7.1500, 112.8600)
            );

            this.map = L.map(mapDiv, {
                maxBounds: surabayaBounds,
                maxBoundsViscosity: 0.9,
                minZoom: 11
            }).setView([centerLat, centerLng], 15);

            // Layer OpenStreetMap
            const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors • Wilayah Kota Surabaya'
            }).addTo(this.map);

            // Layer Google Satelit Hybrid (Free XYZ tile)
            const googleSatLayer = L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
                attribution: '&copy; Google Maps Satelit'
            });

            // Layer Control
            L.control.layers({
                'Peta Jalan': osmLayer,
                'Satelit Google': googleSatLayer
            }, null, { position: 'topright' }).addTo(this.map);

            // Customer Destination Marker (Blue house)
            const customerIcon = L.divIcon({
                className: 'custom-customer-icon',
                html: `<div style="background-color: #0284c7; color: white; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 6px rgba(0,0,0,0.3); border: 2px solid white;">
                        <i class="fa-solid fa-house" style="font-size: 16px;"></i>
                       </div>`,
                iconSize: [36, 36],
                iconAnchor: [18, 18]
            });

            this.customerMarker = L.marker([this.customerLat, this.customerLng], { icon: customerIcon })
                .addTo(this.map)
                .bindPopup(`<b>Alamat Pengantaran</b><br>Pesanan #${this.orderNumber}`)
                .openPopup();

            // Driver Marker (Green Motorbike)
            const driverIcon = L.divIcon({
                className: 'custom-driver-icon',
                html: `<div style="background-color: #10b981; color: white; border-radius: 50%; width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 8px rgba(0,0,0,0.4); border: 3px solid white;">
                        <i class="fa-solid fa-motorcycle" style="font-size: 20px;"></i>
                       </div>`,
                iconSize: [42, 42],
                iconAnchor: [21, 21]
            });

            this.driverMarker = L.marker([this.driverLat, this.driverLng], { icon: driverIcon })
                .addTo(this.map)
                .bindPopup(`<b>Armada Pengantaran</b><br>Sedang dalam perjalanan`);

            // Fit bounds to show both customer and driver
            const group = new L.featureGroup([this.customerMarker, this.driverMarker]);
            this.map.fitBounds(group.getBounds().pad(0.3));

            // Staggered invalidateSize calls to ensure clean paint
            setTimeout(() => {
                if (this.map) this.map.invalidateSize();
            }, 150);
            setTimeout(() => {
                if (this.map) this.map.invalidateSize();
            }, 500);
        },

        startPolling() {
            this.pollInterval = setInterval(() => {
                this.fetchLocation();
            }, 4000);
        },

        async fetchLocation() {
            try {
                const response = await fetch(this.apiEndpoint);
                if (!response.ok) return;

                const data = await response.json();
                if (data.driver_lat && data.driver_lng) {
                    this.driverLat = data.driver_lat;
                    this.driverLng = data.driver_lng;
                    this.driverName = data.driver_name;
                    this.driverPhone = data.driver_phone;
                    this.isStale = data.is_stale;
                    this.lastUpdated = data.recorded_at || 'Baru saja';

                    if (this.driverMarker) {
                        this.driverMarker.setLatLng([this.driverLat, this.driverLng]);
                    }
                }
            } catch (err) {
                console.error('Failed to poll location:', err);
            }
        }
    };
}
</script>
@endsection
