<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'OrderFlow') }} — Portal Masuk Karyawan Terotorisasi</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-800 bg-[#F5F6FA] min-h-full flex flex-col justify-center selection:bg-amber-600 selection:text-white">

        <div class="min-h-screen flex flex-col lg:flex-row">
            
            <!-- Left Branding Showcase Panel (Deep Navy #0A1D37 with Gold Prestige Elements) -->
            <div class="hidden lg:flex lg:w-1/2 relative bg-[#0A1D37] overflow-hidden text-white flex-col justify-between p-12 xl:p-16">
                <!-- Background Image with Clean Contemporary Vignette -->
                <div class="absolute inset-0 z-0">
                    <img src="{{ asset('images/procurement_team_hero.jpg') }}" alt="Pusat Otorisasi Eksekutif OrderFlow" class="w-full h-full object-cover object-center transform scale-105 filter brightness-75">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#0A1D37] via-[#0A1D37]/80 to-[#0A1D37]/60"></div>
                </div>

                <!-- Top Left Logo -->
                <div class="relative z-10">
                    <a href="{{ url('/') }}" class="inline-flex items-center space-x-3.5 group">
                        <div class="w-12 h-12 rounded-xl bg-[#070D1E] border-2 border-amber-500/80 p-1 flex items-center justify-center shadow-lg shadow-black/30 group-hover:scale-105 transition-transform duration-200 overflow-hidden">
                            <img src="{{ asset('images/orderflow_emblem.jpg') }}" alt="OrderFlow Enterprise" class="w-full h-full object-contain">
                        </div>
                        <div>
                            <div class="text-xl font-serif-prestige font-bold tracking-tight text-white leading-none">
                                ORDERFLOW
                            </div>
                            <div class="text-[10px] font-sans font-bold tracking-[0.2em] uppercase text-amber-400 mt-1">
                                ENTERPRISE GOVERNANCE
                            </div>
                            <p class="text-[9px] text-slate-400 uppercase tracking-wider">Tata Kelola &bull; Akuntabilitas</p>
                        </div>
                    </a>
                </div>

                <!-- Center Value Proposition & Live Trust Pill -->
                <div class="relative z-10 my-auto max-w-lg space-y-6">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-400/30 backdrop-blur-md shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                        Portal Resmi Terotorisasi Internal
                    </div>
                    
                    <h2 class="text-3xl xl:text-4xl font-serif-prestige font-bold text-white tracking-tight leading-tight">
                        Akses Terpadu Pengadaan &amp; Tata Kelola Anggaran Korporat
                    </h2>

                    <p class="text-slate-300 text-xs sm:text-sm leading-relaxed font-normal">
                        Mendukung pemenuhan prinsip <strong class="text-white font-semibold">Good Corporate Governance (GCG)</strong> melalui integrasi persetujuan berjenjang, komparasi penawaran rekanan, dan rekonsiliasi Three-Way Matching yang presisi.
                    </p>

                    <!-- Trust Cards -->
                    <div class="grid grid-cols-2 gap-4 pt-2">
                        <div class="p-4 rounded-xl bg-white/10 border border-white/15 backdrop-blur-md">
                            <div class="text-[10px] font-extrabold text-amber-400 uppercase tracking-wider">Keamanan &amp; Audit</div>
                            <div class="text-sm font-bold text-white mt-1">ISO 27001 &amp; ISO 37001</div>
                            <div class="text-[11px] text-slate-300 mt-0.5">Audit log anti-tamper tervalidasi</div>
                        </div>
                        <div class="p-4 rounded-xl bg-white/10 border border-white/15 backdrop-blur-md">
                            <div class="text-[10px] font-extrabold text-amber-400 uppercase tracking-wider">Rekonsiliasi Faktur</div>
                            <div class="text-sm font-bold text-white mt-1">3-Way Matching 1:1</div>
                            <div class="text-[11px] text-slate-300 mt-0.5">PO &bull; BAST Gudang &bull; Tagihan</div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Footnote -->
                <div class="relative z-10 flex items-center justify-between text-xs text-slate-400 pt-6 border-t border-white/15">
                    <span>&copy; {{ date('Y') }} PT Solusi Korporasi Nusantara</span>
                    <span class="inline-flex items-center gap-1.5 text-amber-400 font-semibold">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        Server Status: Online (99.98% SLA)
                    </span>
                </div>
            </div>

            <!-- Right Form Panel (Clean, Soft Pillowed Card Aesthetic) -->
            <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-10 lg:p-16 bg-[#F5F6FA]">
                <div class="w-full max-w-md space-y-6">
                    
                    <!-- Mobile Logo Header (shown only on mobile) -->
                    <div class="lg:hidden flex items-center justify-between pb-4 border-b border-slate-200">
                        <a href="{{ url('/') }}" class="flex items-center space-x-2.5">
                            <div class="w-9 h-9 rounded-xl bg-[#070D1E] border border-amber-500/80 p-0.5 flex items-center justify-center overflow-hidden">
                                <img src="{{ asset('images/orderflow_emblem.jpg') }}" alt="OF" class="w-full h-full object-contain">
                            </div>
                            <div class="text-base font-bold text-slate-900 font-serif-prestige">ORDERFLOW</div>
                        </a>
                        <a href="{{ url('/') }}" class="text-xs font-bold text-amber-700 hover:text-amber-800">
                            &larr; Beranda
                        </a>
                    </div>

                    <!-- Back to Home Link (Desktop) -->
                    <div class="hidden lg:block">
                        <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-amber-700 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            <span>Kembali ke Beranda Utama</span>
                        </a>
                    </div>

                    <!-- Slot Content inside Soft Pillowed White Card -->
                    <div class="card-soft p-7 sm:p-8">
                        {{ $slot }}
                    </div>

                    <!-- Footer Note -->
                    <div class="pt-2 text-center text-xs text-slate-400">
                        Sistem ini dilindungi enkripsi TLS 1.3 256-Bit. Akses hanya diizinkan bagi personil korporat terdaftar.
                    </div>

                </div>
            </div>

        </div>

    </body>
</html>
