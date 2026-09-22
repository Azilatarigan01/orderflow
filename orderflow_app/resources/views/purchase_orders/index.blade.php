<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-slate-900 tracking-tight leading-tight">
                    Surat Pesanan Pembelian (Purchase Orders)
                </h2>
                <p class="text-xs text-slate-500 mt-1">
                    Kontrak resmi pesanan pengadaan barang dan jasa kepada rekanan vendor terpilih.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('quotations.index') }}" class="px-3.5 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold shadow-xs transition flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Hub RFQ & Quotation
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Purchase Order</p>
                        <h4 class="text-2xl font-mono font-extrabold text-slate-900 mt-1">{{ $metrics['total'] }}</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">Semua PO terdaftar</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-indigo-500">Diterbitkan (Issued)</p>
                        <h4 class="text-2xl font-mono font-extrabold text-indigo-600 mt-1">{{ $metrics['issued'] }}</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">Menunggu pengiriman vendor</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-amber-500">Diterima Sebagian</p>
                        <h4 class="text-2xl font-mono font-extrabold text-amber-600 mt-1">{{ $metrics['partially_received'] }}</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">Pengiriman bertahap</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Selesai (Completed)</p>
                        <h4 class="text-2xl font-mono font-extrabold text-emerald-600 mt-1">{{ $metrics['completed'] }}</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">100% diterima lengkap</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
            </div>

            <!-- Search & Filters -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
                <form method="GET" action="{{ route('purchase-orders.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-2 relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari nomor PO, nama vendor, atau nomor PR..."
                            class="w-full pl-9 pr-4 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>

                    <div class="flex items-center gap-2">
                        <select name="status" class="w-full py-2 px-3 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-indigo-500">
                            <option value="">Semua Status PO</option>
                            <option value="issued" {{ request('status') === 'issued' ? 'selected' : '' }}>Diterbitkan (Issued)</option>
                            <option value="partially_received" {{ request('status') === 'partially_received' ? 'selected' : '' }}>Diterima Sebagian</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai (Completed)</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draf</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                        <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold transition">
                            Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Table of POs -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Daftar Purchase Order Resmi</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Surat pesanan pembelian yang mengikat vendor rekanan secara hukum.</p>
                    </div>
                    <span class="text-xs font-bold text-slate-500 font-mono">Total: {{ $purchaseOrders->total() }} PO</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-50/80 text-slate-500 uppercase tracking-wider text-[10px] font-bold border-b border-slate-200">
                            <tr>
                                <th class="px-5 py-3.5">Nomor PO & Tanggal</th>
                                <th class="px-4 py-3.5">Referensi PR</th>
                                <th class="px-4 py-3.5">Vendor Rekanan</th>
                                <th class="px-4 py-3.5 text-right">Nilai Kontrak (PO)</th>
                                <th class="px-4 py-3.5 text-center">Status PO</th>
                                <th class="px-4 py-3.5 text-center">Progres Penerimaan</th>
                                <th class="px-5 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($purchaseOrders as $po)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="px-5 py-4">
                                        <div class="font-mono font-extrabold text-indigo-600">{{ $po->po_number }}</div>
                                        <div class="text-[11px] text-slate-400 mt-0.5">Tanggal: {{ $po->order_date->format('d M Y') }}</div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="font-mono font-bold text-slate-700">{{ $po->purchaseRequest?->pr_number }}</div>
                                        <div class="text-[11px] text-slate-500 max-w-xs truncate">{{ $po->purchaseRequest?->title }}</div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="font-bold text-slate-900">{{ $po->vendor?->name }}</div>
                                        <div class="text-[11px] text-slate-400">Syarat: {{ $po->payment_terms }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-right font-mono font-extrabold text-slate-900">
                                        {{ $po->formatted_grand_total }}
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $po->status_badge_class }}">
                                            {{ $po->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <div class="w-28 mx-auto space-y-1">
                                            <div class="flex items-center justify-between text-[10px] font-mono text-slate-500">
                                                <span>{{ $po->total_received_quantity }}/{{ $po->total_ordered_quantity }} Unit</span>
                                                <span class="font-bold">{{ $po->receipt_progress_percentage }}%</span>
                                            </div>
                                            <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                                <div class="bg-emerald-500 h-1.5 rounded-full transition-all duration-300" style="width: {{ $po->receipt_progress_percentage }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="{{ route('purchase-orders.print', $po) }}" target="_blank"
                                                class="px-2.5 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-lg text-[11px] font-semibold transition" title="Cetak / Simpan PDF">
                                                🖨️ PDF
                                            </a>
                                            <a href="{{ route('purchase-orders.show', $po) }}"
                                                class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-[11px] font-bold shadow-xs transition">
                                                Detail PO & GR
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                        <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <p class="font-semibold text-slate-600">Belum ada Purchase Order yang diterbitkan.</p>
                                        <p class="text-xs text-slate-400 mt-1">Terbitkan PO dari PR yang sudah dipilih vendornya pada menu RFQ & Quotation.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($purchaseOrders->hasPages())
                    <div class="p-4 border-t border-slate-100">
                        {{ $purchaseOrders->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
