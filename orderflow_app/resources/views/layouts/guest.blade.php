<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased bg-slate-950 min-h-screen flex items-center justify-center p-4 selection:bg-indigo-500 selection:text-white">
        <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
            <div class="absolute -top-40 -left-40 w-96 h-96 rounded-full bg-indigo-600/20 blur-3xl"></div>
            <div class="absolute top-1/2 -right-40 w-96 h-96 rounded-full bg-sky-600/20 blur-3xl"></div>
        </div>

        <div class="w-full max-w-md relative z-10">
            <!-- Corporate Brand Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-sky-500 shadow-xl shadow-indigo-500/25 mb-4 text-white">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-white">OrderFlow Enterprise</h1>
                <p class="text-sm text-slate-400 mt-1">Internal Procurement & Vendor Management System</p>
            </div>

            <!-- Card Box -->
            <div class="bg-slate-900/90 backdrop-blur-xl border border-slate-800 rounded-3xl p-8 shadow-2xl">
                {{ $slot }}
            </div>

            <div class="text-center mt-6 text-xs text-slate-500">
                &copy; {{ date('Y') }} OrderFlow System. Hak Cipta Dilindungi Undang-Undang. Akses Terbatas untuk Karyawan Terotorisasi.
            </div>
        </div>
    </body>
</html>
