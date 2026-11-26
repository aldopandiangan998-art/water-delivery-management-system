@extends('layouts.customer')

@section('title', __('Alamat Pengantaran Saya'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6" 
     x-data="{ showModal: false, viewMode: localStorage.getItem('view_mode_customer_addresses') || 'grid' }"
     x-effect="localStorage.setItem('view_mode_customer_addresses', viewMode)">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Alamat Pengantaran') }}</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Kelola daftar lokasi rumah, kantor, atau toko tempat air minum diantar.') }}</p>
        </div>
        <div class="flex items-center space-x-2.5">
            <x-grid-list-toggle model="viewMode" />
            <x-button type="button" variant="primary" @click="showModal = true" icon="fa-solid fa-plus">
                {{ __('Tambah Alamat Baru') }}
            </x-button>
        </div>
    </div>

    <!-- List View -->
    <div x-show="viewMode === 'list'" x-cloak class="space-y-3">
        @forelse ($addresses as $address)
            <x-card class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 {{ $address->is_default ? 'ring-2 ring-cyan-500 border-cyan-500/40' : '' }}">
                <div class="flex-1 space-y-1.5">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-black uppercase tracking-wider px-2.5 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                            {{ $address->label }}
                        </span>
                        @if ($address->is_default)
                            <x-badge variant="info" size="sm">
                                <i class="fa-solid fa-circle-check mr-1"></i> {{ __('Utama') }}
                            </x-badge>
                        @endif
                    </div>

                    <div>
                        <span class="font-extrabold text-sm text-slate-900 dark:text-white">{{ $address->recipient_name }}</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400 ml-2">({{ $address->recipient_phone }})</span>
                    </div>

                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ $address->address_line }}
                    </p>

                    <div class="flex flex-wrap items-center gap-3 text-xs">
                        @if ($address->benchmark)
                            <span class="text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-950/60 px-2 py-0.5 rounded border border-slate-100 dark:border-slate-800">
                                <span class="font-bold text-slate-700 dark:text-slate-300">{{ __('Patokan') }}:</span> {{ $address->benchmark }}
                            </span>
                        @endif

                        @if ($address->serviceArea)
                            <span class="font-semibold text-cyan-600 dark:text-cyan-400">
                                <i class="fa-solid fa-location-dot mr-1"></i>{{ $address->serviceArea->name }} (Rp {{ number_format($address->serviceArea->delivery_fee, 0, ',', '.') }})
                            </span>
                        @endif
                    </div>
                </div>

                <div class="pt-3 sm:pt-0 sm:border-t-0 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between sm:justify-end gap-4 text-xs">
                    @if (!$address->is_default)
                        <form method="POST" action="{{ route('customer.addresses.set-default', $address) }}">
                            @csrf
                            <button type="submit" class="font-bold text-cyan-600 dark:text-cyan-400 hover:underline cursor-pointer">
                                {{ __('Jadikan Utama') }}
                            </button>
                        </form>
                    @else
                        <span class="text-slate-400 dark:text-slate-500 text-[11px] font-medium">{{ __('Alamat Pilihan') }}</span>
                    @endif

                    <form id="delete-customer-address-{{ $address->id }}" method="POST" action="{{ route('customer.addresses.destroy', $address) }}">
                        @csrf
                        @method('DELETE')
                        <button type="button" 
                                @click="$dispatch('open-confirm-modal', {
                                    form: 'delete-customer-address-{{ $address->id }}',
                                    title: '{{ __('Hapus Alamat Pengantaran') }}',
                                    message: '{{ __('Apakah Anda yakin ingin menghapus alamat ini?') }}'
                                })"
                                class="text-rose-600 dark:text-rose-400 hover:underline font-bold cursor-pointer inline-flex items-center">
                            <i class="fa-solid fa-trash mr-1"></i> {{ __('Hapus') }}
                        </button>
                    </form>
                </div>
            </x-card>
        @empty
            <x-card class="p-12 text-center">
                <x-empty-state 
                    icon="fa-solid fa-map-location-dot" 
                    :title="__('Belum Ada Alamat Pengantaran')" 
                    :description="__('Tambahkan alamat rumah, toko, atau kantor Anda agar pemesanan air galon dapat segera diantar.')"
                />
                <div class="mt-4">
                    <x-button type="button" variant="primary" @click="showModal = true" icon="fa-solid fa-plus">
                        {{ __('Tambah Alamat Sekarang') }}
                    </x-button>
                </div>
            </x-card>
        @endforelse
    </div>

    <!-- Grid View -->
    <div x-show="viewMode === 'grid'" x-cloak class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($addresses as $address)
            <x-card class="p-5 flex flex-col justify-between h-full {{ $address->is_default ? 'ring-2 ring-cyan-500 border-cyan-500/40' : '' }}">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black uppercase tracking-wider px-2.5 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                            {{ $address->label }}
                        </span>
                        @if ($address->is_default)
                            <x-badge variant="info" size="sm">
                                <i class="fa-solid fa-circle-check mr-1"></i> {{ __('Utama') }}
                            </x-badge>
                        @endif
                    </div>

                    <div>
                        <div class="font-extrabold text-sm text-slate-900 dark:text-white">{{ $address->recipient_name }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $address->recipient_phone }}</div>
                    </div>

                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ $address->address_line }}
                    </p>

                    @if ($address->benchmark)
                        <div class="text-xs text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-950/60 p-2.5 rounded-xl border border-slate-100 dark:border-slate-800">
                            <span class="font-bold text-slate-700 dark:text-slate-300">{{ __('Patokan') }}:</span> {{ $address->benchmark }}
                        </div>
                    @endif

                    @if ($address->serviceArea)
                        <div class="text-xs font-semibold text-cyan-600 dark:text-cyan-400">
                            {{ __('Area') }}: {{ $address->serviceArea->name }} ({{ __('Ongkir') }}: Rp {{ number_format($address->serviceArea->delivery_fee, 0, ',', '.') }})
                        </div>
                    @endif
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 mt-4 flex items-center justify-between text-xs">
                    @if (!$address->is_default)
                        <form method="POST" action="{{ route('customer.addresses.set-default', $address) }}">
                            @csrf
                            <button type="submit" class="font-bold text-cyan-600 dark:text-cyan-400 hover:underline cursor-pointer">
                                {{ __('Jadikan Utama') }}
                            </button>
                        </form>
                    @else
                        <span class="text-slate-400 dark:text-slate-500 text-[11px] font-medium">{{ __('Alamat Pilihan') }}</span>
                    @endif

                    <form id="delete-customer-address-{{ $address->id }}" method="POST" action="{{ route('customer.addresses.destroy', $address) }}">
                        @csrf
                        @method('DELETE')
                        <button type="button" 
                                @click="$dispatch('open-confirm-modal', {
                                    form: 'delete-customer-address-{{ $address->id }}',
                                    title: '{{ __('Hapus Alamat Pengantaran') }}',
                                    message: '{{ __('Apakah Anda yakin ingin menghapus alamat ini?') }}'
                                })"
                                class="text-rose-600 dark:text-rose-400 hover:underline font-bold cursor-pointer inline-flex items-center">
                            <i class="fa-solid fa-trash mr-1"></i> {{ __('Hapus') }}
                        </button>
                    </form>
                </div>
            </x-card>
        @empty
            <div class="col-span-full">
                <x-card class="p-12 text-center">
                    <x-empty-state 
                        icon="fa-solid fa-map-location-dot" 
                        :title="__('Belum Ada Alamat Pengantaran')" 
                        :description="__('Tambahkan alamat rumah, toko, atau kantor Anda agar pemesanan air galon dapat segera diantar.')"
                    />
                    <div class="mt-4">
                        <x-button type="button" variant="primary" @click="showModal = true" icon="fa-solid fa-plus">
                            {{ __('Tambah Alamat Sekarang') }}
                        </x-button>
                    </div>
                </x-card>
            </div>
        @endforelse
    </div>

    <!-- Modal Tambah Alamat -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-xl w-full p-6 shadow-2xl space-y-4 border border-slate-100 dark:border-slate-800" 
             @click.away="showModal = false"
             x-data="{
                gettingGps: false,
                gpsCaptured: {{ old('latitude') ? 'true' : 'false' }},
                lat: '{{ old('latitude', '') }}',
                lng: '{{ old('longitude', '') }}',
                getGps() {
                    if (!navigator.geolocation) {
                        alert('{{ __('Browser Anda tidak mendukung sensor GPS.') }}');
                        return;
                    }
                    this.gettingGps = true;
                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            this.lat = pos.coords.latitude.toFixed(6);
                            this.lng = pos.coords.longitude.toFixed(6);
                            this.gpsCaptured = true;
                            this.gettingGps = false;
                        },
                        (err) => {
                            this.gettingGps = false;
                            alert('{{ __('Gagal membaca titik GPS: ') }}' + err.message + '. {{ __('Pastikan izin lokasi diaktifkan pada browser.') }}');
                        },
                        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                    );
                }
             }">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="font-black text-lg text-slate-900 dark:text-white tracking-tight">{{ __('Tambah Alamat Pengantaran') }}</h3>
                <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('customer.addresses.store') }}" class="space-y-4">
                @csrf

                <input type="hidden" name="latitude" x-model="lat">
                <input type="hidden" name="longitude" x-model="lng">

                <x-input 
                    :label="__('Label Alamat')" 
                    name="label" 
                    placeholder="{{ __('Contoh: Rumah, Toko Kelontong, Kantor') }}" 
                    :value="old('label')" 
                    required 
                />

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-input 
                        :label="__('Nama Penerima')" 
                        name="recipient_name" 
                        placeholder="{{ __('Nama lengkap penerima') }}" 
                        :value="old('recipient_name', auth()->user()->name)" 
                        required 
                    />

                    <x-input 
                        :label="__('Nomor WhatsApp / HP Penerima')" 
                        name="recipient_phone" 
                        placeholder="08123456789" 
                        :value="old('recipient_phone', auth()->user()->phone)" 
                        required 
                    />
                </div>

                <x-textarea 
                    :label="__('Alamat Jalan Lengkap')" 
                    name="address_line" 
                    rows="2" 
                    placeholder="{{ __('Nama jalan, nomor rumah, RT/RW (contoh: Jl. Bratang Gede No. 12)...') }}" 
                    :value="old('address_line')" 
                    required 
                />

                <x-input 
                    :label="__('Patokan Lokasi (Opsional)')" 
                    name="benchmark" 
                    placeholder="{{ __('Contoh: Pagar hitam depan masjid, samping warung makan...') }}" 
                    :value="old('benchmark')" 
                />

                <!-- GPS Pinpoint Helper -->
                <div class="p-3.5 bg-cyan-50/70 dark:bg-cyan-950/30 rounded-2xl border border-cyan-200 dark:border-cyan-800 flex items-center justify-between gap-3">
                    <div class="text-xs">
                        <span class="font-bold text-slate-800 dark:text-slate-200 block">
                            <i class="fa-solid fa-location-crosshairs text-cyan-600 mr-1"></i> {{ __('Kunci Lokasi Akurat Rumah (GPS)') }}
                        </span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400" x-text="gpsCaptured ? 'Titik koordinat rumah Anda telah terkunci (' + lat + ', ' + lng + ')' : '{{ __('Klik tombol untuk mengunci titik GPS rumah Anda agar pengantar tidak tersesat') }}'"></span>
                    </div>
                    <button type="button" @click="getGps()" :disabled="gettingGps"
                            class="px-3.5 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-bold text-xs shrink-0 flex items-center shadow-xs cursor-pointer transition">
                        <i class="fa-solid fa-location-arrow mr-1.5" :class="gettingGps ? 'animate-spin' : ''"></i>
                        <span x-text="gettingGps ? '{{ __('Membaca...') }}' : (gpsCaptured ? '{{ __('Perbarui GPS') }}' : '{{ __('Gunakan GPS Saya') }}')"></span>
                    </button>
                </div>

                <x-checkbox 
                    id="is_default" 
                    name="is_default" 
                    value="1" 
                    :checked="old('is_default', $addresses->isEmpty())"
                    :label="__('Jadikan sebagai alamat pengantaran utama')" 
                />

                <div class="pt-3 flex justify-end space-x-2 border-t border-slate-100 dark:border-slate-800">
                    <x-button type="button" variant="ghost" size="sm" @click="showModal = false">
                        {{ __('Batal') }}
                    </x-button>
                    <x-button type="submit" variant="primary" size="sm" icon="fa-solid fa-save">
                        {{ __('Simpan Alamat') }}
                    </x-button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
