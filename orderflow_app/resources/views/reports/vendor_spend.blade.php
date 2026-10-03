<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                    <a href="{{ route('reports.index') }}" class="hover:text-slate-700">Laporan</a>
                    <span>/</span>
                    <span class="text-slate-800 font-medium">Pengeluaran Vendor</span>
                </div>
                <h2 class="font-bold text-2xl text-slate-900 tracking-tight">Laporan Pengeluaran Per Vendor</h2>
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

            {{-- Filters --}}
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <form method="GET" action="{{ route('reports.vendor-spend') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Kategori Vendor</label>
                        <select name="category" id="rvs_category" class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Dari Tanggal PO</label>
                        <input type="date" name="date_from" id="rvs_date_from" value="{{ request('date_from') }}"
                               class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Sampai Tanggal PO</label>
                        <input type="date" name="date_to" id="rvs_date_to" value="{{ request('date_to') }}"
                               class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div class="sm:col-span-3 flex gap-3">
                        <button type="submit" id="rvs_filter_btn" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition">Terapkan Filter</button>
                        <a href="{{ route('reports.vendor-spend') }}" class="px-5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg transition">Reset</a>
                    </div>
                </form>
            </div>

            {{-- Summary --}}
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white rounded-xl border border-slate-200 p-4">
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Vendor</div>
                    <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ $vendorSpend->count() }}</div>
                </div>
                <div class="bg-white rounded-xl border border-slate-200 p-4">
                    <div class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Total Pengeluaran</div>
                    <div class="text-lg font-extrabold text-slate-900 mt-1 font-mono">Rp {{ number_format($grandTotal, 0, ',', '.') }}</div>
                </div>
            </div>

            {{-- Vendor Spend Chart (CSS bar) + Table --}}
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h3 class="font-bold text-sm text-slate-900">Ranking Pengeluaran Per Vendor</h3>
                </div>

                {{-- Visual bar chart --}}
                @if($vendorSpend->count() > 0)
                <div class="px-6 py-4 space-y-3 border-b border-slate-100">
                    @foreach($vendorSpend->take(8) as $v)
                        @php
                            $pct = $grandTotal > 0 ? ($v->total_spend / $grandTotal) * 100 : 0;
                        @endphp
                        <div class="flex items-center gap-3">
                            <div class="w-32 text-xs font-semibold text-slate-700 truncate">{{ $v->name }}</div>
                            <div class="flex-1 bg-slate-100 rounded-full h-4 overflow-hidden">
                                <div class="h-4 bg-emerald-500 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                            </div>
                            <div class="w-28 text-right text-xs font-mono font-bold text-slate-900">
                                Rp {{ number_format($v->total_spend, 0, ',', '.') }}
                            </div>
                            <div class="w-10 text-right text-[10px] text-slate-500">{{ round($pct, 1) }}%</div>
                        </div>
                    @endforeach
                </div>
                @endif

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600 divide-y divide-slate-100">
                        <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-5 py-3">Rank</th>
                                <th class="px-5 py-3">Kode</th>
                                <th class="px-5 py-3">Nama Vendor</th>
                                <th class="px-5 py-3">Kategori</th>
                                <th class="px-5 py-3 text-center">Jumlah PO</th>
                                <th class="px-5 py-3 text-right">Total Pengeluaran</th>
                                <th class="px-5 py-3 text-center">Rating</th>
                                <th class="px-5 py-3">Order Terakhir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($vendorSpend as $idx => $v)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-5 py-3">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-[10px] font-bold {{ $idx < 3 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600' }}">
                                            {{ $idx + 1 }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 font-mono text-slate-500">{{ $v->code }}</td>
                                    <td class="px-5 py-3 font-semibold text-slate-900">{{ $v->name }}</td>
                                    <td class="px-5 py-3 text-slate-500">{{ $v->category }}</td>
                                    <td class="px-5 py-3 text-center font-bold text-slate-900">{{ $v->po_count }}</td>
                                    <td class="px-5 py-3 text-right font-mono font-bold text-slate-900">
                                        Rp {{ number_format($v->total_spend, 0, ',', '.') }}
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        <span class="font-semibold text-slate-700">{{ number_format($v->rating, 1) }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-slate-500">{{ $v->last_order_date ? \Carbon\Carbon::parse($v->last_order_date)->format('d/m/Y') : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-8 text-center text-slate-400">Belum ada data pengeluaran vendor.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
