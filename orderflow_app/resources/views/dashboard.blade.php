<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-slate-900 tracking-tight leading-tight">
                    Dashboard Operasional Pengadaan
                </h2>
                <div class="flex items-center gap-3 mt-1 text-sm text-slate-500 font-medium">
                    <span>Pengguna: <strong class="text-slate-800">{{ $user->name }}</strong></span>
                    <span>&bull;</span>
                    <span>Divisi: <strong class="text-slate-800">{{ $user->department?->name ?? 'Lintas Divisi' }}</strong></span>
                    <span>&bull;</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                        {{ $user->role_label }}
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-xs font-medium px-3 py-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-md flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Sesi Terotentikasi Aman
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Executive Summary Banner -->
            <div class="bg-slate-900 rounded-xl p-8 text-white border border-slate-800 shadow-sm relative overflow-hidden">
                <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                    <div class="space-y-2">
                        <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded text-[11px] font-semibold bg-slate-800 text-slate-300 border border-slate-700 tracking-wide uppercase">
                            Sistem Tata Kelola Pengadaan
                        </div>
                        <h3 class="text-xl font-bold tracking-tight">Portal Manajemen Pengadaan & Rekanan Terintegrasi</h3>
                        <p class="text-sm text-slate-300 max-w-2xl leading-relaxed font-normal">
                            Menjaga akuntabilitas dan efisiensi pengeluaran internal korporat melalui integrasi formulir pengajuan, approval otorisasi bertingkat, komparasi penawaran supplier, hingga rekonsiliasi tiga arah (three-way matching) faktur pembayaran.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('vendors.index') }}" class="px-5 py-2.5 bg-white hover:bg-slate-100 text-slate-900 text-sm font-semibold rounded-lg shadow-sm transition flex items-center gap-2">
                            <span>Buka Direktori Rekanan</span>
                            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- KPI Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-xs">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <span>Total Pengajuan PR</span>
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    </div>
                    <div class="text-3xl font-extrabold text-slate-900 mt-3 tracking-tight">{{ $totalPr }}</div>
                    <div class="text-xs text-slate-500 mt-1.5 flex items-center justify-between">
                        <span>Pagu: <strong class="text-slate-800 font-mono">Rp {{ number_format($totalPrBudget, 0, ',', '.') }}</strong></span>
                        <a href="{{ route('purchase-requests.index') }}" class="text-indigo-600 font-semibold hover:underline">Lihat &rarr;</a>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-xs">
                    <div class="flex items-center justify-between text-xs font-semibold text-sky-600 uppercase tracking-wider">
                        <span>Menunggu Approval</span>
                        <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                    </div>
                    <div class="text-3xl font-extrabold text-sky-700 mt-3 tracking-tight">{{ $pendingPr }}</div>
                    <div class="text-xs text-slate-500 mt-1.5">Memerlukan tinjauan atasan divisi</div>
                </div>

                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-xs">
                    <div class="flex items-center justify-between text-xs font-semibold text-amber-600 uppercase tracking-wider">
                        <span>Perlu Revisi</span>
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    </div>
                    <div class="text-3xl font-extrabold text-amber-700 mt-3 tracking-tight">{{ $revisionPr }}</div>
                    <div class="text-xs text-slate-500 mt-1.5">Permintaan perbaikan dari reviewer</div>
                </div>

                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-xs">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <span>Rekanan Terdaftar</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    </div>
                    <div class="text-3xl font-extrabold text-slate-900 mt-3 tracking-tight">{{ $totalVendors }}</div>
                    <div class="text-xs text-slate-500 mt-1.5">
                        <span class="text-emerald-600 font-semibold">{{ $activeVendors }} aktif</span> &bull; Terverifikasi legalitas
                    </div>
                </div>
            </div>

            <!-- Procurement Lifecycle Pipeline -->
            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between mb-5 border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Alur Tata Kelola Pengadaan Terpadu</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Siklus pengadaan end-to-end terstandarisasi operasional perusahaan.</p>
                    </div>
                    <span class="text-xs font-semibold text-slate-700 bg-slate-100 px-2.5 py-1 rounded border border-slate-200">
                        Standar Operasional Prosedur
                    </span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
                    <div class="p-4 rounded-lg border border-slate-200 bg-slate-50">
                        <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Tahap 01</div>
                        <div class="text-sm font-bold text-slate-900 mt-1">Purchase Request</div>
                        <p class="text-xs text-slate-500 mt-1">Pengajuan kebutuhan divisi</p>
                    </div>

                    <div class="p-4 rounded-lg border border-slate-200 bg-slate-50">
                        <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Tahap 02</div>
                        <div class="text-sm font-bold text-slate-900 mt-1">Otorisasi / Approval</div>
                        <p class="text-xs text-slate-500 mt-1">Persetujuan berjenjang</p>
                    </div>

                    <div class="p-4 rounded-lg border border-slate-200 bg-slate-50">
                        <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Tahap 03</div>
                        <div class="text-sm font-bold text-slate-900 mt-1">RFQ & Komparasi</div>
                        <p class="text-xs text-slate-500 mt-1">Perbandingan penawaran</p>
                    </div>

                    <div class="p-4 rounded-lg border border-slate-200 bg-slate-50">
                        <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Tahap 04</div>
                        <div class="text-sm font-bold text-slate-900 mt-1">Purchase Order</div>
                        <p class="text-xs text-slate-500 mt-1">Kontrak pesanan resmi</p>
                    </div>

                    <div class="p-4 rounded-lg border border-slate-200 bg-slate-50">
                        <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Tahap 05</div>
                        <div class="text-sm font-bold text-slate-900 mt-1">Goods Receipt</div>
                        <p class="text-xs text-slate-500 mt-1">Penerimaan fisik & QC</p>
                    </div>

                    <div class="p-4 rounded-lg border border-slate-200 bg-slate-50">
                        <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Tahap 06</div>
                        <div class="text-sm font-bold text-slate-900 mt-1">Three-Way Match</div>
                        <p class="text-xs text-slate-500 mt-1">Verifikasi & bayar invoice</p>
                    </div>
                </div>
            </div>

            <!-- Recent Purchase Requests -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h4 class="font-bold text-sm text-slate-900">Aktivitas Pengajuan Pembelian (PR) Terbaru</h4>
                        <p class="text-xs text-slate-500">Daftar permohonan pengadaan barang/jasa internal divisi Anda</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('purchase-requests.create') }}" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-xs transition flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Buat PR Baru
                        </a>
                        <a href="{{ route('purchase-requests.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                            Lihat Semua PR &rarr;
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600 divide-y divide-slate-100">
                        <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-6 py-3">Nomor PR</th>
                                <th class="px-6 py-3">Judul Pengadaan</th>
                                <th class="px-6 py-3">Pemohon</th>
                                <th class="px-6 py-3 text-right">Total Anggaran</th>
                                <th class="px-6 py-3 text-center">Status</th>
                                <th class="px-6 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-normal">
                            @forelse($recentPrs as $pr)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-6 py-3.5 whitespace-nowrap">
                                        <a href="{{ route('purchase-requests.show', $pr) }}" class="font-mono font-bold text-indigo-600 hover:underline">
                                            {{ $pr->pr_number }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-3.5 max-w-xs truncate font-semibold text-slate-900">
                                        {{ $pr->title }}
                                    </td>
                                    <td class="px-6 py-3.5 whitespace-nowrap">
                                        {{ $pr->user?->name ?? '-' }} ({{ $pr->department?->code ?? '-' }})
                                    </td>
                                    <td class="px-6 py-3.5 whitespace-nowrap text-right font-mono font-bold text-slate-900">
                                        Rp {{ number_format($pr->estimated_total, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-3.5 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $pr->status_badge_class }}">
                                            {{ $pr->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3.5 whitespace-nowrap text-right">
                                        <a href="{{ route('purchase-requests.show', $pr) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-900">
                                            Buka &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-6 text-center text-slate-400">
                                        Belum ada aktivitas pengajuan PR.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Role Scope & Recent Vendors -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Role Scope -->
                <div class="lg:col-span-1 bg-white p-6 rounded-xl border border-slate-200 shadow-xs flex flex-col justify-between">
                    <div>
                        <span class="text-[11px] uppercase font-bold text-slate-400 tracking-wider">Wewenang Otoritas Akun</span>
                        <h4 class="font-bold text-base text-slate-900 mt-1">{{ $user->role_label }}</h4>
                        
                        <div class="mt-3 text-xs text-slate-600 leading-relaxed space-y-2">
                            @if($user->isRequester())
                                <p>Anda berwenang membuat pengajuan belanja barang (*Purchase Request*) untuk kebutuhan operasional unit kerja Anda, serta melakukan konfirmasi bukti fisik penerimaan barang (*Goods Receipt*).</p>
                            @elseif($user->isManager())
                                <p>Anda berwenang menelaah, menyetujui (*approve*), menolak (*reject*), atau mengembalikan revisi atas permohonan pengadaan yang diajukan oleh personil pada divisi {{ $user->department?->name ?? 'Anda' }}.</p>
                            @elseif($user->isProcurement())
                                <p>Anda berwenang mengelola master data rekanan vendor, mengumpulkan surat penawaran (*quotations*), menyusun analisis komparasi harga, dan menerbitkan Surat Pesanan Pembelian (*Purchase Order*).</p>
                            @elseif($user->isFinance())
                                <p>Anda berwenang memvalidasi alokasi anggaran belanja di atas Rp5.000.000 dan melaksanakan rekonsiliasi tiga arah (*Three-Way Matching*) atas kesesuaian PO, bukti terima barang, dan tagihan faktur vendor.</p>
                            @elseif($user->isAdmin())
                                <p>Anda memiliki hak kendali penuh atas administrasi master user, alokasi peran RBAC, struktur departemen, dan pengawasan audit trail transaksi secara keseluruhan.</p>
                            @else
                                <p>Anda memiliki hak akses audit kepatuhan (*independent review*) untuk memeriksa seluruh jejak aktivitas transaksi pengadaan perusahaan.</p>
                            @endif
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-100 space-y-2">
                        <a href="{{ route('vendors.index') }}" class="w-full text-center block px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-semibold transition shadow-xs">
                            Buka Direktori Rekanan Vendor
                        </a>
                        @if($user->hasRole(['procurement', 'admin']))
                            <a href="{{ route('vendors.create') }}" class="w-full text-center block px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition">
                                Tambah Rekanan Vendor Baru
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Recent Vendors Directory -->
                <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h4 class="font-bold text-sm text-slate-900">Rekanan Vendor Terdaftar</h4>
                            <p class="text-xs text-slate-500">Daftar mitra penyedia barang dan jasa resmi</p>
                        </div>
                        <a href="{{ route('vendors.index') }}" class="text-xs font-semibold text-slate-700 hover:text-slate-900">
                            Lihat Semua Rekanan &rarr;
                        </a>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach($recentVendors as $v)
                            <div class="px-6 py-4 flex items-center justify-between hover:bg-slate-50 transition">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded bg-slate-100 border border-slate-200 text-slate-700 font-bold flex items-center justify-center text-xs">
                                        {{ substr($v->code, -3) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900">{{ $v->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $v->category }} &bull; Kontak: {{ $v->contact_person }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                        Rating {{ number_format($v->rating, 1) }}
                                    </span>
                                    <a href="{{ route('vendors.show', $v) }}" class="px-3 py-1.5 text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-md transition font-medium">
                                        Detail
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
