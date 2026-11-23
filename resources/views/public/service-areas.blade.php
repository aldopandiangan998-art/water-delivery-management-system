@extends('layouts.app')

@section('title', 'Wilayah & Tarif Pengantaran')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="text-center max-w-3xl mx-auto mb-10">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Wilayah Layanan & Ongkos Kirim</h1>
        <p class="mt-2 text-base text-slate-600 leading-relaxed">
            Daftar jangkauan pengiriman air minum AquaFlow di wilayah Bandung dan sekitarnya dengan tarif ongkir terjangkau.
        </p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 font-semibold uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Wilayah / Kelurahan</th>
                        <th class="px-6 py-4">Kecamatan / Kota</th>
                        <th class="px-6 py-4">Tarif Pengantaran</th>
                        <th class="px-6 py-4">Minimal Order</th>
                        <th class="px-6 py-4">Estimasi Waktu</th>
                        <th class="px-6 py-4">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach ($serviceAreas as $area)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-bold text-slate-900">{{ $area->name }}</td>
                            <td class="px-6 py-4 text-slate-500">{{ $area->district ?? '-' }}</td>
                            <td class="px-6 py-4 font-semibold">
                                @if ($area->delivery_fee == 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                        Gratis Ongkir
                                    </span>
                                @else
                                    Rp {{ number_format($area->delivery_fee, 0, ',', '.') }}
                                @endif
                            </td>
                            <td class="px-6 py-4">Rp {{ number_format($area->min_order_amount, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-slate-500">{{ $area->estimated_delivery_time }}</td>
                            <td class="px-6 py-4 text-xs text-slate-500">{{ $area->notes ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-10 text-center">
        <p class="text-sm text-slate-500 mb-4">Lokasi Anda belum tercantum di atas? Hubungi kami via WhatsApp untuk konfirmasi pengantaran khusus.</p>
        <a href="https://wa.me/6281223456789?text=Halo%20AquaFlow,%20apakah%20bisa%20antar%20ke%20alamat%20saya?" target="_blank"
           class="inline-flex items-center px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-sm transition">
            <i class="fa-brands fa-whatsapp text-lg mr-2"></i> Tanya Admin via WhatsApp
        </a>
    </div>
</div>
@endsection
