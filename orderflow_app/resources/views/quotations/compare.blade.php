<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="font-extrabold text-2xl text-slate-900 tracking-tight leading-tight">
                        Matriks Perbandingan Vendor (Quotation Comparison)
                    </h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $purchaseRequest->status_badge_class }}">
                        {{ $purchaseRequest->status_label }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Evaluasi penawaran harga, estimasi pengiriman, durasi garansi, dan reputasi vendor untuk PR: <strong class="font-mono text-indigo-600">{{ $purchaseRequest->pr_number }}</strong>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('quotations.index') }}" class="px-3.5 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold shadow-xs transition">
                    Kembali ke Hub RFQ
                </a>
                <a href="{{ route('purchase-requests.show', $purchaseRequest) }}" class="px-3.5 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold shadow-xs transition">
                    Lihat PR Asli
                </a>
                @php
                    $allSelectedQuotes = $purchaseRequest->selectedQuotations();
                    $unissuedQuotes = $purchaseRequest->unissuedQuotations();
                @endphp
                @if($allSelectedQuotes->count() > 0 && Auth::user()->hasRole(['procurement', 'admin']))
                    @if($allSelectedQuotes->count() === 1)
                        <a href="{{ route('purchase-orders.create', ['purchase_request_id' => $purchaseRequest->id, 'quotation_id' => $allSelectedQuotes->first()->id]) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/25 transition flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Terbitkan PO Resmi</span>
                        </a>
                    @else
                        <a href="{{ route('purchase-orders.create', ['purchase_request_id' => $purchaseRequest->id]) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/25 transition flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            <span>Terbitkan PO (Multi-Vendor: {{ $unissuedQuotes->count() > 0 ? $unissuedQuotes->count().' Belum Terbit' : 'Lengkap' }})</span>
                        </a>
                    @endif
                @endif
                @if(Auth::user()->hasRole(['procurement', 'admin']) && in_array($purchaseRequest->status, ['approved', 'processing']))
                    <button type="button" onclick="document.getElementById('fail-tender-modal').classList.remove('hidden')" class="px-3.5 py-2 bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-300 rounded-xl text-xs font-bold shadow-xs transition flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Gagal Tender / Kembalikan</span>
                    </button>
                    <a href="{{ route('quotations.create', $purchaseRequest) }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/20 transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>+ Tambah Penawaran</span>
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Alerts -->
            @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
                    <p class="font-bold">Perhatian: Keputusan belum dapat disimpan:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- PR Summary Header Banner -->
            <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-2xl p-6 shadow-xl border border-indigo-500/20" x-data="{ showRfqModal: false }">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-400 font-mono">{{ $purchaseRequest->pr_number }}</span>
                            <span class="text-xs text-slate-400">&bull; {{ $purchaseRequest->department?->name }}</span>
                        </div>
                        <h3 class="text-lg font-extrabold text-white tracking-tight">{{ $purchaseRequest->title }}</h3>
                        <p class="text-xs text-slate-300 line-clamp-2">{{ $purchaseRequest->description }}</p>
                    </div>

                    <div class="space-y-1 border-t md:border-t-0 md:border-l border-slate-800 pt-3 md:pt-0 md:pl-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Anggaran PR</p>
                        <p class="text-lg font-mono font-extrabold text-white">{{ $purchaseRequest->formatted_estimated_total }}</p>
                        <p class="text-[11px] text-slate-400">Target Tiba: {{ $purchaseRequest->required_date->format('d M Y') }}</p>
                    </div>

                    <div class="space-y-1 border-t md:border-t-0 md:border-l border-slate-800 pt-3 md:pt-0 md:pl-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Aturan Pengadaan</p>
                        @if($ruleRequiresTwoQuotations)
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                <span>⚠️ > Rp10 Juta: Min. 2 Quotation</span>
                            </div>
                        @else
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                <span>✓ ≤ Rp10 Juta: Fleksibel</span>
                            </div>
                        @endif
                        <p class="text-[10px] text-slate-400 mt-1">Tersedia {{ $quotationCount }} Surat Penawaran</p>
                    </div>

                    <div class="space-y-1 border-t md:border-t-0 md:border-l border-slate-800 pt-3 md:pt-0 md:pl-4">
                        <div class="flex items-center justify-between">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tenggat RFQ (Batas Waktu)</p>
                            @if(Auth::user()->hasRole(['procurement', 'admin']))
                                <button type="button" @click="showRfqModal = true" class="text-[10px] text-indigo-400 hover:text-indigo-300 underline font-semibold">
                                    {{ $purchaseRequest->rfq_deadline ? 'Ubah' : '+ Atur' }}
                                </button>
                            @endif
                        </div>
                        @if($purchaseRequest->rfq_deadline)
                            <p class="text-sm font-mono font-bold {{ $purchaseRequest->is_rfq_closed ? 'text-rose-400' : 'text-emerald-300' }}">
                                {{ $purchaseRequest->formatted_rfq_deadline }}
                            </p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $purchaseRequest->is_rfq_closed ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' }}">
                                {{ $purchaseRequest->is_rfq_closed ? '⏳ RFQ Ditutup' : '🟢 Masa Penawaran Aktif' }}
                            </span>
                        @else
                            <p class="text-xs text-slate-400">Tidak dibatasi (Open Tender)</p>
                        @endif
                    </div>
                </div>

                <!-- Modal / Drawer for setting RFQ Deadline -->
                @if(Auth::user()->hasRole(['procurement', 'admin']))
                    <div x-show="showRfqModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                        <div class="bg-white rounded-2xl max-w-md w-full p-6 text-slate-800 shadow-2xl space-y-4 border border-slate-200" @click.outside="showRfqModal = false">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900">Atur Batas Waktu RFQ</h3>
                                <button type="button" @click="showRfqModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                            </div>
                            <form method="POST" action="{{ route('quotations.rfq-deadline', $purchaseRequest) }}" class="space-y-4">
                                @csrf
                                @method('PATCH')
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal & Waktu Tenggat (Deadline):</label>
                                    <input type="datetime-local" name="rfq_deadline" value="{{ $purchaseRequest->rfq_deadline ? $purchaseRequest->rfq_deadline->format('Y-m-d\TH:i') : '' }}"
                                        class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 focus:ring-indigo-500">
                                    <p class="text-[11px] text-slate-400 mt-1">Kosongkan jika ingin membuka penawaran tanpa batas waktu.</p>
                                </div>
                                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                                    <button type="button" @click="showRfqModal = false" class="px-3 py-1.5 text-xs text-slate-600 hover:bg-slate-100 rounded-lg">Batal</button>
                                    <button type="submit" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-sm">Simpan Batas Waktu</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Quotations State Checking -->
            @if($quotations->isEmpty())
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-12 text-center space-y-4">
                    <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-base text-slate-900">Belum Ada Penawaran Vendor yang Dicatat</h4>
                        <p class="text-xs text-slate-500 max-w-md mx-auto mt-1">
                            PR ini telah disetujui, namun tim procurement belum memasukkan surat penawaran dari vendor rekanan.
                        </p>
                    </div>
                    @if(Auth::user()->hasRole(['procurement', 'admin']))
                        <div>
                            <a href="{{ route('quotations.create', $purchaseRequest) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/20 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Input Penawaran Vendor Pertama</span>
                            </a>
                        </div>
                    @endif
                </div>
            @else

                <!-- Side-by-side Matrix Comparison Table (Wireframe Compliant) -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                    <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                                <span>Matriks Evaluasi Komparasi Vendor</span>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-mono">
                                    {{ $quotations->count() }} Penawaran Masuk
                                </span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Parameter perbandingan disajikan berdampingan dengan evaluasi skor sistem otomatis.</p>
                        </div>

                        <!-- System Scoring Legend -->
                        <div class="text-[11px] text-slate-500 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200 flex items-center gap-2 flex-wrap">
                            <span class="font-bold text-slate-700">Bobot Skor:</span>
                            <span>Harga (45%)</span> &bull;
                            <span>Pengiriman (25%)</span> &bull;
                            <span>Garansi (15%)</span> &bull;
                            <span>Reputasi (15%)</span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-200">
                                    <th class="px-4 py-4 font-bold text-slate-500 uppercase tracking-wider text-[11px] w-48 border-r border-slate-200">
                                        Parameter Pengadaan
                                    </th>
                                    @foreach($quotations as $q)
                                        <th class="px-5 py-4 border-r border-slate-200 last:border-r-0 min-w-[240px] align-top {{ $q->is_selected ? 'bg-emerald-50/50' : '' }}">
                                            <div class="space-y-1">
                                                @if($q->is_recommended)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-indigo-600 text-white shadow-xs">
                                                        ★ REKOMENDASI TERBAIK
                                                    </span>
                                                @endif
                                                @if($q->is_selected)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-600 text-white shadow-xs">
                                                        ✓ VENDOR TERPILIH
                                                    </span>
                                                @endif
                                                <h4 class="font-extrabold text-sm text-slate-900 leading-snug">{{ $q->vendor?->name }}</h4>
                                                <p class="text-[11px] text-slate-500 font-mono">No: {{ $q->quotation_number ?? '-' }}</p>
                                                <div class="flex items-center gap-1.5 pt-0.5">
                                                    @if($q->is_late_submission)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300" title="Dispensasi: {{ $q->late_dispensation_reason }}">
                                                            ⏳ Terlambat (Dispensasi)
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600">
                                                            ✓ Tepat Waktu
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">

                                <!-- Row: Subtotal Barang -->
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-3 font-semibold text-slate-600 bg-slate-50/50 border-r border-slate-200">
                                        Harga Subtotal Barang
                                    </td>
                                    @foreach($quotations as $q)
                                        <td class="px-5 py-3 border-r border-slate-200 last:border-r-0 font-mono font-medium {{ $q->is_selected ? 'bg-emerald-50/20' : '' }}">
                                            {{ $q->formatted_subtotal }}
                                        </td>
                                    @endforeach
                                </tr>

                                <!-- Row: Ongkos Kirim -->
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-3 font-semibold text-slate-600 bg-slate-50/50 border-r border-slate-200">
                                        Ongkos Kirim
                                    </td>
                                    @foreach($quotations as $q)
                                        <td class="px-5 py-3 border-r border-slate-200 last:border-r-0 font-mono {{ $q->is_selected ? 'bg-emerald-50/20' : '' }}">
                                            @if($q->shipping_cost == 0)
                                                <span class="text-emerald-700 font-bold">Rp 0 <span class="text-[10px] font-sans px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 ml-1">★ Gratis</span></span>
                                            @else
                                                <span>{{ $q->formatted_shipping_cost }}</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>

                                <!-- Row: Pajak -->
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-3 font-semibold text-slate-600 bg-slate-50/50 border-r border-slate-200">
                                        Pajak (PPN/Tax)
                                    </td>
                                    @foreach($quotations as $q)
                                        <td class="px-5 py-3 border-r border-slate-200 last:border-r-0 font-mono text-slate-500 {{ $q->is_selected ? 'bg-emerald-50/20' : '' }}">
                                            {{ $q->formatted_tax_amount }}
                                        </td>
                                    @endforeach
                                </tr>

                                <!-- Row: Grand Total -->
                                <tr class="bg-indigo-50/30 hover:bg-indigo-50/50 font-bold border-y-2 border-indigo-100">
                                    <td class="px-4 py-3.5 text-slate-900 uppercase tracking-wider text-[11px] bg-indigo-50/60 border-r border-slate-200">
                                        Grand Total Penawaran
                                    </td>
                                    @foreach($quotations as $q)
                                        <td class="px-5 py-3.5 border-r border-slate-200 last:border-r-0 font-mono font-extrabold text-sm text-indigo-900 {{ $q->is_selected ? 'bg-emerald-100/40 text-emerald-950' : '' }}">
                                            <div>{{ $q->formatted_grand_total }}</div>
                                            @if($q->is_lowest_price && $quotations->count() > 1)
                                                <span class="inline-block mt-1 text-[10px] font-sans font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                    ★ Termurah
                                                </span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>

                                <!-- Row: Estimasi Waktu Pengiriman -->
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-3 font-semibold text-slate-600 bg-slate-50/50 border-r border-slate-200">
                                        Estimasi Pengiriman
                                    </td>
                                    @foreach($quotations as $q)
                                        <td class="px-5 py-3 border-r border-slate-200 last:border-r-0 {{ $q->is_selected ? 'bg-emerald-50/20' : '' }}">
                                            <span class="font-bold text-slate-900">{{ $q->estimated_delivery_days }} Hari Kerja</span>
                                            @if($q->is_fastest_delivery && $quotations->count() > 1)
                                                <span class="inline-block text-[10px] font-bold px-1.5 py-0.5 rounded bg-sky-100 text-sky-800 ml-1">
                                                    ★ Tercepat
                                                </span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>

                                <!-- Row: Durasi Garansi -->
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-3 font-semibold text-slate-600 bg-slate-50/50 border-r border-slate-200">
                                        Ketentuan Garansi
                                    </td>
                                    @foreach($quotations as $q)
                                        <td class="px-5 py-3 border-r border-slate-200 last:border-r-0 {{ $q->is_selected ? 'bg-emerald-50/20' : '' }}">
                                            <div class="font-semibold text-slate-900">{{ $q->warranty_months }} Bulan</div>
                                            <div class="text-[11px] text-slate-500">{{ $q->warranty_info ?? 'Garansi Standar' }}</div>
                                            @if($q->is_longest_warranty && $quotations->count() > 1)
                                                <span class="inline-block mt-0.5 text-[10px] font-bold px-1.5 py-0.5 rounded bg-purple-100 text-purple-800">
                                                    ★ Terlama
                                                </span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>

                                <!-- Row: Rating Historis Vendor -->
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-3 font-semibold text-slate-600 bg-slate-50/50 border-r border-slate-200">
                                        Rating Rekanan Vendor
                                    </td>
                                    @foreach($quotations as $q)
                                        <td class="px-5 py-3 border-r border-slate-200 last:border-r-0 {{ $q->is_selected ? 'bg-emerald-50/20' : '' }}">
                                            <div class="flex items-center gap-1 font-bold text-amber-600">
                                                <span>⭐ {{ number_format($q->vendor?->rating ?? 5.0, 1) }}</span>
                                                <span class="text-slate-400 font-normal text-[11px]">/ 5.0</span>
                                            </div>
                                            <div class="text-[10px] text-slate-400">{{ $q->vendor?->category }}</div>
                                        </td>
                                    @endforeach
                                </tr>

                                <!-- Row: Masa Berlaku Penawaran & Status Kedaluwarsa -->
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-3 font-semibold text-slate-600 bg-slate-50/50 border-r border-slate-200">
                                        Masa Berlaku Penawaran
                                    </td>
                                    @foreach($quotations as $q)
                                        <td class="px-5 py-3 border-r border-slate-200 last:border-r-0 {{ $q->is_selected ? 'bg-emerald-50/20' : '' }}">
                                            @if($q->valid_until)
                                                <div class="font-medium {{ $q->is_expired ? 'text-rose-600' : 'text-slate-800' }}">
                                                    {{ $q->valid_until->format('d M Y') }}
                                                </div>
                                                @if($q->is_expired)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold bg-rose-100 text-rose-800 border border-rose-200 mt-1 animate-pulse">
                                                        ⚠️ KEDALUWARSA / EXPIRED
                                                    </span>
                                                @else
                                                    <span class="text-[10px] text-slate-400">Masih berlaku</span>
                                                @endif
                                            @else
                                                <span class="text-slate-400 italic">Tidak ditentukan</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>

                                <!-- Row: Berkas Dokumen PDF -->
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-3 font-semibold text-slate-600 bg-slate-50/50 border-r border-slate-200">
                                        Lampiran Dokumen
                                    </td>
                                    @foreach($quotations as $q)
                                        <td class="px-5 py-3 border-r border-slate-200 last:border-r-0 {{ $q->is_selected ? 'bg-emerald-50/20' : '' }}">
                                            @if($q->file_path)
                                                <a href="{{ route('quotations.attachment', $q) }}" target="_blank"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1 bg-white border border-slate-300 hover:bg-slate-50 text-indigo-700 text-xs font-semibold rounded-lg shadow-2xs transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    <span>Buka Berkas (Terkunci & Aman)</span>
                                                </a>
                                            @else
                                                <span class="text-slate-400 italic">Tidak ada lampiran</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>

                                <!-- Row: Rincian Skor Evaluasi Sistem (Weighted Scoring) -->
                                <tr class="bg-slate-900 text-white font-bold">
                                    <td class="px-4 py-3.5 text-slate-200 uppercase tracking-wider text-[11px] bg-slate-950 border-r border-slate-800">
                                        Skor Rekomendasi Sistem (100)
                                    </td>
                                    @foreach($quotations as $q)
                                        <td class="px-5 py-3.5 border-r border-slate-800 last:border-r-0 {{ $q->is_selected ? 'bg-emerald-950/80 text-emerald-200' : '' }}">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xl font-mono font-extrabold {{ $q->is_recommended ? 'text-amber-400' : 'text-white' }}">
                                                    {{ number_format($q->score, 1) }}
                                                </span>
                                                <span class="text-xs text-slate-400">/ 100</span>
                                            </div>
                                            @if(isset($q->score_breakdown))
                                                <div class="text-[10px] text-slate-300 space-y-0.5 mt-1 font-mono font-normal">
                                                    <div>Hrg: {{ $q->score_breakdown['price'] }}/45 &bull; Krm: {{ $q->score_breakdown['delivery'] }}/25</div>
                                                    <div>Grn: {{ $q->score_breakdown['warranty'] }}/15 &bull; Rep: {{ $q->score_breakdown['rating'] }}/15</div>
                                                </div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>

                                <!-- Row: Hapus Penawaran (Jika belum terpilih) -->
                                @if(Auth::user()->hasRole(['procurement', 'admin']))
                                    <tr class="bg-slate-50/30">
                                        <td class="px-4 py-3 text-slate-400 text-[10px] font-medium bg-slate-100/50 border-r border-slate-200">
                                            Kelola Baris Penawaran
                                        </td>
                                        @foreach($quotations as $q)
                                            <td class="px-5 py-3 border-r border-slate-200 last:border-r-0">
                                                @if(!$q->is_selected)
                                                    <form method="POST" action="{{ route('quotations.destroy', $q) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus surat penawaran dari {{ $q->vendor?->name }}?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-rose-600 hover:text-rose-800 font-medium text-[11px] transition">
                                                            Hapus Penawaran
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-emerald-700 font-bold text-[11px]">Terpilih (Aktif)</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endif

                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Methodology Transparency Panel (Corporate Audit & Governance Compliant) -->
                <div x-data="{ openMethodology: false }" class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                    <button type="button" @click="openMethodology = !openMethodology" class="w-full p-4 text-left flex items-center justify-between hover:bg-slate-50 transition">
                        <div class="flex items-center gap-2.5">
                            <span class="text-indigo-600 text-lg">📐</span>
                            <div>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900">Transparansi Metodologi & Rumus Pembobotan Skor Sistem (0 - 100 Poin)</h4>
                                <p class="text-[11px] text-slate-500 mt-0.5">Penjelasan formula matematis, normalisasi data, dan aturan pemutus seri (tie-breaker) untuk audit pengadaan.</p>
                            </div>
                        </div>
                        <span class="text-xs font-semibold text-indigo-600 flex items-center gap-1" x-text="openMethodology ? 'Tutup Rincian ▲' : 'Lihat Formula Matematis ▼'"></span>
                    </button>

                    <div x-show="openMethodology" x-transition class="p-5 border-t border-slate-100 bg-slate-50/50 space-y-4 text-xs">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                            <div class="bg-white p-3.5 rounded-xl border border-slate-200 space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-900 text-xs">1. Efisiensi Harga</span>
                                    <span class="font-bold font-mono px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 text-[11px]">Bobot 45%</span>
                                </div>
                                <p class="font-mono text-[11px] text-indigo-600 font-bold mt-1">(Harga Terendah / Harga Penawaran) × 45</p>
                                <p class="text-[10px] text-slate-500 leading-relaxed">
                                    Rasio terbalik: Rekanan dengan harga paling hemat memperoleh 45.0 poin. Harga yang lebih mahal terdepresiasi secara proporsional.
                                </p>
                            </div>

                            <div class="bg-white p-3.5 rounded-xl border border-slate-200 space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-900 text-xs">2. Kecepatan Kirim</span>
                                    <span class="font-bold font-mono px-2 py-0.5 rounded bg-blue-50 text-blue-700 text-[11px]">Bobot 25%</span>
                                </div>
                                <p class="font-mono text-[11px] text-blue-600 font-bold mt-1">(Hari Tercepat / Hari Penawaran) × 25</p>
                                <p class="text-[10px] text-slate-500 leading-relaxed">
                                    Vendor dengan estimasi tiba paling cepat mendapat 25.0 poin penuh, mendorong pemenuhan kebutuhan operasional tepat waktu.
                                </p>
                            </div>

                            <div class="bg-white p-3.5 rounded-xl border border-slate-200 space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-900 text-xs">3. Proteksi Garansi</span>
                                    <span class="font-bold font-mono px-2 py-0.5 rounded bg-purple-50 text-purple-700 text-[11px]">Bobot 15%</span>
                                </div>
                                <p class="font-mono text-[11px] text-purple-600 font-bold mt-1">(Bulan Garansi / Garansi Maks) × 15</p>
                                <p class="text-[10px] text-slate-500 leading-relaxed">
                                    Memberikan apresiasi pada rekanan yang menjamin ketahanan produk dan layanan purna jual resmi terlama.
                                </p>
                            </div>

                            <div class="bg-white p-3.5 rounded-xl border border-slate-200 space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-900 text-xs">4. Reputasi Vendor</span>
                                    <span class="font-bold font-mono px-2 py-0.5 rounded bg-amber-50 text-amber-700 text-[11px]">Bobot 15%</span>
                                </div>
                                <p class="font-mono text-[11px] text-amber-700 font-bold mt-1">(Rating Rekanan / 5.0) × 15</p>
                                <p class="text-[10px] text-slate-500 leading-relaxed">
                                    Berdasarkan rekam jejak performa historis, kepatuhan mutu QC, dan integritas transaksi sebelumnya.
                                </p>
                            </div>
                        </div>

                        <!-- Tie-breaker rules -->
                        <div class="p-3 bg-indigo-50/50 rounded-xl border border-indigo-100 flex items-start gap-2.5">
                            <span class="text-indigo-600 text-sm">⚖️</span>
                            <div class="text-[11px] text-indigo-950 space-y-0.5">
                                <span class="font-bold block">Hierarki Penentuan Rekomendasi Jika Terjadi Skor Seri (Tie-Breaker Rule):</span>
                                <p class="text-indigo-900 leading-relaxed">
                                    Jika terdapat dua atau lebih vendor dengan skor akhir identik, sistem menggunakan urutan prioritas deterministik objektif: 
                                    <strong>(1) Harga Lebih Rendah</strong> &rarr; 
                                    <strong>(2) Waktu Pengiriman Lebih Cepat</strong> &rarr; 
                                    <strong>(3) Rating Historis Lebih Tinggi</strong> &rarr; 
                                    <strong>(4) Registrasi Penawaran Lebih Awal</strong>.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vendor Decision & Award Panel -->
                @if(Auth::user()->hasRole(['procurement', 'admin']))
                    <div x-data="{
                        selectedId: '{{ $selectedQuotation?->id ?? '' }}',
                        isSingleSource: {{ $selectedQuotation && $selectedQuotation->is_single_source ? 'true' : 'false' }},
                        requiresTwo: {{ $ruleRequiresTwoQuotations ? 'true' : 'false' }},
                        totalQ: {{ $quotationCount }}
                    }" class="bg-white rounded-2xl border-2 {{ $selectedQuotation ? 'border-emerald-300 bg-emerald-50/10' : 'border-slate-200' }} shadow-xs p-6 space-y-6">

                        <div class="border-b border-slate-100 pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <h3 class="text-base font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                                    <span>Keputusan Penetapan Vendor (Vendor Awarding)</span>
                                    @if($selectedQuotation)
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            ✓ Sudah Ditetapkan
                                        </span>
                                    @endif
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Sistem memberikan rekomendasi skor, namun wewenang keputusan akhir sepenuhnya berada pada tim Procurement.
                                </p>
                            </div>
                        </div>

                        <!-- Single Source Established Certificate Box -->
                        @if($selectedQuotation && $selectedQuotation->is_single_source)
                            <div class="p-4 bg-amber-50 border-2 border-amber-300 rounded-xl space-y-2 text-xs">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-lg">📜</span>
                                        <h4 class="font-extrabold text-amber-950">Sertifikat Otorisasi Penyedia Tunggal (Single Source Governance)</h4>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-200 text-amber-900">
                                        Otorisasi Sah Direksi
                                    </span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-amber-900 pt-1">
                                    <div>
                                        <span class="text-amber-700 text-[10px] block uppercase font-bold">Kategori Diskresi:</span>
                                        <strong class="text-xs">{{ $selectedQuotation->single_source_category_label ?? '-' }}</strong>
                                    </div>
                                    <div>
                                        <span class="text-amber-700 text-[10px] block uppercase font-bold">Nomor Nota Dinas / SK:</span>
                                        <strong class="font-mono text-xs">{{ $selectedQuotation->single_source_memo_number ?? '-' }}</strong>
                                    </div>
                                    <div>
                                        <span class="text-amber-700 text-[10px] block uppercase font-bold">Pejabat Pengesah:</span>
                                        <strong class="text-xs">{{ $selectedQuotation->single_source_approver_name ?? '-' }}</strong>
                                    </div>
                                </div>
                                <div class="bg-white/80 p-2.5 rounded-lg border border-amber-200 text-amber-900 mt-1">
                                    <span class="font-bold text-[10px] uppercase block text-amber-800">Justifikasi Teknis / Bisnis Pengadaan:</span>
                                    <p class="mt-0.5 text-[11px] leading-relaxed">{{ $selectedQuotation->single_source_reason }}</p>
                                </div>
                            </div>
                        @endif

                        <!-- Rule Minimum Quotation Warning -->
                        @if($ruleRequiresTwoQuotations && $quotationCount < 2)
                            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-start gap-3">
                                <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <div>
                                    <p class="font-bold text-amber-950">Aturan Pengadaan Bernilai Tinggi (> Rp10.000.000)</p>
                                    <p class="text-amber-800 mt-0.5 leading-relaxed">
                                        PR ini bernilai di atas Rp10 juta dan baru memiliki <strong>{{ $quotationCount }} penawaran vendor</strong>. Sesuai tata kelola pengadaan, diperlukan minimal 2 quotation pembanding, kecuali jika Anda menetapkan alasan khusus sebagai <strong>Penyedia Tunggal (Single Source)</strong>.
                                    </p>
                                </div>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('quotations.select', $purchaseRequest) }}" class="space-y-5">
                            @csrf

                            <!-- Radio Selection List -->
                            <div class="space-y-3">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                    Pilih Vendor Pemenang Pengadaan: <span class="text-rose-500">*</span>
                                </label>
                                <div class="grid grid-cols-1 md:grid-cols-{{ min(3, $quotations->count()) }} gap-4">
                                    @foreach($quotations as $q)
                                        <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all duration-150"
                                            :class="selectedId == '{{ $q->id }}' ? 'border-indigo-600 bg-indigo-50/30 ring-2 ring-indigo-500/20' : 'border-slate-200 hover:border-slate-300 bg-white'">
                                            <div class="flex items-start justify-between">
                                                <input type="radio" name="quotation_id" value="{{ $q->id }}" x-model="selectedId" required
                                                    class="w-4 h-4 text-indigo-600 border-slate-300 focus:ring-indigo-500 mt-0.5">
                                                <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700">
                                                    Skor: {{ number_format($q->score, 1) }}
                                                </span>
                                            </div>
                                            <div class="mt-3">
                                                <h5 class="font-extrabold text-sm text-slate-900 leading-tight">{{ $q->vendor?->name }}</h5>
                                                <p class="font-mono font-extrabold text-indigo-700 text-base mt-1">{{ $q->formatted_grand_total }}</p>
                                                <p class="text-[11px] text-slate-500 mt-1">
                                                    Pengiriman: {{ $q->estimated_delivery_days }} hari &bull; Garansi: {{ $q->warranty_months }} bln
                                                </p>
                                                @if($q->is_expired)
                                                    <p class="text-[10px] font-bold text-rose-600 mt-1">⚠️ Surat penawaran kedaluwarsa</p>
                                                @endif
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Single Source Checkbox & Multi-Field Governance Form -->
                            <div class="pt-3 border-t border-slate-100">
                                <label class="flex items-start gap-2.5 cursor-pointer">
                                    <input type="checkbox" name="is_single_source" value="1" x-model="isSingleSource"
                                        class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500 mt-0.5">
                                    <div>
                                        <span class="text-xs font-bold text-slate-900">Tetapkan Sebagai Penyedia Tunggal (Single Source Procurement)</span>
                                        <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                                            Centang jika barang/jasa ini bersifat hak paten eksklusif, distibutor tunggal resmi, kompatibilitas sistem kritis, atau tidak memiliki vendor pembanding di pasar.
                                        </p>
                                    </div>
                                </label>

                                <div x-show="isSingleSource" x-transition class="mt-4 pl-6 space-y-3">
                                    <div class="p-3 bg-amber-50/70 border border-amber-200 rounded-xl text-xs space-y-1">
                                        <span class="font-bold text-amber-900">Formulir Kepatuhan Audit Penunjukan Langsung (Single Source):</span>
                                        <p class="text-amber-800 text-[11px]">
                                            Sesuai ketentuan audit BPK/Internal, penetapan diskresi single source wajib mendokumentasikan dasar pertimbangan, nomor nota dinas persetujuan, dan pejabat yang mengesahkan.
                                        </p>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                                                Kategori Diskresi Single Source <span class="text-rose-500">*</span>
                                            </label>
                                            <select name="single_source_category" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 focus:ring-indigo-500 bg-white" :required="isSingleSource">
                                                <option value="">-- Pilih Kategori Diskresi --</option>
                                                <option value="sole_distributor" {{ old('single_source_category', $selectedQuotation?->single_source_category) === 'sole_distributor' ? 'selected' : '' }}>
                                                    Distributor Tunggal / Hak Paten Resmi
                                                </option>
                                                <option value="standardization" {{ old('single_source_category', $selectedQuotation?->single_source_category) === 'standardization' ? 'selected' : '' }}>
                                                    Standarisasi Perangkat / Lisensi Khusus
                                                </option>
                                                <option value="emergency" {{ old('single_source_category', $selectedQuotation?->single_source_category) === 'emergency' ? 'selected' : '' }}>
                                                    Keadaan Darurat / Bencana Operasional
                                                </option>
                                                <option value="urgent_operational" {{ old('single_source_category', $selectedQuotation?->single_source_category) === 'urgent_operational' ? 'selected' : '' }}>
                                                    Kebutuhan Mendesak Terikat Kontrak Proyek
                                                </option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                                                Nomor Nota Dinas / Surat Persetujuan Direksi <span class="text-rose-500">*</span>
                                            </label>
                                            <input type="text" name="single_source_memo_number" value="{{ old('single_source_memo_number', $selectedQuotation?->single_source_memo_number) }}"
                                                placeholder="Cth: ND-DIR/09/2026/088 atau SK-DIREKSI/2026/014" :required="isSingleSource"
                                                class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 focus:ring-indigo-500">
                                        </div>

                                        <div class="sm:col-span-2">
                                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                                                Pejabat / Direktur Pemberi Otorisasi <span class="text-rose-500">*</span>
                                            </label>
                                            <input type="text" name="single_source_approver_name" value="{{ old('single_source_approver_name', $selectedQuotation?->single_source_approver_name) }}"
                                                placeholder="Cth: Bpk. Ir. Bambang Wijaya (Direktur Operasional & IT)" :required="isSingleSource"
                                                class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 focus:ring-indigo-500">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                                            Justifikasi Teknis & Bisnis Single Source <span class="text-rose-500">*</span>
                                        </label>
                                        <textarea name="single_source_reason" rows="2" placeholder="Jelaskan alasan mengapa pengadaan ini hanya dapat dilakukan melalui vendor tunggal dan tidak dimungkinkan tender terbuka (minimal 10 karakter)..."
                                            :required="isSingleSource"
                                            class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-indigo-500">{{ old('single_source_reason', $selectedQuotation?->single_source_reason) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Multi-Vendor / Split PO Awarding Option -->
                            <div class="pt-3 border-t border-slate-100">
                                <label class="flex items-start gap-2.5 cursor-pointer">
                                    <input type="checkbox" name="is_split_award" value="1"
                                        class="w-4 h-4 text-emerald-600 border-slate-300 rounded focus:ring-emerald-500 mt-0.5">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-slate-900">Tetapkan Sebagai Pemenang Parsial (Multi-Vendor / Split PO)</span>
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800 font-mono">Split PO</span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                                            Centang jika PR ini memiliki item berbeda yang dimenangkan oleh vendor berbeda (contoh: Vendor IT untuk Laptop dan Vendor Furnitur untuk Kursi). Pilihan vendor lain tidak akan dibatalkan, dan Anda dapat menerbitkan PO terpisah untuk masing-masing vendor.
                                        </p>
                                    </div>
                                </label>
                            </div>

                            <!-- Selection Reason Textarea -->
                            <div class="pt-3 border-t border-slate-100">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                    Alasan Keputusan Pemilihan Vendor <span class="text-rose-500">*</span>
                                </label>
                                <textarea name="selection_reason" rows="3" required placeholder="Contoh: Menawarkan harga paling efisien, garansi resmi 2 tahun, dan rekam jejak performa kerja sangat memuaskan."
                                    class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-indigo-500">{{ old('selection_reason', $selectedQuotation?->selection_reason) }}</textarea>
                                <p class="text-[11px] text-slate-400 mt-1">Catatan ini akan tersimpan permanen pada Jejak Audit (*Status History*) pengadaan.</p>
                            </div>

                            <!-- Action Button -->
                            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                                <p class="text-xs text-slate-500">
                                    * Menetapkan vendor akan mengubah status PR menjadi <strong>Processing (Dalam Pengadaan)</strong>.
                                </p>
                                <button type="submit"
                                    class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/25 transition flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Simpan Penetapan Vendor</span>
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

            @endif

        </div>
    </div>

    <!-- Modal Gagal Tender / Batalkan RFQ -->
    <div id="fail-tender-modal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 border border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2 text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <h3 class="font-bold text-slate-900 text-sm">Gagal Tender / Kembalikan ke Requester</h3>
                </div>
                <button type="button" onclick="document.getElementById('fail-tender-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-lg leading-none">&times;</button>
            </div>

            <p class="text-xs text-slate-600 leading-relaxed">
                Tindakan ini akan membatalkan proses tender RFQ saat ini, mengubah status PR menjadi <strong class="text-amber-700">Minta Revisi (Revision Required)</strong>, dan mengembalikan berkas ke pemohon agar spesifikasi teknis atau estimasi anggaran dapat disesuaikan.
            </p>

            <form method="POST" action="{{ route('quotations.fail-tender', $purchaseRequest) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Alasan Tender Gagal *</label>
                    <select name="failure_category" required class="w-full text-xs border border-slate-300 rounded-xl p-2.5 focus:ring-1 focus:ring-amber-500">
                        <option value="">-- Pilih Alasan Kegagalan --</option>
                        <option value="out_of_stock">Seluruh Vendor Menolak / Stok Habis (Out of Stock / Discontinued)</option>
                        <option value="over_budget">Seluruh Penawaran Jauh Melampaui Pagu Anggaran PR (Over Budget)</option>
                        <option value="no_responsive_bids">Tidak Ada Penawaran yang Memenuhi Syarat Kepatuhan (Non-Responsive)</option>
                        <option value="specification_revision">Perlu Penyesuaian Spesifikasi Teknis / Kebutuhan Berubah</option>
                        <option value="other">Alasan Lainnya</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kronologi & Catatan Penjelasan *</label>
                    <textarea name="failure_reason" rows="4" required minlength="5"
                        placeholder="Contoh: Dari 3 vendor yang diundang, 2 menolak karena stok laptop tipe ini discontinued dari distributor, dan 1 vendor menawarkan harga Rp 22 juta (pagu anggaran PR hanya Rp 15 juta). Disarankan requester mengubah tipe processor atau menaikkan estimasi biaya."
                        class="w-full text-xs border border-slate-300 rounded-xl p-3 focus:ring-1 focus:ring-amber-500 focus:border-amber-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('fail-tender-modal').classList.add('hidden')"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shadow-md shadow-amber-600/20 transition">
                        Konfirmasi Kembalikan ke Requester
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
