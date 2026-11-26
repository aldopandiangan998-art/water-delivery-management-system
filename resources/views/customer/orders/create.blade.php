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
         defaultAddressId: {{ $addresses->where('is_default', true)->first()?->id ?? ($addresses->first()?->id ?? 0) }}
     })">

    <div class="mb-6">
        <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Formulir Pemesanan Air Minum') }}</h1>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Pilih alamat pengantaran, produk galon yang dibutuhkan, dan metode pembayaran.') }}</p>
    </div>

    @if ($addresses->isEmpty())
        <div class="bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 p-5 rounded-3xl mb-6 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xs">
            <div class="flex items-center space-x-3 text-amber-800 dark:text-amber-200 text-sm">
                <i class="fa-solid fa-location-dot text-xl text-amber-600 shrink-0"></i>
                <div>
                    <span class="font-bold">{{ __('Belum ada alamat pengantaran tersimpan!') }}</span>
                    <p class="text-xs text-amber-700 dark:text-amber-300 mt-0.5">{{ __('Silakan tambahkan alamat rumah / toko Anda terlebih dahulu agar pesanan dapat diantar.') }}</p>
                </div>
            </div>
            <x-button :href="route('customer.addresses.index')" variant="secondary" size="sm" class="shrink-0" icon="fa-solid fa-plus">
                {{ __('Tambah Alamat Sekarang') }}
            </x-button>
        </div>
    @endif

    <form method="POST" action="{{ route('customer.orders.store') }}" @submit="handleSubmit">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
            <!-- Left col: Address & Products Selection -->
            <div class="lg:col-span-8 space-y-6">

                <!-- 1. Select Delivery Address -->
                <x-card class="p-5 sm:p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="font-black text-base text-slate-900 dark:text-white flex items-center tracking-tight">
                            <span class="w-6 h-6 rounded-lg bg-cyan-600 text-white text-xs font-bold flex items-center justify-center mr-2 shadow-xs">1</span>
                            {{ __('Alamat Pengantaran') }}
                        </h2>
                        <a href="{{ route('customer.addresses.index') }}" class="text-xs font-bold text-cyan-600 dark:text-cyan-400 hover:underline">
                            + {{ __('Kelola Alamat') }}
                        </a>
                    </div>

                    @if ($addresses->isNotEmpty())
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <template x-for="addr in addresses" :key="addr.id">
                                <label :class="selectedAddressId === addr.id ? 'border-cyan-600 bg-cyan-50/50 dark:bg-cyan-950/30 ring-2 ring-cyan-500' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600'"
                                       class="p-4 rounded-2xl border cursor-pointer transition block relative bg-white dark:bg-slate-900 shadow-2xs">
                                    <input type="radio" name="customer_address_id" :value="addr.id" x-model="selectedAddressId" class="sr-only">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="font-extrabold text-[10px] uppercase px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300" x-text="addr.label"></span>
                                        <span x-show="selectedAddressId === addr.id" class="text-cyan-600 dark:text-cyan-400 text-sm"><i class="fa-solid fa-circle-check"></i></span>
                                    </div>
                                    <div class="font-bold text-sm text-slate-900 dark:text-white" x-text="addr.recipient_name + ' (' + addr.recipient_phone + ')'"></div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2" x-text="addr.address_line"></div>
                                    <div class="mt-2 text-[11px] text-slate-400" x-show="addr.benchmark" x-text="'Patokan: ' + addr.benchmark"></div>
                                    <div class="mt-2 flex items-center text-[11px] text-emerald-600 font-semibold" x-show="addr.service_area">
                                        <i class="fa-solid fa-truck mr-1"></i>
                                        <span>Area: <span x-text="addr.service_area ? addr.service_area.name : ''"></span> &bull; Ongkir: <span x-text="formatRupiah(addr.service_area ? addr.service_area.delivery_fee : 0)"></span></span>
                                    </div>
                                </label>
                            </template>
                        </div>
                    @else
                        <p class="text-xs text-slate-400">{{ __('Silakan tambah alamat di atas terlebih dahulu.') }}</p>
                    @endif
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
</div>

@push('scripts')
<script>
function orderApp(config) {
    return {
        products: config.products,
        addresses: config.addresses,
        selectedAddressId: config.defaultAddressId,
        paymentMethod: 'cod',
        cart: {},

        init() {
            if (this.products.length > 0) {
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
            const addr = this.addresses.find(a => a.id == this.selectedAddressId);
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
