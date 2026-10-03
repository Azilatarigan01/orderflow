<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-slate-900 tracking-tight">Pusat Laporan & Audit Pengadaan</h2>
                <p class="text-sm text-slate-500 mt-0.5">Analisis realisasi anggaran, komitmen PO, dan penerimaan fisik barang/jasa perusahaan</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Standar Kepatuhan & Penjelasan Posting Date -->
            <div class="bg-indigo-900 text-white rounded-2xl p-6 shadow-sm border border-indigo-800 space-y-2">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-500/30 text-indigo-200 border border-indigo-400/30">
                        Standar Tata Kelola Korporat (ERP & Audit)
                    </span>
                    <span class="text-xs text-indigo-200">ISO 27001 &bull; ISO 37001 GCG</span>
                </div>
                <h3 class="text-lg font-extrabold text-white">Prinsip Tanggal Pencatatan Resmi (Posting Date)</h3>
                <p class="text-xs text-indigo-100 leading-relaxed max-w-4xl">
                    Di OrderFlow Enterprise, laporan dibedakan secara tegas berdasarkan siklus pengadaan untuk mencegah distorsi data audit:
                    <strong>Perencanaan Belanja (PR)</strong> dicatat saat diajukan, 
                    <strong>Komitmen Anggaran (PO)</strong> dicatat pada tanggal persetujuan penerbitan PO ke vendor, sedangkan 
                    <strong>Realisasi Fisik (GR/BAST)</strong> dicatat pada tanggal inspeksi fisik barang tiba di gudang.
                </p>
            </div>

            <!-- Grid 4 Modul Laporan Eksekutif -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                {{-- 1. PR Report --}}
                <a href="{{ route('reports.pr') }}" class="group block bg-white rounded-2xl border border-slate-200 p-6 hover:border-indigo-300 hover:shadow-lg transition-all">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center group-hover:bg-indigo-100 text-indigo-600 transition flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <h3 class="font-extrabold text-slate-900 group-hover:text-indigo-600 transition text-base">Laporan Purchase Request (PR)</h3>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">Perencanaan Belanja</span>
                            </div>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Rekapitulasi permohonan pengadaan divisi, estimasi kebutuhan dana, persetujuan bertingkat, dan status verifikasi.
                            </p>
                            <div class="text-[11px] text-slate-400 pt-1">
                                <strong>Posting Date:</strong> Tanggal pengajuan dibuat &bull; Rentang dinamis &bull; Max 365 hari
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-semibold">
                        <span class="text-indigo-600 group-hover:underline flex items-center gap-1">
                            Buka Rekapitulasi &rarr;
                        </span>
                        <div class="flex gap-2">
                            <span class="text-[10px] px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">CSV Export</span>
                            <span class="text-[10px] px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">PDF Landscape</span>
                        </div>
                    </div>
                </a>

                {{-- 2. PO Report --}}
                <a href="{{ route('reports.po') }}" class="group block bg-white rounded-2xl border border-slate-200 p-6 hover:border-purple-300 hover:shadow-lg transition-all">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-purple-50 border border-purple-100 flex items-center justify-center group-hover:bg-purple-100 text-purple-600 transition flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        </div>
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <h3 class="font-extrabold text-slate-900 group-hover:text-purple-600 transition text-base">Laporan Purchase Order (PO)</h3>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-700">Komitmen Anggaran</span>
                            </div>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Kontrak belanja resmi per vendor, nilai subtotal + PPN, syarat pembayaran (*Term of Payment*), dan outstanding PO belum selesai.
                            </p>
                            <div class="text-[11px] text-slate-400 pt-1">
                                <strong>Posting Date:</strong> Tanggal penerbitan PO ke vendor (Order Date)
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-semibold">
                        <span class="text-purple-600 group-hover:underline flex items-center gap-1">
                            Buka Rekapitulasi &rarr;
                        </span>
                        <div class="flex gap-2">
                            <span class="text-[10px] px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">CSV Export</span>
                            <span class="text-[10px] px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">PDF Landscape</span>
                        </div>
                    </div>
                </a>

                {{-- 3. GR & BAST Report (Realisasi Fisik) --}}
                <a href="{{ route('reports.gr') }}" class="group block bg-white rounded-2xl border border-slate-200 p-6 hover:border-amber-300 hover:shadow-lg transition-all">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center group-hover:bg-amber-100 text-amber-600 transition flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <h3 class="font-extrabold text-slate-900 group-hover:text-amber-600 transition text-base">Laporan Penerimaan Barang & Jasa (GR / BAST)</h3>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Realisasi Fisik & Mutu</span>
                            </div>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Pengawasan serah terima fisik gudang, surat jalan rekanan, berita acara jasa, inspeksi barang lolos QC vs cacat (RTV).
                            </p>
                            <div class="text-[11px] text-slate-400 pt-1">
                                <strong>Posting Date:</strong> Tanggal penerimaan fisik barang/jasa (Received Date)
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-semibold">
                        <span class="text-amber-600 group-hover:underline flex items-center gap-1">
                            Buka Rekapitulasi &rarr;
                        </span>
                        <div class="flex gap-2">
                            <span class="text-[10px] px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">CSV Export</span>
                            <span class="text-[10px] px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">PDF Landscape</span>
                        </div>
                    </div>
                </a>

                {{-- 4. Vendor Spend --}}
                <a href="{{ route('reports.vendor-spend') }}" class="group block bg-white rounded-2xl border border-slate-200 p-6 hover:border-emerald-300 hover:shadow-lg transition-all">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center group-hover:bg-emerald-100 text-emerald-600 transition flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <h3 class="font-extrabold text-slate-900 group-hover:text-emerald-600 transition text-base">Pengeluaran Per Vendor (Spend Analysis)</h3>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Evaluasi Rekanan</span>
                            </div>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Konsentrasi pengeluaran fiskal rekanan, evaluasi rating bintang, rekam jejak lead time, dan diversifikasi pasokan.
                            </p>
                            <div class="text-[11px] text-slate-400 pt-1">
                                <strong>Posting Date:</strong> Akumulasi tahun buku berjalan &bull; Analisis kuartal
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-semibold">
                        <span class="text-emerald-600 group-hover:underline flex items-center gap-1">
                            Buka Rekapitulasi &rarr;
                        </span>
                        <div class="flex gap-2">
                            <span class="text-[10px] px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">CSV Export</span>
                            <span class="text-[10px] px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">PDF Landscape</span>
                        </div>
                    </div>
                </a>

            </div>

        </div>
    </div>
</x-app-layout>
