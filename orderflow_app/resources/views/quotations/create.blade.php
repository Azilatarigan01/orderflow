<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-slate-900 tracking-tight leading-tight">
                    Input Penawaran Vendor (Quotation)
                </h2>
                <p class="text-sm text-slate-500 mt-0.5">
                    Catat penawaran harga resmi dari rekanan vendor untuk PR: <strong class="font-mono text-indigo-600">{{ $purchaseRequest->pr_number }}</strong>
                </p>
            </div>
            <a href="{{ route('quotations.compare', $purchaseRequest) }}" class="px-3.5 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-semibold transition">
                Batal & Kembali ke Matriks
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- PR Summary Card -->
            <div class="bg-indigo-950 text-white rounded-2xl p-6 shadow-xl border border-indigo-900/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-indigo-500/20 text-indigo-300 border border-indigo-400/30">
                        Referensi Pengajuan (PR)
                    </span>
                    <h3 class="text-lg font-bold text-white tracking-tight">{{ $purchaseRequest->title }}</h3>
                    <p class="text-xs text-slate-300 max-w-2xl leading-relaxed">
                        {{ $purchaseRequest->description }}
                    </p>
                </div>
                <div class="text-left md:text-right flex-shrink-0">
                    <p class="text-[11px] text-slate-400 uppercase font-bold tracking-wider">Estimasi Anggaran PR</p>
                    <p class="text-xl font-mono font-extrabold text-white mt-0.5">{{ $purchaseRequest->formatted_estimated_total }}</p>
                    <p class="text-[11px] text-indigo-300 mt-0.5">{{ $purchaseRequest->department?->name }} &bull; Target: {{ $purchaseRequest->required_date->format('d M Y') }}</p>
                </div>
            </div>

            @if ($errors->any())
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
                    <p class="font-bold">Mohon periksa kembali formulir penawaran:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('quotations.store', $purchaseRequest) }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Section 1: Profil Rekanan Vendor & Identitas Surat -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
                    <div class="border-b border-slate-100 pb-3">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900">1. Identitas Rekanan Vendor & Dokumen Penawaran</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Pilih rekanan resmi yang telah mengirimkan surat penawaran.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Pilih Vendor <span class="text-rose-500">*</span>
                            </label>
                            <select name="vendor_id" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-indigo-500">
                                <option value="">-- Pilih Vendor Aktif --</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                        {{ $vendor->name }} (Kategori: {{ $vendor->category }} &bull; Rating: ⭐ {{ number_format($vendor->rating, 1) }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-slate-400 mt-1">Hanya vendor berstatus aktif yang ditampilkan.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Nomor Surat Penawaran (Quotation No.)
                            </label>
                            <input type="text" name="quotation_number" value="{{ old('quotation_number') }}" placeholder="Cth: QTO/2026/IX/0042"
                                class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Masa Berlaku Penawaran (Valid Until)
                            </label>
                            <input type="date" name="valid_until" value="{{ old('valid_until') }}" min="{{ date('Y-m-d') }}"
                                class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-indigo-500">
                            <p class="text-[11px] text-slate-400 mt-1">Batas tanggal harga penawaran ini dijamin oleh vendor.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Upload Berkas PDF / Foto Penawaran
                            </label>
                            <input type="file" name="attachment" accept=".pdf,.png,.jpg,.jpeg,.doc,.docx,.xls,.xlsx"
                                class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                            <p class="text-[11px] text-slate-400 mt-1">Maks. 10MB (PDF, Gambar, Word, atau Excel).</p>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Parameter Pengiriman & Garansi -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
                    <div class="border-b border-slate-100 pb-3">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900">2. Parameter Pengiriman & Ketentuan Garansi</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Parameter ini akan memengaruhi skor evaluasi teknis sistem.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Estimasi Waktu Pengiriman <span class="text-rose-500">*</span>
                            </label>
                            <div class="flex items-center">
                                <input type="number" name="estimated_delivery_days" value="{{ old('estimated_delivery_days', 3) }}" min="1" required
                                    class="w-full px-3 py-2 text-xs border border-slate-300 rounded-l-xl focus:ring-1 focus:ring-indigo-500">
                                <span class="px-3 py-2 text-xs bg-slate-100 border border-l-0 border-slate-300 text-slate-600 rounded-r-xl font-medium">Hari Kerja</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Durasi Garansi (Bulan)
                            </label>
                            <div class="flex items-center">
                                <input type="number" name="warranty_months" value="{{ old('warranty_months', 12) }}" min="0"
                                    class="w-full px-3 py-2 text-xs border border-slate-300 rounded-l-xl focus:ring-1 focus:ring-indigo-500">
                                <span class="px-3 py-2 text-xs bg-slate-100 border border-l-0 border-slate-300 text-slate-600 rounded-r-xl font-medium">Bulan</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Keterangan Detail Garansi
                            </label>
                            <input type="text" name="warranty_info" value="{{ old('warranty_info', 'Garansi Resmi Distributor') }}" placeholder="Cth: 1 Tahun Part & Service"
                                class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-indigo-500">
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Catatan Khusus / Terms of Payment dari Vendor
                            </label>
                            <textarea name="notes" rows="2" placeholder="Cth: Syarat pembayaran Net 30 hari setelah invoice diterima, franco Jakarta."
                                class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Rincian Harga Item Barang & Ongkir -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
                    <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900">3. Rincian Harga Penawaran per Item</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Disesuaikan otomatis dengan daftar barang dari PR terkait.</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-700">
                            <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] font-bold border-b border-slate-200">
                                <tr>
                                    <th class="px-3 py-2 text-center w-10">No</th>
                                    <th class="px-3 py-2">Nama Barang / Jasa</th>
                                    <th class="px-3 py-2">Spesifikasi</th>
                                    <th class="px-3 py-2 text-center w-16">Qty</th>
                                    <th class="px-3 py-2 text-center w-20">Satuan</th>
                                    <th class="px-3 py-2 text-right w-44">Harga Satuan Penawaran <span class="text-rose-500">*</span></th>
                                    <th class="px-3 py-2 text-right w-40">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100" id="itemsBody">
                                @foreach($purchaseRequest->items as $idx => $item)
                                    <tr class="item-row hover:bg-slate-50/50">
                                        <td class="px-3 py-2.5 text-center text-slate-400 font-mono font-bold">{{ $idx + 1 }}</td>
                                        <td class="px-3 py-2.5">
                                            <input type="hidden" name="items[{{ $idx }}][purchase_request_item_id]" value="{{ $item->id }}">
                                            <input type="hidden" name="items[{{ $idx }}][item_name]" value="{{ $item->item_name }}">
                                            <span class="font-bold text-slate-900">{{ $item->item_name }}</span>
                                        </td>
                                        <td class="px-3 py-2.5 text-slate-500">
                                            <input type="hidden" name="items[{{ $idx }}][specification]" value="{{ $item->specification }}">
                                            <span>{{ $item->specification ?? '-' }}</span>
                                        </td>
                                        <td class="px-3 py-2.5 text-center font-bold">
                                            <input type="hidden" name="items[{{ $idx }}][quantity]" value="{{ $item->quantity }}" class="item-qty">
                                            <span>{{ $item->quantity }}</span>
                                        </td>
                                        <td class="px-3 py-2.5 text-center text-slate-500">
                                            <input type="hidden" name="items[{{ $idx }}][unit]" value="{{ $item->unit }}">
                                            <span>{{ $item->unit }}</span>
                                        </td>
                                        <td class="px-3 py-2.5">
                                            <input type="text" inputmode="numeric" name="items[{{ $idx }}][unit_price]" value="" placeholder="Cth: 15.000.000" required
                                                class="w-full px-2.5 py-1.5 text-xs border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500 text-right item-price font-mono font-bold" oninput="formatPriceInput(this)">
                                        </td>
                                        <td class="px-3 py-2.5 text-right font-mono font-extrabold text-slate-900 item-subtotal">
                                            Rp 0
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Additional Costs & Grand Total Calculator -->
                    <div class="pt-4 border-t border-slate-200 flex flex-col md:flex-row justify-end">
                        <div class="w-full md:w-80 space-y-2.5 bg-slate-50 p-4 rounded-xl border border-slate-200">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-semibold text-slate-600">Subtotal Barang:</span>
                                <span class="font-mono font-bold text-slate-900" id="itemsSubtotalDisplay">Rp 0</span>
                            </div>

                            <div class="flex items-center justify-between text-xs gap-3">
                                <label class="font-semibold text-slate-600">Ongkos Kirim:</label>
                                <div class="w-36">
                                    <input type="text" inputmode="numeric" name="shipping_cost" id="shippingCostInput" value="0" placeholder="0"
                                        class="w-full px-2 py-1 text-xs border border-slate-300 rounded-lg text-right font-mono font-bold" oninput="formatFeeInput(this)">
                                </div>
                            </div>

                            <div class="flex items-center justify-between text-xs gap-3">
                                <label class="font-semibold text-slate-600">Pajak (PPN/Tax):</label>
                                <div class="w-36">
                                    <input type="text" inputmode="numeric" name="tax_amount" id="taxAmountInput" value="0" placeholder="0"
                                        class="w-full px-2 py-1 text-xs border border-slate-300 rounded-lg text-right font-mono font-bold" oninput="formatFeeInput(this)">
                                </div>
                            </div>

                            <div class="pt-2 border-t border-slate-200 flex items-center justify-between text-sm">
                                <span class="font-bold text-slate-900 uppercase tracking-wider text-xs">Total Penawaran:</span>
                                <span class="font-mono font-extrabold text-base text-indigo-700" id="grandTotalDisplay">Rp 0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-3 p-4 bg-white rounded-2xl border border-slate-200 shadow-xs">
                    <a href="{{ route('quotations.compare', $purchaseRequest) }}" class="px-5 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-xs rounded-xl shadow-xs transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-indigo-600/20 transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Simpan Penawaran Vendor</span>
                    </button>
                </div>
            </form>

        </div>
    </div>

    <!-- Script for Dynamic Subtotal & Grand Total -->
    <script>
        function formatRupiah(number) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(number);
        }

        function formatPriceInput(input) {
            let raw = input.value.replace(/[^0-9]/g, '');
            input.value = raw === '' ? '' : new Intl.NumberFormat('id-ID').format(raw);
            calculateSubtotal(input);
        }

        function formatFeeInput(input) {
            let raw = input.value.replace(/[^0-9]/g, '');
            input.value = raw === '' ? '0' : new Intl.NumberFormat('id-ID').format(raw);
            updateGrandTotal();
        }

        function calculateSubtotal(element) {
            const row = element.closest('tr');
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const priceRaw = row.querySelector('.item-price').value.replace(/[^0-9]/g, '');
            const price = parseFloat(priceRaw) || 0;
            const subtotal = qty * price;

            row.querySelector('.item-subtotal').textContent = formatRupiah(subtotal);
            updateGrandTotal();
        }

        function updateGrandTotal() {
            let subtotal = 0;
            document.querySelectorAll('.item-row').forEach(row => {
                const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
                const priceRaw = row.querySelector('.item-price').value.replace(/[^0-9]/g, '');
                const price = parseFloat(priceRaw) || 0;
                subtotal += (qty * price);
            });

            const shippingRaw = document.getElementById('shippingCostInput').value.replace(/[^0-9]/g, '');
            const shipping = parseFloat(shippingRaw) || 0;

            const taxRaw = document.getElementById('taxAmountInput').value.replace(/[^0-9]/g, '');
            const tax = parseFloat(taxRaw) || 0;

            const grandTotal = subtotal + shipping + tax;

            document.getElementById('itemsSubtotalDisplay').textContent = formatRupiah(subtotal);
            document.getElementById('grandTotalDisplay').textContent = formatRupiah(grandTotal);
        }

        document.addEventListener('DOMContentLoaded', () => {
            updateGrandTotal();
        });
    </script>
</x-app-layout>
