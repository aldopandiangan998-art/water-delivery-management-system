@extends('layouts.customer')

@section('title', __('Detail Pesanan #') . $order->order_number)

@section('breadcrumbs')
    <x-breadcrumbs :items="[
        ['title' => __('Riwayat Pesanan'), 'url' => route('customer.orders.index')],
        ['title' => '#' . $order->order_number]
    ]" />
@endsection

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6" 
     x-data="{ showAddressModal: false, selectedAddressId: {{ $order->customer_address_id ?? ($addresses->first()?->id ?? 0) }} }">

    <!-- Header bar -->
    <x-card class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 flex-wrap gap-y-1">
                <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight font-mono">{{ __('Pesanan') }} {{ $order->order_number }}</h1>
                <x-badge variant="neutral" class="{{ $order->status_badge['color'] }}">
                    {{ __($order->status_badge['label']) }}
                </x-badge>
                <x-badge variant="neutral" class="{{ $order->payment_badge['color'] }}">
                    {{ __($order->payment_badge['label']) }}
                </x-badge>
                <x-badge variant="neutral" class="{{ $order->payment_method_badge['color'] }}">
                    <i class="fa-solid {{ $order->payment_method_badge['icon'] }} mr-1"></i>
                    {{ __($order->payment_method_badge['label']) }}
                </x-badge>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Dibuat pada') }} {{ $order->created_at->format('d M Y, H:i') }} WIB</p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('customer.orders.receipt', $order) }}" target="_blank"
               class="px-3.5 py-2 rounded-xl text-xs font-bold text-cyan-700 dark:text-cyan-300 hover:bg-cyan-50 dark:hover:bg-cyan-950/40 border border-cyan-200 dark:border-cyan-800 transition flex items-center cursor-pointer shadow-xs">
                <i class="fa-solid fa-file-pdf mr-1.5 text-rose-500"></i> {{ __('Unduh Struk') }}
            </a>

            @if ($order->status === 'completed')
                <a href="{{ route('customer.orders.reorder', $order) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold bg-cyan-600 hover:bg-cyan-700 text-white shadow-xs transition flex items-center cursor-pointer">
                    <i class="fa-solid fa-rotate-right mr-1.5"></i> {{ __('Pesan Ulang') }}
                </a>
            @endif

            @if ($order->status === 'pending')
                <form id="cancel-order-show-form" method="POST" action="{{ route('customer.orders.cancel', $order) }}" class="inline">
                    @csrf
                    <button type="button" 
                            @click="$dispatch('open-confirm-modal', {
                                form: 'cancel-order-show-form',
                                title: '{{ __('Batalkan Pesanan') }}',
                                message: '{{ __('Apakah Anda yakin ingin membatalkan pesanan #') }}{{ $order->order_number }}? {{ __('Stok pesanan akan dikembalikan.') }}',
                                confirmText: '{{ __('Ya, Batalkan Pesanan') }}',
                                confirmButtonClass: 'bg-rose-600 hover:bg-rose-700 text-white shadow-md shadow-rose-600/20'
                            })"
                            class="px-3.5 py-2 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 border border-rose-200 dark:border-rose-800 transition flex items-center cursor-pointer">
                        <i class="fa-solid fa-ban mr-1.5"></i> {{ __('Batalkan Pesanan') }}
                    </button>
                </form>
            @endif

            @if ($order->delivery && in_array($order->delivery->status, ['assigned', 'in_transit', 'arrived']))
                <x-button :href="route('customer.orders.track', $order)" variant="success" size="sm" icon="fa-solid fa-map-location-dot">
                    {{ __('Lacak Pengantaran Live') }}
                </x-button>
            @endif

            <x-button :href="route('customer.orders.index')" variant="secondary" size="sm" icon="fa-solid fa-arrow-left">
                {{ __('Kembali') }}
            </x-button>
        </div>
    </x-card>

    @if ($order->status === 'completed')
        <!-- Completed Delivery Banner -->
        <div class="bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-3xl p-5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-start space-x-3.5">
                <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-xs text-white flex items-center justify-center shrink-0 shadow-sm text-2xl">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <h2 class="text-sm sm:text-base font-black">{{ __('Pesanan Berhasil Terkirim!') }}</h2>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-white text-emerald-800">{{ __('Selesai') }}</span>
                    </div>
                    <p class="text-xs text-emerald-50 mt-1 leading-relaxed">
                        {{ __('Air minum Anda telah berhasil diantar dan diserahterimakan. Terima kasih telah memesan di toko kami!') }}
                    </p>
                </div>
            </div>
        </div>
    @else
        <!-- COD Notice Banner -->
        <div class="bg-gradient-to-r from-emerald-50 via-teal-50 to-cyan-50 dark:from-emerald-950/40 dark:via-teal-950/40 dark:to-cyan-950/40 border border-emerald-300 dark:border-emerald-700 rounded-3xl p-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-start space-x-3.5">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-sm text-2xl">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <h2 class="text-sm sm:text-base font-black text-slate-900 dark:text-white">{{ __('Pesanan Tunai Siap Diproses') }}</h2>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-700">{{ __('Bayar di Tempat') }}</span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                        {{ __('Pesanan Anda telah tercatat! Pesanan akan segera disiapkan dan diantar ke alamat Anda.') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Delivery & Status Card -->
    @if ($order->delivery)
        <x-card class="p-6 space-y-4">
            <h2 class="font-black text-sm text-slate-900 dark:text-white uppercase tracking-wider flex items-center">
                <i class="fa-solid fa-truck text-cyan-600 dark:text-cyan-400 mr-2"></i> {{ __('Status Pengantaran') }}
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 dark:bg-slate-950/60 p-4 rounded-2xl text-xs border border-slate-100 dark:border-slate-800">
                <div>
                    <span class="text-slate-400 dark:text-slate-500 block">{{ __('No. Pengantaran') }}:</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200 text-sm font-mono">{{ $order->delivery->delivery_number }}</span>
                </div>
                <div>
                    <span class="text-slate-400 dark:text-slate-500 block">{{ __('Status Pengiriman') }}:</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200 text-sm">{{ __($order->delivery->status_badge['label'] ?? $order->delivery->status) }}</span>
                </div>
            </div>

            <!-- Delivery Proof Photo if uploaded -->
            @if ($order->delivery->photos->isNotEmpty())
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                    <h3 class="text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">{{ __('Foto Bukti Serah Terima') }}:</h3>
                    <div class="flex flex-wrap gap-3">
                        @foreach ($order->delivery->photos as $photo)
                            <div class="border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-2xs">
                                <img src="/storage/{{ $photo->photo_path }}" alt="Bukti Serah Terima" class="h-36 w-36 object-cover hover:scale-105 transition">
                                <div class="p-1.5 text-[10px] text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-950 text-center">
                                    {{ $photo->created_at->format('H:i, d M Y') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </x-card>
    @endif

    <!-- Order Items Breakdown Card -->
    <x-card class="p-6 space-y-4">
        <h2 class="font-black text-sm text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Barang Dipesan') }}</h2>

        <div class="divide-y divide-slate-100 dark:divide-slate-800">
            @foreach ($order->items as $item)
                <div class="py-3 flex justify-between items-center text-sm">
                    <div>
                        <div class="font-bold text-slate-900 dark:text-white">{{ $item->product_name }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">
                            {{ $item->quantity }} {{ $item->unit }} &times; Rp {{ number_format($item->price, 0, ',', '.') }}
                            @if ($item->is_exchange)
                                <span class="text-blue-600 dark:text-blue-400 font-semibold ml-1">({{ __('Tukar Galon') }})</span>
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
                <span>{{ __('Subtotal Produk') }}:</span>
                <span class="font-bold text-slate-800 dark:text-slate-200">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-slate-600 dark:text-slate-400">
                <span>{{ __('Ongkos Kirim') }}:</span>
                <span class="font-bold text-slate-800 dark:text-slate-200">Rp {{ number_format($order->delivery_fee, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-base font-black text-slate-900 dark:text-white pt-2 border-t border-slate-100 dark:border-slate-800">
                <span>{{ __('Total Tagihan') }}:</span>
                <span class="text-cyan-600 dark:text-cyan-400">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
            </div>
        </div>
    </x-card>

    <!-- Delivery Address & Notes -->
    <x-card class="p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="font-black text-sm text-slate-900 dark:text-white uppercase tracking-wider flex items-center">
                <i class="fa-solid fa-location-dot text-cyan-600 dark:text-cyan-400 mr-2"></i> {{ __('Alamat Pengantaran') }}
            </h2>
            @if ($order->status === 'pending')
                <button type="button" 
                        @click="showAddressModal = true" 
                        class="inline-flex items-center text-xs font-bold text-cyan-600 dark:text-cyan-400 hover:text-cyan-700 dark:hover:text-cyan-300 cursor-pointer bg-cyan-50 dark:bg-cyan-950/50 hover:bg-cyan-100 dark:hover:bg-cyan-900/60 px-3 py-1.5 rounded-xl border border-cyan-200 dark:border-cyan-800 transition">
                    <i class="fa-solid fa-location-pen mr-1.5"></i> {{ __('Ubah / Pilih Alamat') }}
                </button>
            @endif
        </div>

        <div class="text-xs text-slate-700 dark:text-slate-300 space-y-1.5 bg-slate-50 dark:bg-slate-950/60 p-4 rounded-2xl border border-slate-100 dark:border-slate-800">
            <div><span class="text-slate-400 dark:text-slate-500">{{ __('Penerima') }}:</span> <span class="font-bold text-slate-900 dark:text-white">{{ $order->recipient_name }} ({{ $order->recipient_phone }})</span></div>
            <div><span class="text-slate-400 dark:text-slate-500">{{ __('Alamat') }}:</span> {{ $order->delivery_address_text }}</div>
            @if ($order->delivery_benchmark)
                <div><span class="text-slate-400 dark:text-slate-500">{{ __('Patokan') }}:</span> {{ $order->delivery_benchmark }}</div>
            @endif
            @if ($order->customer_notes)
                <div class="p-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-800 dark:text-slate-200 italic mt-2">
                    {{ __('Catatan Khusus') }}: "{{ $order->customer_notes }}"
                </div>
            @endif
        </div>
    </x-card>

    <!-- Customer Comment / Review Section when order is completed -->
    @if ($order->status === 'completed')
        <x-card class="p-6 space-y-4 border-2 border-cyan-100 dark:border-cyan-900 bg-white dark:bg-slate-900">
            <div class="flex items-center space-x-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="w-10 h-10 rounded-2xl bg-cyan-100 dark:bg-cyan-950 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-comment-dots"></i>
                </div>
                <div>
                    <h2 class="font-black text-sm text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Komentar & Masukan Pengantaran') }}</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Bagikan tanggapan Anda mengenai pengantaran air minum ini.') }}</p>
                </div>
            </div>

            @if ($order->review)
                <div class="space-y-2">
                    <div class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ __('Komentar yang telah Anda kirimkan:') }}</div>
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 text-xs text-slate-700 dark:text-slate-300 italic leading-relaxed">
                        "{{ $order->review->comment }}"
                    </div>
                    <div class="text-[10px] text-slate-400">
                        {{ __('Dikirim pada') }} {{ $order->review->created_at->format('d M Y, H:i') }} WIB
                    </div>
                </div>
            @else
                <form method="POST" action="{{ route('customer.orders.review', $order) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            {{ __('Tulis Komentar Anda:') }}
                        </label>
                        <textarea name="comment" required rows="3" 
                                  placeholder="{{ __('Tuliskan komentar atau masukan Anda mengenai pengantaran air minum ini...') }}"
                                  class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white p-3 focus:ring-2 focus:ring-cyan-500"></textarea>
                    </div>

                    <div class="flex justify-end">
                        <x-button type="submit" variant="primary" icon="fa-solid fa-paper-plane" size="sm">
                            {{ __('Kirim Komentar') }}
                        </x-button>
                    </div>
                </form>
            @endif
        </x-card>
    @endif

    <!-- Modal Ubah / Pilih Alamat Pengantaran -->
    @if ($order->status === 'pending')
        <div x-show="showAddressModal" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs transition-opacity"
             @keydown.escape.window="showAddressModal = false">
            <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-5"
                 @click.outside="showAddressModal = false">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-xl bg-cyan-100 dark:bg-cyan-950 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <h3 class="font-extrabold text-base text-slate-900 dark:text-white">{{ __('Pilih Alamat Pengantaran') }}</h3>
                    </div>
                    <button type="button" @click="showAddressModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg cursor-pointer">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('customer.orders.update-address', $order) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
                            {{ __('Daftar Alamat Tersimpan Anda:') }}
                        </label>
                        <div class="space-y-2.5 max-h-72 overflow-y-auto pr-1">
                            @forelse ($addresses as $addr)
                                <div @click="selectedAddressId = {{ $addr->id }}"
                                     class="p-3.5 rounded-2xl border cursor-pointer transition block relative"
                                     :class="Number(selectedAddressId) === {{ $addr->id }} ? 'border-cyan-600 bg-cyan-50/50 dark:bg-cyan-950/30 ring-2 ring-cyan-500' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 bg-white dark:bg-slate-950'">
                                    <input type="radio" name="customer_address_id" value="{{ $addr->id }}" :checked="Number(selectedAddressId) === {{ $addr->id }}" class="sr-only">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="font-extrabold text-[10px] uppercase px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                            {{ $addr->label }}
                                        </span>
                                        <span x-show="Number(selectedAddressId) === {{ $addr->id }}" class="text-cyan-600 dark:text-cyan-400 text-xs font-bold flex items-center gap-1">
                                            <i class="fa-solid fa-circle-check"></i> {{ __('Terpilih') }}
                                        </span>
                                    </div>
                                    <div class="font-bold text-xs text-slate-900 dark:text-white">
                                        {{ $addr->recipient_name }} ({{ $addr->recipient_phone }})
                                    </div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-2">
                                        {{ $addr->address_line }}
                                    </div>
                                    @if ($addr->benchmark)
                                        <div class="text-[10px] text-slate-400 mt-1">
                                            {{ __('Patokan') }}: {{ $addr->benchmark }}
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center py-6 text-slate-400 text-xs">
                                    {{ __('Belum ada alamat lain.') }}
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-800">
                        <a href="{{ route('customer.addresses.index') }}" class="text-xs font-bold text-cyan-600 dark:text-cyan-400 hover:underline">
                            + {{ __('Kelola Alamat') }}
                        </a>
                        <div class="flex items-center space-x-2">
                            <x-button type="button" @click="showAddressModal = false" variant="secondary" size="sm">
                                {{ __('Batal') }}
                            </x-button>
                            <x-button type="submit" variant="primary" size="sm" icon="fa-solid fa-check">
                                {{ __('Simpan Perubahan') }}
                            </x-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
@endsection
