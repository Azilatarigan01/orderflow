<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                    <a href="{{ route('reports.index') }}" class="hover:text-slate-700">Laporan</a>
                    <span>/</span>
                    <span class="text-slate-800 font-medium">Penerimaan Barang & BAST (Realisasi Fisik)</span>
                </div>
                <h2 class="font-bold text-2xl text-slate-900 tracking-tight">Laporan Realisasi Fisik (Goods Receipt & BAST)</h2>
                <p class="text-xs text-slate-500 mt-0.5">Posting Date: Tanggal pemeriksaan serah terima fisik di gudang &bull; Batas penarikan: maks 365 hari</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}"
                   class="px-3.5 py-2 text-xs font-bold bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl shadow-xs transition flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-300" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm1 8h4.5L14 4.5V10zM8.5 17.5l1.8-3-1.8-3h1.6l1 2 1-2h1.6l-1.8 3 1.8 3h-1.6l-1-2.1-1 2.1H8.5z"/></svg>
                    Unduh Excel (.xls)
                </a>
                <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}"
                   class="px-2.5 py-2 text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 rounded-xl transition flex items-center gap-1">
                    CSV
                </a>
                <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" target="_blank"
                   class="px-3.5 py-2 text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white rounded-xl shadow-xs transition flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-rose-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak Rekap PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Validation Error Alert -->
            @if($errors->any())
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-3">
                    <div class="text-xl">⚠️</div>
                    <div>
                        <strong class="font-bold">Pemeriksaan Rentang Tanggal:</strong>
                        <p class="text-xs mt-0.5">{{ $errors->first() }}</p>
                    </div>
                </div>
            @endif

            {{-- Filters with Dynamic Preset --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                <form method="GET" action="{{ route('reports.gr') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Preset Rentang Waktu</label>
                        <select name="period_preset" id="rgr_period_preset" onchange="toggleCustomDates(this.value)" class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500 font-medium">
                            <option value="today" @selected(($dateFilter['preset'] ?? '') === 'today')>Hari Ini</option>
                            <option value="this_week" @selected(($dateFilter['preset'] ?? '') === 'this_week')>Minggu Ini</option>
                            <option value="this_month" @selected(($dateFilter['preset'] ?? '') === 'this_month')>Bulan Berjalan</option>
                            <option value="this_quarter" @selected(($dateFilter['preset'] ?? '') === 'this_quarter')>Kuartal Berjalan</option>
                            <option value="this_year" @selected(($dateFilter['preset'] ?? '') === 'this_year')>Tahun Berjalan</option>
                            <option value="custom" @selected(($dateFilter['preset'] ?? '') === 'custom')>Rentang Kustom (Custom)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Dari Tanggal Fisik Tiba</label>
                        <input type="date" name="date_from" id="rgr_date_from" value="{{ $dateFilter['date_from'] ?? request('date_from') }}"
                               class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Sampai Tanggal Fisik Tiba</label>
                        <input type="date" name="date_to" id="rgr_date_to" value="{{ $dateFilter['date_to'] ?? request('date_to') }}"
                               class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tipe Serah Terima</label>
                        <select name="receipt_type" class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500">
                            <option value="">Semua (Barang & Jasa)</option>
                            <option value="goods" @selected(request('receipt_type') === 'goods')>Barang Fisik (Surat Jalan)</option>
                            <option value="service" @selected(request('receipt_type') === 'service')>Jasa (BAST)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Status QC / Klaim</label>
                        <select name="status" class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500">
                            <option value="">Semua Status</option>
                            <option value="completed" @selected(request('status') === 'completed')>Lengkap (100% OK)</option>
                            <option value="partially_received" @selected(request('status') === 'partially_received')>Sebagian (Parsial)</option>
                            <option value="disputed" @selected(request('status') === 'disputed')>Klaim Cacat / Retur (Disputed)</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2 md:col-span-5 flex items-center justify-between pt-2 border-t border-slate-100">
                        <div class="text-[11px] text-slate-400">
                            *Catatan Audit: Tanggal penerimaan fisik memisahkan transaksi logistik dari tanggal pengajuan PR dan tanggal PO.
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold rounded-lg shadow-xs transition">Terapkan Filter</button>
                            <a href="{{ route('reports.gr') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg transition">Reset</a>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Summary Metric Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Tanda Terima</div>
                    <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ $receipts->count() }} Dokumen</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Surat jalan & BAST tercatat</div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                    <div class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Total Unit Fisik Tiba</div>
                    <div class="text-2xl font-extrabold text-emerald-700 mt-1 font-mono">{{ number_format($totalItemsReceived, 0, ',', '.') }} Item</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Akumulasi kuantitas fisik</div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                    <div class="text-[11px] font-bold text-rose-600 uppercase tracking-wider">Unit Rusak / Retur (RTV)</div>
                    <div class="text-2xl font-extrabold text-rose-700 mt-1 font-mono">{{ number_format($totalItemsRejected, 0, ',', '.') }} Item</div>
                    <div class="text-[11px] text-rose-600 mt-0.5 font-semibold">Tidak ditagihkan ke kas</div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                    <div class="text-[11px] font-bold text-indigo-600 uppercase tracking-wider">Tingkat Lolos QC (Fulfillment)</div>
                    @php
                        $qcPassRate = ($totalItemsReceived > 0) ? round((($totalItemsReceived - $totalItemsRejected) / $totalItemsReceived) * 100, 1) : 100;
                    @endphp
                    <div class="text-2xl font-extrabold text-indigo-700 mt-1 font-mono">{{ $qcPassRate }}%</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Lolos inspeksi spesifikasi</div>
                </div>
            </div>

            {{-- Table --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-extrabold text-sm text-slate-900 uppercase tracking-wider">Buku Besar Penerimaan Barang & Jasa ({{ $receipts->count() }} Data)</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Data terverifikasi petugas logistik dan berita acara pemeriksaan fisik</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 divide-y divide-slate-100">
                        <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3 text-center w-10">No</th>
                                <th class="px-4 py-3">No. GR / BAST</th>
                                <th class="px-4 py-3">No. PO Referensi</th>
                                <th class="px-4 py-3">Tipe</th>
                                <th class="px-4 py-3">Vendor / Rekanan</th>
                                <th class="px-4 py-3 text-center">Tgl Terima Fisik</th>
                                <th class="px-4 py-3">Penerima</th>
                                <th class="px-4 py-3 text-center">Kuantitas Tiba</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($receipts as $idx => $r)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-3 text-center font-mono text-slate-400 font-bold">{{ $idx + 1 }}</td>
                                    <td class="px-4 py-3 font-mono font-bold text-slate-900">
                                        <a href="{{ route('goods-receipts.show', $r) }}" class="text-indigo-600 hover:underline">
                                            {{ $r->gr_number }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 font-mono font-semibold text-slate-600">
                                        <a href="{{ route('purchase-orders.show', $r->purchaseOrder) }}" class="hover:underline">
                                            {{ $r->purchaseOrder?->po_number }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($r->is_service)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800">BAST Jasa</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-sky-100 text-sky-800">Barang Fisik</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-bold text-slate-900">
                                        {{ $r->purchaseOrder?->vendor?->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-mono font-bold text-slate-700">
                                        {{ $r->received_date?->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">
                                        {{ $r->receiver?->name ?? 'Tim Logistik' }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-mono font-bold text-emerald-700">
                                        {{ $r->items->sum('quantity_received') }} unit
                                        @if($r->items->sum('quantity_rejected') > 0)
                                            <span class="block text-[10px] text-rose-600 font-semibold">({{ $r->items->sum('quantity_rejected') }} rusak)</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $r->status_badge_class }}">
                                            {{ $r->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('goods-receipts.show', $r) }}" class="px-2.5 py-1 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg shadow-2xs transition">
                                            Rincian &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-10 text-slate-400">
                                        Tidak ada rekaman tanda terima fisik barang/jasa dalam rentang periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <script>
        function toggleCustomDates(preset) {
            const dateFromInput = document.getElementById('rgr_date_from');
            const dateToInput = document.getElementById('rgr_date_to');
            const today = new Date();
            
            function formatDate(d) {
                const year = d.getFullYear();
                const month = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            if (preset === 'today') {
                dateFromInput.value = formatDate(today);
                dateToInput.value = formatDate(today);
            } else if (preset === 'this_week') {
                const firstDay = new Date(today.setDate(today.getDate() - today.getDay() + (today.getDay() === 0 ? -6 : 1)));
                const lastDay = new Date(today.setDate(firstDay.getDate() + 6));
                dateFromInput.value = formatDate(firstDay);
                dateToInput.value = formatDate(lastDay);
            } else if (preset === 'this_month') {
                const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                dateFromInput.value = formatDate(firstDay);
                dateToInput.value = formatDate(lastDay);
            } else if (preset === 'this_year') {
                const firstDay = new Date(today.getFullYear(), 0, 1);
                const lastDay = new Date(today.getFullYear(), 11, 31);
                dateFromInput.value = formatDate(firstDay);
                dateToInput.value = formatDate(lastDay);
            }
        }
    </script>
</x-app-layout>
