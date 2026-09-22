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
                @if($selectedQuotation && Auth::user()->hasRole(['procurement', 'admin']))
                    <a href="{{ route('purchase-orders.create', ['purchase_request_id' => $purchaseRequest->id]) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/25 transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Terbitkan PO Resmi</span>
                    </a>
                @endif
                @if(Auth::user()->hasRole(['procurement', 'admin']))
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
            <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-2xl p-6 shadow-xl border border-indigo-500/20">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2 space-y-1">
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
                </div>
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
                                                <a href="{{ asset('storage/' . $q->file_path) }}" target="_blank"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1 bg-white border border-slate-300 hover:bg-slate-50 text-indigo-700 text-xs font-semibold rounded-lg shadow-2xs transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    <span>Buka Berkas</span>
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

                            <!-- Single Source Checkbox (if needed) -->
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

                                <div x-show="isSingleSource" x-transition class="mt-3 pl-6">
                                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                        Justifikasi Bisnis Single Source <span class="text-rose-500">*</span>
                                    </label>
                                    <textarea name="single_source_reason" rows="2" placeholder="Jelaskan alasan mengapa pengadaan ini hanya dapat dilakukan melalui vendor tunggal..."
                                        class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-indigo-500">{{ old('single_source_reason', $selectedQuotation?->single_source_reason) }}</textarea>
                                </div>
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
</x-app-layout>
