@extends('layouts.customer')

@section('title', 'Pesan Air Minum')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" 
     x-data="orderApp({
         products: {{ Js::from($products) }},
         addresses: {{ Js::from($addresses) }},
         defaultAddressId: {{ $addresses->where('is_default', true)->first()?->id ?? ($addresses->first()?->id ?? 0) }}
     })">

    <div class="mb-6">
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Formulir Pemesanan Air Minum</h1>
        <p class="text-sm text-slate-500">Pilih alamat pengantaran, produk galon yang dibutuhkan, dan metode pembayaran.</p>
    </div>

    @if ($addresses->isEmpty())
        <div class="bg-amber-50 border border-amber-200 p-5 rounded-2xl mb-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center space-x-3 text-amber-800 text-sm">
                <i class="fa-solid fa-location-dot text-xl text-amber-600"></i>
                <div>
                    <span class="font-bold">Belum ada alamat pengantaran tersimpan!</span>
                    <p class="text-xs text-amber-700">Silakan tambahkan alamat rumah / toko Anda terlebih dahulu agar kurir dapat mengantar.</p>
                </div>
            </div>
            <a href="{{ route('customer.addresses.index') }}" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                + Tambah Alamat Sekarang
            </a>
        </div>
    @endif

    <form method="POST" action="{{ route('customer.orders.store') }}" @submit="handleSubmit">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- Left col: Address & Products Selection -->
            <div class="lg:col-span-8 space-y-6">

                <!-- 1. Select Delivery Address -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-bold text-base text-slate-900 flex items-center">
                            <span class="w-6 h-6 rounded-full bg-cyan-600 text-white text-xs font-bold flex items-center justify-center mr-2">1</span>
                            Alamat Pengantaran
                        </h2>
                        <a href="{{ route('customer.addresses.index') }}" class="text-xs font-bold text-cyan-600 hover:text-cyan-700">
                            + Kelola / Tambah Alamat
                        </a>
                    </div>

                    @if ($addresses->isNotEmpty())
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <template x-for="addr in addresses" :key="addr.id">
                                <label :class="selectedAddressId === addr.id ? 'border-cyan-600 bg-cyan-50/50 ring-2 ring-cyan-500' : 'border-slate-200 hover:border-slate-300'"
                                       class="p-4 rounded-xl border cursor-pointer transition block relative">
                                    <input type="radio" name="customer_address_id" :value="addr.id" x-model="selectedAddressId" class="sr-only">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="font-bold text-xs uppercase px-2 py-0.5 rounded-md bg-slate-100 text-slate-700" x-text="addr.label"></span>
                                        <span x-show="selectedAddressId === addr.id" class="text-cyan-600 text-sm"><i class="fa-solid fa-circle-check"></i></span>
                                    </div>
                                    <div class="font-bold text-sm text-slate-800" x-text="addr.recipient_name + ' (' + addr.recipient_phone + ')'"></div>
                                    <div class="text-xs text-slate-500 mt-1 line-clamp-2" x-text="addr.address_line"></div>
                                    <div class="mt-2 text-[11px] text-slate-400" x-show="addr.benchmark" x-text="'Patokan: ' + addr.benchmark"></div>
                                    <div class="mt-2 text-[11px] font-semibold text-cyan-700" x-show="addr.service_area">
                                        Ongkir Area: <span x-text="formatRupiah(addr.service_area ? addr.service_area.delivery_fee : 0)"></span>
                                    </div>
                                </label>
                            </template>
                        </div>
                    @endif
                </div>

                <!-- 2. Select Products -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
                    <h2 class="font-bold text-base text-slate-900 flex items-center mb-4">
                        <span class="w-6 h-6 rounded-full bg-cyan-600 text-white text-xs font-bold flex items-center justify-center mr-2">2</span>
                        Pilih Produk Air Minum
                    </h2>

                    <div class="space-y-4">
                        <template x-for="(prod, index) in products" :key="prod.id">
                            <div class="p-4 rounded-xl border border-slate-200 hover:border-slate-300 transition flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <!-- Product info -->
                                <div class="flex items-start space-x-3.5">
                                    <div class="w-12 h-12 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center text-xl shrink-0 mt-0.5">
                                        <i class="fa-solid fa-bottle-water"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-sm text-slate-900" x-text="prod.name"></h3>
                                        <div class="text-xs text-slate-500 mt-0.5" x-text="prod.description"></div>
                                        <div class="flex items-center space-x-2 mt-1.5">
                                            <span class="text-xs font-bold text-cyan-700" x-text="formatRupiah(prod.price) + ' / ' + prod.unit"></span>
                                            <span x-show="prod.is_returnable_gallon" class="text-[10px] bg-blue-50 text-blue-700 font-semibold px-2 py-0.5 rounded-full border border-blue-100">
                                                Galon Tukar
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quantity & Refill Exchange controls -->
                                <div class="flex flex-col sm:items-end space-y-2 shrink-0">
                                    <div class="flex items-center space-x-2">
                                        <button type="button" @click="decrement(prod.id)" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold flex items-center justify-center transition">
                                            -
                                        </button>
                                        <span class="w-10 text-center font-bold text-sm text-slate-800" x-text="cart[prod.id] ? cart[prod.id].quantity : 0"></span>
                                        <button type="button" @click="increment(prod.id)" class="w-8 h-8 rounded-lg bg-cyan-600 hover:bg-cyan-700 text-white font-bold flex items-center justify-center transition">
                                            +
                                        </button>
                                    </div>

                                    <!-- Exchange Toggle for returnable gallon -->
                                    <div x-show="prod.is_returnable_gallon && cart[prod.id] && cart[prod.id].quantity > 0" class="text-[11px] flex items-center space-x-1.5">
                                        <input type="checkbox" :id="'exch_' + prod.id" x-model="cart[prod.id].is_exchange" class="rounded text-cyan-600 focus:ring-cyan-500">
                                        <label :for="'exch_' + prod.id" class="text-slate-600 cursor-pointer">
                                            Ada galon kosong untuk ditukar
                                        </label>
                                    </div>

                                    <!-- If not exchanging, show extra deposit note -->
                                    <div x-show="prod.is_returnable_gallon && cart[prod.id] && cart[prod.id].quantity > 0 && !cart[prod.id].is_exchange" class="text-[10px] text-amber-700 font-medium">
                                        + Deposit galon baru: <span x-text="formatRupiah(prod.deposit_price * cart[prod.id].quantity)"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- 3. Notes -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
                    <h2 class="font-bold text-base text-slate-900 flex items-center mb-3">
                        <span class="w-6 h-6 rounded-full bg-cyan-600 text-white text-xs font-bold flex items-center justify-center mr-2">3</span>
                        Catatan Khusus untuk Kurir (Opsional)
                    </h2>
                    <textarea name="customer_notes" rows="2" placeholder="Contoh: Tolong titip di pos sekuriti, atau pasang ke dispenser lantai 2..."
                              class="w-full text-sm border border-slate-300 rounded-xl p-3 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500"></textarea>
                </div>
            </div>

            <!-- Right col: Order Summary & Checkout (Sticky Follows Scroll) -->
            <div class="lg:col-span-4 lg:sticky lg:top-20 z-10">
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-md space-y-5 max-h-[calc(100vh-6rem)] overflow-y-auto custom-sidebar-scroll">
                    <h2 class="font-black text-lg text-slate-900 border-b border-slate-100 pb-3 flex items-center justify-between">
                        <span>Ringkasan Pesanan</span>
                        <span class="text-xs font-bold text-cyan-600 bg-cyan-50 px-2 py-0.5 rounded-full border border-cyan-100">Live</span>
                    </h2>

                    <!-- Items breakdown in summary -->
                    <div class="space-y-2 text-xs max-h-48 overflow-y-auto custom-sidebar-scroll pr-1">
                        <template x-for="item in cartItems" :key="item.product_id">
                            <div class="flex justify-between items-center py-1 border-b border-slate-50">
                                <div class="overflow-hidden pr-2">
                                    <div class="font-bold text-slate-800 truncate" x-text="item.name"></div>
                                    <div class="text-[11px] text-slate-400" x-text="item.quantity + 'x @ ' + formatRupiah(item.unit_price) + (item.is_returnable && !item.is_exchange ? ' (termasuk galon baru)' : '')"></div>
                                </div>
                                <div class="font-bold text-slate-800 whitespace-nowrap" x-text="formatRupiah(item.subtotal)"></div>
                            </div>
                        </template>

                        <div x-show="cartItems.length === 0" class="py-6 text-center text-slate-400">
                            Pilih setidaknya 1 produk air minum di sebelah kiri.
                        </div>
                    </div>

                    <!-- Cost Totals -->
                    <div class="space-y-2 text-xs pt-2 border-t border-slate-100">
                        <div class="flex justify-between text-slate-600">
                            <span>Subtotal Produk</span>
                            <span class="font-semibold text-slate-800" x-text="formatRupiah(calculateSubtotal())"></span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Ongkos Kirim</span>
                            <span class="font-semibold text-slate-800" x-text="formatRupiah(deliveryFee)"></span>
                        </div>
                        <div x-show="paymentMethod === 'qris' && calculateQrisFee() > 0" x-cloak class="flex justify-between text-teal-700">
                            <span class="flex items-center">
                                <span>Biaya Layanan QRIS (0.7%)</span>
                                <i class="fa-solid fa-circle-question ml-1 text-[10px] text-teal-500" title="Biaya resmi transaksi QRIS"></i>
                            </span>
                            <span class="font-semibold" x-text="formatRupiah(calculateQrisFee())"></span>
                        </div>
                        <div class="flex justify-between text-base font-black text-slate-900 pt-3 border-t border-slate-200">
                            <span>Total Pembayaran</span>
                            <span class="text-cyan-700" x-text="formatRupiah(calculateTotal())"></span>
                        </div>
                    </div>

                    <!-- Payment Method Selection -->
                    <div class="space-y-2 pt-2 border-t border-slate-100">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Metode Pembayaran</label>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <label :class="paymentMethod === 'cod' ? 'border-cyan-600 bg-cyan-50 font-bold text-cyan-800 ring-1 ring-cyan-500 shadow-xs' : 'border-slate-200 text-slate-700 hover:border-slate-300'"
                                   class="p-3 rounded-xl border text-center cursor-pointer transition flex flex-col items-center justify-center">
                                <input type="radio" name="payment_method" value="cod" x-model="paymentMethod" class="sr-only">
                                <i class="fa-solid fa-hand-holding-dollar block text-lg mb-1 text-emerald-600"></i>
                                <span>Tunai / COD</span>
                                <span class="text-[10px] text-slate-500 font-normal mt-0.5">Bayar di Tempat</span>
                            </label>

                            <label :class="paymentMethod === 'qris' ? 'border-teal-600 bg-teal-50 font-bold text-teal-900 ring-1 ring-teal-500 shadow-xs' : 'border-slate-200 text-slate-700 hover:border-slate-300'"
                                   class="p-3 rounded-xl border text-center cursor-pointer transition flex flex-col items-center justify-center">
                                <input type="radio" name="payment_method" value="qris" x-model="paymentMethod" class="sr-only">
                                <i class="fa-solid fa-qrcode block text-lg mb-1 text-teal-600"></i>
                                <span>QRIS Instan</span>
                                <span class="text-[10px] text-slate-500 font-normal mt-0.5">Semua E-Wallet / Bank</span>
                            </label>
                        </div>

                        <!-- Info Box for QRIS -->
                        <div x-show="paymentMethod === 'qris'" x-cloak class="mt-2 p-3.5 bg-gradient-to-r from-teal-50 to-cyan-50 border border-teal-200 rounded-2xl text-xs space-y-2">
                            <div class="flex items-center space-x-2 text-teal-900 font-bold">
                                <i class="fa-solid fa-qrcode text-base text-teal-600"></i>
                                <span>QRIS Toko (Doppnnstore)</span>
                            </div>
                            <p class="text-[11px] text-slate-600 leading-relaxed">
                                Barcode QRIS resmi Toko Nova Jaya beserta nominal pas pembayaran akan langsung ditampilkan di layar setelah Anda mengklik <b>Pesan Sekarang</b>.
                            </p>
                            <div class="flex items-center space-x-1.5 text-[10px] font-semibold text-teal-700">
                                <span class="px-1.5 py-0.5 bg-white rounded border border-teal-200">BCA</span>
                                <span class="px-1.5 py-0.5 bg-white rounded border border-teal-200">Mandiri</span>
                                <span class="px-1.5 py-0.5 bg-white rounded border border-teal-200">BRI</span>
                                <span class="px-1.5 py-0.5 bg-white rounded border border-teal-200">GoPay</span>
                                <span class="px-1.5 py-0.5 bg-white rounded border border-teal-200">OVO</span>
                                <span class="px-1.5 py-0.5 bg-white rounded border border-teal-200">Dana</span>
                                <span class="px-1.5 py-0.5 bg-white rounded border border-teal-200">ShopeePay</span>
                            </div>
                        </div>

                        <!-- Info Box for COD -->
                        <div x-show="paymentMethod === 'cod'" x-cloak class="mt-2 p-3 bg-slate-50 border border-slate-200 rounded-xl text-[11px] text-slate-600 space-y-1">
                            <div class="font-bold text-slate-800 flex items-center">
                                <i class="fa-solid fa-circle-info text-cyan-600 mr-1.5"></i> Pembayaran Tunai saat Galon Tiba
                            </div>
                            <div class="text-[10px] text-slate-500">
                                Mohon siapkan uang pas sebesar <b class="text-slate-800" x-text="formatRupiah(calculateTotal())"></b> saat kurir mengantar pesanan ke alamat Anda.
                            </div>
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
                    <button type="submit" 
                            :disabled="cartItems.length === 0 || !selectedAddressId"
                            :class="cartItems.length === 0 || !selectedAddressId ? 'opacity-50 cursor-not-allowed bg-slate-400' : 'bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 shadow-md'"
                            class="w-full py-3.5 px-4 rounded-xl text-white font-extrabold text-sm transition flex items-center justify-center">
                        <i class="fa-solid fa-paper-plane mr-2"></i> Konfirmasi & Pesan Sekarang
                    </button>

                    <p class="text-[11px] text-slate-500 text-center mt-2 flex items-center justify-center">
                        <i class="fa-brands fa-whatsapp text-emerald-600 mr-1.5 text-sm"></i>
                        Dapat langsung diteruskan ke WhatsApp Admin setelah klik pesan.
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function orderApp(config) {
    return {
        products: config.products,
        addresses: config.addresses,
        selectedAddressId: config.defaultAddressId,
        paymentMethod: 'cod',
        cart: {},

        init() {
            // Initialize cart with first product quantity = 2 as friendly default
            if (this.products.length > 0) {
                const first = this.products[0];
                this.cart[first.id] = {
                    quantity: 2,
                    is_exchange: true,
                };
            }
        },

        increment(productId) {
            if (!this.cart[productId]) {
                this.cart[productId] = { quantity: 1, is_exchange: true };
            } else {
                this.cart[productId].quantity++;
            }
        },

        decrement(productId) {
            if (this.cart[productId] && this.cart[productId].quantity > 0) {
                this.cart[productId].quantity--;
                if (this.cart[productId].quantity === 0) {
                    delete this.cart[productId];
                }
            }
        },

        get cartItems() {
            const list = [];
            for (const [pId, item] of Object.entries(this.cart)) {
                if (item.quantity > 0) {
                    const prod = this.products.find(p => p.id == pId);
                    if (prod) {
                        let unitPrice = parseFloat(prod.price);
                        if (prod.is_returnable_gallon && !item.is_exchange) {
                            unitPrice += parseFloat(prod.deposit_price || 0);
                        }
                        list.push({
                            product_id: prod.id,
                            name: prod.name,
                            unit: prod.unit,
                            quantity: item.quantity,
                            unit_price: unitPrice,
                            is_returnable: prod.is_returnable_gallon,
                            is_exchange: item.is_exchange,
                            subtotal: unitPrice * item.quantity
                        });
                    }
                }
            }
            return list;
        },

        get deliveryFee() {
            const addr = this.addresses.find(a => a.id == this.selectedAddressId);
            if (addr && addr.service_area) {
                return parseFloat(addr.service_area.delivery_fee || 0);
            }
            return 0;
        },

        calculateSubtotal() {
            return this.cartItems.reduce((acc, it) => acc + it.subtotal, 0);
        },

        calculateQrisFee() {
            if (this.paymentMethod !== 'qris') return 0;
            const base = this.calculateSubtotal() + this.deliveryFee;
            if (base <= 0) return 0;
            const gross = Math.ceil(base / 0.993);
            return Math.max(0, gross - base);
        },

        calculateTotal() {
            return this.calculateSubtotal() + this.deliveryFee + this.calculateQrisFee();
        },

        formatRupiah(val) {
            return 'Rp ' + Number(val || 0).toLocaleString('id-ID');
        },

        handleSubmit(e) {
            if (this.cartItems.length === 0) {
                e.preventDefault();
                alert('Pilih setidaknya 1 produk air minum untuk dipesan.');
            }
        }
    };
}
</script>
@endsection
