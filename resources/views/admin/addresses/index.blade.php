@extends('layouts.admin')

@section('page_title', 'Kelola Alamat Pelanggan')

@section('content')
<div class="space-y-6">

    <!-- Top header card -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight">Manajemen Alamat Pelanggan</h1>
            <p class="text-xs text-slate-500">Kelola buku alamat pelanggan, patokan lokasi rumah, koordinat GPS, dan wilayah pengantaran.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.addresses.create') }}" 
               class="px-4 py-2.5 rounded-xl text-xs font-bold bg-cyan-600 hover:bg-cyan-700 text-white shadow-xs transition flex items-center">
                <i class="fa-solid fa-plus mr-1.5"></i> Tambah Alamat Baru
            </a>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('admin.addresses.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            <div class="sm:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari penerima, no. telp, jalan, patokan, atau nama pelanggan..."
                       class="w-full border border-slate-200 rounded-xl p-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-cyan-500 transition">
            </div>

            <div>
                <select name="user_id" onchange="this.form.submit()" class="w-full border border-slate-200 rounded-xl p-2.5 bg-white font-medium">
                    <option value="">Semua Pelanggan</option>
                    @foreach ($customers as $cust)
                        <option value="{{ $cust->id }}" {{ request('user_id') == $cust->id ? 'selected' : '' }}>
                            {{ $cust->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex space-x-2">
                <select name="service_area_id" onchange="this.form.submit()" class="flex-1 border border-slate-200 rounded-xl p-2.5 bg-white font-medium">
                    <option value="">Semua Wilayah</option>
                    @foreach ($serviceAreas as $area)
                        <option value="{{ $area->id }}" {{ request('service_area_id') == $area->id ? 'selected' : '' }}>
                            {{ $area->name }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl shadow-xs transition">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Addresses Table -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Akun Pelanggan</th>
                        <th class="px-5 py-3.5">Penerima & Kontak</th>
                        <th class="px-5 py-3.5">Alamat & Patokan</th>
                        <th class="px-5 py-3.5">Wilayah Layanan</th>
                        <th class="px-5 py-3.5">Koordinat GPS</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($addresses as $addr)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-900 flex items-center">
                                    <div class="w-6 h-6 rounded-full bg-cyan-100 text-cyan-700 font-bold flex items-center justify-center text-[10px] mr-2">
                                        {{ strtoupper(substr($addr->user?->name ?? 'P', 0, 1)) }}
                                    </div>
                                    <span>{{ $addr->user?->name ?? 'Pelanggan Tidak Dikenal' }}</span>
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5 ml-8">{{ $addr->user?->email }}</div>
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <div class="flex items-center space-x-1.5">
                                    <span class="font-bold text-slate-900">{{ $addr->recipient_name }}</span>
                                    <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">
                                        {{ $addr->label }}
                                    </span>
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5 flex items-center">
                                    <i class="fa-solid fa-phone text-slate-400 mr-1 text-[10px]"></i> {{ $addr->recipient_phone }}
                                </div>
                            </td>

                            <td class="px-5 py-3.5">
                                <div class="font-medium text-slate-800 line-clamp-2 max-w-[240px]">
                                    {{ $addr->address_line }}
                                </div>
                                @if ($addr->benchmark)
                                    <div class="text-[11px] text-amber-800 bg-amber-50 px-2 py-0.5 rounded-md inline-block mt-1 border border-amber-200">
                                        <span class="font-bold">Patokan:</span> {{ $addr->benchmark }}
                                    </div>
                                @endif
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if ($addr->serviceArea)
                                    <div class="font-semibold text-slate-900">{{ $addr->serviceArea->name }}</div>
                                    <div class="text-[10px] text-emerald-700 font-bold">
                                        Ongkir: Rp {{ number_format($addr->serviceArea->delivery_fee, 0, ',', '.') }}
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">Belum diatur</span>
                                @endif
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if ($addr->latitude && $addr->longitude)
                                    <div class="font-mono text-[11px] text-slate-600">
                                        {{ number_format($addr->latitude, 4) }}, {{ number_format($addr->longitude, 4) }}
                                    </div>
                                    <a href="https://maps.google.com/?q={{ $addr->latitude }},{{ $addr->longitude }}" target="_blank"
                                       class="text-[10px] font-bold text-cyan-600 hover:text-cyan-800 flex items-center mt-0.5">
                                        <i class="fa-solid fa-arrow-up-right-from-square mr-1 text-[9px]"></i> Google Maps
                                    </a>
                                @else
                                    <span class="text-slate-400 italic">Tanpa Titik GPS</span>
                                @endif
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if ($addr->is_default)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-cyan-50 text-cyan-800 border border-cyan-200 flex items-center w-fit">
                                        <i class="fa-solid fa-check-circle mr-1"></i> Utama
                                    </span>
                                @else
                                    <form method="POST" action="{{ route('admin.addresses.set-default', $addr) }}">
                                        @csrf
                                        <button type="submit" class="text-[11px] text-slate-500 hover:text-cyan-600 font-medium underline">
                                            Jadikan Utama
                                        </button>
                                    </form>
                                @endif
                            </td>

                            <td class="px-5 py-3.5 text-right whitespace-nowrap space-x-1.5">
                                <a href="{{ route('admin.addresses.show', $addr) }}" 
                                   class="text-xs font-semibold px-2.5 py-1 rounded-lg text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                                    <i class="fa-solid fa-eye"></i>
                                </a>

                                <a href="{{ route('admin.addresses.edit', $addr) }}" 
                                   class="text-xs font-bold px-2.5 py-1 rounded-lg text-cyan-700 bg-cyan-50 hover:bg-cyan-100 transition">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </a>

                                <form method="POST" action="{{ route('admin.addresses.destroy', $addr) }}" 
                                      class="inline-block"
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus alamat ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-bold px-2.5 py-1 rounded-lg text-rose-600 bg-rose-50 hover:bg-rose-100 transition">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-map-location-dot text-4xl mb-3 text-slate-300 block"></i>
                                Tidak ada data alamat pelanggan yang sesuai kriteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $addresses->links() }}
        </div>
    </div>

</div>
@endsection
