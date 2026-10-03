<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="font-mono font-extrabold text-2xl text-slate-900 tracking-tight leading-tight">
                        {{ $invoice->invoice_number }}
                    </h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $invoice->matching_status_badge_class }}">
                        {{ $invoice->matching_status_label }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $invoice->payment_status_badge_class }}">
                        {{ $invoice->payment_status_label }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Registrasi Internal: <span class="font-mono font-bold text-slate-700">{{ $invoice->internal_invoice_number }}</span> &bull; Referensi PO: <a href="{{ route('purchase-orders.show', $invoice->purchaseOrder) }}" class="font-mono text-indigo-600 font-bold hover:underline">{{ $invoice->purchaseOrder?->po_number }}</a>
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('invoices.index') }}" class="px-3.5 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold shadow-xs transition">
                    Kembali ke Daftar Faktur
                </a>
                @if(Auth::user()->hasRole(['finance', 'admin']) && $invoice->payment_status !== 'paid')
                    <button type="button" onclick="document.getElementById('payment-modal').classList.remove('hidden')"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>+ Catat Pembayaran Finance</span>
                    </button>
                @endif
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

            @if(session('error'))
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- 3-Way Matching Status Card -->
            <div class="p-6 rounded-2xl border {{ $invoice->matching_status === 'matched' ? 'bg-emerald-50/70 border-emerald-200' : 'bg-rose-50/70 border-rose-200' }} shadow-xs space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="p-3 {{ $invoice->matching_status === 'matched' ? 'bg-emerald-600' : 'bg-rose-600' }} text-white rounded-xl shadow-xs">
                            @if($invoice->matching_status === 'matched')
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @else
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            @endif
                        </div>
                        <div>
                            <h4 class="text-sm font-extrabold {{ $invoice->matching_status === 'matched' ? 'text-emerald-950' : 'text-rose-950' }} uppercase tracking-wider">
                                Hasil Rekonsiliasi 3-Way Match: {{ $invoice->matching_status_label }}
                            </h4>
                            <p class="text-xs {{ $invoice->matching_status === 'matched' ? 'text-emerald-800' : 'text-rose-800' }} mt-0.5">
                                {{ $invoice->matching_notes }}
                            </p>
                        </div>
                    </div>

                    <div class="text-left sm:text-right">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Bersih yang Wajib Dibayar</span>
                        <p class="text-2xl font-mono font-extrabold text-indigo-700 mt-0.5">{{ $invoice->formatted_net_payable_amount }}</p>
                    </div>
                </div>

                <!-- Financial Breakdown -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 pt-3 border-t {{ $invoice->matching_status === 'matched' ? 'border-emerald-200' : 'border-rose-200' }} text-xs">
                    <div class="bg-white/80 p-3 rounded-xl border border-slate-200/80">
                        <span class="text-slate-400 text-[11px] block">Subtotal Tagihan:</span>
                        <span class="font-bold font-mono text-slate-900">{{ $invoice->formatted_subtotal }}</span>
                    </div>
                    <div class="bg-white/80 p-3 rounded-xl border border-slate-200/80">
                        <span class="text-slate-400 text-[11px] block">PPN (Pajak):</span>
                        <span class="font-bold font-mono text-slate-900">{{ $invoice->formatted_tax_amount }}</span>
                    </div>
                    <div class="bg-white/80 p-3 rounded-xl border border-slate-200/80">
                        <span class="text-slate-400 text-[11px] block">Potongan Denda Overdue:</span>
                        <span class="font-bold font-mono {{ $invoice->penalty_deduction > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                            {{ $invoice->penalty_deduction > 0 ? '-' . $invoice->formatted_penalty_deduction : 'Rp 0' }}
                        </span>
                    </div>
                    <div class="bg-white/80 p-3 rounded-xl border border-slate-200/80">
                        <span class="text-slate-400 text-[11px] block">Telah Dibayarkan:</span>
                        <span class="font-bold font-mono text-emerald-600">{{ $invoice->formatted_paid_amount }}</span>
                    </div>
                    <div class="bg-white/80 p-3 rounded-xl border border-slate-200/80">
                        <span class="text-slate-400 text-[11px] block">Sisa Kewajiban:</span>
                        <span class="font-bold font-mono text-indigo-700">{{ $invoice->formatted_remaining_amount }}</span>
                    </div>
                    <div class="bg-white/80 p-3 rounded-xl border border-slate-200/80">
                        <span class="text-slate-400 text-[11px] block">Jatuh Tempo:</span>
                        <span class="font-bold text-slate-900">{{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- 3-Way Line Item Matching Comparison Table -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900">Matriks Perbandingan 3-Way Matching</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Perbandingan kuantitas dan harga satuan antara Kontrak PO, Bukti Fisik GR/BAST, dan Faktur Vendor.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] font-bold border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3 text-center w-10">No</th>
                                <th class="px-4 py-3">Nama Barang / Jasa</th>
                                <th class="px-4 py-3 text-center">Dipesan (PO)</th>
                                <th class="px-4 py-3 text-center">Diterima Fisik (GR)</th>
                                <th class="px-4 py-3 text-center bg-indigo-50/50">Ditagih Faktur</th>
                                <th class="px-4 py-3 text-right">Harga PO</th>
                                <th class="px-4 py-3 text-right bg-indigo-50/50">Harga Faktur</th>
                                <th class="px-4 py-3 text-right">Subtotal</th>
                                <th class="px-4 py-3 text-center">Status Item</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($invoice->items as $idx => $invItem)
                                @php
                                    $poItem = $invItem->poItem;
                                    $qtyMismatch = $poItem && ($invItem->quantity_invoiced > $poItem->received_quantity);
                                    $priceMismatch = $poItem && ($invItem->unit_price > $poItem->unit_price);
                                @endphp
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-3.5 text-center text-slate-400 font-mono font-bold">{{ $idx + 1 }}</td>
                                    <td class="px-4 py-3.5 font-bold text-slate-900">{{ $invItem->item_name }}</td>
                                    <td class="px-4 py-3.5 text-center font-mono text-slate-600">
                                        {{ $poItem ? $poItem->quantity . ' ' . $poItem->unit : '-' }}
                                    </td>
                                    <td class="px-4 py-3.5 text-center font-mono font-bold text-emerald-600">
                                        {{ $poItem ? $poItem->received_quantity . ' ' . $poItem->unit : '-' }}
                                    </td>
                                    <td class="px-4 py-3.5 text-center font-mono font-bold bg-indigo-50/30 {{ $qtyMismatch ? 'text-rose-600 bg-rose-50' : 'text-slate-900' }}">
                                        {{ $invItem->quantity_invoiced }} {{ $poItem?->unit }}
                                        @if($qtyMismatch)
                                            <span class="block text-[10px] text-rose-600 font-bold">(Lebih dari GR)</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-right font-mono text-slate-600">
                                        {{ $poItem ? $poItem->formatted_unit_price : '-' }}
                                    </td>
                                    <td class="px-4 py-3.5 text-right font-mono font-bold bg-indigo-50/30 {{ $priceMismatch ? 'text-rose-600 bg-rose-50' : 'text-slate-900' }}">
                                        {{ $invItem->formatted_unit_price }}
                                        @if($priceMismatch)
                                            <span class="block text-[10px] text-rose-600 font-bold">(Lebih dari PO)</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-right font-mono font-bold text-slate-900">
                                        {{ $invItem->formatted_subtotal }}
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        @if(!$qtyMismatch && !$priceMismatch)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                ✓ Cocok
                                            </span>
                                        @elseif($qtyMismatch)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                                Selisih Qty
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                                Selisih Harga
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Payment Settlement History Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900">
                        Status Pembayaran &amp; Rekam Pencairan Dana (Settlement)
                    </h3>
                    <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $invoice->payment_status_badge_class }}">
                        {{ $invoice->payment_status_label }}
                    </span>
                </div>

                @if($invoice->payment_date)
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs">
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                            <span class="text-slate-400 text-[11px] block">Tanggal Pencairan:</span>
                            <span class="font-bold text-slate-900">{{ $invoice->payment_date->format('d M Y') }}</span>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                            <span class="text-slate-400 text-[11px] block">Metode Pembayaran:</span>
                            <span class="font-bold text-slate-900">{{ $invoice->payment_method }}</span>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                            <span class="text-slate-400 text-[11px] block">Bukti Transfer / No Referensi:</span>
                            <span class="font-bold font-mono text-indigo-700">{{ $invoice->payment_reference }}</span>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                            <span class="text-slate-400 text-[11px] block">Total Dana Ditransfer:</span>
                            <span class="font-bold font-mono text-emerald-600 text-sm">{{ $invoice->formatted_paid_amount }}</span>
                        </div>
                    </div>
                @else
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-slate-500 text-xs flex items-center justify-between">
                        <span>Belum ada rekaman pencairan pembayaran untuk faktur ini.</span>
                        @if(Auth::user()->hasRole(['finance', 'admin']))
                            <button type="button" onclick="document.getElementById('payment-modal').classList.remove('hidden')"
                                class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition">
                                Catat Pembayaran Sekarang
                            </button>
                        @endif
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- Modal Catat Pembayaran Finance -->
    <div id="payment-modal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 border border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2 text-indigo-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <h3 class="font-bold text-slate-900 text-sm">Catat Pembayaran Faktur (Disbursement)</h3>
                </div>
                <button type="button" onclick="document.getElementById('payment-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-lg leading-none">&times;</button>
            </div>

            <p class="text-xs text-slate-600 leading-relaxed">
                Pencairan dana kepada vendor <strong>{{ $invoice->vendor?->name }}</strong> untuk tagihan bersih sebesar <strong class="text-indigo-700 font-mono">{{ $invoice->formatted_net_payable_amount }}</strong>.
            </p>

            <form method="POST" action="{{ route('invoices.payment', $invoice) }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Jumlah yang Dibayarkan (Rp) *</label>
                    <input type="number" step="any" min="1" name="paid_amount" value="{{ old('paid_amount', $invoice->remaining_amount) }}" required
                        class="w-full text-xs font-mono font-bold border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-700 font-bold mb-1">Tanggal Transfer *</label>
                        <input type="date" name="payment_date" value="{{ old('payment_date', now()->toDateString()) }}" required
                            class="w-full text-xs border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-slate-700 font-bold mb-1">Metode Pembayaran *</label>
                        <select name="payment_method" required class="w-full text-xs border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500">
                            <option value="Bank Transfer">Bank Transfer (RTGS / BI-FAST)</option>
                            <option value="Giro">Bilyet Giro</option>
                            <option value="Cheque">Cek Perusahaan</option>
                            <option value="Tunai">Kas Kecil / Tunai</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-700 font-bold mb-1">Nomor Referensi Transfer / No Voucher *</label>
                    <input type="text" name="payment_reference" required placeholder="Contoh: TRF-BCA-20260927-0091"
                        class="w-full text-xs font-mono border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500">
                </div>

                @if($invoice->matching_status !== 'matched')
                    <div class="p-3 rounded-xl bg-amber-50 border border-amber-300 text-amber-900 space-y-1">
                        <label class="flex items-center gap-2 cursor-pointer font-bold">
                            <input type="checkbox" name="override_mismatch" value="1" class="rounded text-amber-600 focus:ring-amber-500">
                            <span>Setujui pencairan dana meskipun terdapat catatan selisih 3-Way Match</span>
                        </label>
                    </div>
                @endif

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('payment-modal').classList.add('hidden')"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 transition">
                        Konfirmasi Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
