<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Akses Terbatas | OrderFlow Enterprise</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-[#0A1D37] text-slate-100 flex items-center justify-center p-6 font-['Plus_Jakarta_Sans',sans-serif]">
    <div class="max-w-xl w-full text-center">
        <!-- Logo Badge -->
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-400 mb-8 shadow-xl shadow-amber-500/5">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>

        <p class="text-xs font-bold uppercase tracking-[0.25em] text-amber-400 mb-2">Kode Keamanan: 403 Forbidden</p>
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white mb-4 font-['Playfair_Display',serif]">
            Akses Otorisasi Terbatas
        </h1>
        <p class="text-slate-300 text-sm sm:text-base leading-relaxed mb-8 max-w-md mx-auto">
            Anda tidak memiliki izin role atau kewenangan departemen yang memadai untuk mengakses sumber daya ini. Kebijakan Good Corporate Governance membatasi akses sesuai hak otorisasi Anda.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ url('/dashboard') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-semibold text-sm shadow-lg shadow-blue-600/30 transition duration-150 flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Kembali ke Dashboard
            </a>
            <a href="javascript:history.back()" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700 text-slate-300 hover:text-white font-semibold text-sm transition duration-150 flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Halaman Sebelumnya
            </a>
        </div>

        <div class="mt-12 pt-8 border-t border-slate-800/80 text-xs text-slate-500">
            OrderFlow Enterprise Governance System &bull; Keamanan Terjamin
        </div>
    </div>
</body>
</html>
