<x-app-layout>
    <!-- Top Greeting & Header Bar (Image 2 style: Hello Admin / Hello User, Search Bar, Orange Pill Button) -->
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 py-1">
            
            <!-- Left Greeting -->
            <div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Selamat Datang, {{ $user->name }}!</span>
                </h2>
                <p class="text-xs text-slate-500 font-medium mt-0.5">
                    @if($user->hasRole('requester'))
                        Ringkasan status permohonan pengadaan barang dan penerimaan fisik unit kerja Anda.
                    @elseif($user->hasRole('warehouse'))
                        Monitoring operasional penerimaan fisik barang dan dokumen surat jalan gudang.
                    @elseif($user->hasRole('finance'))
                        Pengawasan realisasi anggaran belanja, verifikasi approval, dan pencocokan 3-way match.
                    @elseif($user->hasRole('procurement'))
                        Monitoring proses penawaran vendor (RFQ), penerbitan PO, dan efisiensi pengadaan.
                    @elseif($user->hasRole('hod'))
                        Ikhtisar eksekutif persetujuan pagu anggaran dan kepatuhan pengadaan lintas divisi.
                    @else
                        Sistem manajemen pengadaan barang &amp; jasa terintegrasi berbasis tata kelola Good Corporate Governance.
                    @endif
                </p>
            </div>

            <!-- Right Controls: Year Filter, Search Bar, Currency, Notifications, and CTA -->
            <div class="flex flex-wrap items-center gap-3">
                
                <!-- Year dropdown pill -->
                <div class="hidden sm:inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-slate-200/80 rounded-2xl shadow-xs text-xs font-semibold text-slate-700">
                    <span>Tahun {{ date('Y') }}</span>
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>

                <!-- Search input pill -->
                <div class="relative w-48 sm:w-60">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" placeholder="Cari nomor PR / PO / Vendor..." class="w-full pl-9 pr-4 py-2 bg-white border border-slate-200/80 rounded-2xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-xs">
                </div>


                <!-- Role Pill -->
                <div class="hidden md:inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-slate-200/80 rounded-2xl shadow-xs text-xs font-bold text-slate-800">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>{{ $user->role_label }}</span>
                </div>

                <!-- Notification Bell -->
                @php
                    $unreadCountForBell = \App\Models\InAppNotification::where('user_id', Auth::id())->unread()->count();
                @endphp
                <a href="{{ route('notifications.index') }}" class="relative p-2.5 bg-white border border-slate-200/80 rounded-2xl shadow-xs text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition" title="Notifikasi ({{ $unreadCountForBell }} belum dibaca)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    @if($unreadCountForBell > 0)
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                    @endif
                </a>

                <!-- Primary CTA Button (Only for roles authorized to create PR) -->
                @if(Auth::user()->hasRole(['requester', 'manager', 'admin']))
                    <a href="{{ route('purchase-requests.create') }}" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white rounded-2xl text-xs font-bold shadow-lg shadow-blue-500/25 transition-all flex items-center gap-1.5">
                        <span class="text-base leading-none">+</span>
                        <span>Buat PR Baru</span>
                    </a>
                @endif
            </div>

        </div>
    </x-slot>

    <div class="py-2" x-data="{ chartPeriod: 'monthly' }">
        <div class="max-w-7xl mx-auto space-y-6">

            <!-- 1. Overview Heading & 5 Pill KPI Cards -->
            <div>
                <h3 class="text-xl font-extrabold text-slate-900 tracking-tight mb-3">Ringkasan Kinerja Pengadaan</h3>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5 sm:gap-4">
                    
                    <!-- Pill Card 1: Coral Ring ↗ (Total PR) -->
                    <div class="card-soft-pill p-4 flex items-center space-x-3.5 hover:shadow-md transition">
                        <div class="w-12 h-12 rounded-full border-[3px] border-orange-400 bg-orange-50/70 flex items-center justify-center flex-shrink-0 text-orange-500 font-bold text-lg shadow-xs">
                            <span class="transform -rotate-12">&nearr;</span>
                        </div>
                        <div class="min-w-0">
                            <div class="text-2xl font-black text-slate-900 tracking-tight leading-none">{{ $totalPr }}</div>
                            <div class="text-xs text-slate-500 font-semibold mt-1 truncate">Total PR</div>
                        </div>
                    </div>

                    <!-- Pill Card 2: Sky Blue Ring ↙ (Menunggu Approval) -->
                    <div class="card-soft-pill p-4 flex items-center space-x-3.5 hover:shadow-md transition">
                        <div class="w-12 h-12 rounded-full border-[3px] border-sky-400 bg-sky-50/70 flex items-center justify-center flex-shrink-0 text-sky-500 font-bold text-lg shadow-xs">
                            <span class="transform -rotate-12">&swarr;</span>
                        </div>
                        <div class="min-w-0">
                            <div class="text-2xl font-black text-slate-900 tracking-tight leading-none">{{ $pendingPr }}</div>
                            <div class="text-xs text-slate-500 font-semibold mt-1 truncate">Perlu Approval</div>
                        </div>
                    </div>

                    <!-- Pill Card 3: Golden Amber Ring ↗ (PO Aktif) -->
                    <div class="card-soft-pill p-4 flex items-center space-x-3.5 hover:shadow-md transition">
                        <div class="w-12 h-12 rounded-full border-[3px] border-amber-400 bg-amber-50/70 flex items-center justify-center flex-shrink-0 text-amber-500 font-bold text-lg shadow-xs">
                            <span class="transform -rotate-12">&nearr;</span>
                        </div>
                        <div class="min-w-0">
                            <div class="text-2xl font-black text-slate-900 tracking-tight leading-none">{{ $activePo }}</div>
                            <div class="text-xs text-slate-500 font-semibold mt-1 truncate">PO Berjalan</div>
                        </div>
                    </div>

                    <!-- Pill Card 4: Emerald Green Ring ↙ (Realisasi PO Spend) -->
                    <div class="card-soft-pill p-4 flex items-center space-x-3.5 hover:shadow-md transition">
                        <div class="w-12 h-12 rounded-full border-[3px] border-emerald-400 bg-emerald-50/70 flex items-center justify-center flex-shrink-0 text-emerald-500 font-bold text-lg shadow-xs">
                            <span class="transform -rotate-12">&swarr;</span>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight leading-none font-mono">
                                {{ number_format($totalSpend / 1000000, 1, ',', '.') }}M
                            </div>
                            <div class="text-xs text-slate-500 font-semibold mt-1 truncate">Realisasi PO</div>
                        </div>
                    </div>

                    <!-- Pill Card 5: Purple Ring ↗ (3-Way Match Valid) -->
                    <div class="card-soft-pill p-4 flex items-center space-x-3.5 hover:shadow-md transition">
                        <div class="w-12 h-12 rounded-full border-[3px] border-purple-400 bg-purple-50/70 flex items-center justify-center flex-shrink-0 text-purple-500 font-bold text-lg shadow-xs">
                            <span class="transform -rotate-12">&nearr;</span>
                        </div>
                        <div class="min-w-0">
                            <div class="text-2xl font-black text-slate-900 tracking-tight leading-none">100%</div>
                            <div class="text-xs text-slate-500 font-semibold mt-1 truncate">3-Way Match Sesuai</div>
                        </div>
                    </div>

                </div>
            </div>

            @if(!Auth::user()->hasRole(['requester', 'warehouse']))
            <!-- 2. Middle Row: Spend Statistics Spline Chart + Semi-Circular Gauge Meter (Executive / Management View) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                <!-- Left: Spend Statistics Spline Chart -->
                <div class="lg:col-span-8 card-soft p-6 flex flex-col justify-between">
                    <div>
                        <!-- Chart Card Header -->
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-100 gap-3">
                            
                            <!-- Left: Title -->
                            <div class="flex items-center space-x-2">
                                <h4 class="text-base font-extrabold text-slate-800">Statistik Pengeluaran &amp; Anggaran</h4>
                            </div>

                            <!-- Center: Floating Milestone Pill -->
                            <div class="hidden md:inline-flex items-center gap-2 px-3 py-1 rounded-xl bg-slate-50 border border-slate-200/80 text-[11px] font-semibold text-slate-600 shadow-2xs">
                                <span class="w-2 h-2 rounded-full bg-[#FF7A45]"></span>
                                <span>PR: <strong class="text-slate-900 font-mono">Rp {{ number_format($totalPrBudget / 1000000, 1) }}M</strong></span>
                                <span class="text-slate-300">|</span>
                                <span class="w-2 h-2 rounded-full bg-[#64748B]"></span>
                                <span>PO: <strong class="text-slate-900 font-mono">Rp {{ number_format($totalSpend / 1000000, 1) }}M</strong></span>
                            </div>

                            <!-- Right: Timeframe Switcher & Download Icon -->
                            <div class="flex items-center space-x-2">
                                <div class="inline-flex p-0.5 bg-slate-100 rounded-xl text-xs font-bold border border-slate-200/70">
                                    <button type="button" @click="chartPeriod = 'monthly'; switchChart('monthly')"
                                        :class="chartPeriod === 'monthly' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-800'"
                                        class="px-3 py-1 rounded-lg transition text-[11px]">
                                        Bulanan
                                    </button>
                                    <button type="button" @click="chartPeriod = 'weekly'; switchChart('weekly')"
                                        :class="chartPeriod === 'weekly' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-800'"
                                        class="px-3 py-1 rounded-lg transition text-[11px]">
                                        Mingguan
                                    </button>
                                </div>

                                <!-- Export Icon Button -->
                                @if(Auth::user()->hasRole(['admin', 'auditor', 'finance', 'procurement', 'manager', 'hod']))
                                    <a href="{{ route('reports.po') }}" title="Unduh Rekapitulasi Laporan" class="p-1.5 rounded-xl border border-slate-200/80 text-slate-500 hover:text-slate-800 hover:bg-slate-50 shadow-2xs transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    </a>
                                @endif
                            </div>

                        </div>

                        <!-- Spline Chart Canvas Container -->
                        <div class="pt-5 pb-2 relative" style="height: 280px;">
                            <canvas id="spendTrendChart"></canvas>
                        </div>
                    </div>

                    <!-- Footer Legend -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                        <div class="flex items-center gap-6">
                            <span class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-[#FF7A45]"></span>
                                <span class="font-medium text-slate-600">Pengajuan PR</span>
                            </span>
                            <span class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-[#64748B]"></span>
                                <span class="font-medium text-slate-600">Realisasi PO</span>
                            </span>
                        </div>
                        <span class="text-[11px]">Nilai dalam jutaan Rupiah (IDR)</span>
                    </div>
                </div>

                <!-- Right: Semi-Circular Gauge Meter -->
                <div class="lg:col-span-4 card-soft p-6 flex flex-col justify-between">
                    <div>
                        <!-- Header -->
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <h4 class="text-base font-extrabold text-slate-800">Kepatuhan SLA &amp; Tata Kelola</h4>
                            <div class="p-1.5 rounded-xl border border-slate-200/80 text-slate-500 shadow-2xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                        </div>

                        <!-- Semi-Circular Gauge Meter -->
                        <div class="pt-4 pb-2 relative flex flex-col items-center justify-center">
                            
                            <!-- Gauge Canvas -->
                            <div class="relative w-56 h-36 flex items-center justify-center">
                                <canvas id="satisfactionGaugeChart"></canvas>
                                
                                <div class="absolute inset-0 flex flex-col items-center justify-center pt-8">
                                    <div class="w-9 h-9 rounded-full bg-orange-50 border border-orange-200 text-orange-500 flex items-center justify-center shadow-xs mb-1">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/></svg>
                                    </div>
                                    <div class="text-3xl font-black text-slate-900 tracking-tight leading-none">94%</div>
                                    <div class="text-[10px] font-bold text-emerald-600 mt-1">&check; Sesuai Standar</div>
                                </div>
                            </div>

                            <!-- 0% and 100% Labels -->
                            <div class="w-56 flex justify-between text-xs font-bold text-slate-400 px-2 -mt-2">
                                <span>0%</span>
                                <span>100%</span>
                            </div>

                        </div>
                    </div>

                    <!-- Subtitle note -->
                    <div class="pt-4 border-t border-slate-100 text-center">
                        <div class="text-xs font-bold text-slate-800">SLA Otorisasi &lt; 24 Jam Terpenuhi</div>
                        <p class="text-[11px] text-slate-500 mt-0.5">Nol penyimpangan &amp; 100% verifikasi Three-Way Match valid</p>
                    </div>
                </div>

            </div>

            <!-- 3. Bottom Row: Performance Statistics + Monthly PR Volume -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                <!-- Left: Performance Statistics per Department -->
                <div class="lg:col-span-5 card-soft p-6 flex flex-col justify-between">
                    <div>
                        <!-- Header -->
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <h4 class="text-base font-extrabold text-slate-800">Realisasi Anggaran per Divisi</h4>
                            <div class="p-1.5 rounded-xl border border-slate-200/80 text-slate-500 shadow-2xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            </div>
                        </div>

                        <!-- Progress Bars List -->
                        <div class="space-y-4 pt-4">
                            
                            <!-- Bar 1: IT Team -->
                            <div class="space-y-1.5">
                                <div class="flex justify-between text-xs font-bold text-slate-700">
                                    <span>Divisi Teknologi Informasi (IT)</span>
                                    <span class="text-slate-900">65%</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: 65%;"></div>
                                </div>
                            </div>

                            <!-- Bar 2: Operational Team -->
                            <div class="space-y-1.5">
                                <div class="flex justify-between text-xs font-bold text-slate-700">
                                    <span>Divisi Operasional &amp; Pengadaan</span>
                                    <span class="text-slate-900">84%</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                    <div class="bg-emerald-500 h-2.5 rounded-full" style="width: 84%;"></div>
                                </div>
                            </div>

                            <!-- Bar 3: Marketing Team -->
                            <div class="space-y-1.5">
                                <div class="flex justify-between text-xs font-bold text-slate-700">
                                    <span>Divisi Pemasaran &amp; Komunikasi</span>
                                    <span class="text-slate-900">28%</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                    <div class="bg-cyan-500 h-2.5 rounded-full" style="width: 28%;"></div>
                                </div>
                            </div>

                            <!-- Bar 4: HR Team -->
                            <div class="space-y-1.5">
                                <div class="flex justify-between text-xs font-bold text-slate-700">
                                    <span>Divisi SDM &amp; Umum (HRD)</span>
                                    <span class="text-slate-900">16%</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                    <div class="bg-purple-500 h-2.5 rounded-full" style="width: 16%;"></div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 text-xs text-slate-400 flex items-center justify-between">
                        <span>Pagu Anggaran Tahun {{ date('Y') }}</span>
                        @if(Auth::user()->hasRole(['admin', 'auditor', 'finance', 'procurement', 'manager', 'hod']))
                            <a href="{{ route('reports.po') }}" class="text-blue-600 font-bold hover:underline">Detail Laporan &rarr;</a>
                        @endif
                    </div>
                </div>

                <!-- Right: Bar Chart -->
                <div class="lg:col-span-7 card-soft p-6 flex flex-col justify-between">
                    <div>
                        <!-- Header -->
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-100 gap-3">
                            <div class="flex items-center space-x-2">
                                <h4 class="text-base font-extrabold text-slate-800">Tren Volume Pengadaan</h4>
                            </div>

                            <div class="hidden sm:inline-flex items-center gap-2 px-3 py-1 rounded-xl bg-orange-50 border border-orange-200/80 text-[11px] font-bold text-orange-800 shadow-2xs">
                                <span>Periode Aktif</span>
                                <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                                <span>Efisiensi: <strong>88%</strong></span>
                            </div>

                            <div class="flex items-center space-x-2">
                                <span class="text-xs font-bold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-lg">Periode Bulanan</span>
                            </div>
                        </div>

                        <!-- Bar Chart Container -->
                        <div class="pt-4 pb-2 relative" style="height: 220px;">
                            <canvas id="monthlyBarChart"></canvas>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                        <div class="flex items-center gap-4">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#94A3B8]"></span>
                                <span class="font-medium text-slate-600">Pengajuan Masuk</span>
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#FF7A45]"></span>
                                <span class="font-medium text-slate-600">Realisasi Disetujui</span>
                            </span>
                        </div>
                        <span class="text-[11px]">Monitoring Siklus Bulanan</span>
                    </div>
                </div>

            </div>
            @endif

            <!-- 4. Antrean Persetujuan Anda (My Approval Queue) -->
            @if($myApprovalQueue->count() > 0)
            <div class="card-soft overflow-hidden">
                <div class="px-6 py-4.5 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                        <h4 class="font-extrabold text-sm text-slate-900">Antrean Persetujuan Anda ({{ $myApprovalQueue->count() }} PR Memerlukan Tindakan)</h4>
                    </div>
                    <a href="{{ route('approvals.index') }}" class="text-xs font-bold text-amber-600 hover:text-amber-700">Buka Antrean &rarr;</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 divide-y divide-slate-100">
                        <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-6 py-3.5">No. PR</th>
                                <th class="px-6 py-3.5">Judul Pengajuan</th>
                                <th class="px-6 py-3.5">Pemohon</th>
                                <th class="px-6 py-3.5">Divisi</th>
                                <th class="px-6 py-3.5 text-right">Nilai Anggaran</th>
                                <th class="px-6 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($myApprovalQueue as $item)
                                @php $pr = ($item instanceof \App\Models\PurchaseRequest) ? $item : $item->purchaseRequest; @endphp
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="px-6 py-3.5 font-mono font-bold text-slate-900">{{ $pr?->pr_number }}</td>
                                    <td class="px-6 py-3.5 max-w-xs truncate font-semibold text-slate-800">{{ $pr?->title }}</td>
                                    <td class="px-6 py-3.5 whitespace-nowrap text-slate-600">{{ $pr?->user?->name ?? '-' }}</td>
                                    <td class="px-6 py-3.5 whitespace-nowrap text-slate-600">{{ $pr?->department?->name ?? '-' }}</td>
                                    <td class="px-6 py-3.5 text-right font-mono font-bold text-slate-900">Rp {{ number_format($pr?->estimated_total, 0, ',', '.') }}</td>
                                    <td class="px-6 py-3.5 text-right">
                                        <a href="{{ route('purchase-requests.show', $pr) }}" class="px-3.5 py-1.5 bg-[#0A1D37] hover:bg-slate-900 text-white rounded-xl text-xs font-bold shadow-xs transition">
                                            Proses &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- 5. Aktivitas Pengajuan Pembelian (PR) Terbaru Table -->
            <div class="card-soft overflow-hidden">
                <div class="px-6 py-4.5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h4 class="font-extrabold text-sm text-slate-900">Aktivitas Pengajuan Pembelian (PR) Terbaru</h4>
                        <p class="text-xs text-slate-400 mt-0.5">Daftar permohonan pengadaan barang/jasa internal divisi Anda</p>
                    </div>
                    <div class="flex items-center gap-3">
                        @if(Auth::user()->hasRole(['requester', 'manager', 'admin']))
                            <a href="{{ route('purchase-requests.create') }}" class="px-4 py-2 bg-[#FF7A45] hover:bg-[#F9652B] text-white rounded-xl text-xs font-bold shadow-md shadow-orange-500/20 transition flex items-center gap-1.5">
                                <span class="text-base leading-none">+</span>
                                <span>Buat PR Baru</span>
                            </a>
                        @endif
                        <a href="{{ route('purchase-requests.index') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900">
                            Lihat Semua &rarr;
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600 divide-y divide-slate-100">
                        <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-6 py-3.5">Nomor PR</th>
                                <th class="px-6 py-3.5">Judul Pengadaan</th>
                                <th class="px-6 py-3.5">Pemohon</th>
                                <th class="px-6 py-3.5 text-right">Total Anggaran</th>
                                <th class="px-6 py-3.5 text-center">Status</th>
                                <th class="px-6 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentPrs as $pr)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="px-6 py-3.5 whitespace-nowrap">
                                        <a href="{{ route('purchase-requests.show', $pr) }}" class="font-mono font-bold text-slate-900 hover:underline">
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
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $pr->status_badge_class }}">
                                            {{ $pr->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3.5 whitespace-nowrap text-right">
                                        <a href="{{ route('purchase-requests.show', $pr) }}" class="text-xs font-bold text-blue-600 hover:text-blue-800">
                                            Buka &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                                        Belum ada aktivitas pengajuan PR.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Chart.js Scripts for Exact Image 2 Dashboards -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chartCanvas = document.getElementById('spendTrendChart');
            if (!chartCanvas) return;

            const monthlyData = @json($monthlyTrend ?? []);
            const weeklyData  = @json($weeklyTrend ?? []);

            const formatRupiah = (val) => {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
            };

            // 1. Spend Trend Spline Chart (Image 2 style: smooth dual bezier curves in Coral & Slate Blue)
            const ctxTrend = chartCanvas.getContext('2d');
            
            const getChartConfig = (dataset) => {
                const labels = dataset.map(d => d.label || d.month || d.week);
                const prTotals = dataset.map(d => d.pr_total);
                const poTotals = dataset.map(d => d.po_total);

                return {
                    labels: labels,
                    datasets: [
                        {
                            type: 'line',
                            label: 'Pengajuan PR',
                            data: prTotals,
                            borderColor: '#FF7A45', // Coral Orange
                            backgroundColor: 'rgba(255, 122, 69, 0.08)',
                            borderWidth: 3,
                            pointBackgroundColor: '#FF7A45',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2.5,
                            pointRadius: 4.5,
                            pointHoverRadius: 6.5,
                            tension: 0.4, // Smooth Spline
                            fill: true,
                        },
                        {
                            type: 'line',
                            label: 'Realisasi PO',
                            data: poTotals,
                            borderColor: '#64748B', // Slate Blue
                            backgroundColor: 'rgba(100, 116, 139, 0.05)',
                            borderWidth: 3,
                            pointBackgroundColor: '#64748B',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2.5,
                            pointRadius: 4.5,
                            pointHoverRadius: 6.5,
                            tension: 0.4, // Smooth Spline
                            fill: true,
                        }
                    ]
                };
            };

            let currentConfig = getChartConfig(monthlyData);

            window.trendChart = new Chart(ctxTrend, {
                type: 'line',
                data: currentConfig,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1E293B',
                            titleFont: { family: 'Plus Jakarta Sans', size: 12, weight: 'bold' },
                            bodyFont: { family: 'Plus Jakarta Sans', size: 11 },
                            padding: 10,
                            cornerRadius: 10,
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + formatRupiah(context.raw);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: { family: 'Plus Jakarta Sans', size: 11, weight: '600' },
                                color: '#94A3B8',
                            }
                        },
                        y: {
                            grid: { color: '#F1F5F9' },
                            ticks: {
                                font: { family: 'Plus Jakarta Sans', size: 10 },
                                color: '#94A3B8',
                                callback: function(value) {
                                    if (value >= 1000000000) return (value / 1000000000).toFixed(1) + 'M';
                                    if (value >= 1000000) return (value / 1000000).toFixed(0) + 'Jt';
                                    return value;
                                }
                            }
                        }
                    }
                }
            });

            window.switchChart = function (period) {
                const newData = (period === 'weekly') ? weeklyData : monthlyData;
                const newCfg = getChartConfig(newData);
                window.trendChart.data.labels = newCfg.labels;
                window.trendChart.data.datasets[0].data = newCfg.datasets[0].data;
                window.trendChart.data.datasets[1].data = newCfg.datasets[1].data;
                window.trendChart.update();
            };

            // 2. Semi-Circular Speedometer Gauge Chart (Image 2 style: Employee Satisfaction / Efficiency Gauge)
            const ctxGauge = document.getElementById('satisfactionGaugeChart').getContext('2d');
            window.gaugeChart = new Chart(ctxGauge, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [94, 6],
                        backgroundColor: [
                            '#FF7A45', // Coral Arc
                            '#F1F5F9'  // Soft Gray Track
                        ],
                        borderWidth: 0,
                        circumference: 180,
                        rotation: 270,
                        cutout: '76%'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: { enabled: false },
                        legend: { display: false }
                    }
                }
            });

            // 3. Monthly Volume Dual-Color Bar Chart (Image 2 style: New Employees bar chart)
            const ctxBar = document.getElementById('monthlyBarChart').getContext('2d');
            const barLabels = monthlyData.map(d => d.label || d.month);
            const barPrCounts = [4, 6, 8, 7, 9, 12];
            const barPoCounts = [3, 5, 7, 6, 8, 10];

            window.monthlyBar = new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: barLabels,
                    datasets: [
                        {
                            label: 'Pengajuan Masuk',
                            data: barPrCounts,
                            backgroundColor: '#94A3B8',
                            borderRadius: 8,
                            barPercentage: 0.5,
                            categoryPercentage: 0.7,
                        },
                        {
                            label: 'Realisasi Disetujui',
                            data: barPoCounts,
                            backgroundColor: '#FF7A45',
                            borderRadius: 8,
                            barPercentage: 0.5,
                            categoryPercentage: 0.7,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1E293B',
                            cornerRadius: 8,
                            callbacks: {
                                label: function(c) {
                                    return c.dataset.label + ': ' + c.raw + ' Dokumen';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: { family: 'Plus Jakarta Sans', size: 10, weight: '600' },
                                color: '#94A3B8',
                            }
                        },
                        y: {
                            grid: { color: '#F8FAFC' },
                            ticks: {
                                font: { family: 'Plus Jakarta Sans', size: 10 },
                                color: '#CBD5E1',
                                stepSize: 3
                            }
                        }
                    }
                }
            });

        });
    </script>
</x-app-layout>
