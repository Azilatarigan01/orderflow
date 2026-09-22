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
    <body class="font-sans antialiased bg-slate-100">

        <!-- Vertical Sidebar -->
        @include('layouts.navigation')

        <!-- Main Content Area -->
        <div class="main-content min-h-screen flex flex-col pt-0 lg:pt-0">

            <!-- Mobile top bar spacer -->
            <div class="h-14 lg:hidden"></div>

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white border-b border-slate-200 shadow-sm">
                    <div class="px-6 py-4">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Flash Messages -->
            @if (session('success') || session('error'))
                <div class="px-6 pt-5">
                    @if (session('success'))
                        <div class="flex items-center p-4 text-emerald-800 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl shadow-sm" role="alert">
                            <svg class="w-5 h-5 mr-2 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-medium">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="flex items-center p-4 text-rose-800 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl shadow-sm" role="alert">
                            <svg class="w-5 h-5 mr-2 text-rose-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-medium">{{ session('error') }}</span>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Page Content -->
            <main class="flex-1 px-6 py-6">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
