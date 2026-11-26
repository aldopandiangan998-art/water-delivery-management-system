@extends('layouts.admin')

@section('page_title', 'Rincian Alamat: ' . $address->label)

@section('content')
<div class="space-y-6">

    <!-- Header bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xs uppercase font-extrabold px-2.5 py-0.5 rounded bg-slate-100 text-slate-700">
                    {{ $address->label }}
                </span>
                @if ($address->is_default)
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-cyan-50 text-cyan-700 border border-cyan-200">
                        <i class="fa-solid fa-check-circle mr-1"></i> Alamat Utama
                    </span>
                @endif
            </div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight mt-1">{{ $address->recipient_name }}</h1>
            <p class="text-xs text-slate-500">Pemilik Akun: <span class="font-bold text-slate-800">{{ $address->user?->name }}</span> ({{ $address->user?->email }})</p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.addresses.edit', $address) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold bg-cyan-600 hover:bg-cyan-700 text-white shadow-xs transition flex items-center">
                <i class="fa-solid fa-pen-to-square mr-1.5"></i> Edit Alamat
            </a>

            @if ($address->latitude && $address->longitude)
                <a href="https://maps.google.com/?q={{ $address->latitude }},{{ $address->longitude }}" target="_blank"
                   class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition flex items-center">
                    <i class="fa-solid fa-map-location-dot mr-1.5"></i> Buka Google Maps
                </a>
            @endif

            <form id="delete-address-show-form" method="POST" action="{{ route('admin.addresses.destroy', $address) }}" class="inline">
                @csrf
                @method('DELETE')
                <button type="button" 
                        @click="$dispatch('open-confirm-modal', {
                            form: 'delete-address-show-form',
                            title: '{{ __('Hapus Alamat Pelanggan') }}',
                            message: '{{ __('Apakah Anda yakin ingin menghapus alamat ini?') }}'
                        })"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold bg-rose-50 text-rose-600 hover:bg-rose-100 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-200 dark:border-rose-800 transition flex items-center cursor-pointer">
                    <i class="fa-solid fa-trash mr-1.5"></i> Hapus
                </button>
            </form>

            <a href="{{ route('admin.addresses.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">
                &larr; Kembali
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Left: Address Information -->
        <div class="space-y-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2 flex items-center">
                    <i class="fa-solid fa-location-dot text-cyan-600 mr-2"></i> Rincian Alamat Pengantaran
                </h2>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-slate-400 block">Nama Penerima:</span>
                        <span class="font-bold text-slate-900 text-sm">{{ $address->recipient_name }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 block">Nomor WhatsApp / HP:</span>
                        <div class="flex items-center space-x-2 mt-0.5">
                            <span class="font-bold text-slate-800">{{ $address->recipient_phone }}</span>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $address->recipient_phone) }}" target="_blank"
                               class="text-emerald-600 font-bold hover:underline flex items-center">
                                <i class="fa-brands fa-whatsapp ml-1 mr-1"></i> Chat
                            </a>
                        </div>
                    </div>

                    <div>
                        <span class="text-slate-400 block">Alamat Lengkap:</span>
                        <p class="text-slate-700 font-medium leading-relaxed mt-0.5">{{ $address->address_line }}</p>
                    </div>

                    @if ($address->benchmark)
                        <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-amber-900">
                            <span class="font-bold block text-[11px] mb-0.5">Patokan Lokasi:</span>
                            {{ $address->benchmark }}
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <div>
                            <span class="text-slate-400 block">Wilayah Layanan:</span>
                            <span class="font-bold text-slate-800">{{ $address->serviceArea?->name ?? 'Belum ditentukan' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Tarif Ongkir Standar:</span>
                            <span class="font-bold text-emerald-700">Rp {{ number_format($address->serviceArea?->delivery_fee ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-slate-400 block">Koordinat GPS:</span>
                        <span class="font-mono text-slate-700 font-bold">
                            {{ $address->latitude ?? '-' }}, {{ $address->longitude ?? '-' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Recent Orders Card -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-3">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2 flex items-center">
                    <i class="fa-solid fa-clock-rotate-left text-cyan-600 mr-2"></i> Riwayat Pesanan ke Alamat Ini
                </h2>

                <div class="space-y-2 text-xs">
                    @forelse ($recentOrders as $ord)
                        <div class="p-3 bg-slate-50 hover:bg-slate-100 transition rounded-xl border border-slate-100 flex items-center justify-between">
                            <div>
                                <a href="{{ route('admin.orders.show', $ord) }}" class="font-bold text-cyan-700 hover:underline">
                                    #{{ $ord->order_number }}
                                </a>
                                <div class="text-[10px] text-slate-400">{{ $ord->created_at->format('d M Y, H:i') }} WIB</div>
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-0.5 rounded-full font-bold text-[10px] border {{ $ord->status_badge['color'] }}">
                                    {{ $ord->status_badge['label'] }}
                                </span>
                                <div class="font-bold text-slate-800 mt-1">Rp {{ number_format($ord->total_amount, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    @empty
                        <p class="text-slate-400 py-3 text-center">Belum ada pesanan yang diantar ke alamat ini.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right: Leaflet Map Preview -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-3">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2 flex items-center justify-between">
                <span class="flex items-center">
                    <i class="fa-solid fa-map text-emerald-600 mr-2"></i> Peta Lokasi Pengantaran
                </span>
                <span class="text-[10px] text-slate-400">Leaflet OpenStreetMap</span>
            </h2>

            @if ($address->latitude && $address->longitude)
                <div id="viewMap" class="w-full h-96 rounded-2xl border border-slate-200 shadow-2xs z-10"></div>
            @else
                <div class="h-96 rounded-2xl border border-dashed border-slate-300 flex flex-col items-center justify-center text-slate-400 text-xs">
                    <i class="fa-solid fa-location-crosshairs text-4xl mb-2 text-slate-300"></i>
                    <p>Alamat ini belum memiliki koordinat GPS.</p>
                    <a href="{{ route('admin.addresses.edit', $address) }}" class="mt-2 text-cyan-600 font-bold hover:underline">
                        + Tambahkan Titik GPS Sekarang
                    </a>
                </div>
            @endif
        </div>
    </div>

</div>

@if ($address->latitude && $address->longitude)
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const lat = {{ $address->latitude }};
        const lng = {{ $address->longitude }};

        const map = L.map('viewMap').setView([lat, lng], 16);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const marker = L.marker([lat, lng]).addTo(map);
        marker.bindPopup('<b>{{ $address->label }}</b><br>{{ $address->recipient_name }}<br>{{ $address->address_line }}').openPopup();

        setTimeout(() => {
            map.invalidateSize();
        }, 300);
    });
    </script>
@endif
@endsection
