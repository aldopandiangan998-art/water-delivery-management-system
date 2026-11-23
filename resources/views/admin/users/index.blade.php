@extends('layouts.admin')

@section('page_title', 'Manajemen Pengguna & Peran')

@section('content')
<div class="space-y-6">

    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight">Manajemen Akun & Hak Akses</h1>
            <p class="text-xs text-slate-500">Kelola daftar pengguna, tetapkan peran (Super Admin, Admin, Kurir, Pelanggan), dan status akun.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Nama & Kontak</th>
                        <th class="px-5 py-3.5">Email</th>
                        <th class="px-5 py-3.5">Peran Akun (Role)</th>
                        <th class="px-5 py-3.5">Status Akun</th>
                        <th class="px-5 py-3.5">Terdaftar Sejak</th>
                        <th class="px-5 py-3.5 text-right">Ubah Peran & Akses</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($users as $u)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-900 text-sm flex items-center">
                                    <div class="w-7 h-7 rounded-full bg-cyan-100 text-cyan-700 font-bold flex items-center justify-center text-xs mr-2">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                    {{ $u->name }}
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5 ml-9">{{ $u->phone ?? '-' }}</div>
                            </td>

                            <td class="px-5 py-3.5 font-mono text-slate-600">
                                {{ $u->email }}
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                                    {{ $u->role === 'super_admin' ? 'bg-purple-100 text-purple-800 border border-purple-200' : '' }}
                                    {{ $u->role === 'admin' ? 'bg-indigo-100 text-indigo-800 border border-indigo-200' : '' }}
                                    {{ $u->role === 'driver' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : '' }}
                                    {{ $u->role === 'customer' ? 'bg-slate-100 text-slate-700 border border-slate-200' : '' }}">
                                    {{ str_replace('_', ' ', $u->role) }}
                                </span>
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $u->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                    {{ $u->is_active ? 'Aktif' : 'Dinonaktifkan' }}
                                </span>
                            </td>

                            <td class="px-5 py-3.5 text-slate-400 whitespace-nowrap">
                                {{ $u->created_at->format('d M Y') }}
                            </td>

                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end space-x-2">
                                    <!-- Role update dropdown form -->
                                    <form method="POST" action="{{ route('admin.users.role', $u) }}" class="inline-flex items-center">
                                        @csrf
                                        @method('PUT')
                                        <select name="role" onchange="this.form.submit()" class="text-xs border border-slate-200 rounded-lg p-1 bg-white font-medium">
                                            <option value="customer" {{ $u->role === 'customer' ? 'selected' : '' }}>Pelanggan</option>
                                            <option value="driver" {{ $u->role === 'driver' ? 'selected' : '' }}>Kurir (Driver)</option>
                                            <option value="admin" {{ $u->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                            <option value="super_admin" {{ $u->role === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                        </select>
                                    </form>

                                    <!-- Toggle Active status -->
                                    @if ($u->id !== Auth::id())
                                        <form method="POST" action="{{ route('admin.users.toggle-active', $u) }}" class="inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="text-xs font-semibold px-2 py-1 rounded-lg {{ $u->is_active ? 'text-rose-600 hover:bg-rose-50' : 'text-emerald-600 hover:bg-emerald-50' }}">
                                                {{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                                Tidak ada data pengguna.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $users->links() }}
        </div>
    </div>

</div>
@endsection
