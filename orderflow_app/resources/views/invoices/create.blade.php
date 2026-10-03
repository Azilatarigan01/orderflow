<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-slate-900 tracking-tight leading-tight">
                    Pendaftaran Faktur Vendor &amp; Verifikasi 3-Way Match
                </h2>
                <p class="text-xs text-slate-500 mt-1">
                    Registrasi tagihan invoice dari rekanan vendor untuk Purchase Order: <span class="font-mono font-bold text-indigo-600">{{ $purchaseOrder->po_number }}</span>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="px-3.5 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold shadow-xs transition">
                    Kembali ke Detail PO
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if($errors->any())
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
                    <div class="font-bold flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Terdapat kesalahan validasi input:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 pl-2">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- 3-Way Match Governance Rule Alert -->
            <div class="p-5 rounded-2xl bg-indigo-50/70 border border-indigo-200 text-slate-700 text-xs space-y-2">
                <div class="flex items-center gap-2 text-indigo-900 font-bold">
                    <svg class="w-5 h-5 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Standar Validasi 3-Way Matching (PO &bull; GR/BAST &bull; Invoice)</span>
                </div>
                <p class="leading-relaxed text-slate-600">
                    Sistem akan secara otomatis mencocokkan tagihan vendor dengan bukti tanda terima gudang (Goods Receipt) dan kontrak Purchase Order:
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                    <div class="bg-white p-3 rounded-xl border border-indigo-100">
                        <span class="font-bold text-slate-900 block mb-0.5">1. Kontrak PO</span>
                        <span class="text-[11px] text-slate-500">Harga satuan faktur tidak boleh melebihi harga yang disetujui pada PO.</span>
                    </div>
                    <div class="bg-white p-3 rounded-xl border border-indigo-100">
                        <span class="font-bold text-slate-900 block mb-0.5">2. Bukti Fisik (GR / BAST)</span>
                        <span class="text-[11px] text-slate-500">Kuantitas yang ditagih tidak boleh melebihi jumlah barang/jasa yang sudah diterima.</span>
                    </div>
                    <div class="bg-white p-3 rounded-xl border border-indigo-100">
                        <span class="font-bold text-slate-900 block mb-0.5">3. Klausul Denda Overdue</span>
                        <span class="text-[11px] text-slate-500">Jika vendor terlambat, denda keterlambatan (1‰/hari maks 5%) akan otomatis dipotong.</span>
                    </div>
                </div>
            </div>

            <!-- Overdue Penalty Notice if PO is Overdue -->
            @if($purchaseOrder->is_overdue)
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-300 text-amber-900 text-xs flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <div>
                            <span class="font-bold">Peringatan: PO Ini Mengalami Keterlambatan Pengiriman ({{ $purchaseOrder->overdue_days }} Hari).</span>
                            <span class="block text-[11px] text-amber-800">Klausul denda keterlambatan sebesar <strong>{{ $purchaseOrder->formatted_estimated_penalty }} ({{ $purchaseOrder->penalty_percentage }}%)</strong> akan otomatis dipotong dari total tagihan bersih (Net Payable).</span>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 bg-amber-200 text-amber-900 font-bold rounded-lg text-[10px] font-mono whitespace-nowrap">
                        Denda Otomatis Aktif
                    </span>
                </div>
            @endif

            <form method="POST" action="{{ route('invoices.store') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="purchase_order_id" value="{{ $purchaseOrder->id }}">

                <!-- Header Information Card -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-3">
                        Informasi Header Dokumen Faktur
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                        <div>
                            <label class="block text-slate-600 font-bold mb-1">Nomor Faktur Vendor *</label>
                            <input type="text" name="invoice_number" value="{{ old('invoice_number') }}" required
                                placeholder="Contoh: INV-2026/09/8812"
                                class="w-full text-xs font-mono border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-slate-600 font-bold mb-1">Tanggal Faktur *</label>
                            <input type="date" name="invoice_date" value="{{ old('invoice_date', now()->toDateString()) }}" required
                                class="w-full text-xs border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-slate-600 font-bold mb-1">Tanggal Jatuh Tempo *</label>
                            <input type="date" name="due_date" value="{{ old('due_date', now()->addDays(30)->toDateString()) }}" required
                                class="w-full text-xs border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-slate-600 font-bold mb-1">Rekanan Vendor</label>
                            <div class="p-2 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-800 truncate">
                                {{ $purchaseOrder->vendor?->name }}
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-slate-600 font-bold mb-1 text-xs">Catatan Faktur / Nomor Surat Tagihan Vendor</label>
                        <textarea name="notes" rows="2" placeholder="Catatan tambahan dari faktur vendor..."
                            class="w-full text-xs border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <!-- Line Item Matching Matrix -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900">Rincian Item yang Ditagihkan (Line Item Matching)</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Isi kuantitas dan harga satuan sesuai fisik dokumen faktur yang dikirimkan vendor.</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-700">
                            <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] font-bold border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3 text-center w-10">No</th>
                                    <th class="px-4 py-3">Nama Barang / Jasa</th>
                                    <th class="px-4 py-3 text-center">Qty Dipesan (PO)</th>
                                    <th class="px-4 py-3 text-center">Fisik Diterima (GR)</th>
                                    <th class="px-4 py-3 text-center w-36">Qty Ditagih *</th>
                                    <th class="px-4 py-3 text-right">Harga PO</th>
                                    <th class="px-4 py-3 text-right w-44">Harga Faktur *</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($purchaseOrder->items as $idx => $item)
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="px-4 py-3.5 text-center text-slate-400 font-mono font-bold">{{ $idx + 1 }}</td>
                                        <td class="px-4 py-3.5 font-bold text-slate-900">
                                            {{ $item->item_name }}
                                            <input type="hidden" name="items[{{ $idx }}][po_item_id]" value="{{ $item->id }}">
                                        </td>
                                        <td class="px-4 py-3.5 text-center font-mono font-semibold text-slate-600">
                                            {{ $item->quantity }} {{ $item->unit }}
                                        </td>
                                        <td class="px-4 py-3.5 text-center font-mono font-bold {{ $item->received_quantity > 0 ? 'text-emerald-600' : 'text-slate-400' }}">
                                            {{ $item->received_quantity }} {{ $item->unit }}
                                        </td>
                                        <td class="px-4 py-3.5 text-center">
                                            <input type="number" step="any" min="0"
                                                name="items[{{ $idx }}][quantity_invoiced]"
                                                value="{{ old('items.'.$idx.'.quantity_invoiced', $item->received_quantity > 0 ? $item->received_quantity : $item->quantity) }}"
                                                class="w-28 text-center text-xs font-mono font-bold border border-slate-300 rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-indigo-500" required>
                                        </td>
                                        <td class="px-4 py-3.5 text-right font-mono text-slate-600">
                                            {{ $item->formatted_unit_price }}
                                        </td>
                                        <td class="px-4 py-3.5 text-right">
                                            <input type="number" step="any" min="0"
                                                name="items[{{ $idx }}][unit_price]"
                                                value="{{ old('items.'.$idx.'.unit_price', $item->unit_price) }}"
                                                class="w-36 text-right text-xs font-mono font-bold border border-slate-300 rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-indigo-500" required>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="flex items-center justify-end gap-3 pt-3">
                    <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/20 transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Daftarkan Faktur &amp; Jalankan 3-Way Match</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
