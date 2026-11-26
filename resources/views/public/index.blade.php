@extends('layouts.app')

@section('title', 'Air Minum Higienis Antar Cepat')

@section('content')
@php
    $waPhone = preg_replace('/[^0-9]/', '', $settings['company_whatsapp'] ?? '6283186109775');
    $waOrderUrl = "https://wa.me/{$waPhone}?text=" . rawurlencode('Halo Toko Nova Jaya, saya ingin pesan air minum galon.');
@endphp
<!-- Hero Section -->
<section class="relative overflow-hidden bg-gradient-to-b from-cyan-50 via-white to-white py-16 sm:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <!-- Left col -->
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <div class="inline-flex items-center px-3.5 py-1.5 rounded-full bg-cyan-100 text-cyan-800 text-xs font-semibold tracking-wide border border-cyan-200">
                    <span class="w-2 h-2 rounded-full bg-cyan-600 mr-2 animate-ping"></span>
                    Pengantaran Cepat 20-35 Menit Langsung ke Rumah Anda
                </div>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 tracking-tight leading-[1.15]">
                    Pesan Air Minum <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-600 to-blue-600">Higienis & Segar</span> Tanpa Repot Angkat Galon
                </h1>
                <p class="text-base sm:text-lg text-slate-600 max-w-2xl leading-relaxed">
                    Air minum murni Reverse Osmosis (RO) & Bio-Mineral teruji laboratorium. Kurir kami siap mengantar dan membantu mengangkat galon hingga ke dispenser dapur Anda.
                </p>
                <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4 justify-center lg:justify-start pt-2">
                    <a href="{{ $waOrderUrl }}" target="_blank"
                       class="inline-flex items-center justify-center px-7 py-3.5 rounded-xl font-bold text-white bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 shadow-md hover:shadow-lg transition transform hover:-translate-y-0.5">
                        <i class="fa-brands fa-whatsapp mr-2.5 text-lg"></i> Pesan via WhatsApp
                    </a>
                    <a href="#produk" 
                       class="inline-flex items-center justify-center px-6 py-3.5 rounded-xl font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 shadow-xs transition">
                        <i class="fa-solid fa-list mr-2 text-cyan-600"></i> Lihat Katalog Produk
                    </a>
                </div>

                <!-- Trust Badges -->
                <div class="pt-6 grid grid-cols-3 gap-4 border-t border-slate-200 text-center lg:text-left">
                    <div>
                        <div class="text-2xl font-black text-cyan-600">0.0001&mu;</div>
                        <div class="text-xs text-slate-500 font-medium">Membran RO Murni</div>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-blue-600">100%</div>
                        <div class="text-xs text-slate-500 font-medium">Bebas Bakteri UV & O3</div>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-emerald-600">Live GPS</div>
                        <div class="text-xs text-slate-500 font-medium">Lacak Kurir Real-Time</div>
                    </div>
                </div>
            </div>

            <!-- Right col: Interactive card illustration -->
            <div class="lg:col-span-5 relative">
                <div class="relative mx-auto max-w-md bg-white rounded-3xl shadow-xl border border-slate-100 p-6 space-y-5">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-cyan-100 text-cyan-700 flex items-center justify-center font-bold">
                                <i class="fa-solid fa-motorcycle text-lg"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-sm text-slate-900">Kurir Sedang Mengantar</h4>
                                <p class="text-xs text-emerald-600 flex items-center font-medium">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span> Dalam Perjalanan (Live GPS)
                                </p>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-slate-400 bg-slate-100 px-2.5 py-1 rounded-full">#ORD-9821</span>
                    </div>

                    <!-- Mini Map Mockup -->
                    <div class="h-44 bg-slate-100 rounded-2xl relative overflow-hidden border border-slate-200 flex items-center justify-center text-slate-400">
                        <div class="absolute inset-0 opacity-40 bg-[radial-gradient(#38bdf8_1px,transparent_1px)] [background-size:16px_16px]"></div>
                        <!-- Depot point -->
                        <div class="absolute top-8 left-10 text-center">
                            <div class="w-7 h-7 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs shadow-md mx-auto">
                                <i class="fa-solid fa-store"></i>
                            </div>
                            <span class="text-[9px] font-bold text-slate-700 block mt-1 bg-white/90 px-1 rounded">Depot</span>
                        </div>
                        <!-- Driver Moving Point -->
                        <div class="absolute top-16 left-36 text-center animate-bounce">
                            <div class="w-8 h-8 bg-emerald-500 text-white rounded-full flex items-center justify-center text-xs shadow-lg mx-auto">
                                <i class="fa-solid fa-motorcycle"></i>
                            </div>
                            <span class="text-[9px] font-bold text-emerald-700 block mt-0.5 bg-white/90 px-1 rounded shadow-2xs">Kurir Asep</span>
                        </div>
                        <!-- Destination point -->
                        <div class="absolute bottom-8 right-10 text-center">
                            <div class="w-7 h-7 bg-rose-600 text-white rounded-full flex items-center justify-center text-xs shadow-md mx-auto">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <span class="text-[9px] font-bold text-slate-700 block mt-1 bg-white/90 px-1 rounded">Alamat Anda</span>
                        </div>
                    </div>

                    <div class="bg-cyan-50 border border-cyan-100 rounded-xl p-3.5 flex items-center justify-between">
                        <div>
                            <div class="text-xs text-cyan-900 font-semibold">Estimasi Tiba: 8 Menit</div>
                            <div class="text-[11px] text-cyan-700">Driver telah melintasi Jl. Sukajadi</div>
                        </div>
                        <a href="{{ $waOrderUrl }}" target="_blank" class="text-xs font-bold text-white bg-cyan-600 hover:bg-cyan-700 px-3 py-1.5 rounded-lg shadow-xs transition">
                            Pesan via WA
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Advantages Section -->
<section class="py-16 bg-white border-y border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <h2 class="text-xs font-bold uppercase tracking-wider text-cyan-600 mb-2">Kenapa Memilih AquaFlow?</h2>
            <p class="text-3xl font-extrabold text-slate-900">Standar Air Minum Higienis Tertinggi untuk Keluarga</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200/80 hover:border-cyan-400 transition group">
                <div class="w-12 h-12 rounded-xl bg-cyan-100 text-cyan-700 flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition">
                    <i class="fa-solid fa-filter"></i>
                </div>
                <h3 class="font-bold text-slate-900 text-base mb-2">Filtrasi 8 Tahap + RO</h3>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Saringan mikro, karbon aktif granular, membran RO 0.0001 mikron menghasilkan air jernih dan bebas kotoran.
                </p>
            </div>

            <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200/80 hover:border-cyan-400 transition group">
                <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition">
                    <i class="fa-solid fa-shield-virus"></i>
                </div>
                <h3 class="font-bold text-slate-900 text-base mb-2">Sterilisasi UV & Ozon</h3>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Penyinaran ultraviolet ganda dan injeksi ozon aktif membunuh 99.9% bakteri, virus, dan mikroorganisme berbahaya.
                </p>
            </div>

            <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200/80 hover:border-cyan-400 transition group">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition">
                    <i class="fa-solid fa-map-pin"></i>
                </div>
                <h3 class="font-bold text-slate-900 text-base mb-2">Lacak Kurir di Peta</h3>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Pantau pergerakan kurir secara transparan melalui GPS real-time langsung dari layar handphone Anda.
                </p>
            </div>

            <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200/80 hover:border-cyan-400 transition group">
                <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition">
                    <i class="fa-solid fa-handshake-angle"></i>
                </div>
                <h3 class="font-bold text-slate-900 text-base mb-2">Bantuan Angkat Galon</h3>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Kurir sopan kami siap membantu membawa dan memasangkan galon ke dispenser Anda tanpa biaya tambahan.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Product Catalog Section -->
<section id="produk" class="py-16 bg-slate-50" x-data="{ activeCategory: 'all' }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8">
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-cyan-600 mb-2">Katalog Pilihan</h2>
                <p class="text-3xl font-extrabold text-slate-900">Produk Air Minum & Perlengkapan</p>
            </div>
            <!-- Category Tabs -->
            <div class="mt-4 md:mt-0 flex flex-wrap gap-2">
                <button @click="activeCategory = 'all'" 
                        :class="activeCategory === 'all' ? 'bg-cyan-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition">
                    Semua Produk
                </button>
                @foreach ($categories as $cat)
                    <button @click="activeCategory = 'cat-{{ $cat->id }}'" 
                            :class="activeCategory === 'cat-{{ $cat->id }}' ? 'bg-cyan-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                            class="px-4 py-2 rounded-xl text-xs font-bold transition">
                        {{ $cat->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($featuredProducts as $product)
                <div x-show="activeCategory === 'all' || activeCategory === 'cat-{{ $product->category_id }}'"
                     class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs hover:shadow-md transition flex flex-col justify-between">
                    <div>
                        <!-- Badge & Icon Banner -->
                        <div class="h-44 bg-gradient-to-tr from-cyan-100 to-blue-50 relative flex items-center justify-center p-4">
                            @if ($product->is_returnable_gallon)
                                <span class="absolute top-3 left-3 bg-blue-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-xs">
                                    Tukar Galon
                                </span>
                            @endif
                            <div class="w-20 h-20 rounded-2xl bg-white shadow-sm flex items-center justify-center text-cyan-600">
                                @if (str_contains(strtolower($product->name), 'pompa'))
                                    <i class="fa-solid fa-plug-circle-bolt text-4xl"></i>
                                @elseif (str_contains(strtolower($product->unit), 'dus'))
                                    <i class="fa-solid fa-box text-4xl text-amber-600"></i>
                                @else
                                    <i class="fa-solid fa-bottle-water text-5xl"></i>
                                @endif
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="p-5">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                                {{ $product->category?->name ?? 'Produk' }}
                            </div>
                            <h3 class="font-bold text-slate-900 text-base leading-snug line-clamp-2 mb-2">
                                {{ $product->name }}
                            </h3>
                            <p class="text-xs text-slate-500 line-clamp-2 mb-4 leading-relaxed">
                                {{ $product->description }}
                            </p>
                        </div>
                    </div>

                    <div class="px-5 pb-5 pt-0">
                        <div class="flex items-baseline justify-between mb-3 border-t border-slate-100 pt-3">
                            <div>
                                <span class="text-xs text-slate-400 font-medium">Harga / {{ $product->unit }}:</span>
                                <div class="text-lg font-black text-cyan-700">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </div>
                            </div>
                            @if ($product->is_returnable_gallon && $product->deposit_price > 0)
                                <div class="text-right">
                                    <span class="text-[10px] text-slate-400 block">+ Galon Baru</span>
                                    <span class="text-xs font-semibold text-slate-600">Rp {{ number_format($product->deposit_price, 0, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>

                        <a href="{{ $waOrderUrl }}" target="_blank"
                           class="w-full flex items-center justify-center py-2.5 px-4 rounded-xl text-xs font-bold bg-cyan-600 hover:bg-cyan-700 text-white transition shadow-xs">
                            <i class="fa-brands fa-whatsapp mr-1.5 text-sm"></i> Pesan via WhatsApp
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Service Areas Section -->
<section class="py-16 bg-white border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-5 space-y-4">
                <h2 class="text-xs font-bold uppercase tracking-wider text-cyan-600">Jangkauan Pengantaran</h2>
                <h3 class="text-3xl font-extrabold text-slate-900">Siap Mengantar ke Seluruh Wilayah Sukajadi & Sekitarnya</h3>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Kami melayani rute terjadwal setiap hari mulai pukul 07:00 hingga 20:00 WIB. Cek ongkos kirim dan minimal pesanan untuk wilayah Anda.
                </p>
                <div class="pt-2">
                    <a href="{{ route('public.service-areas') }}" class="text-sm font-bold text-cyan-600 hover:text-cyan-700 flex items-center">
                        Lihat daftar tarif & syarat lengkap <i class="fa-solid fa-arrow-right ml-2 text-xs"></i>
                    </a>
                </div>
            </div>

            <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach ($serviceAreas as $area)
                    <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/50 hover:bg-cyan-50/40 hover:border-cyan-300 transition">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-bold text-slate-900 text-sm">{{ $area->name }}</h4>
                            @if ($area->delivery_fee == 0)
                                <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded-full">Gratis Ongkir</span>
                            @else
                                <span class="text-xs font-bold text-slate-700">Rp {{ number_format($area->delivery_fee, 0, ',', '.') }}</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mb-2">{{ $area->district }}</p>
                        <div class="flex items-center text-[11px] text-slate-600 space-x-3 pt-2 border-t border-slate-200/60">
                            <span><i class="fa-solid fa-clock text-cyan-500 mr-1"></i> {{ $area->estimated_delivery_time }}</span>
                            <span><i class="fa-solid fa-basket-shopping text-cyan-500 mr-1"></i> Min Rp {{ number_format($area->min_order_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<!-- Customer Testimonials Section -->
<section id="testimoni" class="py-16 bg-slate-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h2 class="text-xs font-bold uppercase tracking-wider text-cyan-400 mb-2">Ulasan Pembeli</h2>
            <p class="text-3xl font-extrabold text-white">Apa Kata Pelanggan AquaFlow?</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @forelse ($reviews as $rev)
                <div class="bg-slate-800 border border-slate-700 p-6 rounded-2xl flex flex-col justify-between">
                    <div>
                        <div class="flex text-amber-400 text-sm mb-3">
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="fa-solid fa-star {{ $i <= $rev->rating ? 'text-amber-400' : 'text-slate-600' }}"></i>
                            @endfor
                        </div>
                        <p class="text-sm text-slate-300 italic mb-4 leading-relaxed">
                            "{{ $rev->comment }}"
                        </p>
                    </div>
                    <div class="flex items-center space-x-3 border-t border-slate-700/80 pt-3">
                        <div class="w-8 h-8 rounded-full bg-cyan-600 text-white font-bold flex items-center justify-center text-xs">
                            {{ strtoupper(substr($rev->user->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="text-xs font-bold text-white">{{ $rev->user->name }}</div>
                            <div class="text-[10px] text-slate-400">{{ $rev->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center text-slate-400 py-6">
                    Belum ada ulasan yang ditampilkan.
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section id="faq" class="py-16 bg-slate-50" x-data="{ activeFaq: null }">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-xs font-bold uppercase tracking-wider text-cyan-600 mb-2">Pertanyaan Umum</h2>
            <p class="text-3xl font-extrabold text-slate-900">Frequently Asked Questions (FAQ)</p>
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-2xs">
                <button @click="activeFaq = activeFaq === 1 ? null : 1" class="w-full text-left p-5 font-bold text-sm text-slate-900 flex justify-between items-center">
                    <span>Apakah saya harus punya galon kosong untuk memesan?</span>
                    <i :class="activeFaq === 1 ? 'fa-solid fa-chevron-up text-cyan-600' : 'fa-solid fa-chevron-down text-slate-400'"></i>
                </button>
                <div x-show="activeFaq === 1" class="px-5 pb-5 text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                    Jika Anda sudah memiliki galon kosong (standar Aqua atau galon biru), Anda cukup membayar harga isi ulang. Namun jika belum memiliki galon, Anda dapat memilih opsi beli galon baru (dengan deposit galon) pada saat checkout.
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-2xs">
                <button @click="activeFaq = activeFaq === 2 ? null : 2" class="w-full text-left p-5 font-bold text-sm text-slate-900 flex justify-between items-center">
                    <span>Bagaimana cara kerja pelacakan lokasi kurir real-time?</span>
                    <i :class="activeFaq === 2 ? 'fa-solid fa-chevron-up text-cyan-600' : 'fa-solid fa-chevron-down text-slate-400'"></i>
                </button>
                <div x-show="activeFaq === 2" class="px-5 pb-5 text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                    Saat pesanan Anda diserahkan kepada kurir dan kurir mulai jalan, sistem kami akan memperbarui koordinat GPS kurir setiap beberapa detik. Anda dapat membuka menu "Lacak Pesanan" untuk melihat posisi kurir di peta.
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-2xs">
                <button @click="activeFaq = activeFaq === 3 ? null : 3" class="w-full text-left p-5 font-bold text-sm text-slate-900 flex justify-between items-center">
                    <span>Metode pembayaran apa saja yang diterima?</span>
                    <i :class="activeFaq === 3 ? 'fa-solid fa-chevron-up text-cyan-600' : 'fa-solid fa-chevron-down text-slate-400'"></i>
                </button>
                <div x-show="activeFaq === 3" class="px-5 pb-5 text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                    Kami menerima pembayaran tunai saat kurir sampai di tempat (Cash on Delivery / COD) serta Transfer Bank ke rekening resmi BCA PT AquaFlow Fresh Indonesia.
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Footer Banner -->
<section class="bg-gradient-to-r from-cyan-600 to-blue-700 py-12 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between text-center md:text-left">
        <div>
            <h3 class="text-2xl font-bold mb-1">Siap Menikmati Air Minum Segar & Higienis?</h3>
            <p class="text-cyan-100 text-sm">Pesan sekarang dalam 1 menit, kurir kami siap meluncur ke lokasi Anda.</p>
        </div>
        <div class="mt-6 md:mt-0">
            <a href="{{ $waOrderUrl }}" target="_blank" class="px-8 py-3.5 rounded-xl bg-white text-cyan-700 font-extrabold text-sm shadow-lg hover:bg-cyan-50 transition inline-flex items-center">
                <i class="fa-brands fa-whatsapp mr-2 text-base text-emerald-600"></i> Pesan via WhatsApp &rarr;
            </a>
        </div>
    </div>
</section>
@endsection
