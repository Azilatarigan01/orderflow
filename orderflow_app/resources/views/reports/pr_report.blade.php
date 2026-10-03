<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                    <a href="{{ route('reports.index') }}" class="hover:text-slate-700">Laporan</a>
                    <span>/</span>
                    <span class="text-slate-800 font-medium">Purchase Request (Perencanaan Belanja)</span>
                </div>
                <h2 class="font-bold text-2xl text-slate-900 tracking-tight">Laporan Rekapitulasi Purchase Request</h2>
                <p class="text-xs text-slate-500 mt-0.5">Analisis kebutuhan belanja divisi &bull; Maksimal rentang penarikan: 365 hari</p>
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

            <!-- Validation Errors Alert -->
            @if($errors->any())
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-3">
                    <div class="text-xl">⚠️</div>
                    <div>
                        <strong class="font-bold">Pemeriksaan Rentang Tanggal:</strong>
                        <p class="text-xs mt-0.5">{{ $errors->first() }}</p>
                    </div>
                </div>
            @endif

            {{-- Filters --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                <form method="GET" action="{{ route('reports.pr') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-6 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Preset Periode</label>
                        <select name="period_preset" id="rpr_period_preset" onchange="togglePrDates(this.value)" class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium">
                            <option value="today" @selected(($dateFilter['preset'] ?? '') === 'today')>Hari Ini</option>
                            <option value="this_week" @selected(($dateFilter['preset'] ?? '') === 'this_week')>Minggu Ini</option>
                            <option value="this_month" @selected(($dateFilter['preset'] ?? '') === 'this_month')>Bulan Berjalan</option>
                            <option value="this_quarter" @selected(($dateFilter['preset'] ?? '') === 'this_quarter')>Kuartal Berjalan</option>
                            <option value="this_year" @selected(($dateFilter['preset'] ?? '') === 'this_year')>Tahun Berjalan</option>
                            <option value="custom" @selected(($dateFilter['preset'] ?? '') === 'custom')>Rentang Kustom</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Basis Tanggal (Posting Date)</label>
                        <select name="date_basis" class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium bg-slate-50">
                            <option value="created_at" @selected(($dateBasis ?? '') === 'created_at')>Tgl Dibuat (Perencanaan)</option>
                            <option value="required_date" @selected(($dateBasis ?? '') === 'required_date')>Target Batas Kebutuhan</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Dari Tanggal</label>
                        <input type="date" name="date_from" id="rpr_date_from" value="{{ $dateFilter['date_from'] ?? request('date_from') }}"
                               class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Sampai Tanggal</label>
                        <input type="date" name="date_to" id="rpr_date_to" value="{{ $dateFilter['date_to'] ?? request('date_to') }}"
                               class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Divisi / Departemen</label>
                        <select name="department_id" id="rpr_dept" class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Semua Divisi</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" @selected(request('department_id') == $dept->id)>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Status Pengajuan</label>
                        <select name="status" id="rpr_status" class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Semua Status</option>
                            @foreach(['draft','submitted','revision_required','approved','rejected','processing','completed','cancelled'] as $s)
                                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2 md:col-span-6 flex items-center justify-between pt-2 border-t border-slate-100">
                        <div class="text-[11px] text-slate-400">
                            *Rentang maksimal 365 hari per tarikan untuk menjaga kestabilan memori sistem.
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" id="rpr_filter_btn" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-xs transition">Terapkan Filter</button>
                            <a href="{{ route('reports.pr') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg transition">Reset</a>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Summary Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total PR Diajukan</div>
                    <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ $prs->count() }} Dokumen</div>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                    <div class="text-[11px] font-bold text-indigo-600 uppercase tracking-wider">Total Nilai Estimasi</div>
                    <div class="text-xl font-extrabold text-slate-900 mt-1 font-mono">Rp {{ number_format($totalValue, 0, ',', '.') }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Estimasi kebutuhan belanja</div>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                    <div class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Disetujui / Diproses</div>
                    <div class="text-2xl font-extrabold text-emerald-700 mt-1">{{ $byStatus->get('approved', 0) + $byStatus->get('processing', 0) + $byStatus->get('completed', 0) }} PR</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Lolos verifikasi bertingkat</div>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                    <div class="text-[11px] font-bold text-rose-600 uppercase tracking-wider">Ditolak / Dibatalkan</div>
                    <div class="text-2xl font-extrabold text-rose-700 mt-1">{{ $byStatus->get('rejected', 0) + $byStatus->get('cancelled', 0) }} PR</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Tidak berlanjut ke RFQ/PO</div>
                </div>
            </div>

            {{-- Table --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-extrabold text-sm text-slate-900 uppercase tracking-wider">Daftar Rekapitulasi Purchase Request ({{ $prs->count() }} Data)</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Rekapitulasi permohonan pengadaan internal perusahaan</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 divide-y divide-slate-100">
                        <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3 text-center w-10">No</th>
                                <th class="px-4 py-3">No. PR</th>
                                <th class="px-4 py-3">Judul Pengadaan</th>
                                <th class="px-4 py-3">Pemohon</th>
                                <th class="px-4 py-3">Divisi</th>
                                <th class="px-4 py-3 text-right">Nilai Estimasi</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Tanggal Buat</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($prs as $idx => $pr)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-3 text-center font-mono text-slate-400 font-bold">{{ $idx + 1 }}</td>
                                    <td class="px-4 py-3 font-mono font-bold text-slate-900">
                                        <a href="{{ route('purchase-requests.show', $pr) }}" class="text-indigo-600 hover:underline">
                                            {{ $pr->pr_number }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 font-bold text-slate-900">{{ $pr->title }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $pr->user?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $pr->department?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right font-mono font-bold text-slate-900">
                                        Rp {{ number_format($pr->estimated_total, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $pr->status_badge_class }}">
                                            {{ $pr->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center font-mono text-slate-600">
                                        {{ $pr->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('purchase-requests.show', $pr) }}"
                                           class="px-2.5 py-1 bg-slate-50 hover:bg-slate-100 text-slate-600 text-xs font-semibold rounded-lg transition">
                                            Rincian &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-10 text-slate-400">
                                        Tidak ada data Purchase Request ditemukan dalam rentang periode ini.
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
        function togglePrDates(preset) {
            const dateFromInput = document.getElementById('rpr_date_from');
            const dateToInput = document.getElementById('rpr_date_to');
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
