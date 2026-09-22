<aside x-data="{ mobileOpen: false }"
    class="sidebar fixed top-0 left-0 h-screen w-64 bg-slate-900 border-r border-slate-800 flex flex-col z-40
           -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out"
    :class="{ '-translate-x-full': !mobileOpen, 'lg:translate-x-0': true }"
>
    <!-- Logo / Brand -->
    <div class="px-5 py-5 border-b border-slate-800 flex-shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-sky-500 flex items-center justify-center text-white font-bold text-sm shadow-lg shadow-indigo-500/30 flex-shrink-0">
                OF
            </div>
            <div>
                <div class="text-sm font-bold tracking-tight text-white">ORDERFLOW</div>
                <div class="text-[10px] text-slate-500 font-medium tracking-wide uppercase">Enterprise Procurement</div>
            </div>
        </a>
    </div>

    <!-- Navigation Links -->
    <nav class="flex-1 px-3 py-5 space-y-0.5 overflow-y-auto">

        <p class="px-3 mb-3 text-[10px] font-bold uppercase tracking-widest text-slate-600">Menu Utama</p>

        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}"
            class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
            {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span>Dashboard</span>
            @if(request()->routeIs('dashboard'))
                <span class="ms-auto w-1.5 h-1.5 rounded-full bg-white/50 flex-shrink-0"></span>
            @endif
        </a>

        {{-- Pengajuan Pembelian (PR) --}}
        <a href="{{ route('purchase-requests.index') }}"
            class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
            {{ request()->routeIs('purchase-requests.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span>Pengajuan PR</span>
            @if(request()->routeIs('purchase-requests.*'))
                <span class="ms-auto w-1.5 h-1.5 rounded-full bg-white/50 flex-shrink-0"></span>
            @endif
        </a>

        {{-- Antrean Persetujuan (Manager, Finance, Admin, Auditor) --}}
        @if(Auth::user()->hasRole(['manager', 'finance', 'admin', 'auditor']))
            @php
                $pendingApprovalCount = app(\App\Services\ApprovalService::class)->getPendingQueueForUser(Auth::user())->count();
            @endphp
            <a href="{{ route('approvals.index') }}"
                class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                {{ request()->routeIs('approvals.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <span class="flex-1">Antrean Approval</span>
                @if($pendingApprovalCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-500 text-slate-950 font-mono shadow-xs animate-pulse">
                        {{ $pendingApprovalCount }}
                    </span>
                @elseif(request()->routeIs('approvals.*'))
                    <span class="ms-auto w-1.5 h-1.5 rounded-full bg-white/50 flex-shrink-0"></span>
                @endif
            </a>
        @endif

        {{-- RFQ & Quotation Vendor (Procurement, Admin, Finance, Auditor) --}}
        @if(Auth::user()->hasRole(['procurement', 'admin', 'finance', 'auditor']))
            @php
                $activeRfqCount = \App\Models\PurchaseRequest::where('status', 'approved')->count();
            @endphp
            <a href="{{ route('quotations.index') }}"
                class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                {{ request()->routeIs('quotations.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span class="flex-1">RFQ & Quotation</span>
                @if($activeRfqCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-sky-500 text-slate-950 font-mono shadow-xs">
                        {{ $activeRfqCount }}
                    </span>
                @elseif(request()->routeIs('quotations.*'))
                    <span class="ms-auto w-1.5 h-1.5 rounded-full bg-white/50 flex-shrink-0"></span>
                @endif
            </a>
        @endif

        {{-- Purchase Orders (Procurement, Admin, Finance, Auditor) --}}
        @if(Auth::user()->hasRole(['procurement', 'admin', 'finance', 'auditor']))
            @php
                $activePoCount = \App\Models\PurchaseOrder::whereIn('status', ['issued', 'partially_received'])->count();
            @endphp
            <a href="{{ route('purchase-orders.index') }}"
                class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                {{ request()->routeIs('purchase-orders.*') || request()->routeIs('goods-receipts.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="flex-1">Purchase Orders</span>
                @if($activePoCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-indigo-500 text-white font-mono shadow-xs">
                        {{ $activePoCount }}
                    </span>
                @elseif(request()->routeIs('purchase-orders.*'))
                    <span class="ms-auto w-1.5 h-1.5 rounded-full bg-white/50 flex-shrink-0"></span>
                @endif
            </a>
        @endif


        {{-- Direktori Vendor --}}
        <a href="{{ route('vendors.index') }}"
            class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
            {{ request()->routeIs('vendors.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <span>Direktori Vendor</span>
            @if(request()->routeIs('vendors.*'))
                <span class="ms-auto w-1.5 h-1.5 rounded-full bg-white/50 flex-shrink-0"></span>
            @endif
        </a>

        {{-- Notifikasi Dalam Aplikasi (Semua Pengguna) --}}
        @php
            $unreadNotifCount = \App\Models\InAppNotification::where('user_id', Auth::id())->unread()->count();
        @endphp
        <a href="{{ route('notifications.index') }}"
            class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
            {{ request()->routeIs('notifications.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span class="flex-1">Notifikasi</span>
            @if($unreadNotifCount > 0)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-500 text-white font-mono shadow-xs">
                    {{ $unreadNotifCount }}
                </span>
            @elseif(request()->routeIs('notifications.*'))
                <span class="ms-auto w-1.5 h-1.5 rounded-full bg-white/50 flex-shrink-0"></span>
            @endif
        </a>

    </nav>

    <!-- User Profile at Bottom -->
    <div class="px-3 py-4 border-t border-slate-800 flex-shrink-0">
        <div x-data="{ dropup: false }" class="relative">
            <button @click="dropup = !dropup" @click.away="dropup = false"
                class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-xl hover:bg-slate-800 transition-all duration-150 text-left">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-indigo-600 to-sky-500 text-white flex items-center justify-center font-bold text-xs flex-shrink-0">
                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-semibold text-white truncate">{{ Auth::user()->name }}</div>
                    <div class="text-[10px] text-slate-500 truncate">{{ Auth::user()->role_label }}</div>
                </div>
                <svg class="w-4 h-4 text-slate-600 flex-shrink-0 transition-transform duration-200"
                    :class="{ 'rotate-180': dropup }"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <!-- Dropup Menu -->
            <div x-show="dropup"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-2"
                class="absolute bottom-full left-0 right-0 mb-2 bg-slate-800 border border-slate-700 rounded-xl shadow-2xl shadow-black/40 overflow-hidden"
                style="display: none;">

                <div class="px-4 py-3 border-b border-slate-700">
                    <div class="text-[10px] text-slate-500 uppercase tracking-wider">Login sebagai</div>
                    <div class="text-sm font-semibold text-white mt-1">{{ Auth::user()->name }}</div>
                    <div class="text-xs text-slate-400 truncate">{{ Auth::user()->email }}</div>
                    <span class="inline-block mt-2 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-600/20 text-indigo-400 border border-indigo-500/30">
                        {{ Auth::user()->role_label }}
                    </span>
                </div>

                <a href="{{ route('profile.edit') }}"
                    class="flex items-center space-x-2.5 px-4 py-2.5 text-sm text-slate-300 hover:bg-slate-700 hover:text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>Pengaturan Akun</span>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center space-x-2.5 px-4 py-2.5 text-sm text-rose-400 hover:bg-rose-600/10 hover:text-rose-300 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span>Keluar (Log Out)</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</aside>

<!-- Mobile Top Bar -->
<div class="lg:hidden fixed top-0 left-0 right-0 h-14 bg-slate-900 border-b border-slate-800 flex items-center justify-between px-4 z-30">
    <button onclick="(function(){ var s=document.querySelector('aside'); var x=s._x_dataStack[0]; x.mobileOpen=!x.mobileOpen; })()"
        class="text-slate-400 hover:text-white transition p-2 rounded-lg hover:bg-slate-800">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>
    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-tr from-indigo-600 to-sky-500 flex items-center justify-center text-white font-bold text-xs">OF</div>
        <span class="text-sm font-bold text-white">ORDERFLOW</span>
    </a>
    <div class="w-9"></div>
</div>

<!-- Mobile Overlay -->
<div class="lg:hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-30"
    x-data
    x-show="$store && false"
    style="display:none"
    id="mobile-overlay"
    onclick="(function(){ var s=document.querySelector('aside'); var x=s._x_dataStack[0]; x.mobileOpen=false; document.getElementById('mobile-overlay').style.display='none'; })()">
</div>

<script>
    // Sync mobile overlay visibility with sidebar
    document.addEventListener('alpine:init', () => {
        document.addEventListener('click', () => {
            const sidebar = document.querySelector('aside');
            if (sidebar && sidebar._x_dataStack) {
                const open = sidebar._x_dataStack[0].mobileOpen;
                const overlay = document.getElementById('mobile-overlay');
                if (overlay) overlay.style.display = open ? 'block' : 'none';
            }
        }, true);
    });
</script>
