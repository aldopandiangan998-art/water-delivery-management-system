@extends('layouts.admin')

@section('page_title', 'Radar Peta Kurir Real-Time')

@section('content')
<div class="space-y-6" 
     x-data="adminFleetRadar({
         depotLat: {{ $depotLat }},
         depotLng: {{ $depotLng }},
         companyName: '{{ addslashes($companyName) }}',
         apiEndpoint: '{{ route('admin.deliveries.live-drivers') }}'
     })">

    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="w-3 h-3 rounded-full bg-emerald-500 animate-ping"></span>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">Peta Monitoring Kurir Lapangan</h1>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Posisi kurir diperbarui otomatis setiap 4 detik. Menampilkan lokasi depot, kurir aktif, dan alamat tujuan pelanggan.
            </p>
        </div>

        <div class="flex items-center space-x-2 text-xs">
            <span class="px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 font-semibold" x-text="'Kurir Aktif: ' + activeDriversCount + ' orang'"></span>
            <button @click="fetchDrivers()" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl" title="Refresh Peta">
                <i class="fa-solid fa-arrows-rotate"></i>
            </button>
        </div>
    </div>

    <!-- Map & Fleet Sidebar Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Map Container (8 cols) -->
        <div class="lg:col-span-8 bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-md relative">
            <div id="fleetMap" class="w-full h-[620px] z-10"></div>
        </div>

        <!-- Active Deliveries Status List (4 cols) -->
        <div class="lg:col-span-4 bg-white rounded-3xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between space-y-4 max-h-[620px] overflow-y-auto">
            <div>
                <h2 class="font-extrabold text-sm text-slate-900 uppercase tracking-wider mb-3">
                    Pengantaran Berlangsung
                </h2>

                <div class="space-y-3">
                    <template x-for="item in drivers" :key="item.id">
                        <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/60 hover:bg-cyan-50/50 hover:border-cyan-300 transition space-y-2 cursor-pointer"
                             @click="focusDriver(item)">
                            <div class="flex items-center justify-between">
                                <span class="font-black text-xs text-slate-900" x-text="'#' + item.order_number"></span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border bg-amber-100 text-amber-800 border-amber-200" x-text="item.status_label"></span>
                            </div>

                            <div>
                                <div class="font-bold text-xs text-slate-800 flex items-center">
                                    <i class="fa-solid fa-motorcycle text-emerald-600 mr-1.5"></i>
                                    <span x-text="item.driver_name"></span>
                                </div>
                                <div class="text-[11px] text-slate-500 truncate mt-0.5" x-text="'Tujuan: ' + item.customer_name"></div>
                                <div class="text-[10px] text-slate-400 truncate" x-text="item.customer_address"></div>
                            </div>

                            <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between text-[10px] text-slate-500">
                                <span x-text="'Tagihan: Rp ' + item.total_amount"></span>
                                <span class="text-cyan-700 font-bold hover:underline">Fokus di Peta &rarr;</span>
                            </div>
                        </div>
                    </template>

                    <div x-show="drivers.length === 0" class="text-center text-slate-400 py-12 text-xs">
                        Tidak ada kurir yang sedang dalam perjalanan saat ini.
                    </div>
                </div>
            </div>

            <!-- Legend Info -->
            <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100 text-[11px] space-y-1 text-slate-600">
                <div class="font-bold text-slate-700">Keterangan Ikon Peta:</div>
                <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-blue-600 mr-2"></span> Depot Utama AquaFlow</div>
                <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-emerald-500 mr-2"></span> Kurir Lapangan (Live GPS)</div>
                <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-rose-600 mr-2"></span> Alamat Pengantaran Pelanggan</div>
            </div>
        </div>

    </div>

</div>

<script>
function adminFleetRadar(config) {
    return {
        depotLat: config.depotLat,
        depotLng: config.depotLng,
        companyName: config.companyName,
        apiEndpoint: config.apiEndpoint,
        map: null,
        markers: {},
        drivers: [],
        activeDriversCount: 0,
        pollInterval: null,

        init() {
            this.$nextTick(() => {
                this.initMap();
                this.fetchDrivers();
                this.pollInterval = setInterval(() => this.fetchDrivers(), 4000);
            });
        },

        initMap() {
            if (!window.L) return;

            this.map = L.map('fleetMap').setView([this.depotLat, this.depotLng], 14);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(this.map);

            // Depot Marker
            const depotIcon = L.divIcon({
                className: 'depot-pin',
                html: `<div style="background-color: #2563eb; color: white; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 6px rgba(0,0,0,0.3); border: 3px solid white;">
                        <i class="fa-solid fa-store" style="font-size: 18px;"></i>
                       </div>`,
                iconSize: [40, 40],
                iconAnchor: [20, 20]
            });

            L.marker([this.depotLat, this.depotLng], { icon: depotIcon })
                .addTo(this.map)
                .bindPopup(`<b>${this.companyName}</b><br>Depot Pengisian Air Minum`)
                .openPopup();
        },

        async fetchDrivers() {
            try {
                const res = await fetch(this.apiEndpoint);
                if (!res.ok) return;

                const data = await res.json();
                this.drivers = data;
                this.activeDriversCount = data.length;
                this.updateMapMarkers(data);
            } catch (err) {
                console.error('Failed to fetch live fleet:', err);
            }
        },

        updateMapMarkers(data) {
            if (!this.map || !window.L) return;

            data.forEach(item => {
                const driverKey = 'driver_' + item.id;
                const custKey = 'cust_' + item.id;

                // 1. Customer Marker
                if (item.customer_lat && item.customer_lng && !this.markers[custKey]) {
                    const custIcon = L.divIcon({
                        className: 'cust-pin',
                        html: `<div style="background-color: #e11d48; color: white; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                                <i class="fa-solid fa-house" style="font-size: 14px;"></i>
                               </div>`,
                        iconSize: [32, 32],
                        iconAnchor: [16, 16]
                    });

                    this.markers[custKey] = L.marker([item.customer_lat, item.customer_lng], { icon: custIcon })
                        .addTo(this.map)
                        .bindPopup(`<b>Penerima: ${item.customer_name}</b><br>${item.customer_address}<br>Order #${item.order_number}`);
                }

                // 2. Driver Marker
                if (item.driver_lat && item.driver_lng) {
                    if (this.markers[driverKey]) {
                        this.markers[driverKey].setLatLng([item.driver_lat, item.driver_lng]);
                    } else {
                        const driverIcon = L.divIcon({
                            className: 'driver-bike-pin',
                            html: `<div style="background-color: #10b981; color: white; border-radius: 50%; width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 4px 8px rgba(0,0,0,0.4);">
                                    <i class="fa-solid fa-motorcycle" style="font-size: 18px;"></i>
                                   </div>`,
                            iconSize: [38, 38],
                            iconAnchor: [19, 19]
                        });

                        this.markers[driverKey] = L.marker([item.driver_lat, item.driver_lng], { icon: driverIcon })
                            .addTo(this.map)
                            .bindPopup(`<b>Kurir: ${item.driver_name}</b><br>Mengantar #${item.order_number}<br>Pembaruan: ${item.recorded_at}`);
                    }
                }
            });
        },

        focusDriver(item) {
            if (this.map && item.driver_lat && item.driver_lng) {
                this.map.setView([item.driver_lat, item.driver_lng], 16);
                const marker = this.markers['driver_' + item.id];
                if (marker) marker.openPopup();
            }
        }
    };
}
</script>
@endsection
