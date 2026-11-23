@extends('layouts.admin')

@section('page_title', 'Tambah Alamat Pelanggan Baru')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight">Tambah Alamat Pelanggan</h1>
            <p class="text-xs text-slate-500">Daftarkan alamat pengantaran baru untuk akun pelanggan, lengkap dengan pin koordinat peta.</p>
        </div>
        <a href="{{ route('admin.addresses.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">
            <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Daftar
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
        <form method="POST" action="{{ route('admin.addresses.store') }}" class="space-y-6">
            @csrf

            <!-- Section 1: Customer Account & Label -->
            <div class="space-y-4">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2 flex items-center">
                    <i class="fa-solid fa-user-check text-cyan-600 mr-2"></i> 1. Pemilik Alamat & Label
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Pilih Akun Pelanggan <span class="text-rose-500">*</span></label>
                        <select name="user_id" required class="w-full border border-slate-300 rounded-xl p-2.5 bg-white font-medium focus:ring-2 focus:ring-cyan-500">
                            <option value="">-- Pilih Akun Pelanggan --</option>
                            @foreach ($customers as $cust)
                                <option value="{{ $cust->id }}" {{ (old('user_id', $selectedUserId) == $cust->id) ? 'selected' : '' }}>
                                    {{ $cust->name }} ({{ $cust->phone ?? $cust->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Label Alamat <span class="text-rose-500">*</span></label>
                        <input type="text" name="label" required placeholder="Contoh: Rumah Sukajadi, Toko Kelontong, Kantor"
                               value="{{ old('label', 'Rumah') }}"
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
                               value="{{ old('recipient_name') }}"
                               class="w-full border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-cyan-500 font-medium">
                        @error('recipient_name') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nomor WhatsApp / HP <span class="text-rose-500">*</span></label>
                        <input type="text" name="recipient_phone" required placeholder="08xxxxxxxxxx"
                               value="{{ old('recipient_phone') }}"
                               class="w-full border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-cyan-500 font-medium">
                        @error('recipient_phone') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Wilayah Layanan (Ongkir)</label>
                        <select name="service_area_id" class="w-full border border-slate-300 rounded-xl p-2.5 bg-white font-medium focus:ring-2 focus:ring-cyan-500">
                            <option value="">-- Tanpa Wilayah Tertentu --</option>
                            @foreach ($serviceAreas as $area)
                                <option value="{{ $area->id }}" {{ old('service_area_id') == $area->id ? 'selected' : '' }}>
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
                                  class="w-full border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-cyan-500">{{ old('address_line') }}</textarea>
                        @error('address_line') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Patokan Lokasi (Memudahkan Kurir/Pengantar)</label>
                        <input type="text" name="benchmark" placeholder="Contoh: Pagar abu-abu, persis seberang masjid Al-Ikhlas, rumah pojok cat putih..."
                               value="{{ old('benchmark') }}"
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
                    <span class="text-[11px] text-slate-500">Klik peta atau geser pin merah untuk mengubah titik koordinat</span>
                </div>

                <!-- Leaflet Map Container -->
                <div id="addressMap" class="w-full h-72 rounded-2xl border border-slate-200 shadow-2xs z-10"></div>

                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-600 mb-1">Latitude</label>
                        <input type="text" id="latitude" name="latitude" value="{{ old('latitude', $depotLat) }}"
                               class="w-full border border-slate-200 rounded-xl p-2 bg-slate-50 font-mono text-slate-700 font-bold" readonly>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-600 mb-1">Longitude</label>
                        <input type="text" id="longitude" name="longitude" value="{{ old('longitude', $depotLng) }}"
                               class="w-full border border-slate-200 rounded-xl p-2 bg-slate-50 font-mono text-slate-700 font-bold" readonly>
                    </div>
                </div>

                <div class="flex items-center text-xs pt-1">
                    <input type="checkbox" id="is_default" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}
                           class="w-4 h-4 rounded text-cyan-600 focus:ring-cyan-500">
                    <label for="is_default" class="ml-2 font-bold text-slate-700">
                        Jadikan sebagai Alamat Utama (Default) pelanggan ini
                    </label>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="{{ route('admin.addresses.index') }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold bg-cyan-600 hover:bg-cyan-700 text-white shadow-md hover:shadow-lg transition flex items-center">
                    <i class="fa-solid fa-floppy-disk mr-2"></i> Simpan Alamat Pelanggan
                </button>
            </div>
        </form>
    </div>

</div>

<!-- Leaflet Map Script -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const defaultLat = {{ old('latitude', $depotLat) }};
    const defaultLng = {{ old('longitude', $depotLng) }};

    const map = L.map('addressMap').setView([defaultLat, defaultLng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const marker = L.marker([defaultLat, defaultLng], {
        draggable: true
    }).addTo(map);

    marker.bindPopup('<b>Lokasi Pengantaran</b><br>Geser pin ke titik rumah pelanggan').openPopup();

    function updateCoords(lat, lng) {
        document.getElementById('latitude').value = parseFloat(lat).toFixed(7);
        document.getElementById('longitude').value = parseFloat(lng).toFixed(7);
    }

    marker.on('dragend', function (e) {
        const pos = e.target.getLatLng();
        updateCoords(pos.lat, pos.lng);
    });

    map.on('click', function (e) {
        marker.setLatLng(e.latlng);
        updateCoords(e.latlng.lat, e.latlng.lng);
        marker.openPopup();
    });

    // Invalidate map size after render
    setTimeout(() => {
        map.invalidateSize();
    }, 300);
});
</script>
@endsection
