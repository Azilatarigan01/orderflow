<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-slate-900 tracking-tight leading-tight">
                    Faktur Vendor &amp; Rekonsiliasi 3-Way Match
                </h2>
                <p class="text-xs text-slate-500 mt-1">
                    Verifikasi tagihan vendor, rekonsiliasi otomatis PO vs GR/BAST vs Invoice, dan pencatatan pembayaran (P2P).
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('purchase-orders.index') }}" class="px-3.5 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold shadow-xs transition flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Daftar Purchase Order</span>
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

            @if(session('warning'))
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>{{ session('warning') }}</span>
                </div>
            @endif

            <!-- Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Faktur</p>
                        <h4 class="text-2xl font-mono font-extrabold text-slate-900 mt-1">{{ $metrics['total'] }}</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">Semua faktur terdaftar</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">3-Way Match Lolos</p>
                        <h4 class="text-2xl font-mono font-extrabold text-emerald-600 mt-1">{{ $metrics['matched'] }}</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">Kuantitas &amp; harga klop</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-rose-600">Selisih / Ditahan</p>
                        <h4 class="text-2xl font-mono font-extrabold text-rose-600 mt-1">{{ $metrics['mismatch'] }}</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">Mismatch Qty / Harga</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Belum Dibayar</p>
                        <h4 class="text-2xl font-mono font-extrabold text-amber-600 mt-1">{{ $metrics['unpaid'] }}</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">Menunggu pencairan</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-indigo-600">Lunas (Paid)</p>
                        <h4 class="text-2xl font-mono font-extrabold text-indigo-600 mt-1">{{ $metrics['paid'] }}</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">Selesai dibayar</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                </div>
            </div>

            <!-- Search & Filters -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
                <form method="GET" action="{{ route('invoices.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                    <div class="sm:col-span-2 relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari nomor faktur vendor, registrasi internal, nama vendor, atau PO..."
                            class="w-full pl-9 pr-4 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>

                    <div>
                        <select name="matching_status" class="w-full py-2 px-3 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-indigo-500">
                            <option value="">Semua Status 3-Way Match</option>
                            <option value="matched" {{ request('matching_status') === 'matched' ? 'selected' : '' }}>Lolos 3-Way Match</option>
                            <option value="mismatch_quantity" {{ request('matching_status') === 'mismatch_quantity' ? 'selected' : '' }}>Selisih Kuantitas (Qty)</option>
                            <option value="mismatch_price" {{ request('matching_status') === 'mismatch_price' ? 'selected' : '' }}>Selisih Harga</option>
                            <option value="unmatched" {{ request('matching_status') === 'unmatched' ? 'selected' : '' }}>Belum Dicocokkan</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <select name="payment_status" class="w-full py-2 px-3 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-indigo-500">
                            <option value="">Semua Status Pembayaran</option>
                            <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Belum Dibayar</option>
                            <option value="partially_paid" {{ request('payment_status') === 'partially_paid' ? 'selected' : '' }}>Dibayar Sebagian</option>
                            <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Lunas (Paid)</option>
                            <option value="disputed" {{ request('payment_status') === 'disputed' ? 'selected' : '' }}>Dispute / Ditahan</option>
                        </select>
                        <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold transition">
                            Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Invoices Table -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] font-bold border-b border-slate-200">
                            <tr>
                                <th class="px-5 py-3.5">Faktur Vendor &amp; Internal</th>
                                <th class="px-4 py-3.5">Referensi PO &amp; Rekanan</th>
                                <th class="px-4 py-3.5 text-right">Tagihan Bersih</th>
                                <th class="px-4 py-3.5 text-center">3-Way Match</th>
                                <th class="px-4 py-3.5 text-center">Status Bayar</th>
                                <th class="px-4 py-3.5 text-center">Jatuh Tempo</th>
                                <th class="px-5 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($invoices as $inv)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="px-5 py-4">
                                        <div class="font-mono font-extrabold text-slate-900">{{ $inv->invoice_number }}</div>
                                        <div class="text-[11px] font-mono text-indigo-600 mt-0.5">{{ $inv->internal_invoice_number }}</div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <a href="{{ route('purchase-orders.show', $inv->purchaseOrder) }}" class="font-mono font-bold text-indigo-600 hover:underline">
                                            {{ $inv->purchaseOrder?->po_number }}
                                        </a>
                                        <div class="text-[11px] text-slate-500 font-semibold">{{ $inv->vendor?->name }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        <div class="font-mono font-extrabold text-slate-900">{{ $inv->formatted_net_payable_amount }}</div>
                                        @if($inv->penalty_deduction > 0)
                                            <span class="text-[10px] text-rose-600 font-mono font-semibold block">
                                                -{{ $inv->formatted_penalty_deduction }} (Denda)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $inv->matching_status_badge_class }}">
                                            {{ $inv->matching_status_label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $inv->payment_status_badge_class }}">
                                            {{ $inv->payment_status_label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <div class="font-mono text-xs {{ $inv->due_date && today()->gt($inv->due_date) && $inv->payment_status !== 'paid' ? 'text-rose-600 font-bold' : 'text-slate-600' }}">
                                            {{ $inv->due_date ? $inv->due_date->format('d M Y') : '-' }}
                                        </div>
                                        <div class="text-[10px] text-slate-400">Tgl: {{ $inv->invoice_date->format('d M Y') }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <a href="{{ route('invoices.show', $inv) }}" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-[11px] font-bold shadow-xs transition">
                                            Rincian &amp; 3-Way Match
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                                        <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                                        <p class="font-medium text-slate-600">Belum ada faktur vendor terdaftar.</p>
                                        <p class="text-xs text-slate-400 mt-1">Buka Purchase Order yang telah memiliki bukti penerimaan barang (GR/BAST) untuk mendaftarkan faktur.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($invoices->hasPages())
                    <div class="p-4 border-t border-slate-100">
                        {{ $invoices->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
