@extends('layouts.admin')

@section('page_title', __('Rincian Pesanan #') . $order->order_number)

@section('breadcrumbs')
    <x-breadcrumbs :items="[
        ['title' => __('Kelola Pesanan'), 'url' => route('admin.orders.index')],
        ['title' => '#' . $order->order_number]
    ]" />
@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .leaflet-pane { z-index: 10 !important; }
        .leaflet-bottom, .leaflet-top { z-index: 11 !important; }
    </style>
@endpush

@section('content')
<div class="space-y-6">

    <!-- Top Action & Info Bar -->
    <x-card class="p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2.5 flex-wrap gap-y-2">
                <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight font-mono">{{ __('Pesanan') }} #{{ $order->order_number }}</h1>
                <x-badge variant="neutral" class="{{ $order->status_badge['color'] }}">
                    {{ __($order->status_badge['label']) }}
                </x-badge>
                <x-badge variant="neutral" class="{{ $order->payment_badge['color'] }}">
                    {{ __($order->payment_badge['label']) }}
                </x-badge>
            </div>
            <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center space-x-2 flex-wrap gap-y-1">
                <span>{{ __('Dipesan pada') }} {{ $order->created_at->format('d M Y, H:i') }} WIB</span>
                <span>&bull;</span>
                <span>{{ __('Metode') }}:</span>
                <x-badge variant="neutral" size="sm" class="{{ $order->payment_method_badge['color'] }}">
                    <i class="fa-solid {{ $order->payment_method_badge['icon'] }} mr-1"></i>
                    {{ __($order->payment_method_badge['label']) }}
                </x-badge>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            <x-button :href="route('admin.orders.index')" variant="secondary" size="sm" icon="fa-solid fa-arrow-left">
                {{ __('Kembali') }}
            </x-button>
        </div>
    </x-card>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left col: Items, Customer, Delivery (8 cols) -->
        <div class="lg:col-span-8 space-y-6">

            <!-- Products Ordered -->
            <x-card class="p-6 space-y-4">
                <h2 class="font-black text-sm text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Rincian Barang Dipesan') }}</h2>

                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($order->items as $item)
                        <div class="py-3 flex justify-between items-center text-sm">
                            <div>
                                <div class="font-bold text-slate-900 dark:text-white">{{ $item->product_name }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ $item->quantity }} {{ $item->unit }} &times; Rp {{ number_format($item->price, 0, ',', '.') }}
                                    @if ($item->is_exchange)
                                        <span class="text-blue-600 dark:text-blue-400 font-semibold ml-1">({{ __('Tukar Galon Kosong') }})</span>
                                    @endif
                                </div>
                            </div>
                            <div class="font-bold text-slate-900 dark:text-white">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="pt-3 border-t border-slate-200 dark:border-slate-800 space-y-1.5 text-xs">
                    <div class="flex justify-between text-slate-600 dark:text-slate-400">
                        <span>{{ __('Subtotal Barang') }}:</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600 dark:text-slate-400">
                        <span>{{ __('Ongkos Kirim') }}:</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Rp {{ number_format($order->delivery_fee, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-base font-black text-slate-900 dark:text-white pt-2 border-t border-slate-100 dark:border-slate-800">
                        <span>{{ __('Total Tagihan') }}:</span>
                        <span class="text-cyan-600 dark:text-cyan-400">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </x-card>

            <!-- Real-Time Delivery Route & Map Card (Google Maps / Leaflet) -->
            <x-card class="p-5 sm:p-6 space-y-4" 
                    x-data="orderRouteTracker({
                        orderId: {{ $order->id }},
                        orderNumber: '{{ addslashes($order->order_number) }}',
                        recipientName: '{{ addslashes($order->recipient_name) }}',
                        addressText: '{{ addslashes($order->delivery_address_text) }}',
                        benchmark: '{{ addslashes($order->delivery_benchmark ?? '') }}',
                        destLat: {{ $order->delivery_latitude ?? 'null' }},
                        destLng: {{ $order->delivery_longitude ?? 'null' }},
                        depotLat: {{ $depotLat }},
                        depotLng: {{ $depotLng }},
                        companyName: '{{ addslashes($companyName) }}',
                        companyAddress: '{{ addslashes($companyAddress) }}',
                        googleMapsApiKey: '{{ addslashes($googleMapsApiKey) }}',
                        saveCoordinatesUrl: '{{ route('admin.orders.coordinates', $order) }}',
                        saveApiKeyUrl: '{{ route('admin.orders.save-google-maps-key') }}',
                        csrfToken: '{{ csrf_token() }}'
                    })">

                <!-- Header Bar -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="space-y-1">
                        <div class="flex items-center space-x-2 flex-wrap gap-y-1">
                            <h2 class="font-black text-sm text-slate-900 dark:text-white uppercase tracking-wider flex items-center">
                                <i class="fa-solid fa-map-location-dot text-cyan-500 mr-2 text-base"></i>
                                {{ __('Peta Rute & Pengantaran Real-Time') }}
                            </h2>
                            <template x-if="isUsingGoogleMaps">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 flex items-center">
                                    <i class="fa-brands fa-google mr-1 text-[9px]"></i> Google Maps API
                                </span>
                            </template>
                            <template x-if="!isUsingGoogleMaps">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 flex items-center">
                                    <i class="fa-solid fa-map mr-1 text-[9px]"></i> OpenStreetMap (Leaflet)
                                </span>
                            </template>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ __('Rute otomatis dari Depot Toko langsung ke lokasi alamat pelanggan.') }}
                        </p>
                    </div>

                    <!-- Metrics Badges & Settings -->
                    <div class="flex items-center space-x-2 flex-wrap gap-y-1 text-xs">
                        <div class="px-3 py-1.5 rounded-xl bg-cyan-50 dark:bg-cyan-950/60 border border-cyan-100 dark:border-cyan-800 text-cyan-700 dark:text-cyan-300 font-bold flex items-center">
                            <i class="fa-solid fa-route mr-1.5 text-cyan-500"></i>
                            <span x-text="routeDistance || '{{ __('Menghitung...') }}'"></span>
                        </div>
                        <div class="px-3 py-1.5 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-100 dark:border-amber-800 text-amber-700 dark:text-amber-300 font-bold flex items-center">
                            <i class="fa-regular fa-clock mr-1.5 text-amber-500"></i>
                            <span x-text="routeDuration || '{{ __('Estimasi...') }}'"></span>
                        </div>
                        <button type="button" @click="showKeyModal = true" 
                                class="px-2.5 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition cursor-pointer"
                                title="{{ __('Atur Google Maps API Key') }}">
                            <i class="fa-solid fa-gear"></i>
                        </button>
                    </div>
                </div>

                <!-- Toast / Notice Bar if geocoding or status -->
                <template x-if="toastNotice">
                    <div class="p-3 bg-cyan-50 dark:bg-cyan-950/40 border border-cyan-200 dark:border-cyan-800 text-cyan-800 dark:text-cyan-200 rounded-xl text-xs flex items-center justify-between">
                        <span x-text="toastNotice"></span>
                        <button type="button" @click="toastNotice = ''" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 ml-2">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </template>

                <!-- Map Container -->
                <div class="relative rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800 shadow-inner bg-slate-100 dark:bg-slate-950">
                    <div id="orderDeliveryMap" style="height: 380px; width: 100%; z-index: 1;"></div>

                    <!-- Geocoding Loader Overlay -->
                    <div x-show="isGeocoding" x-cloak class="absolute inset-0 z-20 bg-slate-900/40 backdrop-blur-2xs flex items-center justify-center">
                        <div class="p-4 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center space-x-2">
                            <i class="fa-solid fa-spinner fa-spin text-cyan-600"></i>
                            <span>{{ __('Mencari titik koordinat alamat via Google Maps...') }}</span>
                        </div>
                    </div>

                    <!-- Map overlay badges -->
                    <div class="absolute top-3 left-3 z-10 flex flex-col gap-1.5 text-xs">
                        <div class="px-3 py-1 rounded-lg bg-white/95 dark:bg-slate-900/95 backdrop-blur-xs border border-slate-200 dark:border-slate-700 shadow-xs font-semibold text-slate-700 dark:text-slate-300 flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                            <span>{{ __('Depot Toko') }}</span>
                            <span class="text-slate-400">&rarr;</span>
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                            <span>{{ __('Rumah Pelanggan') }}</span>
                        </div>
                        <template x-if="isLiveTracking">
                            <div class="px-3 py-1 rounded-lg bg-emerald-500/90 text-white font-bold text-[11px] shadow-xs flex items-center animate-pulse">
                                <i class="fa-solid fa-location-crosshairs mr-1.5"></i>
                                <span>{{ __('GPS Anda Aktif (Live Tracking)') }}</span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Controls & Navigation Bar -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
                    <div class="text-xs text-slate-600 dark:text-slate-400">
                        <template x-if="destLat && destLng">
                            <div class="flex items-center space-x-1.5 font-mono text-[11px] text-slate-500 dark:text-slate-400">
                                <i class="fa-solid fa-map-pin text-rose-500"></i>
                                <span x-text="Number(destLat).toFixed(6) + ', ' + Number(destLng).toFixed(6)"></span>
                            </div>
                        </template>
                        <template x-if="!destLat || !destLng">
                            <span class="text-amber-600 dark:text-amber-400 font-semibold flex items-center">
                                <i class="fa-solid fa-triangle-exclamation mr-1"></i> {{ __('Koordinat GPS belum tercatat pada pesanan ini') }}
                            </span>
                        </template>
                    </div>

                    <div class="flex items-center space-x-2 flex-wrap gap-y-2">
                        <!-- Toggle Live GPS tracking -->
                        <button type="button" @click="toggleLiveTracking()" 
                                :class="isLiveTracking ? 'bg-rose-600 hover:bg-rose-700 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700'"
                                class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center shadow-xs cursor-pointer">
                            <i class="fa-solid fa-location-crosshairs mr-1.5" :class="isLiveTracking ? 'animate-spin' : ''"></i>
                            <span x-text="isLiveTracking ? '{{ __('Matikan Live GPS') }}' : '{{ __('Gunakan GPS Saya Saat Ini') }}'"></span>
                        </button>

                        <!-- Open Turn-by-Turn in Google Maps -->
                        <a :href="googleNavigationUrl" target="_blank"
                           class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition flex items-center cursor-pointer">
                            <i class="fa-solid fa-diamond-turn-right mr-1.5"></i>
                            {{ __('Buka Navigasi Google Maps') }}
                        </a>
                    </div>
                </div>

                <!-- Modal Pengaturan Google Maps API Key -->
                <div x-show="showKeyModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                    <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-slate-100 dark:border-slate-800" @click.away="showKeyModal = false">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                            <div class="flex items-center space-x-2">
                                <i class="fa-brands fa-google text-emerald-600 text-lg"></i>
                                <h3 class="font-extrabold text-base text-slate-900 dark:text-white">{{ __('Konfigurasi Google Maps API') }}</h3>
                            </div>
                            <button type="button" @click="showKeyModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                                <i class="fa-solid fa-xmark text-lg"></i>
                            </button>
                        </div>

                        <div class="text-xs text-slate-500 dark:text-slate-400 space-y-2 leading-relaxed">
                            <p>{{ __('Masukkan Google Maps JavaScript API Key Anda untuk mengaktifkan perhitungan rute resmi, estimasi jarak jalan raya, serta geocoding alamat.') }}</p>
                            <p class="text-slate-400">{{ __('Jika dikosongkan, sistem otomatis menggunakan OpenStreetMap gratis (Leaflet).') }}</p>
                        </div>

                        <form @submit.prevent="submitApiKey()" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">{{ __('Google Maps API Key') }}</label>
                                <input type="text" x-model="inputApiKey" placeholder="AIzaSy..." 
                                       class="w-full text-xs font-mono border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white rounded-xl p-2.5 focus:ring-2 focus:ring-cyan-500">
                            </div>

                            <div class="flex justify-end space-x-2 pt-2">
                                <button type="button" @click="showKeyModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer">
                                    {{ __('Batal') }}
                                </button>
                                <button type="submit" :disabled="isSavingKey" class="px-5 py-2 rounded-xl text-xs font-bold bg-cyan-600 hover:bg-cyan-700 text-white shadow-xs cursor-pointer flex items-center">
                                    <template x-if="isSavingKey">
                                        <i class="fa-solid fa-spinner fa-spin mr-1.5"></i>
                                    </template>
                                    <span>{{ __('Simpan & Terapkan') }}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </x-card>

            <!-- Customer & Destination Card -->
            <x-card class="p-6 space-y-3">
                <h2 class="font-black text-sm text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Tujuan Pengantaran & Kontak') }}</h2>

                <div class="text-xs text-slate-700 dark:text-slate-300 space-y-2">
                    <div><span class="text-slate-400 dark:text-slate-500">{{ __('Penerima') }}:</span> <span class="font-bold text-slate-900 dark:text-white">{{ $order->recipient_name }}</span></div>
                    <div><span class="text-slate-400 dark:text-slate-500">{{ __('Nomor Telepon / WA') }}:</span> <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $order->recipient_phone }}</span></div>
                    <div><span class="text-slate-400 dark:text-slate-500">{{ __('Alamat Lengkap') }}:</span> {{ $order->delivery_address_text }}</div>
                    @if ($order->delivery_benchmark)
                        <div><span class="text-slate-400 dark:text-slate-500">{{ __('Patokan Lokasi') }}:</span> {{ $order->delivery_benchmark }}</div>
                    @endif
                    @if ($order->customer_notes)
                        <div class="p-3 bg-slate-50 dark:bg-slate-950/60 border border-slate-100 dark:border-slate-800 rounded-xl text-slate-800 dark:text-slate-200 italic">
                            {{ __('Catatan Pelanggan') }}: "{{ $order->customer_notes }}"
                        </div>
                    @endif
                </div>
            </x-card>

            <!-- Delivery Proof Photos if completed -->
            @if ($order->delivery && $order->delivery->photos->isNotEmpty())
                <x-card class="p-6 space-y-3">
                    <h2 class="font-black text-sm text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Foto Bukti Serah Terima Kurir') }}</h2>
                    <div class="flex flex-wrap gap-4">
                        @foreach ($order->delivery->photos as $ph)
                            <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-2xs">
                                <img src="/storage/{{ $ph->photo_path }}" alt="Bukti Serah Terima" class="h-44 w-44 object-cover hover:scale-105 transition">
                                <div class="p-2 text-[10px] text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-950 text-center">
                                    {{ $ph->created_at->format('H:i, d M Y') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            @endif

            <!-- Order Status Audit Timeline -->
            <x-card class="p-6 space-y-4">
                <h2 class="font-black text-sm text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Riwayat Status (Audit Trail)') }}</h2>

                <div class="space-y-3">
                    @foreach ($order->statusHistories as $hist)
                        <div class="flex items-start space-x-3 text-xs">
                            <div class="w-6 h-6 rounded-full bg-cyan-100 dark:bg-cyan-950 text-cyan-600 dark:text-cyan-400 flex items-center justify-center font-bold text-[10px] mt-0.5 shrink-0">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div>
                                <div class="font-bold text-slate-800 dark:text-slate-200 capitalize">{{ $hist->status }}</div>
                                <div class="text-slate-500 dark:text-slate-400">{{ $hist->notes }}</div>
                                <div class="text-[10px] text-slate-400">{{ $hist->created_at->format('d M Y, H:i:s') }} WIB &bull; {{ __('Oleh') }}: {{ $hist->changer?->name ?? __('Sistem') }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>

        </div>

        <!-- Right col: Control Panel (Status, Delivery, Payment) (4 cols) -->
        <div class="lg:col-span-4 space-y-6">

            <!-- 1. Change Status Control -->
            <x-card class="p-5 space-y-4">
                <h3 class="font-black text-sm text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Ubah Status Pesanan') }}</h3>

                <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="space-y-3">
                    @csrf
                    @method('PUT')

                    <div>
                        @php
                            $statusChangeOptions = [
                                'pending' => __('Menunggu Konfirmasi'),
                                'confirmed' => __('Konfirmasi (Confirmed)'),
                                'processing' => __('Sedang Disiapkan (Processing)'),
                                'ready_for_delivery' => __('Siap Dikirim'),
                                'completed' => __('Selesai (Completed)'),
                                'cancelled' => __('Batalkan (Cancelled)'),
                                'rejected' => __('Tolak (Rejected)'),
                            ];
                        @endphp
                        <x-select 
                            name="status" 
                            :options="$statusChangeOptions" 
                            :selected="$order->status" 
                        />
                    </div>

                    <div>
                        <x-input 
                            name="notes" 
                            placeholder="{{ __('Catatan perubahan status...') }}" 
                        />
                    </div>

                    <x-button type="submit" variant="primary" class="w-full">
                        {{ __('Perbarui Status') }}
                    </x-button>
                </form>
            </x-card>

            <!-- 2. Delivery Control -->
            @if ($order->delivery)
                <x-card class="p-5 space-y-3">
                    <h3 class="font-black text-sm text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Pengantaran Pesanan') }}</h3>

                    <div class="p-3.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-2xl text-xs space-y-2">
                        <div>
                            <div class="font-bold text-emerald-900 dark:text-emerald-300">{{ __('No. Pengantaran') }}:</div>
                            <div class="text-sm font-black text-slate-800 dark:text-slate-100 font-mono">{{ $order->delivery->delivery_number }}</div>
                            <div class="text-slate-500 dark:text-slate-400 mt-1">{{ __('Status') }}: <span class="capitalize font-semibold text-emerald-700 dark:text-emerald-400">{{ __($order->delivery->status_badge['label'] ?? $order->delivery->status) }}</span></div>
                        </div>

                        <x-button :href="route('admin.deliveries.show', $order->delivery)" variant="success" size="sm" class="w-full" icon="fa-solid fa-truck-fast">
                            {{ __('Buka Kontrol Pengantaran & Bukti') }}
                        </x-button>
                    </div>
                </x-card>
            @endif

            <!-- 3. Payment Status Control -->
            <x-card class="p-5 space-y-3">
                <h3 class="font-black text-sm text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Status Pembayaran') }}</h3>

                <form method="POST" action="{{ route('admin.orders.payment', $order) }}" class="space-y-3">
                    @csrf
                    @method('PUT')

                    <div>
                        @php
                            $paymentChangeOptions = [
                                'unpaid' => __('Belum Lunas'),
                                'partially_paid' => __('Bayar Sebagian'),
                                'paid' => __('Lunas (Paid)'),
                                'refunded' => __('Refund'),
                            ];
                        @endphp
                        <x-select 
                            name="payment_status" 
                            :options="$paymentChangeOptions" 
                            :selected="$order->payment_status" 
                        />
                    </div>

                    <div>
                        <x-input 
                            :label="__('Nominal Diterima (Rp)')" 
                            type="number" 
                            name="amount_paid" 
                            :value="$order->total_amount" 
                        />
                    </div>

                    <x-button type="submit" variant="success" class="w-full" icon="fa-solid fa-check">
                        {{ __('Simpan Pembayaran') }}
                    </x-button>
                </form>
            </x-card>

        </div>

    </div>

</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
function orderRouteTracker(config) {
    return {
        orderId: config.orderId,
        orderNumber: config.orderNumber,
        recipientName: config.recipientName,
        addressText: config.addressText,
        benchmark: config.benchmark,
        destLat: config.destLat,
        destLng: config.destLng,
        depotLat: config.depotLat,
        depotLng: config.depotLng,
        companyName: config.companyName,
        companyAddress: config.companyAddress,
        googleMapsApiKey: config.googleMapsApiKey || '',
        inputApiKey: config.googleMapsApiKey || '',
        saveCoordinatesUrl: config.saveCoordinatesUrl,
        saveApiKeyUrl: config.saveApiKeyUrl,
        csrfToken: config.csrfToken,

        isUsingGoogleMaps: false,
        isGeocoding: false,
        isLiveTracking: false,
        isSavingKey: false,
        showKeyModal: false,
        toastNotice: '',
        routeDistance: '',
        routeDuration: '',
        watchId: null,

        // Map instances
        gMap: null,
        gDirectionsService: null,
        gDirectionsRenderer: null,
        gDepotMarker: null,
        gDestMarker: null,
        gAdminLiveMarker: null,

        lMap: null,
        lRouteLayer: null,
        lDepotMarker: null,
        lDestMarker: null,
        lAdminLiveMarker: null,

        get googleNavigationUrl() {
            if (this.destLat && this.destLng) {
                return `https://www.google.com/maps/dir/?api=1&origin=${this.depotLat},${this.depotLng}&destination=${this.destLat},${this.destLng}&travelmode=driving`;
            }
            return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(this.addressText + ' Surabaya')}`;
        },

        init() {
            this.$nextTick(() => {
                if (this.googleMapsApiKey) {
                    this.loadGoogleMaps();
                } else {
                    this.initLeaflet();
                }
            });
        },

        loadGoogleMaps() {
            if (window.google && window.google.maps) {
                this.initGoogleMap();
                return;
            }

            window.initOrderMapGmapsCallback = () => {
                this.initGoogleMap();
            };

            window.gm_authFailure = () => {
                console.warn('Google Maps auth failed, falling back to Leaflet OpenStreetMap');
                this.toastNotice = 'Peringatan: Google Maps API Key tidak valid / billing belum aktif. Mengalihkan ke OpenStreetMap...';
                this.isUsingGoogleMaps = false;
                this.initLeaflet();
            };

            const script = document.createElement('script');
            script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(this.googleMapsApiKey)}&callback=initOrderMapGmapsCallback&libraries=places,directions`;
            script.async = true;
            script.defer = true;
            script.onerror = () => {
                console.warn('Gagal memuat script Google Maps, fallback ke Leaflet.');
                this.initLeaflet();
            };
            document.head.appendChild(script);
        },

        initGoogleMap() {
            if (!window.google || !window.google.maps) {
                this.initLeaflet();
                return;
            }

            const mapDiv = document.getElementById('orderDeliveryMap');
            if (!mapDiv) return;

            this.isUsingGoogleMaps = true;
            const depotCenter = { lat: this.depotLat, lng: this.depotLng };

            this.gMap = new google.maps.Map(mapDiv, {
                center: depotCenter,
                zoom: 14,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                mapTypeControl: true,
                streetViewControl: true,
                fullscreenControl: true,
            });

            this.gDirectionsService = new google.maps.DirectionsService();
            this.gDirectionsRenderer = new google.maps.DirectionsRenderer({
                map: this.gMap,
                suppressMarkers: false,
                polylineOptions: {
                    strokeColor: '#06b6d4',
                    strokeWeight: 5,
                    strokeOpacity: 0.85
                }
            });

            if (this.destLat && this.destLng) {
                this.calculateGoogleRoute();
            } else {
                this.autoGeocodeAddress();
            }
        },

        calculateGoogleRoute() {
            if (!this.gDirectionsService || !this.destLat || !this.destLng) return;

            const origin = new google.maps.LatLng(this.depotLat, this.depotLng);
            const destination = new google.maps.LatLng(this.destLat, this.destLng);

            this.gDirectionsService.route({
                origin: origin,
                destination: destination,
                travelMode: google.maps.TravelMode.DRIVING
            }, (response, status) => {
                if (status === 'OK') {
                    this.gDirectionsRenderer.setDirections(response);
                    const leg = response.routes[0].legs[0];
                    this.routeDistance = leg.distance.text;
                    this.routeDuration = leg.duration.text;
                } else {
                    console.warn('Google Directions request failed: ' + status);
                    this.fallbackPlotMarkersGoogle();
                }
            });
        },

        fallbackPlotMarkersGoogle() {
            if (!this.gMap) return;

            new google.maps.Marker({
                position: { lat: this.depotLat, lng: this.depotLng },
                map: this.gMap,
                title: this.companyName,
                icon: {
                    url: 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png',
                    scaledSize: new google.maps.Size(40, 40)
                }
            });

            if (this.destLat && this.destLng) {
                new google.maps.Marker({
                    position: { lat: this.destLat, lng: this.destLng },
                    map: this.gMap,
                    title: this.recipientName,
                    icon: {
                        url: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
                        scaledSize: new google.maps.Size(40, 40)
                    }
                });

                const bounds = new google.maps.LatLngBounds();
                bounds.extend({ lat: this.depotLat, lng: this.depotLng });
                bounds.extend({ lat: this.destLat, lng: this.destLng });
                this.gMap.fitBounds(bounds);
            }
        },

        initLeaflet() {
            if (typeof L === 'undefined') return;

            const mapDiv = document.getElementById('orderDeliveryMap');
            if (!mapDiv) return;

            this.isUsingGoogleMaps = false;

            if (this.lMap) {
                this.lMap.remove();
                this.lMap = null;
            }

            this.lMap = L.map('orderDeliveryMap').setView([this.depotLat, this.depotLng], 14);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(this.lMap);

            const depotIcon = L.divIcon({
                className: 'custom-depot-pin',
                html: `<div style="background-color: #0284c7; color: white; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.3); font-size: 14px;"><i class="fa-solid fa-store"></i></div>`,
                iconSize: [34, 34],
                iconAnchor: [17, 17]
            });

            this.lDepotMarker = L.marker([this.depotLat, this.depotLng], { icon: depotIcon })
                .addTo(this.lMap)
                .bindPopup(`<strong>${this.companyName}</strong><br><span style="font-size: 11px;">${this.companyAddress}</span>`);

            if (this.destLat && this.destLng) {
                this.calculateLeafletRoute();
            } else {
                this.autoGeocodeAddress();
            }
        },

        calculateLeafletRoute() {
            if (!this.lMap || !this.destLat || !this.destLng) return;

            const destIcon = L.divIcon({
                className: 'custom-dest-pin',
                html: `<div style="background-color: #ef4444; color: white; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.3); font-size: 14px;"><i class="fa-solid fa-house"></i></div>`,
                iconSize: [34, 34],
                iconAnchor: [17, 17]
            });

            this.lDestMarker = L.marker([this.destLat, this.destLng], { icon: destIcon })
                .addTo(this.lMap)
                .bindPopup(`<strong>${this.recipientName}</strong><br><span style="font-size: 11px;">${this.addressText}</span>`);

            const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${this.depotLng},${this.depotLat};${this.destLng},${this.destLat}?overview=full&geometries=geojson`;

            fetch(osrmUrl)
                .then(res => res.json())
                .then(data => {
                    if (data.code === 'Ok' && data.routes && data.routes.length > 0) {
                        const route = data.routes[0];
                        const distanceKm = (route.distance / 1000).toFixed(1);
                        const durationMnt = Math.round(route.duration / 60);

                        this.routeDistance = `${distanceKm} km`;
                        this.routeDuration = `${durationMnt} mnt`;

                        if (this.lRouteLayer) {
                            this.lMap.removeLayer(this.lRouteLayer);
                        }

                        this.lRouteLayer = L.geoJSON(route.geometry, {
                            style: {
                                color: '#06b6d4',
                                weight: 5,
                                opacity: 0.85
                            }
                        }).addTo(this.lMap);

                        this.lMap.fitBounds(this.lRouteLayer.getBounds(), { padding: [50, 50] });
                    } else {
                        this.lMap.fitBounds([[this.depotLat, this.depotLng], [this.destLat, this.destLng]], { padding: [50, 50] });
                    }
                })
                .catch(() => {
                    this.lMap.fitBounds([[this.depotLat, this.depotLng], [this.destLat, this.destLng]], { padding: [50, 50] });
                });
        },

        autoGeocodeAddress() {
            if (this.isGeocoding || !this.addressText) return;
            this.isGeocoding = true;
            this.toastNotice = 'Mencari koordinat alamat pelanggan secara otomatis...';

            const searchQuery = `${this.addressText}, Surabaya`;

            if (this.isUsingGoogleMaps && window.google && window.google.maps) {
                const geocoder = new google.maps.Geocoder();
                geocoder.geocode({ address: searchQuery }, (results, status) => {
                    this.isGeocoding = false;
                    if (status === 'OK' && results[0]) {
                        const loc = results[0].geometry.location;
                        this.saveNewCoordinates(loc.lat(), loc.lng());
                    } else {
                        this.toastNotice = 'Alamat belum dapat ditentukan titik koordinatnya secara akurat.';
                    }
                });
            } else {
                const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchQuery)}&limit=1`;
                fetch(url)
                    .then(r => r.json())
                    .then(data => {
                        this.isGeocoding = false;
                        if (data && data.length > 0) {
                            const lat = parseFloat(data[0].lat);
                            const lng = parseFloat(data[0].lon);
                            this.saveNewCoordinates(lat, lng);
                        } else {
                            this.toastNotice = 'Alamat belum dapat ditentukan titik koordinatnya secara akurat.';
                        }
                    })
                    .catch(() => {
                        this.isGeocoding = false;
                    });
            }
        },

        saveNewCoordinates(lat, lng) {
            this.destLat = lat;
            this.destLng = lng;
            this.toastNotice = 'Titik lokasi berhasil ditemukan! Menyimpan koordinat...';

            fetch(this.saveCoordinatesUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ latitude: lat, longitude: lng })
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    this.toastNotice = 'Koordinat alamat berhasil disimpan dan rute diperbarui.';
                    if (this.isUsingGoogleMaps) {
                        this.calculateGoogleRoute();
                    } else {
                        this.calculateLeafletRoute();
                    }
                }
            })
            .catch(err => {
                console.error('Failed to save coordinates:', err);
            });
        },

        toggleLiveTracking() {
            if (this.isLiveTracking) {
                if (this.watchId !== null) {
                    navigator.geolocation.clearWatch(this.watchId);
                    this.watchId = null;
                }
                this.isLiveTracking = false;
                this.toastNotice = 'Live tracking GPS dimatikan.';
                return;
            }

            if (!navigator.geolocation) {
                alert('Browser Anda tidak mendukung sensor GPS (Geolocation).');
                return;
            }

            this.isLiveTracking = true;
            this.toastNotice = 'Mengaktifkan sensor GPS Admin...';

            this.watchId = navigator.geolocation.watchPosition(
                (pos) => {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    this.updateAdminLiveLocation(lat, lng);
                },
                (err) => {
                    console.warn('Geolocation error:', err);
                    this.isLiveTracking = false;
                    alert('Gagal mengakses GPS: ' + err.message);
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        },

        updateAdminLiveLocation(lat, lng) {
            this.toastNotice = `Posisi Admin terdeteksi: ${lat.toFixed(5)}, ${lng.toFixed(5)}`;

            if (this.isUsingGoogleMaps && this.gMap) {
                if (!this.gAdminLiveMarker) {
                    this.gAdminLiveMarker = new google.maps.Marker({
                        position: { lat: lat, lng: lng },
                        map: this.gMap,
                        title: 'Posisi Anda Saat Ini (Admin)',
                        icon: {
                            url: 'https://maps.google.com/mapfiles/ms/icons/motorcycling.png',
                            scaledSize: new google.maps.Size(42, 42)
                        }
                    });
                } else {
                    this.gAdminLiveMarker.setPosition({ lat: lat, lng: lng });
                }
                this.gMap.panTo({ lat: lat, lng: lng });
            } else if (this.lMap) {
                const liveIcon = L.divIcon({
                    className: 'custom-admin-live-pin',
                    html: `<div style="background-color: #10b981; color: white; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 4px 8px rgba(0,0,0,0.4); font-size: 16px;"><i class="fa-solid fa-motorcycle animate-bounce"></i></div>`,
                    iconSize: [36, 36],
                    iconAnchor: [18, 18]
                });

                if (!this.lAdminLiveMarker) {
                    this.lAdminLiveMarker = L.marker([lat, lng], { icon: liveIcon })
                        .addTo(this.lMap)
                        .bindPopup('<strong>Posisi Anda Saat Ini</strong><br>Admin / Pengantar');
                } else {
                    this.lAdminLiveMarker.setLatLng([lat, lng]);
                }
                this.lMap.panTo([lat, lng]);
            }
        },

        submitApiKey() {
            this.isSavingKey = true;
            fetch(this.saveApiKeyUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ api_key: this.inputApiKey })
            })
            .then(r => r.json())
            .then(res => {
                this.isSavingKey = false;
                this.showKeyModal = false;
                this.googleMapsApiKey = res.api_key || '';
                this.toastNotice = res.message;
                if (this.googleMapsApiKey) {
                    this.loadGoogleMaps();
                } else {
                    this.initLeaflet();
                }
            })
            .catch(() => {
                this.isSavingKey = false;
                alert('Gagal menyimpan Google Maps API Key.');
            });
        }
    };
}
</script>
@endpush
