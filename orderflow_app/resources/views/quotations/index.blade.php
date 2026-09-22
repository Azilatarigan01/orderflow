<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-slate-900 tracking-tight leading-tight">
                    RFQ & Penawaran Vendor (Quotations)
                </h2>
                <p class="text-xs text-slate-500 mt-1">
                    Kelola Request for Quotation (RFQ), kumpulkan surat penawaran, bandingkan harga dan parameter vendor, serta tetapkan pemenang.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('vendors.index') }}" class="px-3.5 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold shadow-xs transition flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Direktori Vendor
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Message -->
            @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total PR Disetujui</p>
                        <h4 class="text-2xl font-mono font-extrabold text-slate-900 mt-1">{{ $metrics['total_approved'] }}</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">Siap proses pengadaan</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-amber-500">Belum Ada Quotation</p>
                        <h4 class="text-2xl font-mono font-extrabold text-amber-600 mt-1">{{ $metrics['needs_rfq'] }}</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">Perlu kumpulkan penawaran</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-sky-500">Siap Dikomparasi</p>
                        <h4 class="text-2xl font-mono font-extrabold text-sky-600 mt-1">{{ $metrics['has_quotations'] }}</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">Sudah ada penawaran vendor</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Vendor Ditetapkan</p>
                        <h4 class="text-2xl font-mono font-extrabold text-emerald-600 mt-1">{{ $metrics['vendor_awarded'] }}</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">Menuju Purchase Order (PO)</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                </div>
            </div>

            <!-- Filters & Search -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
                <form method="GET" action="{{ route('quotations.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-2 relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari nomor PR atau judul pengadaan..."
                            class="w-full pl-9 pr-4 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>

                    <div class="flex items-center gap-2">
                        <select name="status" class="w-full py-2 px-3 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-indigo-500">
                            <option value="">Semua Status Pengadaan</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui (Approved)</option>
                            <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Vendor Ditetapkan (Processing)</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai (Completed)</option>
                        </select>
                        <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold transition">
                            Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- RFQ PR Table -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Daftar Pengadaan Membutuhkan Vendor</h3>
                        <p class="text-xs text-slate-500 mt-0.5">PR yang telah selesai disetujui atasan divisi dan manajemen siap diproses ke rekanan vendor.</p>
                    </div>
                    <span class="text-xs font-bold text-slate-500 font-mono">Total: {{ $purchaseRequests->total() }} PR</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-50/80 text-slate-500 uppercase tracking-wider text-[10px] font-bold border-b border-slate-200">
                            <tr>
                                <th class="px-5 py-3.5">Nomor PR & Judul</th>
                                <th class="px-4 py-3.5">Divisi & Pemohon</th>
                                <th class="px-4 py-3.5 text-right">Estimasi Anggaran</th>
                                <th class="px-4 py-3.5 text-center">Status PR</th>
                                <th class="px-4 py-3.5 text-center">Quotation Vendor</th>
                                <th class="px-4 py-3.5">Vendor Terpilih</th>
                                <th class="px-5 py-3.5 text-right">Tindakan Procurement</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($purchaseRequests as $pr)
                                @php
                                    $selectedQ = $pr->selectedQuotation();
                                    $qCount = $pr->quotations->count();
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="px-5 py-4">
                                        <div class="font-mono font-bold text-indigo-600">{{ $pr->pr_number }}</div>
                                        <div class="font-semibold text-slate-900 mt-0.5 max-w-xs truncate">{{ $pr->title }}</div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">Dibutuhkan: {{ $pr->required_date->format('d M Y') }}</div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="font-semibold text-slate-900">{{ $pr->department?->name ?? '-' }}</div>
                                        <div class="text-[11px] text-slate-500">{{ $pr->user?->name ?? 'Pemohon' }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-right font-mono font-bold text-slate-900">
                                        {{ $pr->formatted_estimated_total }}
                                        @if($pr->estimated_total > 10000000)
                                            <span class="block text-[10px] text-amber-600 font-sans font-medium">Wajib min. 2 Quotation</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $pr->status_badge_class }}">
                                            {{ $pr->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        @if($qCount === 0)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                0 Penawaran
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                                {{ $qCount }} Penawaran Masuk
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        @if($selectedQ)
                                            <div class="font-bold text-emerald-700 flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                {{ $selectedQ->vendor?->name ?? 'Vendor' }}
                                            </div>
                                            <div class="text-[10px] font-mono text-slate-500">{{ $selectedQ->formatted_grand_total }}</div>
                                        @else
                                            <span class="text-xs text-slate-400 italic">Belum ditetapkan</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                            @if(Auth::user()->hasRole(['procurement', 'admin']))
                                                <a href="{{ route('quotations.create', $pr) }}"
                                                    class="px-2.5 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-lg text-[11px] font-semibold transition" title="Tambah Penawaran Vendor">
                                                    + Quotation
                                                </a>
                                            @endif
                                            <a href="{{ route('quotations.compare', $pr) }}"
                                                class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-[11px] font-bold shadow-xs transition flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                                <span>Bandingkan</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                        <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                        <p class="font-semibold text-slate-600">Tidak ada pengajuan yang membutuhkan quotation saat ini.</p>
                                        <p class="text-xs text-slate-400 mt-1">PR harus berstatus 'Approved' (disetujui atasan) sebelum penawaran vendor dapat dicatat.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($purchaseRequests->hasPages())
                    <div class="p-4 border-t border-slate-100">
                        {{ $purchaseRequests->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
