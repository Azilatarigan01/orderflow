<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-slate-900 tracking-tight leading-tight">
                    Terbitkan Purchase Order (PO) Resmi
                </h2>
                <p class="text-sm text-slate-500 mt-0.5">
                    Konversi penetapan penawaran vendor menjadi surat pesanan kontrak pembelian untuk PR: <strong class="font-mono text-indigo-600">{{ $purchaseRequest->pr_number }}</strong>
                </p>
            </div>
            <a href="{{ route('quotations.compare', $purchaseRequest) }}" class="px-3.5 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-semibold transition">
                Batal & Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if ($errors->any())
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
                    <p class="font-bold">Mohon perbaiki data sebelum menerbitkan PO:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(isset($existingPos) && $existingPos->count() > 0)
                <div class="p-4 rounded-xl bg-sky-50 border border-sky-200 text-sky-950 text-xs space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 font-bold text-sky-900">
                            <svg class="w-5 h-5 text-sky-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            <span>Alur Pengadaan Multi-Vendor (Split PO): Sudah ada {{ $existingPos->count() }} PO diterbitkan untuk PR ini.</span>
                        </div>
                        <span class="px-2 py-0.5 rounded font-mono text-[10px] bg-sky-100 text-sky-800 font-bold">Split PO Mode</span>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap pt-1 border-t border-sky-200/60">
                        @foreach($existingPos as $poItem)
                            <a href="{{ route('purchase-orders.show', $poItem) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white border border-sky-300 rounded-lg text-sky-800 font-mono text-[11px] hover:bg-sky-100/80 transition">
                                <span class="font-bold">{{ $poItem->po_number }}</span>
                                <span class="text-slate-500">({{ $poItem->vendor?->name }})</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(isset($selectedQuotations) && $selectedQuotations->count() > 1)
                <div class="p-4 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-950 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <span class="font-bold text-indigo-900">Pilih Rekanan Vendor untuk Penerbitan PO:</span>
                        <p class="text-indigo-700 text-[11px] mt-0.5">PR ini memiliki {{ $selectedQuotations->count() }} vendor terpilih untuk item terpisah. Klik vendor di sebelah kanan untuk berganti formulir PO.</p>
                    </div>
                    <div class="flex items-center gap-1.5 flex-wrap">
                        @foreach($selectedQuotations as $sq)
                            <a href="{{ route('purchase-orders.create', ['purchase_request_id' => $purchaseRequest->id, 'quotation_id' => $sq->id]) }}"
                                class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold transition flex items-center gap-1.5 {{ $selectedQuotation->id === $sq->id ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-indigo-800 border border-indigo-300 hover:bg-indigo-100' }}">
                                <span>{{ $sq->vendor?->name }}</span>
                                @if($sq->purchaseOrder)
                                    <span class="text-[9px] px-1 rounded bg-emerald-500 text-white font-mono">PO Terbit</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('purchase-orders.store') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="purchase_request_id" value="{{ $purchaseRequest->id }}">
                <input type="hidden" name="quotation_id" value="{{ $selectedQuotation->id }}">

                <!-- Vendor Information Card (Locked from Quotation) -->
                <div class="bg-indigo-950 text-white rounded-2xl p-6 shadow-xl border border-indigo-900/50">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-indigo-900/80 pb-4">
                        <div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-indigo-500/20 text-indigo-300 border border-indigo-400/30">
                                Rekanan Vendor Terpilih
                            </span>
                            <h3 class="text-xl font-extrabold text-white mt-1">{{ $selectedQuotation->vendor?->name }}</h3>
                            <p class="text-xs text-slate-300 mt-0.5">
                                Kategori: {{ $selectedQuotation->vendor?->category }} &bull; Kontak: {{ $selectedQuotation->vendor?->contact_person }} ({{ $selectedQuotation->vendor?->phone }})
                            </p>
                        </div>
                        <div class="text-left md:text-right">
                            <p class="text-[11px] text-slate-400 uppercase font-bold tracking-wider">Referensi Surat Penawaran</p>
                            <p class="font-mono font-bold text-white text-sm mt-0.5">No: {{ $selectedQuotation->quotation_number ?? 'QTO-REF' }}</p>
                            <p class="text-[11px] text-emerald-400 mt-0.5 font-bold">Skor Evaluasi: {{ number_format($selectedQuotation->score, 1) }}/100</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 text-xs">
                        <div>
                            <span class="text-slate-400 block text-[11px]">NPWP Vendor:</span>
                            <span class="font-mono text-white">{{ $selectedQuotation->vendor?->tax_number ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Rekening Bank:</span>
                            <span class="font-mono text-white">{{ $selectedQuotation->vendor?->bank_name }} - {{ $selectedQuotation->vendor?->bank_account_no }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Garansi Penawaran:</span>
                            <span class="text-white">{{ $selectedQuotation->warranty_months }} Bulan ({{ $selectedQuotation->warranty_info ?? 'Resmi' }})</span>
                        </div>
                    </div>
                </div>

                <!-- Terms & Delivery Parameter Form -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
                    <div class="border-b border-slate-100 pb-3">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900">1. Ketentuan Kontrak & Waktu Pengiriman</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Tentukan batas waktu pengiriman dan klausul pembayaran resmi.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Syarat Pembayaran (Payment Terms) <span class="text-rose-500">*</span>
                            </label>
                            <select name="payment_terms" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-indigo-500">
                                <option value="Net 30 Days" {{ old('payment_terms') === 'Net 30 Days' ? 'selected' : '' }}>Net 30 Hari (Setelah Invoice Diterima)</option>
                                <option value="Net 14 Days" {{ old('payment_terms') === 'Net 14 Days' ? 'selected' : '' }}>Net 14 Hari</option>
                                <option value="Cash On Delivery (COD)" {{ old('payment_terms') === 'Cash On Delivery (COD)' ? 'selected' : '' }}>COD (Bayar Saat Barang Diterima)</option>
                                <option value="DP 50% - Pelunasan Saat Selesai" {{ old('payment_terms') === 'DP 50% - Pelunasan Saat Selesai' ? 'selected' : '' }}>DP 50% - Pelunasan Saat Selesai</option>
                                <option value="Advance Payment (100% di Muka)" {{ old('payment_terms') === 'Advance Payment (100% di Muka)' ? 'selected' : '' }}>100% Bayar di Muka</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Target Tanggal Barang Tiba di Kantor
                            </label>
                            <input type="date" name="delivery_target_date"
                                value="{{ old('delivery_target_date', $purchaseRequest->required_date->format('Y-m-d')) }}"
                                min="{{ date('Y-m-d') }}"
                                class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-indigo-500">
                            <p class="text-[11px] text-slate-400 mt-1">Estimasi vendor: {{ $selectedQuotation->estimated_delivery_days }} hari kerja.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Status Penerbitan <span class="text-rose-500">*</span>
                            </label>
                            <select name="status" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-indigo-500">
                                <option value="issued" selected>Langsung Diterbitkan (Issued)</option>
                                <option value="draft">Simpan Sebagai Draf</option>
                            </select>
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Instruksi Khusus Pengiriman & Penagihan (PO Notes)
                            </label>
                            <textarea name="notes" rows="2" placeholder="Cth: Harap cantumkan nomor PO ini pada Surat Jalan dan Faktur Pajak. Pengiriman dilakukan pada jam kerja (09.00 - 17.00 WIB)."
                                class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Kebijakan Pajak (PPN) & Toleransi Over-Delivery -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
                    <div class="border-b border-slate-100 pb-3">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900">2. Konfigurasi Perpajakan (PPN) & Toleransi Pengadaan</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Atur mode kalkulasi pajak, batas selisih pembulatan, dan persentase toleransi kelebihan kuantitas di gudang.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Mode Kalkulasi PPN <span class="text-rose-500">*</span>
                            </label>
                            <select name="tax_calculation_mode" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-indigo-500">
                                <option value="line_item" {{ old('tax_calculation_mode', 'line_item') === 'line_item' ? 'selected' : '' }}>
                                    Line Item (Rincian per Baris Barang)
                                </option>
                                <option value="header_subtotal" {{ old('tax_calculation_mode') === 'header_subtotal' ? 'selected' : '' }}>
                                    Header Subtotal (Akumulasi Total Subtotal)
                                </option>
                            </select>
                            <p class="text-[11px] text-slate-400 mt-1">Gunakan 'Line Item' jika Faktur Pajak vendor merinci PPN per SKU.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Tarif PPN (Tax Rate) <span class="text-rose-500">*</span>
                            </label>
                            <select name="tax_rate" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-indigo-500">
                                <option value="11" {{ old('tax_rate', '11') == '11' ? 'selected' : '' }}>PPN 11% (Standar Berlaku)</option>
                                <option value="12" {{ old('tax_rate') == '12' ? 'selected' : '' }}>PPN 12% (Tarif Baru UU HPP)</option>
                                <option value="0" {{ old('tax_rate') == '0' ? 'selected' : '' }}>PPN 0% (Bebas Pajak / Rekanan Non-PKP)</option>
                            </select>
                            <p class="text-[11px] text-slate-400 mt-1">Tarif pajak yang dikenakan pada kontrak pembelian.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Toleransi Over-Delivery Gudang <span class="text-rose-500">*</span>
                            </label>
                            <div class="flex items-center">
                                <input type="number" name="over_delivery_tolerance_percentage" value="{{ old('over_delivery_tolerance_percentage', 5) }}" min="0" max="50" step="0.5" required
                                    class="w-full px-3 py-2 text-xs border border-slate-300 rounded-l-xl focus:ring-1 focus:ring-indigo-500">
                                <span class="px-3 py-2 text-xs bg-slate-100 border border-l-0 border-slate-300 text-slate-600 rounded-r-xl font-bold">%</span>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">Batas kelebihan fisik tiba yang diizinkan (Cth: 5% = pesan 100 maks terima 105).</p>
                        </div>
                    </div>

                    <div class="p-3 bg-indigo-50/60 rounded-xl border border-indigo-100 flex items-start gap-2.5 text-xs text-indigo-950">
                        <span class="text-indigo-600 font-bold">ℹ️</span>
                        <div>
                            <span class="font-bold">Rounding Tolerance PPN Otomatis:</span>
                            <span class="text-indigo-900">Sistem mengizinkan toleransi selisih pembulatan desimal s/d <strong>Rp 100</strong> antara perhitungan sistem dan Faktur Pajak vendor untuk mencegah kegagalan rekonsiliasi finance.</span>
                        </div>
                    </div>
                </div>

                <!-- Locked Items & Prices from Quotation -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
                    <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900">2. Rincian Barang & Nilai Kesepakatan Kontrak</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Terkunci otomatis sesuai surat penawaran harga vendor yang telah disetujui.</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-700">
                            <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] font-bold border-b border-slate-200">
                                <tr>
                                    <th class="px-3 py-2.5 text-center w-10">No</th>
                                    <th class="px-4 py-2.5">Item Barang / Jasa</th>
                                    <th class="px-4 py-2.5">Spesifikasi</th>
                                    <th class="px-4 py-2.5 text-center w-20">Kuantitas</th>
                                    <th class="px-4 py-2.5 text-right w-44">Harga Satuan Disepakati</th>
                                    <th class="px-4 py-2.5 text-right w-44">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($selectedQuotation->items as $idx => $qItem)
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="px-3 py-3 text-center text-slate-400 font-mono font-bold">{{ $idx + 1 }}</td>
                                        <td class="px-4 py-3 font-bold text-slate-900">{{ $qItem->item_name }}</td>
                                        <td class="px-4 py-3 text-slate-500">{{ $qItem->specification ?? '-' }}</td>
                                        <td class="px-4 py-3 text-center font-bold font-mono">{{ $qItem->quantity }} {{ $qItem->unit }}</td>
                                        <td class="px-4 py-3 text-right font-mono">{{ $qItem->formatted_unit_price }}</td>
                                        <td class="px-4 py-3 text-right font-mono font-bold text-slate-900">{{ $qItem->formatted_subtotal }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Cost Summary Box -->
                    <div class="pt-4 border-t border-slate-200 flex justify-end">
                        <div class="w-full md:w-80 space-y-2 bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-slate-600">Subtotal Barang:</span>
                                <span class="font-mono font-bold text-slate-900">{{ $selectedQuotation->formatted_subtotal }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-slate-600">Ongkos Kirim:</span>
                                <span class="font-mono font-bold text-slate-900">{{ $selectedQuotation->formatted_shipping_cost }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-slate-600">Pajak (PPN/Tax):</span>
                                <span class="font-mono font-bold text-slate-900">{{ $selectedQuotation->formatted_tax_amount }}</span>
                            </div>
                            <div class="pt-2 border-t border-slate-200 flex items-center justify-between text-sm">
                                <span class="font-bold text-slate-900 uppercase tracking-wider text-xs">Total Kontrak PO:</span>
                                <span class="font-mono font-extrabold text-base text-indigo-700">{{ $selectedQuotation->formatted_grand_total }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-between p-4 bg-white rounded-2xl border border-slate-200 shadow-xs">
                    <p class="text-xs text-slate-500">
                        * Menerbitkan PO akan mengunci pesanan dan memungkinkan pencatatan kedatangan barang fisik (Goods Receipt).
                    </p>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('quotations.compare', $purchaseRequest) }}" class="px-5 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-xs rounded-xl shadow-xs transition">
                            Batal
                        </a>
                        <button type="submit"
                            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-indigo-600/20 transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Terbitkan Purchase Order</span>
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
