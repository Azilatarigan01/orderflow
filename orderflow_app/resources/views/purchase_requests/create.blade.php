<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-slate-900 tracking-tight leading-tight">
                    Buat Pengajuan Pembelian (Purchase Request)
                </h2>
                <p class="text-sm text-slate-500 mt-0.5">Formulir pengajuan kebutuhan barang dan jasa internal divisi.</p>
            </div>
            <a href="{{ route('purchase-requests.index') }}" class="px-3.5 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-medium transition">
                Kembali ke Daftar
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

            <form method="POST" action="{{ route('purchase-requests.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Section 1: Informasi Dasar Pengadaan -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6 space-y-4">
                    <div class="border-b border-slate-100 pb-3">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900">1. Informasi Pengadaan & Pemohon</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Detail identitas pengajuan dan target waktu kedatangan.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Judul Pengadaan <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="title" value="{{ old('title') }}" required
                                placeholder="Cth: Pengadaan 5 Unit Laptop Developer untuk Tim Engineering"
                                class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Divisi Pemohon
                            </label>
                            <div class="px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-700 font-medium flex items-center justify-between">
                                <span>{{ Auth::user()->department?->name ?? 'Lintas Divisi' }}</span>
                                <span class="text-[10px] uppercase font-bold text-slate-400 font-mono">{{ Auth::user()->department?->code ?? 'GEN' }}</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Tanggal Dibutuhkan Tiba di Kantor <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="required_date" value="{{ old('required_date', date('Y-m-d', strtotime('+7 days'))) }}" min="{{ date('Y-m-d') }}" required
                                class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Alasan & Justifikasi Kebutuhan Bisnis <span class="text-rose-500">*</span>
                            </label>
                            <textarea name="description" rows="3" required
                                placeholder="Jelaskan secara detail alasan pengadaan, urgensi penggunaan, atau proyek pendukung terkait..."
                                class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">{{ old('description') }}</textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Berkas Dokumen Pendukung (Opsional)
                            </label>
                            <input type="file" name="attachment" accept=".pdf,.png,.jpg,.jpeg,.doc,.docx,.xls,.xlsx"
                                class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                            <p class="text-[11px] text-slate-400 mt-1">Format: PDF, Gambar, Word, atau Excel (Maksimal 5 MB). Contoh: TOR, proposal kebutuhan, atau brosur spesifikasi.</p>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Rincian Multi-Item Pengadaan -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900">2. Rincian Item Barang / Jasa</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Daftar kebutuhan unit, jumlah, dan estimasi anggaran per item.</p>
                        </div>
                        <button type="button" onclick="addItemRow()"
                            class="inline-flex items-center px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-lg text-xs font-semibold transition">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Tambah Baris Barang
                        </button>
                    </div>

                    <!-- Items Table -->
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
                                <!-- Row 0 Default -->
                                <tr class="item-row hover:bg-slate-50/50">
                                    <td class="px-3 py-2 text-center text-slate-400 font-mono row-index">1</td>
                                    <td class="px-3 py-2">
                                        <input type="text" name="items[0][item_name]" required placeholder="Cth: Laptop ASUS ZenBook 14"
                                            class="w-full px-2.5 py-1.5 text-xs border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" name="items[0][specification]" placeholder="RAM 16GB, SSD 512GB, Core i7"
                                            class="w-full px-2.5 py-1.5 text-xs border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" name="items[0][quantity]" value="1" min="1" required
                                            class="w-full px-2.5 py-1.5 text-xs border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500 item-qty" oninput="calculateSubtotal(this)">
                                    </td>
                                    <td class="px-3 py-2">
                                        <select name="items[0][unit]" required class="w-full px-2 py-1.5 text-xs border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500">
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
                                        <input type="text" inputmode="numeric" name="items[0][estimated_unit_price]" value="" placeholder="Cth: 15.000.000" required
                                            class="w-full px-2.5 py-1.5 text-xs border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500 text-right item-price font-mono" oninput="formatPriceInput(this)">
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
                                </tr>
                            </tbody>
                            <tfoot class="bg-slate-50 border-t-2 border-slate-200">
                                <tr>
                                    <td colspan="6" class="px-4 py-3 text-right font-bold text-slate-700 uppercase tracking-wider text-xs">
                                        Total Estimasi Anggaran Pengadaan:
                                    </td>
                                    <td class="px-3 py-3 text-right font-mono font-extrabold text-base text-indigo-700" id="grandTotalDisplay">
                                        Rp 0
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Form Action Buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-4 bg-white rounded-xl border border-slate-200 shadow-xs">
                    <p class="text-xs text-slate-500">
                        * PR dapat disimpan sebagai draf terlebih dahulu atau langsung diajukan ke atasan divisi.
                    </p>
                    <div class="flex items-center gap-3">
                        <button type="submit" name="action" value="draft"
                            class="px-5 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-xs rounded-xl shadow-xs transition">
                            💾 Simpan sebagai Draf
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

    <!-- Interactive JavaScript for Dynamic Item Rows & Live Subtotal Calculations -->
    <script>
        let rowCounter = 1;

        function formatRupiah(number) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(number);
        }

        function formatPriceInput(input) {
            let raw = input.value.replace(/[^0-9]/g, '');
            if (raw === '') {
                input.value = '';
            } else {
                input.value = new Intl.NumberFormat('id-ID').format(raw);
            }
            calculateSubtotal(input);
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
            let total = 0;
            document.querySelectorAll('.item-row').forEach(row => {
                const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
                const priceRaw = row.querySelector('.item-price').value.replace(/[^0-9]/g, '');
                const price = parseFloat(priceRaw) || 0;
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
                    <input type="text" inputmode="numeric" name="items[${rowCounter}][estimated_unit_price]" value="" placeholder="Cth: 15.000.000" required
                        class="w-full px-2.5 py-1.5 text-xs border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500 text-right item-price font-mono" oninput="formatPriceInput(this)">
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

        // Initialize calculations
        document.addEventListener('DOMContentLoaded', () => {
            updateGrandTotal();
        });
    </script>
</x-app-layout>
