<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-slate-900 tracking-tight leading-tight">
                    Edit Purchase Request: <span class="font-mono text-indigo-600">{{ $purchaseRequest->pr_number }}</span>
                </h2>
                <p class="text-sm text-slate-500 mt-0.5">Perbarui rincian kebutuhan barang sebelum diajukan kembali.</p>
            </div>
            <a href="{{ route('purchase-requests.show', $purchaseRequest) }}" class="px-3.5 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-medium transition">
                Batal
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
                    <p class="font-bold">Mohon perbaiki kesalahan berikut:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('purchase-requests.update', $purchaseRequest) }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Section 1: Informasi Dasar -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6 space-y-4">
                    <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900">1. Informasi Pengadaan</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Detail identitas pengajuan dan target waktu kedatangan.</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $purchaseRequest->status_badge_class }}">
                            Status Saat Ini: {{ $purchaseRequest->status_label }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Judul Pengadaan <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="title" value="{{ old('title', $purchaseRequest->title) }}" required
                                class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Divisi Pemohon
                            </label>
                            <div class="px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-700 font-medium flex items-center justify-between">
                                <span>{{ $purchaseRequest->department?->name ?? 'Lintas Divisi' }}</span>
                                <span class="text-[10px] uppercase font-bold text-slate-400 font-mono">{{ $purchaseRequest->department?->code ?? 'GEN' }}</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Tanggal Dibutuhkan Tiba di Kantor <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="required_date" value="{{ old('required_date', $purchaseRequest->required_date->format('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required
                                class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Alasan & Justifikasi Kebutuhan Bisnis <span class="text-rose-500">*</span>
                            </label>
                            <textarea name="description" rows="3" required
                                class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">{{ old('description', $purchaseRequest->description) }}</textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Tambah / Ganti Berkas Lampiran (Opsional)
                            </label>
                            <input type="file" name="attachment" accept=".pdf,.png,.jpg,.jpeg,.doc,.docx,.xls,.xlsx"
                                class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                            @if($purchaseRequest->attachments->count() > 0)
                                <p class="text-[11px] text-slate-500 mt-1">
                                    Berkas terlampir saat ini: <strong>{{ $purchaseRequest->attachments->first()->file_name }}</strong>
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Section 2: Items Table -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900">2. Rincian Item Barang / Jasa</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Ubah jumlah, harga satuan, atau tambah/hapus baris barang.</p>
                        </div>
                        <button type="button" onclick="addItemRow()"
                            class="inline-flex items-center px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-lg text-xs font-semibold transition">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Tambah Baris Barang
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-700" id="itemsTable">
                            <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                                <tr>
                                    <th class="px-3 py-2.5 w-8 text-center">#</th>
                                    <th class="px-3 py-2.5">Nama Barang / Jasa <span class="text-rose-500">*</span></th>
                                    <th class="px-3 py-2.5">Spesifikasi Detail</th>
                                    <th class="px-3 py-2.5 w-24">Jumlah <span class="text-rose-500">*</span></th>
                                    <th class="px-3 py-2.5 w-28">Satuan <span class="text-rose-500">*</span></th>
                                    <th class="px-3 py-2.5 w-36">Harga Satuan (Rp) <span class="text-rose-500">*</span></th>
                                    <th class="px-3 py-2.5 w-36 text-right">Subtotal (Rp)</th>
                                    <th class="px-3 py-2.5 w-12 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody" class="divide-y divide-slate-100 font-normal">
                                @foreach($purchaseRequest->items as $idx => $item)
                                    <tr class="item-row hover:bg-slate-50/50">
                                        <td class="px-3 py-2 text-center text-slate-400 font-mono row-index">{{ $idx + 1 }}</td>
                                        <td class="px-3 py-2">
                                            <input type="text" name="items[{{ $idx }}][item_name]" value="{{ $item->item_name }}" required
                                                class="w-full px-2.5 py-1.5 text-xs border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="text" name="items[{{ $idx }}][specification]" value="{{ $item->specification }}"
                                                class="w-full px-2.5 py-1.5 text-xs border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" name="items[{{ $idx }}][quantity]" value="{{ $item->quantity }}" min="1" required
                                                class="w-full px-2.5 py-1.5 text-xs border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500 item-qty" oninput="calculateSubtotal(this)">
                                        </td>
                                        <td class="px-3 py-2">
                                            <select name="items[{{ $idx }}][unit]" required class="w-full px-2 py-1.5 text-xs border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500">
                                                <option value="Unit" {{ $item->unit === 'Unit' ? 'selected' : '' }}>Unit</option>
                                                <option value="Pcs" {{ $item->unit === 'Pcs' ? 'selected' : '' }}>Pcs</option>
                                                <option value="Box" {{ $item->unit === 'Box' ? 'selected' : '' }}>Box</option>
                                                <option value="Rim" {{ $item->unit === 'Rim' ? 'selected' : '' }}>Rim</option>
                                                <option value="Paket" {{ $item->unit === 'Paket' ? 'selected' : '' }}>Paket</option>
                                                <option value="Set" {{ $item->unit === 'Set' ? 'selected' : '' }}>Set</option>
                                                <option value="Bulan" {{ $item->unit === 'Bulan' ? 'selected' : '' }}>Bulan (Layanan)</option>
                                            </select>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" name="items[{{ $idx }}][estimated_unit_price]" value="{{ (int)$item->estimated_unit_price }}" min="1" step="100" required
                                                class="w-full px-2.5 py-1.5 text-xs border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500 text-right item-price" oninput="calculateSubtotal(this)">
                                        </td>
                                        <td class="px-3 py-2 text-right font-mono font-bold text-slate-900 item-subtotal">
                                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <button type="button" onclick="removeItemRow(this)"
                                                class="text-slate-400 hover:text-rose-600 transition p-1" title="Hapus Baris">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-slate-50 border-t-2 border-slate-200">
                                <tr>
                                    <td colspan="6" class="px-4 py-3 text-right font-bold text-slate-700 uppercase tracking-wider text-xs">
                                        Total Estimasi Anggaran:
                                    </td>
                                    <td class="px-3 py-3 text-right font-mono font-extrabold text-base text-indigo-700" id="grandTotalDisplay">
                                        Rp {{ number_format($purchaseRequest->estimated_total, 0, ',', '.') }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-4 bg-white rounded-xl border border-slate-200 shadow-xs">
                    <p class="text-xs text-slate-500">
                        * Pilih "Ajukan Persetujuan" jika perbaikan atau draf sudah siap diperiksa atasan.
                    </p>
                    <div class="flex items-center gap-3">
                        <button type="submit" name="action" value="draft"
                            class="px-5 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-xs rounded-xl shadow-xs transition">
                            💾 Simpan Perubahan
                        </button>
                        <button type="submit" name="action" value="submit"
                            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow-sm transition">
                            🚀 Ajukan Persetujuan (Submit)
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>

    <!-- Script for Dynamic Items -->
    <script>
        let rowCounter = {{ $purchaseRequest->items->count() }};

        function formatRupiah(number) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(number);
        }

        function calculateSubtotal(element) {
            const row = element.closest('tr');
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const subtotal = qty * price;

            row.querySelector('.item-subtotal').textContent = formatRupiah(subtotal);
            updateGrandTotal();
        }

        function updateGrandTotal() {
            let total = 0;
            document.querySelectorAll('.item-row').forEach(row => {
                const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
                const price = parseFloat(row.querySelector('.item-price').value) || 0;
                total += (qty * price);
            });
            document.getElementById('grandTotalDisplay').textContent = formatRupiah(total);
        }

        function updateRowIndices() {
            document.querySelectorAll('.item-row').forEach((row, idx) => {
                row.querySelector('.row-index').textContent = idx + 1;
            });
        }

        function addItemRow() {
            const tbody = document.getElementById('itemsBody');
            const newRow = document.createElement('tr');
            newRow.className = 'item-row hover:bg-slate-50/50';
            newRow.innerHTML = `
                <td class="px-3 py-2 text-center text-slate-400 font-mono row-index">${tbody.children.length + 1}</td>
                <td class="px-3 py-2">
                    <input type="text" name="items[${rowCounter}][item_name]" required placeholder="Nama barang / jasa"
                        class="w-full px-2.5 py-1.5 text-xs border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500">
                </td>
                <td class="px-3 py-2">
                    <input type="text" name="items[${rowCounter}][specification]" placeholder="Spesifikasi teknis"
                        class="w-full px-2.5 py-1.5 text-xs border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500">
                </td>
                <td class="px-3 py-2">
                    <input type="number" name="items[${rowCounter}][quantity]" value="1" min="1" required
                        class="w-full px-2.5 py-1.5 text-xs border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500 item-qty" oninput="calculateSubtotal(this)">
                </td>
                <td class="px-3 py-2">
                    <select name="items[${rowCounter}][unit]" required class="w-full px-2 py-1.5 text-xs border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500">
                        <option value="Unit" selected>Unit</option>
                        <option value="Pcs">Pcs</option>
                        <option value="Box">Box</option>
                        <option value="Rim">Rim</option>
                        <option value="Paket">Paket</option>
                        <option value="Set">Set</option>
                        <option value="Bulan">Bulan (Layanan)</option>
                    </select>
                </td>
                <td class="px-3 py-2">
                    <input type="number" name="items[${rowCounter}][estimated_unit_price]" value="0" min="1" step="100" required
                        class="w-full px-2.5 py-1.5 text-xs border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500 text-right item-price" oninput="calculateSubtotal(this)">
                </td>
                <td class="px-3 py-2 text-right font-mono font-bold text-slate-900 item-subtotal">
                    Rp 0
                </td>
                <td class="px-3 py-2 text-center">
                    <button type="button" onclick="removeItemRow(this)"
                        class="text-slate-400 hover:text-rose-600 transition p-1" title="Hapus Baris">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </td>
            `;
            tbody.appendChild(newRow);
            rowCounter++;
            updateRowIndices();
        }

        function removeItemRow(button) {
            const tbody = document.getElementById('itemsBody');
            if (tbody.children.length <= 1) {
                alert('Pengajuan harus memiliki minimal 1 item barang/jasa.');
                return;
            }
            button.closest('tr').remove();
            updateRowIndices();
            updateGrandTotal();
        }

        document.addEventListener('DOMContentLoaded', () => {
            updateGrandTotal();
        });
    </script>
</x-app-layout>
