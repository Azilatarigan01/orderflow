<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OrderFlow — Sistem Informasi Pengadaan & Manajemen Vendor Terpadu</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 font-['Plus_Jakarta_Sans',sans-serif] antialiased selection:bg-slate-800 selection:text-white">

    <!-- Top Utility Bar -->
    <div class="bg-slate-900 text-slate-400 text-xs py-2 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">
            <div class="flex items-center space-x-6">
                <span>Portal Resmi Pengadaan Barang & Jasa Internal</span>
                <span class="hidden md:inline text-slate-600">|</span>
                <span class="hidden md:inline">Standar Tata Kelola & Kepatuhan ISO 9001 / ISO 37001</span>
            </div>
            <div class="flex items-center space-x-4">
                <span class="inline-flex items-center">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2"></span>
                    Sistem Operasional Normal
                </span>
            </div>
        </div>
    </div>

    <!-- Main Navigation Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Corporate Logo & Identity -->
                <div class="flex items-center space-x-4">
                    <div class="w-11 h-11 bg-slate-900 text-white rounded-lg flex items-center justify-center font-bold tracking-wider text-lg shadow-sm">
                        OF
                    </div>
                    <div>
                        <div class="text-xl font-bold tracking-tight text-slate-900 flex items-center">
                            ORDERFLOW
                            <span class="ml-2 text-[10px] font-semibold tracking-wider uppercase px-2 py-0.5 bg-slate-100 text-slate-700 rounded border border-slate-200">Enterprise</span>
                        </div>
                        <p class="text-xs text-slate-500 font-medium tracking-wide">Procurement & Supply Chain Management</p>
                    </div>
                </div>

                <!-- Navigation Action -->
                <div class="flex items-center space-x-4">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ route('dashboard') }}" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold rounded-lg transition duration-150 shadow-sm flex items-center gap-2">
                                <span>Masuk ke Dashboard</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold rounded-lg transition duration-150 shadow-sm flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                <span>Portal Masuk Karyawan</span>
                            </a>
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative bg-white border-b border-slate-200 py-20 lg:py-28 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="max-w-3xl">
                <div class="inline-flex items-center px-3 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200 mb-6 tracking-wide uppercase">
                    Platform Pengadaan Korporat Terpadu
                </div>
                <h1 class="text-4xl sm:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
                    Transparansi, Efisiensi, & Pengendalian Pengeluaran Perusahaan.
                </h1>
                <p class="mt-6 text-lg text-slate-600 leading-relaxed font-normal">
                    Mengintegrasikan seluruh siklus pengadaan barang dan jasa internal dari formulir permintaan kerja, otorisasi persetujuan berjenjang, komparasi penawaran rekanan vendor, hingga validasi <em>three-way matching</em> sebelum otorisasi pembayaran.
                </p>
                <div class="mt-8 flex flex-wrap gap-4 items-center">
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-6 py-3.5 bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                            Buka Sistem Kerja
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-7 py-3.5 bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                            Masuk Menggunakan Akun Korporat
                        </a>
                    @endauth
                    <a href="#tata-kelola" class="px-6 py-3.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 text-sm font-semibold rounded-lg transition">
                        Standar Tata Kelola & Regulasi
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Core Enterprise Pillars -->
    <section id="tata-kelola" class="py-20 bg-slate-50 border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">Prinsip Pengendalian & Akuntabilitas</h2>
                <p class="mt-3 text-sm text-slate-600">Menegakkan integritas transaksi pengadaan sesuai tata kelola perusahaan yang baik (Good Corporate Governance).</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Pillar 1 -->
                <div class="bg-white p-8 rounded-xl border border-slate-200 shadow-xs">
                    <div class="w-12 h-12 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-900 mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">Persetujuan Otoritas Bertingkat</h3>
                    <p class="mt-3 text-sm text-slate-600 leading-relaxed">
                        Delegasi wewenang keuangan tersistem secara otomatis berdasarkan nilai nominal transaksi, mencegah pengeluaran di luar pagu anggaran divisi.
                    </p>
                </div>

                <!-- Pillar 2 -->
                <div class="bg-white p-8 rounded-xl border border-slate-200 shadow-xs">
                    <div class="w-12 h-12 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-900 mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">Mitigasi Risiko & Fair Comparison</h3>
                    <p class="mt-3 text-sm text-slate-600 leading-relaxed">
                        Kewajiban perbandingan minimal dua rekanan penawaran harga untuk menjamin perolehan nilai ekonomis terbaik (Best Value for Money).
                    </p>
                </div>

                <!-- Pillar 3 -->
                <div class="bg-white p-8 rounded-xl border border-slate-200 shadow-xs">
                    <div class="w-12 h-12 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-900 mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">Verifikasi Faktur Three-Way Match</h3>
                    <p class="mt-3 text-sm text-slate-600 leading-relaxed">
                        Rekonsiliasi otomatis antara Purchase Order, bukti fisik Goods Receipt, dan Faktur Tagihan sebelum otorisasi transfer perbankan.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center text-xs text-slate-500 gap-4">
            <div>
                &copy; {{ date('Y') }} OrderFlow Enterprise Procurement System. Sistem Tertutup Khusus Penggunaan Internal.
            </div>
            <div class="flex space-x-6">
                <span class="hover:text-slate-800">Pedoman Pengadaan Korporat</span>
                <span class="hover:text-slate-800">Kebijakan Anti Penyuapan</span>
                <span class="hover:text-slate-800">Bantuan IT & Keuangan</span>
            </div>
        </div>
    </footer>

</body>
</html>
