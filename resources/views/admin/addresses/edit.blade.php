@extends('layouts.admin')

@section('page_title', 'Edit Alamat Pelanggan')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center space-x-2">
                <h1 class="text-xl font-black text-slate-900 tracking-tight">Edit Alamat: {{ $address->label }}</h1>
                @if ($address->is_default)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-cyan-50 text-cyan-700 border border-cyan-200">Alamat Utama</span>
                @endif
            </div>
            <p class="text-xs text-slate-500">Pelanggan: <span class="font-bold text-slate-700">{{ $address->user?->name }}</span> ({{ $address->user?->email }})</p>
        </div>
        <a href="{{ route('admin.addresses.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">
            <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Daftar
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
        <form method="POST" action="{{ route('admin.addresses.update', $address) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Section 1: Customer Account & Label -->
            <div class="space-y-4">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2 flex items-center">
                    <i class="fa-solid fa-user-check text-cyan-600 mr-2"></i> 1. Pemilik Alamat & Label
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Pilih Akun Pelanggan <span class="text-rose-500">*</span></label>
                        <select name="user_id" required class="w-full border border-slate-300 rounded-xl p-2.5 bg-white font-medium focus:ring-2 focus:ring-cyan-500">
                            @foreach ($customers as $cust)
                                <option value="{{ $cust->id }}" {{ (old('user_id', $address->user_id) == $cust->id) ? 'selected' : '' }}>
                                    {{ $cust->name }} ({{ $cust->phone ?? $cust->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Label Alamat <span class="text-rose-500">*</span></label>
                        <input type="text" name="label" required placeholder="Contoh: Rumah Sukajadi, Toko Kelontong, Kantor"
                               value="{{ old('label', $address->label) }}"
                               class="w-full border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-cyan-500 font-medium">
                        @error('label') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Section 2: Recipient Contact & Service Area -->
            <div class="space-y-4">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2 flex items-center">
                    <i class="fa-solid fa-id-card text-cyan-600 mr-2"></i> 2. Kontak Penerima & Wilayah Pengantaran
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Penerima <span class="text-rose-500">*</span></label>
                        <input type="text" name="recipient_name" required placeholder="Nama orang di lokasi..."
                               value="{{ old('recipient_name', $address->recipient_name) }}"
                               class="w-full border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-cyan-500 font-medium">
                        @error('recipient_name') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nomor WhatsApp / HP <span class="text-rose-500">*</span></label>
                        <input type="text" name="recipient_phone" required placeholder="08xxxxxxxxxx"
                               value="{{ old('recipient_phone', $address->recipient_phone) }}"
                               class="w-full border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-cyan-500 font-medium">
                        @error('recipient_phone') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Wilayah Layanan (Ongkir)</label>
                        <select name="service_area_id" class="w-full border border-slate-300 rounded-xl p-2.5 bg-white font-medium focus:ring-2 focus:ring-cyan-500">
                            <option value="">-- Tanpa Wilayah Tertentu --</option>
                            @foreach ($serviceAreas as $area)
                                <option value="{{ $area->id }}" {{ (old('service_area_id', $address->service_area_id) == $area->id) ? 'selected' : '' }}>
                                    {{ $area->name }} (Ongkir: Rp {{ number_format($area->delivery_fee, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                        @error('service_area_id') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Section 3: Detailed Address & Benchmark -->
            <div class="space-y-4">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2 flex items-center">
                    <i class="fa-solid fa-map-pin text-cyan-600 mr-2"></i> 3. Rincian Alamat & Patokan
                </h2>

                <div class="space-y-3 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Alamat Jalan Lengkap <span class="text-rose-500">*</span></label>
                        <textarea name="address_line" rows="2" required placeholder="Nama jalan, nomor rumah, RT/RW, blok, gang..."
                                  class="w-full border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-cyan-500">{{ old('address_line', $address->address_line) }}</textarea>
                        @error('address_line') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Patokan Lokasi (Memudahkan Kurir/Pengantar)</label>
                        <input type="text" name="benchmark" placeholder="Contoh: Pagar abu-abu, seberang masjid Al-Ikhlas..."
                               value="{{ old('benchmark', $address->benchmark) }}"
                               class="w-full border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-cyan-500">
                    </div>
                </div>
            </div>

            <!-- Section 4: Leaflet Map & GPS Coordinates -->
            <div class="space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center">
                        <i class="fa-solid fa-map text-emerald-600 mr-2"></i> 4. Titik Koordinat Peta (GPS Pinpoint)
                    </h2>
                    <span class="text-[11px] text-slate-500">Klik peta atau geser pin merah untuk membetulkan titik lokasi</span>
                </div>

                <!-- Quick Surabaya District Shortcuts -->
                <div class="flex flex-wrap items-center gap-1.5 text-xs">
                    <span class="text-[11px] font-bold text-slate-500 mr-1 flex items-center">
                        <i class="fa-solid fa-map-pin mr-1 text-cyan-600"></i>Area Kota Surabaya:
                    </span>
                    <button type="button" onclick="panToSurabaya(-7.2950273, 112.7537136, 'Toko Nova Jaya (Jl. Bratang Gede VI H, Ngagelrejo, Wonokromo)')" class="px-2.5 py-1 rounded-lg bg-blue-100 text-blue-800 hover:bg-blue-200 text-[11px] font-extrabold transition border border-blue-300 cursor-pointer shadow-2xs">⭐ Toko Nova Jaya (Bratang Gede VI)</button>
                    <button type="button" onclick="panToSurabaya(-7.3050, 112.7350, 'Wonokromo (Darmo / Joyoboyo)')" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-cyan-50 hover:text-cyan-700 text-[11px] font-semibold transition border border-slate-200 cursor-pointer">Wonokromo</button>
                    <button type="button" onclick="panToSurabaya(-7.2655, 112.7480, 'Gubeng (Surabaya Pusat)')" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-cyan-50 hover:text-cyan-700 text-[11px] font-semibold transition border border-slate-200 cursor-pointer">Gubeng</button>
                    <button type="button" onclick="panToSurabaya(-7.3200, 112.7750, 'Rungkut (Surabaya Timur)')" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-cyan-50 hover:text-cyan-700 text-[11px] font-semibold transition border border-slate-200 cursor-pointer">Rungkut</button>
                    <button type="button" onclick="panToSurabaya(-7.2880, 112.7950, 'Sukolilo (Surabaya Timur)')" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-cyan-50 hover:text-cyan-700 text-[11px] font-semibold transition border border-slate-200 cursor-pointer">Sukolilo</button>
                    <button type="button" onclick="panToSurabaya(-7.2620, 112.7380, 'Tegalsari / Tunjungan')" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-cyan-50 hover:text-cyan-700 text-[11px] font-semibold transition border border-slate-200 cursor-pointer">Tegalsari</button>
                    <button type="button" onclick="panToSurabaya(-7.3050, 112.6850, 'Wiyung (Surabaya Barat)')" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-cyan-50 hover:text-cyan-700 text-[11px] font-semibold transition border border-slate-200 cursor-pointer">Wiyung</button>
                </div>

                <!-- Live Search via Maps API -->
                <div class="relative w-full">
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <input type="text" id="addressSearchQuery" placeholder="Ketik nama jalan / patokan di Surabaya lalu tekan Cari via Maps API..."
                                   class="w-full text-xs pl-8 pr-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-cyan-500 bg-white"
                                   onkeydown="if(event.key === 'Enter'){ event.preventDefault(); searchAddressOsm(); }">
                            <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
                        </div>
                        <button type="button" onclick="searchAddressOsm()" class="px-3 py-2 bg-cyan-600 hover:bg-cyan-700 text-white rounded-xl text-xs font-bold transition flex items-center cursor-pointer shadow-2xs">
                            <i class="fa-solid fa-magnifying-glass-location mr-1.5"></i> Cari di Peta (API)
                        </button>
                    </div>
                    <div id="addressSearchResults" class="hidden absolute left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-xl z-50 max-h-52 overflow-y-auto divide-y divide-slate-100 text-xs"></div>
                </div>

                <!-- Leaflet Map Container -->
                <div id="addressMap" class="w-full h-72 rounded-2xl border border-slate-200 shadow-2xs z-10"></div>

                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-600 mb-1">Latitude</label>
                        <input type="text" id="latitude" name="latitude" value="{{ old('latitude', $address->latitude ?? $depotLat) }}"
                               class="w-full border border-slate-200 rounded-xl p-2 bg-slate-50 font-mono text-slate-700 font-bold" readonly>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-600 mb-1">Longitude</label>
                        <input type="text" id="longitude" name="longitude" value="{{ old('longitude', $address->longitude ?? $depotLng) }}"
                               class="w-full border border-slate-200 rounded-xl p-2 bg-slate-50 font-mono text-slate-700 font-bold" readonly>
                    </div>
                </div>

                <div class="flex items-center text-xs pt-1">
                    <input type="checkbox" id="is_default" name="is_default" value="1" {{ old('is_default', $address->is_default) ? 'checked' : '' }}
                           class="w-4 h-4 rounded text-cyan-600 focus:ring-cyan-500">
                    <label for="is_default" class="ml-2 font-bold text-slate-700">
                        Jadikan sebagai Alamat Utama (Default) pelanggan ini
                    </label>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('admin.addresses.show', $address) }}" class="text-xs font-bold text-slate-500 hover:text-slate-800">
                    <i class="fa-solid fa-eye mr-1"></i> Lihat Rincian Alamat
                </a>

                <div class="flex items-center space-x-3">
                    <a href="{{ route('admin.addresses.index') }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold bg-cyan-600 hover:bg-cyan-700 text-white shadow-md hover:shadow-lg transition flex items-center">
                        <i class="fa-solid fa-check mr-2"></i> Simpan Perubahan Alamat
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>

<!-- Leaflet Map Script -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let map, marker;

function updateCoords(lat, lng) {
    document.getElementById('latitude').value = parseFloat(lat).toFixed(7);
    document.getElementById('longitude').value = parseFloat(lng).toFixed(7);
}

function panToSurabaya(lat, lng, name) {
    if (!map || !marker) return;
    map.flyTo([lat, lng], 15, { duration: 1 });
    marker.setLatLng([lat, lng]);
    updateCoords(lat, lng);
    marker.bindPopup(`<b>${name}</b><br>Area Kota Surabaya`).openPopup();
}

document.addEventListener('DOMContentLoaded', function () {
    const defaultLat = {{ old('latitude', $address->latitude ?? $depotLat) }};
    const defaultLng = {{ old('longitude', $address->longitude ?? $depotLng) }};

    // Bounding Box Khusus Kota Surabaya
    const surabayaBounds = L.latLngBounds(
        L.latLng(-7.3800, 112.5800), // Barat Daya (Karang Pilang / Waru)
        L.latLng(-7.1500, 112.8600)  // Timur Laut (Kenjeran / Tanjung Perak / Suramadu)
    );

    map = L.map('addressMap', {
        maxBounds: surabayaBounds,
        maxBoundsViscosity: 0.9,
        minZoom: 11
    }).setView([defaultLat, defaultLng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors • Wilayah Kota Surabaya'
    }).addTo(map);

    marker = L.marker([defaultLat, defaultLng], {
        draggable: true
    }).addTo(map);

    marker.bindPopup('<b>{{ $address->label }} (Kota Surabaya)</b><br>{{ $address->recipient_name }}').openPopup();

    marker.on('dragend', function (e) {
        const pos = e.target.getLatLng();
        updateCoords(pos.lat, pos.lng);
    });

    map.on('click', function (e) {
        marker.setLatLng(e.latlng);
        updateCoords(e.latlng.lat, e.latlng.lng);
        marker.openPopup();
    });

    setTimeout(() => {
        map.invalidateSize();
    }, 300);
});

async function searchAddressOsm() {
    const q = document.getElementById('addressSearchQuery').value.trim();
    if (!q) return;
    const resBox = document.getElementById('addressSearchResults');
    resBox.innerHTML = '<div class="p-3 text-slate-400 text-center"><i class="fa-solid fa-spinner animate-spin mr-1"></i> Mencari via Maps API...</div>';
    resBox.classList.remove('hidden');

    try {
        const query = q.toLowerCase().includes('surabaya') ? q : (q + ' Surabaya');
        const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5`);
        const data = await res.json();

        if (!data || data.length === 0) {
            resBox.innerHTML = '<div class="p-3 text-slate-400 text-center">Tidak ditemukan hasil lokasi di Surabaya.</div>';
            return;
        }

        resBox.innerHTML = '';
        data.forEach(item => {
            const div = document.createElement('div');
            div.className = 'p-2.5 hover:bg-cyan-50 cursor-pointer transition';
            div.innerHTML = `<div class="font-bold text-slate-800">${item.display_name.split(',')[0]}</div><div class="text-[10px] text-slate-500 truncate">${item.display_name}</div>`;
            div.onclick = () => {
                const lat = parseFloat(item.lat);
                const lng = parseFloat(item.lon);
                panToSurabaya(lat, lng, item.display_name.split(',')[0]);
                resBox.classList.add('hidden');
                document.getElementById('addressSearchQuery').value = item.display_name.split(',')[0];
            };
            resBox.appendChild(div);
        });
    } catch (e) {
        resBox.innerHTML = '<div class="p-3 text-rose-500 text-center">Gagal memuat API peta.</div>';
    }
}

document.addEventListener('click', function(e) {
    const searchBox = document.getElementById('addressSearchQuery');
    const resBox = document.getElementById('addressSearchResults');
    if (resBox && !resBox.contains(e.target) && e.target !== searchBox) {
        resBox.classList.add('hidden');
    }
});
</script>
@endsection
