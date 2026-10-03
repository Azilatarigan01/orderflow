<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'OrderFlow') }} — Sistem Pengadaan Korporat</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            /* Sidebar width token */
            :root { --sidebar-w: 16rem; }
            @media (min-width: 1024px) {
                .main-content { margin-left: var(--sidebar-w); }
            }
            /* Sidebar always visible on desktop */
            @media (min-width: 1024px) {
                aside.sidebar { transform: translateX(0) !important; }
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-[#F5F6FA] text-slate-800 selection:bg-orange-500 selection:text-white">

        <!-- Vertical Sidebar -->
        @include('layouts.navigation')

        <!-- Main Content Area -->
        <div class="main-content min-h-screen flex flex-col pt-0 lg:pt-0">

            <!-- Mobile top bar spacer -->
            <div class="h-14 lg:hidden"></div>

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white/90 backdrop-blur-md border-b border-slate-200/70 sticky top-0 z-20 shadow-xs">
                    <div class="px-6 sm:px-8 py-3.5">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Flash Messages -->
            @if (session('success') || session('error'))
                <div class="px-6 sm:px-8 pt-5">
                    @if (session('success'))
                        <div class="flex items-center p-4 text-emerald-900 bg-emerald-50 border border-emerald-200 border-l-4 border-l-emerald-600 rounded-xl shadow-xs" role="alert">
                            <svg class="w-5 h-5 mr-3 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-semibold">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="flex items-center p-4 text-rose-900 bg-rose-50 border border-rose-200 border-l-4 border-l-rose-600 rounded-xl shadow-xs" role="alert">
                            <svg class="w-5 h-5 mr-3 text-rose-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-semibold">{{ session('error') }}</span>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Page Content -->
            <main class="flex-1 px-6 sm:px-8 py-6">
                {{ $slot }}
            </main>

            <!-- Corporate Footer inside App -->
            <footer class="mt-auto px-6 sm:px-8 py-4 border-t border-slate-200 text-xs text-slate-400 flex flex-col sm:flex-row justify-between items-center gap-2">
                <span>&copy; {{ date('Y') }} PT Solusi Korporasi Nusantara &bull; OrderFlow Enterprise Procurement System</span>
                <span class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Sesi Kerja Aman (TLS 1.3)
                </span>
            </footer>
        </div>
        @stack('scripts')
    </body>
</html>
