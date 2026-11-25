@extends('layouts.admin')

@section('page_title', 'Radar Peta Kurir Real-Time')

@section('content')
<div class="space-y-6" 
     x-data="adminFleetRadar({
         depotLat: {{ $depotLat }},
         depotLng: {{ $depotLng }},
         companyName: '{{ addslashes($companyName) }}',
         companyAddress: '{{ addslashes($companyAddress ?? 'Jl. Bratang Gede VI H No. 130, Kel. Ngagelrejo, Kec. Wonokromo, Surabaya') }}',
         googleMapsApiKey: '{{ addslashes($googleMapsApiKey ?? '') }}',
         apiEndpoint: '{{ route('admin.deliveries.live-drivers') }}',
         geocodeUrl: '{{ route('admin.deliveries.geocode') }}',
         updateDepotUrl: '{{ route('admin.deliveries.update-depot') }}',
         saveApiKeyUrl: '{{ route('admin.deliveries.save-google-maps-key') }}',
         csrfToken: '{{ csrf_token() }}'
     })">

    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="w-3 h-3 rounded-full bg-emerald-500 animate-ping"></span>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">Peta Monitoring Kurir Lapangan</h1>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Posisi kurir diperbarui otomatis setiap 4 detik. Terintegrasi API OpenStreetMap untuk pencarian dan kalibrasi titik depot.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 text-xs">
            <!-- Search Bar via Maps API -->
            <div class="relative w-full sm:w-64">
                <div class="relative">
                    <input type="text" x-model="searchQuery" @keydown.enter.prevent="searchLocation()"
                           placeholder="Cari jalan/area via Maps API..."
                           class="w-full text-xs pl-8 pr-16 py-1.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-cyan-500 bg-white">
                    <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2 text-slate-400 text-xs"></i>
                    <button type="button" @click="searchLocation()" :disabled="isSearching"
                            class="absolute right-1 top-1 px-2 py-0.5 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg font-bold text-[11px] cursor-pointer">
                        <span x-show="!isSearching">Cari</span>
                        <i x-show="isSearching" class="fa-solid fa-spinner animate-spin"></i>
                    </button>
                </div>

                <!-- Dropdown Results -->
                <div x-show="searchResults.length > 0" @click.outside="searchResults = []" x-cloak
                     class="absolute left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-xl shadow-xl z-50 max-h-56 overflow-y-auto divide-y divide-slate-100 text-xs">
                    <template x-for="(res, idx) in searchResults" :key="idx">
                        <div @click="selectSearchResult(res)" class="p-2.5 hover:bg-cyan-50 cursor-pointer transition">
                            <div class="font-bold text-slate-800" x-text="res.display_name.split(',')[0]"></div>
                            <div class="text-[10px] text-slate-500 line-clamp-1" x-text="res.display_name"></div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Recenter Toko Nova Jaya -->
            <button type="button" @click="recenterDepot()" 
                    class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold rounded-xl border border-blue-200 transition flex items-center shadow-2xs cursor-pointer"
                    title="Pusatkan peta ke Toko Nova Jaya Bratang Gede">
                <i class="fa-solid fa-store mr-1.5 text-blue-600"></i>
                <span>Toko Nova Jaya</span>
            </button>

            <!-- Toggle Calibration Mode -->
            <button type="button" @click="toggleCalibration()" 
                    :class="isCalibrating ? 'bg-amber-600 text-white border-amber-700 ring-2 ring-amber-300' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border-slate-200'"
                    class="px-3 py-1.5 font-bold rounded-xl border transition flex items-center shadow-2xs cursor-pointer"
                    title="Ubah atau geser titik Toko Nova Jaya langsung di peta">
                <i class="fa-solid fa-location-dot mr-1.5" :class="isCalibrating ? 'text-white animate-bounce' : 'text-amber-600'"></i>
                <span x-text="isCalibrating ? 'Keluar Mode Geser' : 'Kalibrasi Titik Depot'"></span>
            </button>

            <!-- Google Maps API Key Config Button -->
            <button type="button" @click="showApiKeyModal = true"
                    class="px-3 py-1.5 font-bold rounded-xl border transition flex items-center shadow-2xs cursor-pointer text-xs"
                    :class="googleMapsApiKey ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border-slate-200'"
                    title="Atur Google Maps API Key">
                <i class="fa-brands fa-google mr-1.5" :class="googleMapsApiKey ? 'text-emerald-600' : 'text-slate-500'"></i>
                <span x-text="googleMapsApiKey ? 'Google Maps: Aktif' : 'Google Maps API'"></span>
            </button>

            <span class="px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 font-semibold" x-text="'Kurir: ' + activeDriversCount"></span>
            
            <button type="button" @click="fetchDrivers()" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl cursor-pointer" title="Refresh Peta">
                <i class="fa-solid fa-arrows-rotate"></i>
            </button>
        </div>
    </div>

    <!-- Modal Google Maps API Key Configuration -->
    <div x-show="showApiKeyModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
        <div class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl border border-slate-200 space-y-4"
             @click.outside="showApiKeyModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                        <i class="fa-brands fa-google text-base"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-sm text-slate-900">Pengaturan Google Maps API</h3>
                        <p class="text-[11px] text-slate-400">Aktifkan peta Google Maps resmi & fitur satelit</p>
                    </div>
                </div>
                <button type="button" @click="showApiKeyModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="text-xs text-slate-600 space-y-2">
                <p>
                    Masukkan <strong>Google Maps API Key</strong> Anda dari Google Cloud Console. Sistem akan memuat Google Maps resmi (peta satelit, jalan, dan pencarian tempat Google).
                </p>
                <div class="p-3 bg-blue-50 rounded-2xl border border-blue-100 text-[11px] text-blue-900 space-y-1">
                    <div class="font-bold flex items-center text-blue-700">
                        <i class="fa-solid fa-shield-halved mr-1.5"></i> Mode Cadangan Otomatis:
                    </div>
                    <div>Jika API Key kosong atau kuota Google habis, sistem otomatis beralih ke <strong>OpenStreetMap (100% Gratis & Bebas Batas)</strong>.</div>
                </div>
            </div>

            <div class="space-y-1.5 text-xs">
                <label class="block font-bold text-slate-700">Google Maps API Key</label>
                <input type="text" x-model="inputApiKey" placeholder="AIzaSy..."
                       class="w-full font-mono text-xs border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-cyan-500 bg-slate-50 focus:bg-white">
                <div class="text-[10px] text-slate-400">
                    Belum punya API Key? Buat di <a href="https://console.cloud.google.com/google/maps-apis/credentials" target="_blank" class="text-blue-600 underline font-semibold">Google Cloud Console &rarr;</a>
                </div>
            </div>

            <div class="pt-2 flex items-center justify-between text-xs">
                <template x-if="googleMapsApiKey">
                    <button type="button" @click="removeApiKey()" :disabled="isSavingKey"
                            class="text-rose-600 hover:text-rose-800 font-bold cursor-pointer text-[11px]">
                        <i class="fa-solid fa-trash-can mr-1"></i> Hapus Key (Gunakan Gratis)
                    </button>
                </template>
                <template x-if="!googleMapsApiKey">
                    <span></span>
                </template>

                <div class="flex items-center space-x-2">
                    <button type="button" @click="showApiKeyModal = false" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold cursor-pointer">
                        Batal
                    </button>
                    <button type="button" @click="saveApiKey()" :disabled="isSavingKey"
                            class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-xs transition flex items-center cursor-pointer">
                        <i x-show="isSavingKey" class="fa-solid fa-spinner animate-spin mr-1.5"></i>
                        <span>Simpan & Terapkan</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Calibration Floating Banner -->
    <div x-show="isCalibrating" x-cloak 
         class="bg-amber-500 text-white px-5 py-3 rounded-2xl shadow-md border border-amber-600 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs animate-fadeIn">
        <div class="flex items-center space-x-2">
            <i class="fa-solid fa-circle-info text-base"></i>
            <span>
                <strong>Mode Kalibrasi Aktif:</strong> Geser pin biru atau klik titik manapun di peta untuk menentukan lokasi Toko Nova Jaya yang tepat.
                <span class="ml-2 font-mono bg-amber-600/60 px-2 py-0.5 rounded text-[11px]" x-text="'Lat: ' + depotLat.toFixed(7) + ', Lng: ' + depotLng.toFixed(7)"></span>
            </span>
        </div>
        <div class="flex items-center space-x-2">
            <button type="button" @click="saveDepotLocation()" :disabled="isSavingDepot"
                    class="px-4 py-1.5 bg-white text-amber-900 font-extrabold rounded-xl shadow-xs hover:bg-amber-50 transition cursor-pointer flex items-center">
                <i x-show="!isSavingDepot" class="fa-solid fa-check mr-1.5 text-emerald-600 font-black"></i>
                <i x-show="isSavingDepot" class="fa-solid fa-spinner animate-spin mr-1.5"></i>
                <span>Simpan Titik Ini</span>
            </button>
            <button type="button" @click="toggleCalibration()" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-semibold cursor-pointer">
                Selesai
            </button>
        </div>
    </div>

    <!-- Success Feedback Notification -->
    <div x-show="toastMessage" x-cloak
         class="bg-emerald-600 text-white px-4 py-2.5 rounded-xl shadow-md text-xs font-bold flex items-center justify-between transition animate-fadeIn">
        <div class="flex items-center space-x-2">
            <i class="fa-solid fa-circle-check text-base"></i>
            <span x-text="toastMessage"></span>
        </div>
        <button type="button" @click="toastMessage = ''" class="text-white hover:text-emerald-100">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Map & Fleet Sidebar Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Map Container (8 cols) -->
        <div class="lg:col-span-8 bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-md relative" style="min-height: 620px;">
            <div id="fleetMap" class="w-full z-10" style="height: 620px; min-height: 620px; width: 100%;"></div>
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
                <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-blue-600 mr-2"></span> Depot Utama Toko Nova Jaya (Bratang Gede)</div>
                <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-emerald-500 mr-2"></span> Kurir Lapangan (Live GPS)</div>
                <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-rose-600 mr-2"></span> Alamat Pengantaran Pelanggan</div>
            </div>
        </div>

    </div>

</div>

<script>
function adminFleetRadar(config) {
    return {
        depotLat: Number(config.depotLat),
        depotLng: Number(config.depotLng),
        companyName: config.companyName,
        companyAddress: config.companyAddress,
        apiEndpoint: config.apiEndpoint,
        geocodeUrl: config.geocodeUrl,
        updateDepotUrl: config.updateDepotUrl,
        csrfToken: config.csrfToken,
        map: null,
        depotMarker: null,
        markers: {},
        drivers: [],
        activeDriversCount: 0,
        pollInterval: null,

        // Google Maps Support
        googleMapsApiKey: config.googleMapsApiKey || '',
        saveApiKeyUrl: config.saveApiKeyUrl,
        showApiKeyModal: false,
        inputApiKey: config.googleMapsApiKey || '',
        isSavingKey: false,
        isUsingGoogleMaps: false,
        gMap: null,
        gDepotMarker: null,
        gMarkers: {},

        // Maps API Search & Calibration states
        searchQuery: '',
        searchResults: [],
        isSearching: false,
        isCalibrating: false,
        isSavingDepot: false,
        toastMessage: '',

        init() {
            this.$nextTick(() => {
                if (this.googleMapsApiKey) {
                    this.loadGoogleMaps();
                } else {
                    this.initMap();
                }
                this.fetchDrivers();
                this.pollInterval = setInterval(() => this.fetchDrivers(), 4000);
            });
        },

        loadGoogleMaps() {
            if (window.google && window.google.maps) {
                this.initGoogleMap();
                return;
            }

            window.initGoogleMapCallback = () => {
                this.initGoogleMap();
            };

            window.gm_authFailure = () => {
                console.warn('Google Maps auth failed, falling back to Leaflet OpenStreetMap');
                this.toastMessage = 'Peringatan: Google Maps API Key tidak valid / billing belum aktif. Mengalihkan ke OpenStreetMap...';
                this.isUsingGoogleMaps = false;
                this.initMap();
            };

            const script = document.createElement('script');
            script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(this.googleMapsApiKey)}&callback=initGoogleMapCallback&libraries=places`;
            script.async = true;
            script.defer = true;
            script.onerror = () => {
                console.warn('Gagal memuat Google Maps script, fallback ke Leaflet.');
                this.initMap();
            };
            document.head.appendChild(script);
        },

        initGoogleMap() {
            if (!window.google || !window.google.maps) {
                this.initMap();
                return;
            }

            const mapDiv = document.getElementById('fleetMap');
            if (!mapDiv) return;

            this.isUsingGoogleMaps = true;
            const center = { lat: this.depotLat, lng: this.depotLng };

            this.gMap = new google.maps.Map(mapDiv, {
                center: center,
                zoom: 16,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                mapTypeControl: true,
                streetViewControl: true,
                fullscreenControl: true,
            });

            this.gDepotMarker = new google.maps.Marker({
                position: center,
                map: this.gMap,
                title: this.companyName,
                draggable: false,
                icon: {
                    url: 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png',
                    scaledSize: new google.maps.Size(42, 42)
                }
            });

            const depotInfoWindow = new google.maps.InfoWindow({
                content: `
                    <div style="font-family: inherit; max-width: 240px; padding: 4px;">
                        <span style="background: #dbeafe; color: #1d4ed8; font-size: 9px; font-weight: 800; padding: 2px 6px; border-radius: 6px; text-transform: uppercase;">Depot Penjualan Utama</span>
                        <h4 style="font-size: 13px; font-weight: 800; color: #0f172a; margin-top: 4px; margin-bottom: 2px;">${this.companyName}</h4>
                        <p style="font-size: 11px; color: #475569; margin: 0; line-height: 1.3;">${this.companyAddress}</p>
                        <div style="margin-top: 6px; font-size: 10px; font-family: monospace; color: #64748b;">
                            Lat: ${this.depotLat.toFixed(7)}, Lng: ${this.depotLng.toFixed(7)}
                        </div>
                        <div style="margin-top: 6px; font-size: 10px; font-weight: 700; color: #059669;">
                            ✓ Google Maps Platform Resmi
                        </div>
                    </div>
                `
            });

            this.gDepotMarker.addListener('click', () => {
                depotInfoWindow.open(this.gMap, this.gDepotMarker);
            });
            depotInfoWindow.open(this.gMap, this.gDepotMarker);
        },

        async saveApiKey() {
            this.isSavingKey = true;
            try {
                const res = await fetch(this.saveApiKeyUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken
                    },
                    body: JSON.stringify({
                        api_key: this.inputApiKey
                    })
                });

                const data = await res.json();
                if (data.success) {
                    this.googleMapsApiKey = this.inputApiKey;
                    this.toastMessage = data.message;
                    this.showApiKeyModal = false;
                    setTimeout(() => { window.location.reload(); }, 1200);
                }
            } catch (err) {
                console.error('Gagal menyimpan API Key:', err);
                alert('Gagal menyimpan API Key.');
            } finally {
                this.isSavingKey = false;
            }
        },

        removeApiKey() {
            window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                detail: {
                    title: 'Hapus Google Maps API Key',
                    message: 'Apakah Anda yakin ingin menghapus API Key dan kembali menggunakan OpenStreetMap (Gratis)?',
                    confirmText: 'Ya, Hapus Key',
                    callback: async () => {
                        this.inputApiKey = '';
                        await this.saveApiKey();
                    }
                }
            }));
        },

        initMap() {
            if (!window.L) return;

            // Bounding Box Khusus Kota Surabaya
            const surabayaBounds = L.latLngBounds(
                L.latLng(-7.3800, 112.5800), // Barat Daya Surabaya
                L.latLng(-7.1500, 112.8600)  // Timur Laut Surabaya
            );

            this.map = L.map('fleetMap', {
                maxBounds: surabayaBounds,
                maxBoundsViscosity: 0.9,
                minZoom: 11
            }).setView([this.depotLat, this.depotLng], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap • Area Bratang Gede, Wonokromo, Kota Surabaya'
            }).addTo(this.map);

            // Depot Marker Toko Nova Jaya
            const depotIcon = L.divIcon({
                className: 'depot-pin',
                html: `<div style="background: linear-gradient(135deg, #0284c7, #2563eb); color: white; border-radius: 50%; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(37,99,235,0.45); border: 3px solid white;">
                        <i class="fa-solid fa-store" style="font-size: 19px;"></i>
                       </div>`,
                iconSize: [44, 44],
                iconAnchor: [22, 22]
            });

            this.depotMarker = L.marker([this.depotLat, this.depotLng], { 
                icon: depotIcon,
                draggable: false
            }).addTo(this.map);

            this.updateDepotPopup();
            this.depotMarker.openPopup();

            this.depotMarker.on('dragend', (e) => {
                const pos = e.target.getLatLng();
                this.depotLat = pos.lat;
                this.depotLng = pos.lng;
                this.updateDepotPopup();
                this.depotMarker.openPopup();
            });

            this.map.on('click', (e) => {
                if (this.isCalibrating) {
                    this.depotLat = e.latlng.lat;
                    this.depotLng = e.latlng.lng;
                    this.depotMarker.setLatLng(e.latlng);
                    this.updateDepotPopup();
                    this.depotMarker.openPopup();
                }
            });
        },

        updateDepotPopup() {
            if (!this.depotMarker) return;
            this.depotMarker.bindPopup(`
                <div style="min-width: 220px; font-family: inherit;">
                    <span style="background: #dbeafe; color: #1d4ed8; font-size: 9px; font-weight: 800; padding: 2px 6px; border-radius: 6px; text-transform: uppercase;">Depot Penjualan Utama</span>
                    <h4 style="font-size: 13px; font-weight: 800; color: #0f172a; margin-top: 4px; margin-bottom: 2px;">${this.companyName}</h4>
                    <p style="font-size: 11px; color: #475569; margin: 0; line-height: 1.3;">${this.companyAddress}</p>
                    <div style="margin-top: 6px; font-size: 10px; font-family: monospace; color: #64748b;">
                        Lat: ${this.depotLat.toFixed(7)}, Lng: ${this.depotLng.toFixed(7)}
                    </div>
                    <div style="margin-top: 6px; font-size: 10px; font-weight: 700; color: #059669; display: flex; align-items: center;">
                        <span style="width: 7px; height: 7px; border-radius: 50%; background: #10b981; margin-right: 6px; display: inline-block;"></span>
                        Titik Presisi Google Maps Terverifikasi
                    </div>
                    <div style="margin-top: 8px; pt-2; border-top: 1px solid #e2e8f0;">
                        <a href="https://maps.app.goo.gl/fozU4QCYdbE6unCX7" target="_blank" 
                           style="color: #2563eb; font-weight: 700; font-size: 10px; text-decoration: none; display: flex; align-items: center;">
                            <i class="fa-solid fa-arrow-up-right-from-square" style="margin-right: 4px;"></i> Buka di Google Maps Langsung
                        </a>
                    </div>
                </div>
            `);
        },

        recenterDepot() {
            if (this.isUsingGoogleMaps && this.gMap) {
                this.gMap.panTo({ lat: this.depotLat, lng: this.depotLng });
                this.gMap.setZoom(17);
                return;
            }
            if (!this.map) return;
            this.map.flyTo([this.depotLat, this.depotLng], 16, { duration: 1.2 });
            if (this.depotMarker) this.depotMarker.openPopup();
        },

        toggleCalibration() {
            this.isCalibrating = !this.isCalibrating;
            if (this.depotMarker) {
                if (this.isCalibrating) {
                    this.depotMarker.dragging.enable();
                    this.toastMessage = 'Mode kalibrasi aktif. Silakan geser pin atau klik di peta.';
                } else {
                    this.depotMarker.dragging.disable();
                }
            }
        },

        async saveDepotLocation() {
            this.isSavingDepot = true;
            try {
                const res = await fetch(this.updateDepotUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken
                    },
                    body: JSON.stringify({
                        latitude: this.depotLat,
                        longitude: this.depotLng
                    })
                });

                const data = await res.json();
                if (data.success) {
                    this.toastMessage = 'Berhasil! Titik Toko Nova Jaya telah disimpan permanen di database.';
                    this.isCalibrating = false;
                    if (this.depotMarker) this.depotMarker.dragging.disable();
                    setTimeout(() => { this.toastMessage = ''; }, 5000);
                }
            } catch (err) {
                console.error('Gagal menyimpan titik depot:', err);
                alert('Gagal menyimpan titik koordinat. Periksa koneksi.');
            } finally {
                this.isSavingDepot = false;
            }
        },

        async searchLocation() {
            if (!this.searchQuery.trim()) return;
            this.isSearching = true;
            this.searchResults = [];

            try {
                // 1. Try backend proxy
                const res = await fetch(`${this.geocodeUrl}?q=${encodeURIComponent(this.searchQuery)}`);
                if (res.ok) {
                    const data = await res.json();
                    if (Array.isArray(data) && data.length > 0) {
                        this.searchResults = data;
                        return;
                    }
                }

                // 2. Fallback to direct client-side OpenStreetMap Nominatim API
                const osmRes = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.searchQuery + ' Surabaya')}&limit=5`);
                if (osmRes.ok) {
                    this.searchResults = await osmRes.json();
                }
            } catch (err) {
                console.error('Geocoding search failed:', err);
            } finally {
                this.isSearching = false;
            }
        },

        selectSearchResult(item) {
            const lat = parseFloat(item.lat);
            const lng = parseFloat(item.lon);
            this.searchResults = [];
            this.searchQuery = item.display_name.split(',')[0];

            if (this.map) {
                this.map.flyTo([lat, lng], 17, { duration: 1.2 });
                
                if (this.isCalibrating) {
                    this.depotLat = lat;
                    this.depotLng = lng;
                    this.depotMarker.setLatLng([lat, lng]);
                    this.updateDepotPopup();
                    this.depotMarker.openPopup();
                } else {
                    L.popup()
                        .setLatLng([lat, lng])
                        .setContent(`<b>${item.display_name.split(',')[0]}</b><br><span style="font-size:10px; color:#64748b;">${item.display_name}</span>`)
                        .openOn(this.map);
                }
            }
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
