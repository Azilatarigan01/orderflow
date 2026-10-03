<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 py-1">
            
            <!-- Left Greeting & Title -->
            <div>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-blue-50 text-blue-700 text-[11px] font-bold border border-blue-200/80 mb-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                    <span>Dashboard Eksekutif Terpadu</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Selamat Datang, {{ Auth::user()->name }}!</span>
                </h2>
                <p class="text-xs text-slate-500 font-medium mt-0.5">
                    Sistem manajemen pengadaan barang &amp; jasa terintegrasi berbasis tata kelola Good Corporate Governance.
                </p>
            </div>

            <!-- Right Controls: View Switcher, Role Pill, Notifications, CTA -->
            <div class="flex flex-wrap items-center gap-3">
                

                <!-- Role Pill -->
                <div class="hidden md:inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-slate-200/80 rounded-2xl shadow-xs text-xs font-bold text-slate-800">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>{{ Auth::user()->role_label }}</span>
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

    <!-- React Root Mount Node -->
    <div id="react-management-dashboard-root" data-user="{{ json_encode([
        'name' => Auth::user()->name,
        'email' => Auth::user()->email,
        'role' => Auth::user()->role,
        'role_label' => Auth::user()->role_label,
        'department' => Auth::user()->department?->name ?? 'Semua Divisi',
    ]) }}"></div>

    @push('scripts')
        @viteReactRefresh
        @vite(['resources/js/react-dashboard.jsx'])
    @endpush
</x-app-layout>
