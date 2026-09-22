<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-slate-900 tracking-tight leading-tight">
                    Direktori Rekanan Vendor
                </h2>
                <p class="text-sm text-slate-500 mt-0.5">Basis data supplier dan penyedia jasa resmi rekanan perusahaan.</p>
            </div>
            @if(Auth::user()->hasRole(['procurement', 'admin']))
                <a href="{{ route('vendors.create') }}" class="inline-flex items-center px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs rounded-lg shadow-sm transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Rekanan Baru
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Filter & Search Bar -->
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                <form method="GET" action="{{ route('vendors.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                    <div class="md:col-span-6 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama badan usaha, kode vendor, atau nama PIC..." class="w-full pl-9 pr-4 py-2 text-xs border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900">
                    </div>

                    <div class="md:col-span-3">
                        <select name="category" class="w-full py-2 px-3 text-xs border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 text-slate-700">
                            <option value="">Semua Klasifikasi Bidang</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-3 flex gap-2">
                        <button type="submit" class="w-full px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-semibold transition">
                            Terapkan Filter
                        </button>
                        @if(request()->hasAny(['search', 'category', 'status']))
                            <a href="{{ route('vendors.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium transition flex items-center">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Vendors Table -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase font-bold text-slate-500 tracking-wider">
                            <tr>
                                <th class="px-6 py-3.5">Kode & Nama Badan Usaha</th>
                                <th class="px-6 py-3.5">Klasifikasi Bidang</th>
                                <th class="px-6 py-3.5">Penanggung Jawab (PIC)</th>
                                <th class="px-6 py-3.5">Skor Kinerja</th>
                                <th class="px-6 py-3.5">Status Legalitas</th>
                                <th class="px-6 py-3.5 text-right">Opsi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($vendors as $vendor)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="px-6 py-4 font-medium text-slate-900">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 rounded bg-slate-100 border border-slate-200 text-slate-700 font-bold flex items-center justify-center text-xs">
                                                {{ substr($vendor->code, -3) }}
                                            </div>
                                            <div>
                                                <a href="{{ route('vendors.show', $vendor) }}" class="font-semibold text-slate-900 hover:text-indigo-900 transition">
                                                    {{ $vendor->name }}
                                                </a>
                                                <div class="text-[11px] text-slate-400 font-mono">{{ $vendor->code }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ $vendor->category }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-slate-900 font-medium">{{ $vendor->contact_person }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $vendor->email }} &bull; {{ $vendor->phone }}</div>
                                    </td>
                                    <td class="px-6 py-4 font-mono font-semibold text-slate-800">
                                        {{ number_format($vendor->rating, 2) }} / 5.00
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($vendor->is_active)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span> Aktif Terverifikasi
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[11px] font-medium bg-rose-50 text-rose-700 border border-rose-200">
                                                Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-1.5">
                                        <a href="{{ route('vendors.show', $vendor) }}" class="inline-flex items-center px-2.5 py-1 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded text-xs font-medium transition">
                                            Detail
                                        </a>
                                        @if(Auth::user()->hasRole(['procurement', 'admin']))
                                            <a href="{{ route('vendors.edit', $vendor) }}" class="inline-flex items-center px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded text-xs font-medium transition">
                                                Ubah
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                        Tidak ditemukan rekanan vendor yang sesuai.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($vendors->hasPages())
                    <div class="p-4 border-t border-slate-100">
                        {{ $vendors->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
