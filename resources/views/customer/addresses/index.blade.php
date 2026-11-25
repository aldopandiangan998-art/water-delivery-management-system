@extends('layouts.customer')

@section('title', 'Alamat Pengantaran Saya')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6" x-data="{ showModal: false }">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Alamat Pengantaran</h1>
            <p class="text-sm text-slate-500">Kelola daftar lokasi rumah, kantor, atau toko tempat air minum diantar.</p>
        </div>
        <button type="button" @click="showModal = true" 
                class="inline-flex items-center px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-cyan-600 hover:bg-cyan-700 shadow-xs transition">
            <i class="fa-solid fa-plus mr-1.5"></i> Tambah Alamat Baru
        </button>
    </div>

    <!-- Address Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($addresses as $address)
            <div class="bg-white rounded-2xl border {{ $address->is_default ? 'border-cyan-600 ring-2 ring-cyan-500/20' : 'border-slate-200' }} p-6 shadow-xs flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-700">
                            {{ $address->label }}
                        </span>
                        @if ($address->is_default)
                            <span class="text-[11px] font-bold text-cyan-700 bg-cyan-50 border border-cyan-200 px-2 py-0.5 rounded-full flex items-center">
                                <i class="fa-solid fa-check-circle mr-1"></i> Utama
                            </span>
                        @endif
                    </div>

                    <div>
                        <div class="font-extrabold text-sm text-slate-900">{{ $address->recipient_name }}</div>
                        <div class="text-xs text-slate-500">{{ $address->recipient_phone }}</div>
                    </div>

                    <p class="text-xs text-slate-600 leading-relaxed">
                        {{ $address->address_line }}
                    </p>

                    @if ($address->benchmark)
                        <div class="text-xs text-slate-500 bg-slate-50 p-2 rounded-lg border border-slate-100">
                            <span class="font-semibold text-slate-700">Patokan:</span> {{ $address->benchmark }}
                        </div>
                    @endif

                    @if ($address->serviceArea)
                        <div class="text-xs font-semibold text-cyan-700">
                            Area: {{ $address->serviceArea->name }} (Ongkir: Rp {{ number_format($address->serviceArea->delivery_fee, 0, ',', '.') }})
                        </div>
                    @endif
                </div>

                <div class="pt-4 border-t border-slate-100 mt-4 flex items-center justify-between text-xs">
                    @if (!$address->is_default)
                        <form method="POST" action="{{ route('customer.addresses.set-default', $address) }}">
                            @csrf
                            <button type="submit" class="font-bold text-cyan-600 hover:text-cyan-800">
                                Jadikan Utama
                            </button>
                        </form>
                    @else
                        <span class="text-slate-400">Alamat Pilihan</span>
                    @endif

                    <form method="POST" action="{{ route('customer.addresses.destroy', $address) }}">
                        @csrf
                        @method('DELETE')
                        <button type="button" 
                                @click="$dispatch('open-confirm-modal', {
                                    form: $el.closest('form'),
                                    title: 'Hapus Alamat Pengantaran',
                                    message: 'Apakah Anda yakin ingin menghapus alamat \'{{ addslashes($address->label) }}\'? Tindakan ini bersifat permanen.'
                                })"
                                class="text-rose-600 hover:text-rose-800 font-medium cursor-pointer">
                            <i class="fa-solid fa-trash mr-1"></i> Hapus
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-3 bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-400">
                <i class="fa-solid fa-location-dot text-4xl mb-3 text-slate-300"></i>
                <p class="text-sm font-semibold mb-3">Belum ada alamat pengantaran.</p>
                <button type="button" @click="showModal = true" class="px-4 py-2 bg-cyan-600 text-white rounded-xl text-xs font-bold">
                    + Tambah Alamat Sekarang
                </button>
            </div>
        @endforelse
    </div>

    <!-- Modal Tambah Alamat -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl space-y-4" @click.away="showModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-extrabold text-lg text-slate-900">Tambah Alamat Pengantaran</h3>
                <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('customer.addresses.store') }}" class="space-y-4">
                @csrf

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Label Alamat</label>
                        <input type="text" name="label" required placeholder="Rumah, Toko, Kantor" value="Rumah"
                               class="w-full text-xs border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-cyan-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Wilayah Layanan</label>
                        <select name="service_area_id" class="w-full text-xs border border-slate-300 rounded-xl p-2.5 bg-white">
                            @foreach ($serviceAreas as $area)
                                <option value="{{ $area->id }}">{{ $area->name }} (Rp {{ number_format($area->delivery_fee, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Penerima</label>
                        <input type="text" name="recipient_name" required value="{{ Auth::user()->name }}"
                               class="w-full text-xs border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-cyan-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WhatsApp / HP</label>
                        <input type="text" name="recipient_phone" required value="{{ Auth::user()->phone }}"
                               class="w-full text-xs border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-cyan-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Lengkap</label>
                    <textarea name="address_line" rows="2" required placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan..."
                              class="w-full text-xs border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-cyan-500"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Patokan Lokasi (Memudahkan Kurir)</label>
                    <input type="text" name="benchmark" placeholder="Contoh: Pagar hijau depan toko kelontong..."
                           class="w-full text-xs border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-cyan-500">
                </div>

                <!-- Hidden or Optional Coordinates (Default to Toko Nova Jaya Bratang Gede) -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Latitude</label>
                        <input type="number" step="any" name="latitude" value="-7.303488"
                               class="w-full text-xs border border-slate-200 rounded-xl p-2 bg-slate-50 text-slate-600">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Longitude</label>
                        <input type="number" step="any" name="longitude" value="112.753373"
                               class="w-full text-xs border border-slate-200 rounded-xl p-2 bg-slate-50 text-slate-600">
                    </div>
                </div>

                <div class="flex items-center text-xs">
                    <input type="checkbox" id="is_default" name="is_default" value="1" class="rounded text-cyan-600">
                    <label for="is_default" class="ml-2 text-slate-700">Jadikan sebagai alamat utama</label>
                </div>

                <div class="pt-3 flex justify-end space-x-2">
                    <button type="button" @click="showModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-cyan-600 hover:bg-cyan-700 text-white shadow-xs">
                        Simpan Alamat
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
