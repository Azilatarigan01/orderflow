<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OrderFlow Enterprise — Tata Kelola Pengadaan & Manajemen Belanja Korporat</title>
    
    <!-- Meta tags for SEO & Corporate Identity -->
    <meta name="description" content="Portal Pengadaan Barang & Jasa Internal Korporat Terpadu. Menghubungkan Purchase Request, Multi-tier Approval, Scoring Komparasi Vendor, Purchase Order, hingga 3-Way Matching Faktur.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;0,900;1,600&family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F8FAFC] text-slate-800 font-sans antialiased selection:bg-amber-600 selection:text-white">

    <!-- 1. Top Prestige Utility Bar (Deep Navy) -->
    <div class="bg-[#0A1D37] text-slate-300 text-xs py-2 px-4 border-b border-white/10 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-2">
            <!-- Left Contact / Location Info -->
            <div class="flex items-center space-x-2 text-[11px] font-medium tracking-wide">
                <svg class="w-3.5 h-3.5 text-amber-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                </svg>
                <span>Gedung Graha Niaga Lt. 18, Jl. Jend. Sudirman Kav. 52, Jakarta Selatan</span>
            </div>

            <!-- Right Audience Quick Links -->
            <div class="flex items-center space-x-4 text-[11px]">
                <a href="#modul" class="hover:text-amber-400 transition-colors">Divisi Pemohon</a>
                <a href="#modul" class="hover:text-amber-400 transition-colors">Manager Divisi</a>
                <a href="#modul" class="hover:text-amber-400 transition-colors">Procurement</a>
                <a href="#modul" class="hover:text-amber-400 transition-colors">Finance</a>
                <span class="text-white/20">|</span>
                <a href="{{ route('login') }}" class="text-amber-400 font-semibold hover:text-amber-300 transition-colors">Daftar Akun</a>
                <a href="{{ route('login') }}" class="text-white hover:text-amber-400 transition-colors font-medium">Masuk Portal</a>
                <span class="text-white/20">|</span>
                <svg class="w-3.5 h-3.5 text-slate-300 cursor-pointer hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- 2. Main Navigation Header (Clean White with Prestige Branding) -->
    <header class="bg-white border-b border-slate-200 sticky top-8 z-40 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-22 py-3">
                
                <!-- Enterprise Crest Identity -->
                <a href="{{ url('/') }}" class="flex items-center space-x-3.5 group flex-shrink-0">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-[#070D1E] border-2 border-amber-500/80 p-1 flex items-center justify-center shadow-md flex-shrink-0 group-hover:scale-105 transition-transform duration-200 overflow-hidden">
                        <img src="{{ asset('images/orderflow_emblem.jpg') }}" alt="OrderFlow Corporate Emblem" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <div class="text-lg sm:text-xl font-serif-prestige font-extrabold tracking-tight text-[#0A1D37] leading-none">
                            ORDERFLOW
                        </div>
                        <div class="text-[10px] font-sans font-bold tracking-[0.18em] uppercase text-amber-700 mt-1">
                            ENTERPRISE GOVERNANCE
                        </div>
                        <p class="text-[9px] text-slate-400 font-medium tracking-wider uppercase">TATA KELOLA &bull; AKUNTABILITAS &bull; INTEGRITAS</p>
                    </div>
                </a>

                <!-- Desktop Navigation Links (Properly Spaced & Scaled) -->
                <nav class="hidden lg:flex items-center space-x-4 xl:space-x-7 text-[11px] xl:text-xs font-bold uppercase tracking-wider text-slate-700 mx-4">
                    <a href="#tentang" class="hover:text-amber-700 transition-colors py-1">Tentang</a>
                    <a href="#modul" class="hover:text-amber-700 transition-colors py-1">Modul</a>
                    <a href="#alur-kerja" class="hover:text-amber-700 transition-colors py-1">Alur Kerja</a>
                    <a href="#tata-kelola" class="hover:text-amber-700 transition-colors py-1">Tata Kelola</a>
                    <a href="#berita" class="hover:text-amber-700 transition-colors py-1">Berita</a>
                    <a href="#kontak" class="hover:text-amber-700 transition-colors py-1">Bantuan</a>
                </nav>

                <!-- Action Button: Golden Amber Button with safe margins and flex-shrink-0 -->
                <div class="flex items-center space-x-3 flex-shrink-0 ml-2 xl:ml-6">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ route('dashboard') }}" class="px-5 py-2.5 sm:px-6 sm:py-3 bg-[#0A1D37] hover:bg-slate-900 text-amber-400 text-xs font-bold uppercase tracking-wider rounded-lg shadow hover:shadow-md transition-all flex items-center gap-2 whitespace-nowrap">
                                <span>Buka Dashboard</span>
                                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="px-5 py-2.5 sm:px-6 sm:py-3 bg-[#C58B24] hover:bg-[#B45309] text-white text-xs font-extrabold uppercase tracking-wider rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2 whitespace-nowrap">
                                <span>Masuk Portal</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </header>

    <!-- 3. Hero Section (Image 1 Northfield Style: Dual-tone Serif Headline, Real Campus/Tower Photo, Gold Elements) -->
    <section id="tentang" class="relative pt-12 pb-24 lg:pt-16 lg:pb-32 bg-white overflow-hidden border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">
                
                <!-- Left Column: Prestigious Ivy/Enterprise Headline -->
                <div class="lg:col-span-6 space-y-6">
                    
                    <div class="text-xs font-bold uppercase tracking-[0.25em] text-[#C58B24]">
                        TATA KELOLA PENGADAAN &amp; EFISIENSI ANGGARAN.
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-[54px] font-serif-prestige font-bold text-slate-900 leading-[1.12]">
                        Sebuah Legasi Integritas.<br>
                        <span class="text-[#C58B24] font-serif-prestige">Masa Depan Akuntabel.</span>
                    </h1>

                    <p class="text-sm sm:text-base text-slate-600 leading-relaxed font-normal max-w-xl">
                        Di OrderFlow Enterprise, kami memberdayakan seluruh divisi korporat untuk mengendalikan setiap permohonan belanja secara terukur, memastikan transparansi berjenjang, dan mewujudkan pengadaan bebas fraud melalui verifikasi terpadu.
                    </p>

                    <!-- CTAs (Navy Pill Button + Outlined Visit Button) -->
                    <div class="pt-2 flex flex-wrap gap-4 items-center">
                        <a href="#modul" class="px-7 py-3.5 bg-[#0A1D37] hover:bg-slate-900 text-white text-xs font-bold uppercase tracking-wider rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2.5">
                            <span>Jelajahi Modul</span>
                            <span class="text-amber-400">&rarr;</span>
                        </a>
                        <a href="{{ route('login') }}" class="px-7 py-3.5 bg-white hover:bg-slate-50 text-slate-800 border border-slate-300 text-xs font-bold uppercase tracking-wider rounded-lg shadow-xs transition-all flex items-center gap-2">
                            <span>Simulasi Alur Kerja</span>
                            <span class="text-slate-500">&rarr;</span>
                        </a>
                    </div>

                    <!-- Social Proof Avatar Ribbon -->
                    <div class="pt-4 flex items-center space-x-3.5 border-t border-slate-100">
                        <div class="flex -space-x-2">
                            <span class="w-8 h-8 rounded-full border-2 border-white bg-blue-600 text-white text-[10px] font-bold flex items-center justify-center">IT</span>
                            <span class="w-8 h-8 rounded-full border-2 border-white bg-amber-600 text-white text-[10px] font-bold flex items-center justify-center">MG</span>
                            <span class="w-8 h-8 rounded-full border-2 border-white bg-emerald-600 text-white text-[10px] font-bold flex items-center justify-center">FN</span>
                            <span class="w-8 h-8 rounded-full border-2 border-white bg-purple-600 text-white text-[10px] font-bold flex items-center justify-center">AD</span>
                        </div>
                        <div class="text-xs text-slate-600">
                            Bergabung dengan <strong class="text-slate-900 font-bold">12,000+ Karyawan</strong> di 50+ Unit Kerja Perusahaan
                        </div>
                    </div>

                </div>

                <!-- Right Column: Real Corporate Campus Photo with Floating Navy/Gold Badge -->
                <div class="lg:col-span-6 relative">
                    <div class="relative mx-auto max-w-lg lg:max-w-none">
                        
                        <!-- High Quality Photo Frame -->
                        <div class="relative rounded-2xl overflow-hidden shadow-2xl border-4 border-white bg-slate-900">
                            <img src="{{ asset('images/procurement_team_hero.jpg') }}" alt="Pusat Operasional Tata Kelola OrderFlow" class="w-full h-[420px] object-cover object-center">
                            
                            <!-- Vignette bottom overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-[#0A1D37]/80 via-transparent to-transparent"></div>
                            
                            <div class="absolute bottom-4 left-5 right-5 text-white flex items-center justify-between">
                                <div class="text-xs font-bold">
                                    <div class="text-amber-400 text-[10px] uppercase font-bold tracking-wider">Pusat Komando Operasional</div>
                                    <div>Kolaborasi Pengadaan &amp; Tata Kelola Multi-Divisi</div>
                                </div>
                                <span class="px-2.5 py-1 rounded bg-amber-500/90 text-white text-[10px] font-bold uppercase tracking-wider">
                                    Live Sistem
                                </span>
                            </div>
                        </div>

                        <!-- Floating Deep Navy + Gold Badge (Image 1 style: Top Universities Worldwide) -->
                        <div class="absolute -bottom-6 -right-4 sm:-right-6 bg-[#0A1D37] text-white p-5 rounded-xl shadow-2xl border border-amber-500/30 max-w-[210px] hidden sm:block animate-float">
                            <div class="w-8 h-8 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center mb-2.5">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM13.707 8.707a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="text-xs font-serif-prestige font-bold text-white leading-tight">
                                Terakreditasi Top Tier Standar ISO 27001 &amp; ISO 37001 GCG
                            </div>
                            <div class="text-[10px] text-amber-400 mt-1 font-semibold">Integritas 100% Terverifikasi</div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- 4. Floating 4-Feature Ribbon across Hero Bottom (Exact match to Image 1) -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10 relative z-30">
        <div class="bg-white rounded-2xl p-6 sm:p-7 shadow-xl border border-slate-200">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 divide-y sm:divide-y-0 sm:divide-x divide-slate-100">
                
                <!-- Ribbon Item 1 -->
                <div class="flex items-center space-x-4 pt-3 sm:pt-0 sm:px-3">
                    <div class="w-12 h-12 rounded-full bg-[#0A1D37] text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                        <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-900">Tata Kelola Presisi</div>
                        <p class="text-xs text-slate-500 mt-0.5">SOP persetujuan berjenjang dan pemenuhan pagu divisi.</p>
                    </div>
                </div>

                <!-- Ribbon Item 2 -->
                <div class="flex items-center space-x-4 pt-4 sm:pt-0 sm:px-4">
                    <div class="w-12 h-12 rounded-full bg-[#C58B24] text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-900">Kolaborasi Lintas Divisi</div>
                        <p class="text-xs text-slate-500 mt-0.5">Koneksi transparan antara Pemohon, Pengadaan, dan Keuangan.</p>
                    </div>
                </div>

                <!-- Ribbon Item 3 -->
                <div class="flex items-center space-x-4 pt-4 sm:pt-0 sm:px-4">
                    <div class="w-12 h-12 rounded-full bg-[#1E3A8A] text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                        <svg class="w-6 h-6 text-sky-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-900">Platform Terpadu</div>
                        <p class="text-xs text-slate-500 mt-0.5">Satu siklus PR, RFQ, PO hingga penerimaan barang di gudang.</p>
                    </div>
                </div>

                <!-- Ribbon Item 4 -->
                <div class="flex items-center space-x-4 pt-4 sm:pt-0 sm:px-4">
                    <div class="w-12 h-12 rounded-full bg-[#D97706] text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                        <svg class="w-6 h-6 text-amber-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-900">Akuntabilitas Terukur</div>
                        <p class="text-xs text-slate-500 mt-0.5">Rekonsiliasi Three-Way Matching dan audit trail tamper-proof.</p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- 5. "OUR PROGRAMS / Find Your Path to Success" Section (Exact replica of Image 1) -->
    <section id="modul" class="py-20 bg-[#F8FAFC]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- Left Callout Box (Find Your Path to Success) -->
                <div class="lg:col-span-3 space-y-4 pt-2">
                    <div class="text-xs font-bold uppercase tracking-wider text-[#C58B24]">MODUL UTAMA KAMI</div>
                    <h2 class="text-3xl font-serif-prestige font-bold text-slate-900 leading-tight">
                        Temukan Solusi Sesuai Kebutuhan Divisi
                    </h2>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Pilih dari modul terintegrasi untuk menyelaraskan permohonan belanja, matriks otorisasi, evaluasi rekanan, dan validasi rekonsiliasi faktur.
                    </p>
                    <div class="pt-2">
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-[#0A1D37] hover:bg-slate-900 text-white text-xs font-bold uppercase tracking-wider rounded-lg shadow transition">
                            <span>Lihat Semua Modul</span>
                            <span class="text-amber-400">&rarr;</span>
                        </a>
                    </div>
                </div>

                <!-- Right 5 Column Cards Grid with Real Top Photos & Floating Circle Badges -->
                <div class="lg:col-span-9 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                    
                    <!-- Card 1: Business & Economics / PR -->
                    <div class="bg-white rounded-xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-md transition-all flex flex-col justify-between group">
                        <div>
                            <div class="relative h-32 overflow-hidden bg-slate-100">
                                <img src="{{ asset('images/procurement_team_hero.jpg') }}" alt="Purchase Request" class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500">
                                <div class="absolute -bottom-3 left-1/2 -translate-x-1/2 w-8 h-8 rounded-full bg-[#0A1D37] text-white flex items-center justify-center border-2 border-white shadow-sm">
                                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                            </div>
                            <div class="p-4 pt-6 text-center space-y-1.5">
                                <h3 class="text-xs font-bold text-slate-900 uppercase">Purchase Request</h3>
                                <p class="text-[11px] text-slate-500 leading-snug">Pengajuan barang &amp; jasa, pagu dan urgensi divisi.</p>
                            </div>
                        </div>
                        <div class="p-3 pt-0 text-center">
                            <a href="{{ route('login') }}" class="text-[11px] font-bold text-amber-700 hover:text-amber-800 uppercase tracking-wider inline-flex items-center gap-1">
                                <span>Eksplorasi</span>
                                <span>&rarr;</span>
                            </a>
                        </div>
                    </div>

                    <!-- Card 2: Engineering & Tech / Otorisasi -->
                    <div class="bg-white rounded-xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-md transition-all flex flex-col justify-between group">
                        <div>
                            <div class="relative h-32 overflow-hidden bg-slate-100">
                                <img src="{{ asset('images/executive_approval.jpg') }}" alt="Multi-Tier Approval" class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500">
                                <div class="absolute -bottom-3 left-1/2 -translate-x-1/2 w-8 h-8 rounded-full bg-[#C58B24] text-white flex items-center justify-center border-2 border-white shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                            </div>
                            <div class="p-4 pt-6 text-center space-y-1.5">
                                <h3 class="text-xs font-bold text-slate-900 uppercase">Otorisasi Tier</h3>
                                <p class="text-[11px] text-slate-500 leading-snug">Approval berjenjang otomatis sesuai pagu anggaran.</p>
                            </div>
                        </div>
                        <div class="p-3 pt-0 text-center">
                            <a href="{{ route('login') }}" class="text-[11px] font-bold text-amber-700 hover:text-amber-800 uppercase tracking-wider inline-flex items-center gap-1">
                                <span>Eksplorasi</span>
                                <span>&rarr;</span>
                            </a>
                        </div>
                    </div>

                    <!-- Card 3: Arts & Humanities / RFQ Vendor -->
                    <div class="bg-white rounded-xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-md transition-all flex flex-col justify-between group">
                        <div>
                            <div class="relative h-32 overflow-hidden bg-slate-100">
                                <img src="{{ asset('images/corporate_procurement_hero.jpg') }}" alt="RFQ Vendor" class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500">
                                <div class="absolute -bottom-3 left-1/2 -translate-x-1/2 w-8 h-8 rounded-full bg-[#0A1D37] text-white flex items-center justify-center border-2 border-white shadow-sm">
                                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                                </div>
                            </div>
                            <div class="p-4 pt-6 text-center space-y-1.5">
                                <h3 class="text-xs font-bold text-slate-900 uppercase">RFQ &amp; Rekanan</h3>
                                <p class="text-[11px] text-slate-500 leading-snug">Scoring penawaran harga, garansi, &amp; lead time.</p>
                            </div>
                        </div>
                        <div class="p-3 pt-0 text-center">
                            <a href="{{ route('login') }}" class="text-[11px] font-bold text-amber-700 hover:text-amber-800 uppercase tracking-wider inline-flex items-center gap-1">
                                <span>Eksplorasi</span>
                                <span>&rarr;</span>
                            </a>
                        </div>
                    </div>

                    <!-- Card 4: Health & Sciences / Gudang Goods Receipt -->
                    <div class="bg-white rounded-xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-md transition-all flex flex-col justify-between group">
                        <div>
                            <div class="relative h-32 overflow-hidden bg-slate-100">
                                <img src="{{ asset('images/logistics_inspection.jpg') }}" alt="Gudang & GR" class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500">
                                <div class="absolute -bottom-3 left-1/2 -translate-x-1/2 w-8 h-8 rounded-full bg-[#C58B24] text-white flex items-center justify-center border-2 border-white shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                </div>
                            </div>
                            <div class="p-4 pt-6 text-center space-y-1.5">
                                <h3 class="text-xs font-bold text-slate-900 uppercase">Penerimaan GR</h3>
                                <p class="text-[11px] text-slate-500 leading-snug">Verifikasi fisik barang di gudang &amp; berita acara BAST.</p>
                            </div>
                        </div>
                        <div class="p-3 pt-0 text-center">
                            <a href="{{ route('login') }}" class="text-[11px] font-bold text-amber-700 hover:text-amber-800 uppercase tracking-wider inline-flex items-center gap-1">
                                <span>Eksplorasi</span>
                                <span>&rarr;</span>
                            </a>
                        </div>
                    </div>

                    <!-- Card 5: Environment / 3-Way Match -->
                    <div class="bg-white rounded-xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-md transition-all flex flex-col justify-between group">
                        <div>
                            <div class="relative h-32 overflow-hidden bg-slate-100">
                                <img src="{{ asset('images/corporate_tower.jpg') }}" alt="Three-Way Match" class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500">
                                <div class="absolute -bottom-3 left-1/2 -translate-x-1/2 w-8 h-8 rounded-full bg-[#0A1D37] text-white flex items-center justify-center border-2 border-white shadow-sm">
                                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                </div>
                            </div>
                            <div class="p-4 pt-6 text-center space-y-1.5">
                                <h3 class="text-xs font-bold text-slate-900 uppercase">3-Way Match</h3>
                                <p class="text-[11px] text-slate-500 leading-snug">Pencocokan 1:1 PO, bukti terima, dan faktur tagihan.</p>
                            </div>
                        </div>
                        <div class="p-3 pt-0 text-center">
                            <a href="{{ route('login') }}" class="text-[11px] font-bold text-amber-700 hover:text-amber-800 uppercase tracking-wider inline-flex items-center gap-1">
                                <span>Eksplorasi</span>
                                <span>&rarr;</span>
                            </a>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </section>

    <!-- 6. Full-Width Statistics Ribbon (Deep Navy Bar with Gold Icons, exact match to Image 1) -->
    <section class="bg-[#0A1D37] text-white py-12 border-y border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-6 text-center divide-y md:divide-y-0 md:divide-x divide-white/10">
                
                <!-- Stat 1 -->
                <div class="space-y-1 px-3">
                    <div class="w-8 h-8 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center mx-auto mb-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <div class="text-2xl sm:text-3xl font-serif-prestige font-bold text-white tracking-tight">12,000+</div>
                    <div class="text-xs text-slate-300 font-medium">Pengajuan PR Terlayani</div>
                </div>

                <!-- Stat 2 -->
                <div class="space-y-1 px-3 pt-4 md:pt-0">
                    <div class="w-8 h-8 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center mx-auto mb-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/></svg>
                    </div>
                    <div class="text-2xl sm:text-3xl font-serif-prestige font-bold text-white tracking-tight">50+</div>
                    <div class="text-xs text-slate-300 font-medium">Unit Kerja &amp; Departemen</div>
                </div>

                <!-- Stat 3 -->
                <div class="space-y-1 px-3 pt-4 md:pt-0">
                    <div class="w-8 h-8 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center mx-auto mb-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div class="text-2xl sm:text-3xl font-serif-prestige font-bold text-white tracking-tight">100+</div>
                    <div class="text-xs text-slate-300 font-medium">Mitra Rekanan Resmi</div>
                </div>

                <!-- Stat 4 -->
                <div class="space-y-1 px-3 pt-4 md:pt-0">
                    <div class="w-8 h-8 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center mx-auto mb-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="text-2xl sm:text-3xl font-serif-prestige font-bold text-white tracking-tight">100%</div>
                    <div class="text-xs text-slate-300 font-medium">Kepatuhan Anggaran</div>
                </div>

                <!-- Stat 5 -->
                <div class="space-y-1 px-3 pt-4 md:pt-0">
                    <div class="w-8 h-8 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center mx-auto mb-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="text-2xl sm:text-3xl font-serif-prestige font-bold text-white tracking-tight">&lt; 24 Jam</div>
                    <div class="text-xs text-slate-300 font-medium">Rata-rata Siklus Otorisasi</div>
                </div>

            </div>
        </div>
    </section>

    <!-- 7. Video / Life at Northfield Section (More Than a Degree, It's an Experience, exact layout of Image 1) -->
    <section id="alur-kerja" class="py-20 bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                
                <!-- Left Video Card with Circular Play Button -->
                <div class="lg:col-span-5 relative">
                    <div class="relative rounded-2xl overflow-hidden shadow-xl border-4 border-slate-100 group">
                        <img src="{{ asset('images/logistics_inspection.jpg') }}" alt="Operasional Pengadaan" class="w-full h-80 object-cover object-center group-hover:scale-105 transition-transform duration-700">
                        <div class="absolute inset-0 bg-[#0A1D37]/40 flex items-center justify-center">
                            <!-- Circular Play Button -->
                            <div class="w-16 h-16 rounded-full bg-white/90 text-[#0A1D37] flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform cursor-pointer">
                                <svg class="w-8 h-8 translate-x-0.5 text-amber-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="absolute bottom-4 left-4 right-4 text-white text-xs font-bold uppercase tracking-wider">
                            TELUSURI ALUR OPERASIONAL GUDANG
                        </div>
                    </div>
                </div>

                <!-- Right Feature Narrative & 2x2 Grid -->
                <div class="lg:col-span-7 space-y-6">
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wider text-[#C58B24]">STANDAR TATA KELOLA KORPORAT</div>
                        <h2 class="text-3xl font-serif-prestige font-bold text-slate-900 mt-1">
                            Lebih dari Sekadar Formulir, Fondasi Kepercayaan Korporat
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-600 mt-2 leading-relaxed">
                            OrderFlow menyelaraskan kepatuhan anggaran, pemisahan wewenang (*Segregation of Duties*), dan pengawasan forensik agar setiap rupiah pengeluaran dapat dipertanggungjawabkan kepada Dewan Direksi.
                        </p>
                    </div>

                    <!-- 2x2 Mini Feature Cards Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                        
                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 flex items-start space-x-3.5">
                            <div class="w-9 h-9 rounded-lg bg-[#0A1D37] text-amber-400 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-900">Pemisahan Tugas (SoD)</h4>
                                <p class="text-[11px] text-slate-500 mt-0.5">Pemohon belanja tidak berhak menyetujui anggarannya sendiri.</p>
                            </div>
                        </div>

                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 flex items-start space-x-3.5">
                            <div class="w-9 h-9 rounded-lg bg-[#C58B24] text-white flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-900">Validasi Pagu Real-Time</h4>
                                <p class="text-[11px] text-slate-500 mt-0.5">Pencegahan otomatis pengajuan yang melampaui sisa plafon divisi.</p>
                            </div>
                        </div>

                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 flex items-start space-x-3.5">
                            <div class="w-9 h-9 rounded-lg bg-[#0A1D37] text-amber-400 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-900">Audit Trail Forensik</h4>
                                <p class="text-[11px] text-slate-500 mt-0.5">Jejak aktivitas tak terhapus siap untuk pemeriksaan auditor internal.</p>
                            </div>
                        </div>

                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 flex items-start space-x-3.5">
                            <div class="w-9 h-9 rounded-lg bg-[#C58B24] text-white flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-900">Helpdesk &amp; Dukungan</h4>
                                <p class="text-[11px] text-slate-500 mt-0.5">Layanan bantuan IT korporat dan panduan SOP interaktif.</p>
                            </div>
                        </div>

                    </div>

                    <div>
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-amber-700 hover:text-amber-800">
                            <span>Pelajari Kebijakan Tata Kelola</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- 8. LATEST NEWS & EVENTS (4 Cards with Date Badges, exact match to Image 1) -->
    <section id="berita" class="py-20 bg-[#F8FAFC]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="flex justify-between items-end mb-10">
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-[#C58B24]">PEMBARUAN &amp; AGENDA OPERASIONAL</div>
                    <h2 class="text-3xl font-serif-prestige font-bold text-slate-900 mt-1">
                        Kabar Terkini Sistem Pengadaan
                    </h2>
                </div>
                <a href="{{ route('login') }}" class="text-xs font-bold uppercase tracking-wider text-[#0A1D37] hover:text-amber-700 inline-flex items-center gap-1.5">
                    <span>Lihat Semua Berita</span>
                    <span>&rarr;</span>
                </a>
            </div>

            <!-- 4 News Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <!-- News 1 -->
                <div class="bg-white rounded-xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col justify-between group">
                    <div>
                        <div class="relative h-44 overflow-hidden bg-slate-100">
                            <img src="{{ asset('images/procurement_team_hero.jpg') }}" alt="News 1" class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500">
                            <!-- Date Badge on Top Left -->
                            <div class="absolute top-3 left-3 bg-[#0A1D37] text-white px-2.5 py-1.5 rounded text-center leading-none shadow-md">
                                <div class="text-[9px] uppercase font-bold text-amber-400">SEP</div>
                                <div class="text-base font-bold font-serif-prestige mt-0.5">25</div>
                            </div>
                        </div>
                        <div class="p-5 space-y-2">
                            <h3 class="text-sm font-bold text-slate-900 leading-snug group-hover:text-amber-700 transition-colors">
                                Peluncuran Pembaruan Three-Way Matching v2.4 Otomatis
                            </h3>
                            <p class="text-xs text-slate-500 leading-relaxed line-clamp-3">
                                Sistem kini secara langsung memverifikasi nominal PO, kuantitas BAST fisik di gudang, dan faktur pajak invoice vendor sebelum otorisasi pencairan dana.
                            </p>
                        </div>
                    </div>
                    <div class="p-5 pt-0">
                        <a href="{{ route('login') }}" class="text-xs font-bold text-[#0A1D37] group-hover:text-amber-700 uppercase tracking-wider inline-flex items-center gap-1">
                            <span>Baca Selengkapnya</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>

                <!-- News 2 -->
                <div class="bg-white rounded-xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col justify-between group">
                    <div>
                        <div class="relative h-44 overflow-hidden bg-slate-100">
                            <img src="{{ asset('images/executive_approval.jpg') }}" alt="News 2" class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500">
                            <!-- Date Badge on Top Left -->
                            <div class="absolute top-3 left-3 bg-[#0A1D37] text-white px-2.5 py-1.5 rounded text-center leading-none shadow-md">
                                <div class="text-[9px] uppercase font-bold text-amber-400">SEP</div>
                                <div class="text-base font-bold font-serif-prestige mt-0.5">18</div>
                            </div>
                        </div>
                        <div class="p-5 space-y-2">
                            <h3 class="text-sm font-bold text-slate-900 leading-snug group-hover:text-amber-700 transition-colors">
                                Evaluasi Kinerja Anggaran &amp; Plafon Pengadaan Q4
                            </h3>
                            <p class="text-xs text-slate-500 leading-relaxed line-clamp-3">
                                Rapat koordinasi para Kepala Divisi mengenai optimalisasi sisa pagu operasional tahun berjalan dengan tata kelola berbasis wewenang berjenjang.
                            </p>
                        </div>
                    </div>
                    <div class="p-5 pt-0">
                        <a href="{{ route('login') }}" class="text-xs font-bold text-[#0A1D37] group-hover:text-amber-700 uppercase tracking-wider inline-flex items-center gap-1">
                            <span>Baca Selengkapnya</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>

                <!-- News 3 -->
                <div class="bg-white rounded-xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col justify-between group">
                    <div>
                        <div class="relative h-44 overflow-hidden bg-slate-100">
                            <img src="{{ asset('images/corporate_tower.jpg') }}" alt="News 3" class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500">
                            <!-- Date Badge on Top Left -->
                            <div class="absolute top-3 left-3 bg-[#0A1D37] text-white px-2.5 py-1.5 rounded text-center leading-none shadow-md">
                                <div class="text-[9px] uppercase font-bold text-amber-400">SEP</div>
                                <div class="text-base font-bold font-serif-prestige mt-0.5">10</div>
                            </div>
                        </div>
                        <div class="p-5 space-y-2">
                            <h3 class="text-sm font-bold text-slate-900 leading-snug group-hover:text-amber-700 transition-colors">
                                Sosialisasi Akreditasi Mitra Rekanan Terdaftar 2026
                            </h3>
                            <p class="text-xs text-slate-500 leading-relaxed line-clamp-3">
                                Penerapan standar ISO 37001 Sistem Manajemen Anti Penyuapan bagi seluruh rekanan vendor yang mengikuti tender penawaran harga resmi (RFQ).
                            </p>
                        </div>
                    </div>
                    <div class="p-5 pt-0">
                        <a href="{{ route('login') }}" class="text-xs font-bold text-[#0A1D37] group-hover:text-amber-700 uppercase tracking-wider inline-flex items-center gap-1">
                            <span>Baca Selengkapnya</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>

                <!-- News 4 -->
                <div class="bg-white rounded-xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col justify-between group">
                    <div>
                        <div class="relative h-44 overflow-hidden bg-slate-100">
                            <img src="{{ asset('images/corporate_procurement_hero.jpg') }}" alt="News 4" class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500">
                            <!-- Date Badge on Top Left -->
                            <div class="absolute top-3 left-3 bg-[#0A1D37] text-white px-2.5 py-1.5 rounded text-center leading-none shadow-md">
                                <div class="text-[9px] uppercase font-bold text-amber-400">SEP</div>
                                <div class="text-base font-bold font-serif-prestige mt-0.5">02</div>
                            </div>
                        </div>
                        <div class="p-5 space-y-2">
                            <h3 class="text-sm font-bold text-slate-900 leading-snug group-hover:text-amber-700 transition-colors">
                                Implementasi Tanda Tangan Digital Pada Purchase Order
                            </h3>
                            <p class="text-xs text-slate-500 leading-relaxed line-clamp-3">
                                Surat Pesanan Pembelian kini dilengkapi QR-Code validasi keaslian dokumen PDF yang sah menurut standar hukum dan ketentuan audit korporat.
                            </p>
                        </div>
                    </div>
                    <div class="p-5 pt-0">
                        <a href="{{ route('login') }}" class="text-xs font-bold text-[#0A1D37] group-hover:text-amber-700 uppercase tracking-wider inline-flex items-center gap-1">
                            <span>Baca Selengkapnya</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- 9. Stay Connected Banner (Golden Amber Ribbon, exact match to Image 1) -->
    <section class="bg-[#C58B24] text-white py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto flex flex-col lg:flex-row items-center justify-between gap-6">
            
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0 text-white">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-xs font-extrabold uppercase tracking-widest text-amber-100">TERHUBUNG DENGAN TIM PENGADAAN</div>
                    <div class="text-base sm:text-lg font-serif-prestige font-bold text-white">
                        Dapatkan buletin SOP belanja, jadwal evaluasi vendor, dan pemutakhiran pagu.
                    </div>
                </div>
            </div>

            <!-- Email Subscribe Form -->
            <form action="{{ route('login') }}" class="flex w-full lg:w-auto items-center max-w-md gap-2">
                <input type="email" placeholder="nama.karyawan@orderflow.com" class="px-4 py-3 rounded-lg text-slate-900 bg-white text-xs w-full lg:w-72 focus:outline-none focus:ring-2 focus:ring-amber-900 shadow-inner">
                <button type="submit" class="px-6 py-3 bg-[#0A1D37] hover:bg-slate-900 text-white text-xs font-bold uppercase tracking-wider rounded-lg shadow-md transition whitespace-nowrap">
                    Berlangganan
                </button>
            </form>

        </div>
    </section>

    <!-- 10. Corporate Prestige Footer (Deep Navy #0A1D37, exact match to Image 1) -->
    <footer id="kontak" class="bg-[#0A1D37] text-slate-300 text-xs py-14 border-t border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8">
                
                <!-- Col 1: Crest Logo & Mission -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-lg bg-[#070D1E] border border-amber-500/80 p-1 flex items-center justify-center overflow-hidden">
                            <img src="{{ asset('images/orderflow_emblem.jpg') }}" alt="OrderFlow" class="w-full h-full object-contain">
                        </div>
                        <div>
                            <div class="font-serif-prestige font-bold text-lg text-white">ORDERFLOW ENTERPRISE</div>
                            <div class="text-[10px] text-amber-400 uppercase tracking-widest">Sistem Pengadaan Terpadu</div>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed max-w-sm">
                        Memberdayakan integritas, membangun kepatuhan operasional korporat yang transparan dan bebas dari benturan kepentingan.
                    </p>
                    <div class="flex items-center space-x-3 text-slate-400 pt-2">
                        <span class="w-8 h-8 rounded-full bg-white/5 hover:bg-amber-500/20 hover:text-amber-400 flex items-center justify-center cursor-pointer transition">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </span>
                        <span class="w-8 h-8 rounded-full bg-white/5 hover:bg-amber-500/20 hover:text-amber-400 flex items-center justify-center cursor-pointer transition">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.936 9.936 0 0024 4.59z"/></svg>
                        </span>
                        <span class="w-8 h-8 rounded-full bg-white/5 hover:bg-amber-500/20 hover:text-amber-400 flex items-center justify-center cursor-pointer transition">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                        </span>
                    </div>
                </div>

                <!-- Col 2: Quick Links -->
                <div class="space-y-3">
                    <div class="text-xs font-bold uppercase tracking-wider text-amber-400">TENTANG KAMI</div>
                    <ul class="space-y-2 text-slate-400 text-xs">
                        <li><a href="#tentang" class="hover:text-white transition">Profil Perusahaan</a></li>
                        <li><a href="#alur-kerja" class="hover:text-white transition">Kebijakan Pengadaan</a></li>
                        <li><a href="#tata-kelola" class="hover:text-white transition">Kepatuhan Anti-Bribery</a></li>
                        <li><a href="#berita" class="hover:text-white transition">Berita &amp; Rilis Pers</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-white transition">Kontak Dewan Audit</a></li>
                    </ul>
                </div>

                <!-- Col 3: Programs / Modules -->
                <div class="space-y-3">
                    <div class="text-xs font-bold uppercase tracking-wider text-amber-400">MODUL PENGADAAN</div>
                    <ul class="space-y-2 text-slate-400 text-xs">
                        <li><a href="#modul" class="hover:text-white transition">Purchase Request (PR)</a></li>
                        <li><a href="#modul" class="hover:text-white transition">Multi-Tier Approval</a></li>
                        <li><a href="#modul" class="hover:text-white transition">RFQ &amp; Evaluasi Vendor</a></li>
                        <li><a href="#modul" class="hover:text-white transition">Surat Pesanan PO Resmi</a></li>
                        <li><a href="#modul" class="hover:text-white transition">Three-Way Matching</a></li>
                    </ul>
                </div>

                <!-- Col 4: Contact HQ -->
                <div class="space-y-3">
                    <div class="text-xs font-bold uppercase tracking-wider text-amber-400">KONTAK KANTOR PUSAT</div>
                    <ul class="space-y-2 text-slate-400 text-xs">
                        <li class="flex items-start gap-2">
                            <span class="text-amber-400">&bull;</span>
                            <span>Gedung Graha Niaga Lt. 18, Jakarta Selatan</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-amber-400">&bull;</span>
                            <span>(021) 555-1234 / ext. 4101</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-amber-400">&bull;</span>
                            <span>procurement@orderflow.com</span>
                        </li>
                    </ul>
                </div>

            </div>

            <!-- Bottom Copyright & Legal Links -->
            <div class="pt-8 border-t border-white/10 flex flex-col sm:flex-row justify-between items-center text-xs text-slate-400 gap-4">
                <div>
                    &copy; {{ date('Y') }} OrderFlow Enterprise &bull; PT Solusi Korporasi Nusantara. All Rights Reserved.
                </div>
                <div class="flex items-center space-x-6 text-[11px]">
                    <a href="{{ route('login') }}" class="hover:text-white transition">Privacy Policy</a>
                    <a href="{{ route('login') }}" class="hover:text-white transition">Terms of Use</a>
                    <a href="{{ route('login') }}" class="hover:text-white transition">Accessibility</a>
                </div>
            </div>

        </div>
    </footer>

</body>
</html>
