@extends('layouts.customer')

@section('title', __('Pesan Air Minum'))

@section('breadcrumbs')
    <x-breadcrumbs :items="[
        ['title' => __('Riwayat Pesanan'), 'url' => route('customer.orders.index')],
        ['title' => __('Pesan Air Sekarang')]
    ]" />
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6" 
     x-data="orderApp({
         products: {{ Js::from($products) }},
         addresses: {{ Js::from($addresses) }},
         defaultAddressId: {{ $initialAddressId ?? ($addresses->where('is_default', true)->first()?->id ?? ($addresses->first()?->id ?? 0)) }},
         initialCart: {{ Js::from($initialCart ?? []) }}
     })">

    @if (!empty($initialCart))
        <div class="mb-6 p-4 rounded-2xl bg-cyan-50 dark:bg-cyan-950/40 border border-cyan-200 dark:border-cyan-800 text-cyan-900 dark:text-cyan-200 flex items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center space-x-3 text-sm">
                <div class="w-9 h-9 rounded-xl bg-cyan-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                    <i class="fa-solid fa-rotate-right"></i>
                </div>
                <div>
                    <span class="font-bold">{{ __('Mode Pesan Ulang Aktif') }}</span>
                    <p class="text-xs text-cyan-700 dark:text-cyan-300 mt-0.5">
                        {{ __('Item produk dan alamat tujuan telah otomatis terisi dari pesanan Anda sebelumnya. Anda dapat langsung mengklik tombol "Pesan Sekarang" atau menyesuaikan kuantitas di bawah.') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div class="mb-6">
        <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Formulir Pemesanan Air Minum') }}</h1>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Pilih alamat pengantaran, produk galon yang dibutuhkan, dan metode pembayaran.') }}</p>
    </div>

    <!-- Empty Address Notice -->
    <template x-if="addresses.length === 0">
        <div class="bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 p-5 rounded-3xl mb-6 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xs">
            <div class="flex items-center space-x-3 text-amber-800 dark:text-amber-200 text-sm">
                <i class="fa-solid fa-location-dot text-xl text-amber-600 shrink-0"></i>
                <div>
                    <span class="font-bold">{{ __('Belum ada alamat pengantaran tersimpan!') }}</span>
                    <p class="text-xs text-amber-700 dark:text-amber-300 mt-0.5">{{ __('Silakan tambahkan alamat rumah / toko Anda terlebih dahulu agar pesanan dapat diantar.') }}</p>
                </div>
            </div>
            <button type="button" 
                    @click="showAddAddressModal = true" 
                    class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shrink-0 flex items-center gap-1.5 transition shadow-xs cursor-pointer">
                <i class="fa-solid fa-plus"></i> {{ __('Tambah Alamat Sekarang') }}
            </button>
        </div>
    </template>

    <form method="POST" action="{{ route('customer.orders.store') }}" @submit="handleSubmit">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
            <!-- Left col: Address & Products Selection -->
            <div class="lg:col-span-8 space-y-6">

                <!-- 1. Select Delivery Address (Visual Cards) -->
                <x-card class="p-5 sm:p-6 space-y-4">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <h2 class="font-black text-base text-slate-900 dark:text-white flex items-center tracking-tight">
                            <span class="w-6 h-6 rounded-lg bg-cyan-600 text-white text-xs font-bold flex items-center justify-center mr-2 shadow-xs">1</span>
                            {{ __('Alamat Pengantaran') }}
                        </h2>
                        <div class="flex items-center space-x-2">
                            <button type="button" 
                                    @click="showAddAddressModal = true" 
                                    class="inline-flex items-center text-xs font-bold text-cyan-600 dark:text-cyan-400 hover:text-cyan-700 dark:hover:text-cyan-300 bg-cyan-50 dark:bg-cyan-950/60 hover:bg-cyan-100 dark:hover:bg-cyan-900/60 px-3 py-1.5 rounded-xl border border-cyan-200 dark:border-cyan-800 transition cursor-pointer">
                                <i class="fa-solid fa-plus mr-1"></i> {{ __('Tambah Alamat') }}
                            </button>
                            <a href="{{ route('customer.addresses.index') }}" class="text-xs text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 underline">
                                {{ __('Kelola') }}
                            </a>
                        </div>
                    </div>

                    <input type="hidden" name="customer_address_id" :value="selectedAddressId">

                    <!-- Visual Cards Grid (1 col mobile, 2 col tablet/desktop, smooth scroll > 4) -->
                    <template x-if="addresses.length > 0">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-[380px] overflow-y-auto pr-1">
                            <template x-for="addr in addresses" :key="addr.id">
                                <div @click="selectedAddressId = Number(addr.id)"
                                     :class="Number(selectedAddressId) === Number(addr.id) ? 'border-cyan-600 bg-cyan-50/50 dark:bg-cyan-950/30 ring-2 ring-cyan-500 shadow-xs' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 bg-white dark:bg-slate-900 shadow-2xs'"
                                     class="p-4 rounded-2xl border cursor-pointer transition block relative select-none">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="font-extrabold text-[10px] uppercase px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300" x-text="addr.label"></span>
                                        <template x-if="Number(selectedAddressId) === Number(addr.id)">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-cyan-600 text-white flex items-center gap-1 shadow-2xs">
                                                <i class="fa-solid fa-check"></i> {{ __('Terpilih') }}
                                            </span>
                                        </template>
                                        <template x-if="Number(selectedAddressId) !== Number(addr.id)">
                                            <span class="text-[11px] font-medium text-slate-400 hover:text-cyan-600">
                                                {{ __('Pilih') }}
                                            </span>
                                        </template>
                                    </div>
                                    <div class="font-bold text-sm text-slate-900 dark:text-white" x-text="addr.recipient_name + ' (' + addr.recipient_phone + ')'"></div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2" x-text="addr.address_line"></div>
                                    <div class="mt-2 text-[11px] text-slate-400" x-show="addr.benchmark" x-text="'Patokan: ' + addr.benchmark"></div>
                                    <div class="mt-2 flex items-center text-[11px] text-emerald-600 font-semibold" x-show="addr.service_area">
                                        <i class="fa-solid fa-truck mr-1"></i>
                                        <span>Area: <span x-text="addr.service_area ? addr.service_area.name : ''"></span> &bull; Ongkir: <span x-text="formatRupiah(addr.service_area ? addr.service_area.delivery_fee : 0)"></span></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="addresses.length === 0">
                        <div class="p-8 text-center border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-2xl">
                            <i class="fa-solid fa-location-dot text-2xl text-slate-300 dark:text-slate-700 mb-2"></i>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Belum ada alamat pengantaran tersimpan.') }}</p>
                            <button type="button" 
                                    @click="showAddAddressModal = true" 
                                    class="mt-3 inline-flex items-center text-xs font-bold text-cyan-600 dark:text-cyan-400 hover:underline cursor-pointer">
                                <i class="fa-solid fa-plus mr-1"></i> {{ __('Tambah Alamat Pengantaran Sekarang') }}
                            </button>
                        </div>
                    </template>
                </x-card>

                <!-- 2. Select Products & Quantity -->
                <x-card class="p-5 sm:p-6 space-y-4">
                    <h2 class="font-black text-base text-slate-900 dark:text-white flex items-center tracking-tight">
                        <span class="w-6 h-6 rounded-lg bg-cyan-600 text-white text-xs font-bold flex items-center justify-center mr-2 shadow-xs">2</span>
                        {{ __('Pilih Air Galon & Kuantitas') }}
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach ($products as $prod)
                            <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 flex flex-col justify-between space-y-3">
                                <div>
                                    <div class="flex justify-between items-start gap-2">
                                        <h3 class="font-bold text-sm text-slate-900 dark:text-white">{{ $prod->name }}</h3>
                                        <span class="text-xs font-black text-cyan-600 dark:text-cyan-400 whitespace-nowrap">
                                            Rp {{ number_format($prod->price, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ Str::limit($prod->description, 70) }}</p>

                                    @if ($prod->is_returnable_gallon)
                                        <div class="mt-2 text-[11px] text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 p-2 rounded-xl border border-amber-200 dark:border-amber-800">
                                            <i class="fa-solid fa-recycle mr-1"></i> {{ __('Galon Isi Ulang (Tukar Galon Kosong)') }}
                                        </div>
                                    @endif
                                </div>

                                <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                                    <div class="text-xs text-slate-500">
                                        <template x-if="cart[{{ $prod->id }}]">
                                            <span class="font-bold text-slate-700 dark:text-slate-300" x-text="formatRupiah(cart[{{ $prod->id }}].quantity * ({{ $prod->price }} + (!cart[{{ $prod->id }}].is_exchange ? {{ $prod->deposit_price ?? 0 }} : 0)))"></span>
                                        </template>
                                        <template x-if="!cart[{{ $prod->id }}]">
                                            <span>{{ __('Belum dipilih') }}</span>
                                        </template>
                                    </div>

                                    <!-- Counter Buttons -->
                                    <div class="flex items-center space-x-2">
                                        <button type="button" @click="decrement({{ $prod->id }})" 
                                                class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-700 font-bold transition">
                                            -
                                        </button>
                                        <span class="text-xs font-extrabold w-6 text-center text-slate-900 dark:text-white" x-text="cart[{{ $prod->id }}]?.quantity || 0"></span>
                                        <button type="button" @click="increment({{ $prod->id }})" 
                                                class="w-7 h-7 rounded-lg bg-cyan-600 text-white flex items-center justify-center hover:bg-cyan-700 font-bold shadow-xs transition">
                                            +
                                        </button>
                                    </div>
                                </div>

                                <!-- Exchange checkbox if in cart and is returnable -->
                                @if ($prod->is_returnable_gallon)
                                    <div x-show="cart[{{ $prod->id }}]" x-cloak class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                                        <label class="flex items-center space-x-2 cursor-pointer">
                                            <input type="checkbox" x-model="cart[{{ $prod->id }}].is_exchange" class="rounded text-cyan-600 focus:ring-cyan-500">
                                            <span class="text-slate-700 dark:text-slate-300 font-medium">{{ __('Tukar Galon Kosong') }}</span>
                                        </label>
                                        <span x-show="!cart[{{ $prod->id }}]?.is_exchange" class="text-[10px] text-amber-600 font-bold">
                                            + Rp {{ number_format($prod->deposit_price ?? 0, 0, ',', '.') }} (Beli Galon)
                                        </span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </x-card>

                <!-- 3. Customer Notes -->
                <x-card class="p-5 sm:p-6 space-y-3">
                    <h2 class="font-black text-base text-slate-900 dark:text-white flex items-center tracking-tight">
                        <span class="w-6 h-6 rounded-lg bg-cyan-600 text-white text-xs font-bold flex items-center justify-center mr-2 shadow-xs">3</span>
                        {{ __('Catatan Pesanan (Opsional)') }}
                    </h2>
                    <textarea name="customer_notes" rows="2" 
                              placeholder="{{ __('Contoh: Titipkan di satpam, pagar warna hitam, mohon bantu angkat ke lantai 2...') }}"
                              class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white p-3 focus:ring-2 focus:ring-cyan-500"></textarea>
                </x-card>

            </div>

            <!-- Right col: Order Summary & Checkout -->
            <div class="lg:col-span-4 space-y-6 lg:sticky lg:top-6">
                <x-card class="p-5 sm:p-6 space-y-5">
                    <h2 class="font-black text-base text-slate-900 dark:text-white tracking-tight border-b border-slate-100 dark:border-slate-800 pb-3">
                        {{ __('Ringkasan Pesanan') }}
                    </h2>

                    <!-- Items List in Summary -->
                    <div class="space-y-3">
                        <template x-for="item in cartItems" :key="item.product_id">
                            <div class="flex justify-between items-center text-xs">
                                <div>
                                    <div class="font-bold text-slate-800 dark:text-slate-200" x-text="item.name"></div>
                                    <div class="text-[11px] text-slate-400">
                                        <span x-text="item.quantity + 'x'"></span> &bull; 
                                        <span x-text="formatRupiah(item.unit_price)"></span>
                                        <span x-show="item.is_returnable && item.is_exchange" class="text-cyan-600 font-semibold">(Tukar Galon)</span>
                                    </div>
                                </div>
                                <span class="font-extrabold text-slate-800 dark:text-slate-200" x-text="formatRupiah(item.subtotal)"></span>
                            </div>
                        </template>

                        <div x-show="cartItems.length === 0" class="text-xs text-slate-400 py-4 text-center">
                            {{ __('Belum ada produk yang dipilih.') }}
                        </div>
                    </div>

                    <!-- Cost Calculations -->
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 space-y-2 text-xs">
                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>{{ __('Subtotal Produk') }}:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200" x-text="formatRupiah(calculateSubtotal())"></span>
                        </div>
                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>{{ __('Ongkos Kirim') }}:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200" x-text="formatRupiah(deliveryFee)"></span>
                        </div>
                        <div class="pt-2 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center text-base">
                            <span class="font-black text-slate-900 dark:text-white">{{ __('Total Bayar') }}:</span>
                            <span class="font-black text-cyan-600 dark:text-cyan-400" x-text="formatRupiah(calculateTotal())"></span>
                        </div>
                    </div>

                    <!-- Payment Method: COD Only -->
                    <div class="p-3.5 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-700 space-y-2">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">{{ __('Metode Pembayaran') }}</label>
                        <input type="hidden" name="payment_method" value="cod">
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-emerald-500 shadow-2xs">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">
                                    <i class="fa-solid fa-hand-holding-dollar"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-slate-900 dark:text-white">{{ __('Tunai / COD (Bayar di Tempat)') }}</div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 font-normal">{{ __('Bayar langsung tunai kepada petugas saat pesanan tiba') }}</div>
                                </div>
                            </div>
                            <span class="text-xs font-black text-emerald-600 dark:text-emerald-400">COD</span>
                        </div>
                    </div>

                    <!-- Hidden Inputs for Form Submission -->
                    <template x-for="(item, idx) in cartItems" :key="item.product_id">
                        <div>
                            <input type="hidden" :name="'items[' + idx + '][product_id]'" :value="item.product_id">
                            <input type="hidden" :name="'items[' + idx + '][quantity]'" :value="item.quantity">
                            <input type="hidden" :name="'items[' + idx + '][is_exchange]'" :value="item.is_exchange ? 1 : 0">
                        </div>
                    </template>

                    <!-- Submit Button -->
                    <x-button 
                        type="submit" 
                        variant="primary" 
                        class="w-full py-3.5"
                        ::disabled="cartItems.length === 0 || !selectedAddressId"
                        icon="fa-solid fa-paper-plane"
                    >
                        {{ __('Konfirmasi & Pesan Sekarang') }}
                    </x-button>
                </x-card>
            </div>
        </div>
    </form>

    <!-- Modal Tambah Alamat Baru Langsung (AJAX / No-Reload) -->
    <div x-show="showAddAddressModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs transition-opacity"
         @keydown.escape.window="showAddAddressModal = false">
        <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4"
             @click.outside="showAddAddressModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-xl bg-cyan-100 dark:bg-cyan-950 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <h3 class="font-extrabold text-base text-slate-900 dark:text-white">{{ __('Tambah Alamat Pengantaran Baru') }}</h3>
                </div>
                <button type="button" @click="showAddAddressModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Error banner if validation fails -->
            <div x-show="newAddressError" x-cloak class="p-3 bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 rounded-xl text-rose-700 dark:text-rose-300 text-xs">
                <span x-text="newAddressError"></span>
            </div>

            <form @submit.prevent="saveNewAddress" class="space-y-3.5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        {{ __('Label Alamat') }} <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" x-model="newAddressForm.label" required placeholder="{{ __('Contoh: Rumah, Kantor, Toko') }}"
                           class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white p-2.5 focus:ring-2 focus:ring-cyan-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            {{ __('Nama Penerima') }} <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" x-model="newAddressForm.recipient_name" required placeholder="{{ __('Nama penerima air') }}"
                               class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white p-2.5 focus:ring-2 focus:ring-cyan-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            {{ __('No. WhatsApp / HP') }} <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" x-model="newAddressForm.recipient_phone" required placeholder="08xxxxxxxxxx"
                               class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white p-2.5 focus:ring-2 focus:ring-cyan-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        {{ __('Alamat Lengkap') }} <span class="text-rose-500">*</span>
                    </label>
                    <textarea x-model="newAddressForm.address_line" required rows="2" placeholder="{{ __('Nama jalan, nomor rumah, RT/RW, dsb') }}"
                              class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white p-2.5 focus:ring-2 focus:ring-cyan-500"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        {{ __('Patokan Lokasi') }} <span class="text-slate-400 text-[10px]">({{ __('Opsional') }})</span>
                    </label>
                    <input type="text" x-model="newAddressForm.benchmark" placeholder="{{ __('Contoh: Depan pos ronda, pagar hijau') }}"
                           class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white p-2.5 focus:ring-2 focus:ring-cyan-500">
                </div>

                <!-- GPS Pinpoint Helper -->
                <div class="p-3.5 bg-cyan-50/70 dark:bg-cyan-950/30 rounded-2xl border border-cyan-200 dark:border-cyan-800 flex items-center justify-between gap-3">
                    <div class="text-xs">
                        <span class="font-bold text-slate-800 dark:text-slate-200 block">
                            <i class="fa-solid fa-location-crosshairs text-cyan-600 mr-1"></i> {{ __('Kunci Lokasi Akurat Rumah (GPS)') }}
                        </span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400" x-text="newAddressForm.latitude ? 'Titik koordinat terkunci (' + Number(newAddressForm.latitude).toFixed(5) + ', ' + Number(newAddressForm.longitude).toFixed(5) + ')' : '{{ __('Kunci titik GPS rumah Anda agar pengantar tidak tersesat') }}'"></span>
                    </div>
                    <button type="button" @click="getModalGps()" :disabled="gettingModalGps"
                            class="px-3.5 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-bold text-xs shrink-0 flex items-center shadow-xs cursor-pointer transition">
                        <i class="fa-solid fa-location-arrow mr-1.5" :class="gettingModalGps ? 'animate-spin' : ''"></i>
                        <span x-text="gettingModalGps ? '{{ __('Membaca...') }}' : (newAddressForm.latitude ? '{{ __('Perbarui GPS') }}' : '{{ __('Gunakan GPS Saya') }}')"></span>
                    </button>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <x-button type="button" @click="showAddAddressModal = false" variant="secondary" size="sm" ::disabled="savingAddress">
                        {{ __('Batal') }}
                    </x-button>
                    <x-button type="submit" variant="primary" size="sm" icon="fa-solid fa-check" ::disabled="savingAddress">
                        <span x-text="savingAddress ? '{{ __('Menyimpan...') }}' : '{{ __('Simpan & Pakai Alamat') }}'"></span>
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function orderApp(config) {
    return {
        products: config.products,
        addresses: config.addresses,
        selectedAddressId: Number(config.defaultAddressId) || (config.addresses.length > 0 ? Number(config.addresses[0].id) : 0),
        paymentMethod: 'cod',
        cart: (config.initialCart && Object.keys(config.initialCart).length > 0) ? { ...config.initialCart } : {},

        showAddAddressModal: false,
        savingAddress: false,
        gettingModalGps: false,
        newAddressError: '',
        newAddressForm: {
            label: 'Rumah',
            recipient_name: '{{ addslashes(auth()->user()->name) }}',
            recipient_phone: '{{ addslashes(auth()->user()->phone ?? '') }}',
            address_line: '',
            benchmark: '',
            latitude: null,
            longitude: null,
            is_default: true,
        },

        getModalGps() {
            if (!navigator.geolocation) {
                alert('{{ __('Browser Anda tidak mendukung sensor GPS.') }}');
                return;
            }
            this.gettingModalGps = true;
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    this.newAddressForm.latitude = pos.coords.latitude.toFixed(6);
                    this.newAddressForm.longitude = pos.coords.longitude.toFixed(6);
                    this.gettingModalGps = false;
                },
                (err) => {
                    this.gettingModalGps = false;
                    alert('{{ __('Gagal membaca titik GPS: ') }}' + err.message + '. {{ __('Pastikan izin lokasi diaktifkan pada browser.') }}');
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        },

        async saveNewAddress() {
            this.newAddressError = '';
            if (!this.newAddressForm.label || !this.newAddressForm.recipient_name || !this.newAddressForm.recipient_phone || !this.newAddressForm.address_line) {
                this.newAddressError = '{{ __("Mohon lengkapi seluruh kolom yang berbintang (*).") }}';
                return;
            }

            this.savingAddress = true;
            try {
                const response = await fetch("{{ route('customer.addresses.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(this.newAddressForm)
                });

                const data = await response.json();
                if (!response.ok) {
                    this.newAddressError = data.message || '{{ __("Terjadi kesalahan saat menyimpan alamat.") }}';
                    return;
                }

                if (data.success && data.address) {
                    this.addresses.push(data.address);
                    this.selectedAddressId = Number(data.address.id);
                    this.showAddAddressModal = false;
                    this.newAddressForm = {
                        label: 'Rumah',
                        recipient_name: '{{ addslashes(auth()->user()->name) }}',
                        recipient_phone: '{{ addslashes(auth()->user()->phone ?? '') }}',
                        address_line: '',
                        benchmark: '',
                        is_default: false,
                    };
                }
            } catch (err) {
                this.newAddressError = '{{ __("Gagal menghubungi server. Silakan periksa koneksi Anda.") }}';
            } finally {
                this.savingAddress = false;
            }
        },

        init() {
            if (config.initialCart && Object.keys(config.initialCart).length > 0) {
                this.cart = { ...config.initialCart };
            } else if (this.products.length > 0) {
                const first = this.products[0];
                this.cart[first.id] = {
                    product_id: first.id,
                    quantity: 2,
                    is_exchange: first.is_returnable_gallon ? true : false
                };
            }
        },

        get deliveryFee() {
            if (!this.selectedAddressId) return 0;
            const addr = this.addresses.find(a => Number(a.id) === Number(this.selectedAddressId));
            return (addr && addr.service_area) ? Number(addr.service_area.delivery_fee) : 0;
        },

        increment(productId) {
            if (!this.cart[productId]) {
                const prod = this.products.find(p => p.id == productId);
                this.cart[productId] = {
                    product_id: productId,
                    quantity: 1,
                    is_exchange: prod && prod.is_returnable_gallon ? true : false
                };
            } else {
                this.cart[productId].quantity++;
            }
        },

        decrement(productId) {
            if (this.cart[productId]) {
                this.cart[productId].quantity--;
                if (this.cart[productId].quantity <= 0) {
                    delete this.cart[productId];
                }
            }
        },

        get cartItems() {
            return Object.values(this.cart).map(item => {
                const prod = this.products.find(p => p.id == item.product_id);
                let unitPrice = Number(prod ? prod.price : 0);
                if (prod && prod.is_returnable_gallon && !item.is_exchange) {
                    unitPrice += Number(prod.deposit_price || 0);
                }
                return {
                    product_id: item.product_id,
                    name: prod ? prod.name : '',
                    quantity: item.quantity,
                    is_exchange: item.is_exchange,
                    is_returnable: prod ? prod.is_returnable_gallon : false,
                    unit_price: unitPrice,
                    subtotal: unitPrice * item.quantity
                };
            });
        },

        calculateSubtotal() {
            return this.cartItems.reduce((acc, item) => acc + item.subtotal, 0);
        },

        calculateTotal() {
            return this.calculateSubtotal() + this.deliveryFee;
        },

        formatRupiah(amount) {
            return 'Rp ' + Number(amount || 0).toLocaleString('id-ID');
        },

        handleSubmit(e) {
            if (this.cartItems.length === 0) {
                alert('{{ __("Pilih setidaknya 1 produk!") }}');
                e.preventDefault();
                return;
            }
            if (!this.selectedAddressId) {
                alert('{{ __("Pilih alamat pengantaran!") }}');
                e.preventDefault();
                return;
            }
        }
    };
}
</script>
@endpush
@endsection
