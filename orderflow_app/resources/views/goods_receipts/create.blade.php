<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-slate-900 tracking-tight leading-tight">
                    Penerimaan Barang & Jasa (Goods Receipt / BAST)
                </h2>
                <p class="text-sm text-slate-500 mt-0.5">
                    Catat kedatangan fisik barang atau serah terima pekerjaan untuk PO: <strong class="font-mono text-indigo-600">{{ $purchaseOrder->po_number }}</strong>
                </p>
            </div>
            <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="px-3.5 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-semibold transition">
                Batal & Kembali ke PO
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6" x-data="{ receiptType: '{{ old('receipt_type', 'goods') }}' }">

            @if ($errors->any())
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
                    <p class="font-bold">Mohon perbaiki kendala berikut:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- PO Reference Card -->
            <div class="bg-indigo-950 text-white rounded-2xl p-6 shadow-xl border border-indigo-900/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-indigo-500/20 text-indigo-300 border border-indigo-400/30 font-mono">
                            {{ $purchaseOrder->po_number }}
                        </span>
                        <span class="text-xs text-slate-300">&bull; Rekanan: <strong class="text-white">{{ $purchaseOrder->vendor?->name }}</strong></span>
                    </div>
                    <h3 class="text-lg font-bold text-white tracking-tight">{{ $purchaseOrder->purchaseRequest?->title }}</h3>
                    <p class="text-xs text-slate-300">
                        Progres saat ini: <strong>{{ $purchaseOrder->total_received_quantity }}/{{ $purchaseOrder->total_ordered_quantity }} unit</strong> diterima ({{ $purchaseOrder->receipt_progress_percentage }}%).
                    </p>
                </div>
                <div class="text-left sm:text-right">
                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Nilai Kontrak</p>
                    <p class="text-xl font-mono font-extrabold text-white mt-0.5">{{ $purchaseOrder->formatted_grand_total }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('goods-receipts.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <input type="hidden" name="purchase_order_id" value="{{ $purchaseOrder->id }}">

                <!-- Selector: Tipe Penerimaan (Barang vs Jasa) -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
                    <div class="border-b border-slate-100 pb-3">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900">1. Jenis Penerimaan</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Pilih apakah Anda menerima barang fisik atau hasil serah terima pekerjaan jasa.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label class="relative flex items-center p-4 rounded-xl border-2 cursor-pointer transition"
                            :class="receiptType === 'goods' ? 'border-indigo-600 bg-indigo-50/30 ring-2 ring-indigo-500/20' : 'border-slate-200 hover:border-slate-300'">
                            <input type="radio" name="receipt_type" value="goods" x-model="receiptType" class="w-4 h-4 text-indigo-600">
                            <div class="ml-3">
                                <span class="block text-xs font-bold text-slate-900">📦 Penerimaan Barang Fisik (Goods Receipt)</span>
                                <span class="block text-[11px] text-slate-500 mt-0.5">Penerimaan paket fisik dengan Surat Jalan dari kurir vendor.</span>
                            </div>
                        </label>

                        <label class="relative flex items-center p-4 rounded-xl border-2 cursor-pointer transition"
                            :class="receiptType === 'service' ? 'border-indigo-600 bg-indigo-50/30 ring-2 ring-indigo-500/20' : 'border-slate-200 hover:border-slate-300'">
                            <input type="radio" name="receipt_type" value="service" x-model="receiptType" class="w-4 h-4 text-indigo-600">
                            <div class="ml-3">
                                <span class="block text-xs font-bold text-slate-900">💼 Serah Terima Jasa (BAST / Service Acceptance)</span>
                                <span class="block text-[11px] text-slate-500 mt-0.5">Penyelesaian pekerjaan layanan dengan dokumen Berita Acara.</span>
                            </div>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-3 border-t border-slate-100">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Tanggal Diterima di Kantor <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="received_date" value="{{ old('received_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required
                                class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Kondisi Fisik Paket / Hasil Kerja <span class="text-rose-500">*</span>
                            </label>
                            <select name="item_condition" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-indigo-500">
                                <option value="Baik (100% OK)" {{ old('item_condition') === 'Baik (100% OK)' ? 'selected' : '' }}>Baik (100% Sesuai Spesifikasi)</option>
                                <option value="Segel Utuh Tanpa Cacat" {{ old('item_condition') === 'Segel Utuh Tanpa Cacat' ? 'selected' : '' }}>Segel Utuh Tanpa Cacat</option>
                                <option value="Sebagian Rusak / Cacat" {{ old('item_condition') === 'Sebagian Rusak / Cacat' ? 'selected' : '' }}>Sebagian Rusak / Cacat (Perlu Penggantian)</option>
                                <option value="Perlu Uji Coba Lanjutan" {{ old('item_condition') === 'Perlu Uji Coba Lanjutan' ? 'selected' : '' }}>Perlu Uji Coba Lanjutan</option>
                            </select>
                        </div>

                        <!-- Goods: Nomor Surat Jalan -->
                        <div x-show="receiptType === 'goods'">
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Nomor Surat Jalan Vendor (Delivery Note)
                            </label>
                            <input type="text" name="delivery_note_no" value="{{ old('delivery_note_no') }}" placeholder="Cth: SJ/2026/09/889"
                                class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-indigo-500">
                        </div>

                        <!-- Goods: Upload Surat Jalan -->
                        <div x-show="receiptType === 'goods'" class="md:col-span-3">
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Upload Foto Fisik Paket / Surat Jalan Vendor
                            </label>
                            <input type="file" name="delivery_note_doc" accept=".pdf,.png,.jpg,.jpeg,.doc,.docx"
                                class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                        </div>

                        <!-- Service BAST Fields (Termin, Milestone, Progress %, Nominal) -->
                        <div x-show="receiptType === 'service'" class="md:col-span-3 space-y-4 bg-purple-50/40 p-5 rounded-2xl border border-purple-200"
                            x-data="{
                                prevProgress: {{ $previousServiceProgress }},
                                currentProgress: {{ old('progress_percentage', $remainingServiceProgress) }},
                                poTotal: {{ $purchaseOrder->grand_total }},
                                get cumulativeProgress() {
                                    return Math.min(100, Math.round((this.prevProgress + parseFloat(this.currentProgress || 0)) * 10) / 10);
                                },
                                get calculatedNominal() {
                                    return Math.round((parseFloat(this.currentProgress || 0) / 100) * this.poTotal);
                                },
                                formatRupiah(num) {
                                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
                                }
                            }">
                            
                            <!-- Progress Overview Tracker -->
                            <div class="p-4 bg-white rounded-xl border border-purple-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div class="space-y-1">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-purple-100 text-purple-800 border border-purple-300">
                                        ⚡ Skema Serah Terima Jasa Berbasis Termin / Progres
                                    </span>
                                    <h4 class="text-xs font-bold text-slate-800">
                                        Progres Disetujui Sebelumnya: <span class="text-purple-700 font-mono font-extrabold">{{ $previousServiceProgress }}%</span>
                                        &bull; Sisa Belum Diserahterimakan: <span class="text-slate-600 font-mono font-extrabold">{{ $remainingServiceProgress }}%</span>
                                    </h4>
                                    <p class="text-[11px] text-slate-500">
                                        Jasa tidak dihitung berdasarkan kuantitas fisik kardus/unit, melainkan melalui persentase capaian pekerjaan BAST.
                                    </p>
                                </div>
                                <div class="text-left md:text-right bg-purple-50 p-2.5 rounded-xl border border-purple-100">
                                    <span class="text-[10px] text-purple-600 uppercase font-bold tracking-wider block">Akumulasi Setelah Termin Ini:</span>
                                    <span class="text-lg font-mono font-extrabold text-purple-900" x-text="cumulativeProgress + '% / 100%'"></span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                        Nama / Tahap Termin <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" name="termin_name" value="{{ old('termin_name', $suggestedTerminName) }}" placeholder="Cth: Termin 1 (DP 30%) atau Serah Terima Akhir"
                                        class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-purple-500">
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                        Progres Termin Ini (%) <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="number" step="0.1" min="0.1" max="{{ $remainingServiceProgress }}" name="progress_percentage"
                                            x-model="currentProgress" required
                                            class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-purple-500 font-mono font-bold pr-8">
                                        <span class="absolute right-3 top-2 text-xs font-bold text-slate-400">%</span>
                                    </div>
                                    <span class="block text-[10px] text-slate-400 mt-0.5">Maksimum sisa: {{ $remainingServiceProgress }}%</span>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                        Nilai Klaim Termin Ini (Rp)
                                    </label>
                                    <input type="number" step="1" name="nominal_claimed" :value="calculatedNominal"
                                        class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-purple-500 font-mono font-bold">
                                    <span class="block text-[10px] text-slate-400 mt-0.5" x-text="'Estimasi: ' + formatRupiah(calculatedNominal)"></span>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                        Periode Jasa: Tanggal Mulai
                                    </label>
                                    <input type="date" name="service_period_start" value="{{ old('service_period_start') }}"
                                        class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-purple-500">
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                        Periode Jasa: Tanggal Selesai
                                    </label>
                                    <input type="date" name="service_period_end" value="{{ old('service_period_end') }}"
                                        class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-purple-500">
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                        Penanggung Jawab / Pengawas Jasa <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" name="acceptance_approver_name" value="{{ old('acceptance_approver_name') }}" placeholder="Cth: Budi Santoso (Lead IT / PIC Proyek)"
                                        class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-purple-500">
                                </div>

                                <div class="md:col-span-3">
                                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                        Deskripsi Hasil Pekerjaan & Deliverable yang Telah Selesai
                                    </label>
                                    <textarea name="service_deliverables" rows="2" placeholder="Jelaskan deliverable pekerjaan yang telah selesai dikerjakan sesuai Service Level Agreement (SLA)..."
                                        class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-purple-500">{{ old('service_deliverables') }}</textarea>
                                </div>

                                <div class="md:col-span-3">
                                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                        Upload Dokumen Berita Acara Serah Terima (BAST) & Laporan Progres
                                    </label>
                                    <input type="file" name="bast_document" accept=".pdf,.png,.jpg,.jpeg,.doc,.docx"
                                        class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-purple-100 file:text-purple-800 hover:file:bg-purple-200">
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Catatan Hasil Pemeriksaan / Inspeksi Barang
                            </label>
                            <textarea name="inspection_notes" rows="2" placeholder="Cth: Paket dibuka dan diperiksa bersama kurir ekspedisi. Fisik barang mulus dan nomor seri sesuai invoice."
                                class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-indigo-500">{{ old('inspection_notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Line Items Quantity Input -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
                    <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900">2. Kuantitas Barang yang Tiba Saat Ini</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Jumlah yang diterima tidak boleh melebihi sisa barang yang belum datang.</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-700">
                            <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] font-bold border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-2.5 text-center w-10">No</th>
                                    <th class="px-4 py-2.5">Item Barang / Jasa</th>
                                    <th class="px-4 py-2.5 text-center">Dipesan</th>
                                    <th class="px-4 py-2.5 text-center">Sudah Tiba</th>
                                    <th class="px-4 py-2.5 text-center">Sisa Pesanan</th>
                                    <th class="px-4 py-2.5 text-center w-36">Jumlah Diterima Hari Ini <span class="text-rose-500">*</span></th>
                                    <th class="px-4 py-2.5 text-center w-28">Barang Rusak</th>
                                    <th class="px-4 py-2.5">Catatan Item</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($purchaseOrder->items as $idx => $item)
                                    @php
                                        $remaining = $item->remaining_quantity;
                                    @endphp
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="px-4 py-3 text-center text-slate-400 font-mono font-bold">{{ $idx + 1 }}</td>
                                        <td class="px-4 py-3">
                                            <input type="hidden" name="items[{{ $idx }}][po_item_id]" value="{{ $item->id }}">
                                            <span class="font-bold text-slate-900">{{ $item->item_name }}</span>
                                            <span class="block text-[11px] text-slate-400">{{ $item->specification ?? '-' }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-center font-bold font-mono">{{ $item->quantity }} {{ $item->unit }}</td>
                                        <td class="px-4 py-3 text-center font-bold font-mono text-emerald-600">{{ $item->received_quantity }} {{ $item->unit }}</td>
                                        <td class="px-4 py-3 text-center font-bold font-mono {{ $remaining > 0 ? 'text-amber-600' : 'text-slate-400' }}">
                                            {{ $remaining }} {{ $item->unit }}
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($remaining > 0)
                                                @php
                                                    $uomOpts = $uomOptionsPerItem[$item->id] ?? [];
                                                @endphp
                                                <div class="flex items-center gap-1.5" x-data="{
                                                    qty: {{ old("items.$idx.raw_quantity_received", $remaining) }},
                                                    unit: '{{ old("items.$idx.received_unit", $item->unit) }}',
                                                    baseUnit: '{{ $item->unit }}'
                                                }">
                                                    <input type="number" step="any" name="items[{{ $idx }}][raw_quantity_received]"
                                                        x-model="qty"
                                                        value="{{ old("items.$idx.raw_quantity_received", $remaining) }}"
                                                        min="0" required
                                                        class="w-20 px-2 py-1.5 text-xs border border-slate-300 rounded-lg text-center font-mono font-extrabold focus:ring-1 focus:ring-indigo-500">
                                                    
                                                    @if(count($uomOpts) > 1)
                                                        <select name="items[{{ $idx }}][received_unit]" x-model="unit"
                                                            class="text-xs border border-slate-300 rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-indigo-500 font-semibold bg-slate-50">
                                                            @foreach($uomOpts as $code => $opt)
                                                                <option value="{{ $code }}" @selected(old("items.$idx.received_unit", $item->unit) === $code)>
                                                                    {{ $code }} {{ $opt['is_base'] ? '(PO)' : '' }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    @else
                                                        <input type="hidden" name="items[{{ $idx }}][received_unit]" value="{{ $item->unit }}">
                                                        <span class="text-xs font-mono font-bold text-slate-500 px-1">{{ $item->unit }}</span>
                                                    @endif
                                                </div>
                                                <span class="block text-[10px] text-slate-400 mt-1">Sisa PO: {{ $remaining }} {{ $item->unit }}</span>
                                            @else
                                                <input type="hidden" name="items[{{ $idx }}][raw_quantity_received]" value="0">
                                                <input type="hidden" name="items[{{ $idx }}][received_unit]" value="{{ $item->unit }}">
                                                <span class="text-center block text-emerald-600 font-bold text-[11px]">✓ Selesai</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($remaining > 0)
                                                <input type="number" step="any" name="items[{{ $idx }}][raw_quantity_rejected]"
                                                    value="{{ old("items.$idx.raw_quantity_rejected", 0) }}"
                                                    min="0"
                                                    class="w-full px-2 py-1.5 text-xs border border-slate-300 rounded-lg text-center font-mono text-rose-600 font-bold focus:ring-1 focus:ring-rose-500">
                                            @else
                                                <span class="text-center block text-slate-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="text" name="items[{{ $idx }}][notes]" value="{{ old("items.$idx.notes") }}" placeholder="Keterangan..."
                                                class="w-full px-2 py-1 text-xs border border-slate-300 rounded-lg">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-between p-4 bg-white rounded-2xl border border-slate-200 shadow-xs">
                    <p class="text-xs text-slate-500">
                        * Menyimpan penerimaan akan otomatis menghitung pemenuhan PO dan memperbarui status menjadi <strong>Partially Received</strong> atau <strong>Completed</strong>.
                    </p>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="px-5 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-xs rounded-xl shadow-xs transition">
                            Batal
                        </a>
                        <button type="submit"
                            class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-emerald-600/25 transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Simpan Penerimaan Barang / BAST</span>
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
