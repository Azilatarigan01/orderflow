<aside x-data="{ mobileOpen: false }"
    class="sidebar fixed top-0 left-0 h-screen w-64 bg-white border-r border-slate-200 flex flex-col z-40
           -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out shadow-2xs"
    :class="{ '-translate-x-full': !mobileOpen, 'lg:translate-x-0': true }"
>
    <!-- Logo / Brand (Image 2 style: colorful geometric mark + modern text) -->
    <div class="px-5 py-5 border-b border-slate-100 flex-shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group">
            <div class="w-10 h-10 rounded-xl bg-[#070D1E] border border-amber-500/70 p-1 flex items-center justify-center shadow-md shadow-blue-950/10 flex-shrink-0 group-hover:scale-105 transition-transform duration-150 overflow-hidden">
                <img src="{{ asset('images/orderflow_emblem.jpg') }}" alt="OrderFlow" class="w-full h-full object-contain">
            </div>
            <div>
                <div class="text-sm font-extrabold tracking-tight text-slate-900 flex items-center">
                    ORDERFLOW
                    <span class="ml-1.5 text-[9px] font-extrabold uppercase px-1.5 py-0.2 bg-amber-50 text-amber-700 rounded-md border border-amber-200/80">ERP</span>
                </div>
                <div class="text-[10px] text-slate-400 font-medium tracking-wide">Procurement Suite</div>
            </div>
        </a>
    </div>

    <!-- Navigation Links -->
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">

        <p class="px-3 pt-2 mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-400">Menu Operasional</p>

        {{-- Dashboard (Unified Single Entry Point) --}}
        <a href="{{ route('dashboard') }}"
            class="group flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
            {{ (request()->routeIs('dashboard') || request()->routeIs('management.dashboard')) ? 'bg-blue-600 text-white shadow-md shadow-blue-500/25 font-semibold' : 'text-slate-600 hover:bg-slate-100/90 hover:text-slate-900' }}">
            <svg class="w-5 h-5 flex-shrink-0 {{ (request()->routeIs('dashboard') || request()->routeIs('management.dashboard')) ? 'text-white' : 'text-slate-400 group-hover:text-slate-700' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span>Dashboard</span>
            @if(request()->routeIs('dashboard') || request()->routeIs('management.dashboard'))
                <span class="ms-auto w-1.5 h-1.5 rounded-full bg-white flex-shrink-0"></span>
            @endif
        </a>

        {{-- Pengajuan Pembelian (PR) --}}
        <a href="{{ route('purchase-requests.index') }}"
            class="group flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
            {{ request()->routeIs('purchase-requests.index') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/25 font-semibold' : 'text-slate-600 hover:bg-slate-100/90 hover:text-slate-900' }}">
            <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('purchase-requests.index') ? 'text-white' : 'text-slate-400 group-hover:text-blue-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span>Daftar PR</span>
            @if(request()->routeIs('purchase-requests.index'))
                <span class="ms-auto w-1.5 h-1.5 rounded-full bg-indigo-300 flex-shrink-0"></span>
            @endif
        </a>

        {{-- Buat PR Baru (Requester, Manager, Admin) --}}
        @if(Auth::user()->hasRole(['requester', 'manager', 'admin']))
            <a href="{{ route('purchase-requests.create') }}"
                class="group flex items-center space-x-3 px-3 py-2 pl-9 rounded-xl text-xs font-semibold transition-all duration-150
                {{ request()->routeIs('purchase-requests.create') ? 'text-blue-600 bg-blue-50 font-bold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('purchase-requests.create') ? 'bg-blue-600' : 'bg-slate-300 group-hover:bg-slate-500' }}"></span>
                <span>+ Buat PR Baru</span>
            </a>
        @endif

        {{-- Antrean Persetujuan (Manager, Finance, HoD, Admin, Auditor) --}}
        @if(Auth::user()->hasRole(['manager', 'finance', 'hod', 'admin', 'auditor']))
            @php
                $pendingApprovalCount = app(\App\Services\ApprovalService::class)->getPendingQueueForUser(Auth::user())->count();
            @endphp
            <a href="{{ route('approvals.index') }}"
                class="group flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                {{ request()->routeIs('approvals.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/25 font-semibold' : 'text-slate-600 hover:bg-slate-100/90 hover:text-slate-900' }}">
                <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('approvals.*') ? 'text-white' : 'text-amber-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <span class="flex-1">
                    @if(Auth::user()->hasRole('finance'))
                        Budget Review (Approval)
                    @else
                        Antrean Approval
                    @endif
                </span>
                @if($pendingApprovalCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ request()->routeIs('approvals.*') ? 'bg-amber-400 text-slate-950 shadow-xs' : 'bg-amber-100 text-amber-900 border border-amber-300/80 animate-pulse' }} font-mono shadow-xs">
                        {{ $pendingApprovalCount }}
                    </span>
                @elseif(request()->routeIs('approvals.*'))
                    <span class="ms-auto w-1.5 h-1.5 rounded-full bg-amber-300 flex-shrink-0"></span>
                @endif
            </a>
        @endif

        {{-- Delegasi Plt (Manager, Finance, Admin, HOD) --}}
        @if(Auth::user()->hasRole(['manager', 'finance', 'admin', 'hod']))
            <a href="{{ route('delegations.index') }}"
                class="group flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                {{ request()->routeIs('delegations.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/25 font-semibold' : 'text-slate-600 hover:bg-slate-100/90 hover:text-slate-900' }}">
                <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('delegations.*') ? 'text-white' : 'text-indigo-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                <span class="flex-1">Delegasi Plt (Cuti)</span>
            </a>
        @endif

        {{-- RFQ & Quotation Vendor (Procurement & Admin only) --}}
        @if(Auth::user()->hasRole(['procurement', 'admin']))
            @php
                $activeRfqCount = \App\Models\PurchaseRequest::where('status', 'approved')->count();
            @endphp
            <a href="{{ route('quotations.index') }}"
                class="group flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                {{ request()->routeIs('quotations.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/25 font-semibold' : 'text-slate-600 hover:bg-slate-100/90 hover:text-slate-900' }}">
                <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('quotations.*') ? 'text-white' : 'text-slate-400 group-hover:text-sky-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span class="flex-1">RFQ &amp; Komparasi</span>
                @if($activeRfqCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ request()->routeIs('quotations.*') ? 'bg-white text-slate-900' : 'bg-blue-50 text-blue-700 border border-blue-200/80' }} font-mono shadow-xs">
                        {{ $activeRfqCount }}
                    </span>
                @elseif(request()->routeIs('quotations.*'))
                    <span class="ms-auto w-1.5 h-1.5 rounded-full bg-white flex-shrink-0"></span>
                @endif
            </a>
        @endif

        {{-- Purchase Orders & Penerimaan Barang (Procurement, Admin, Finance, Warehouse, Requester) --}}
        @if(Auth::user()->hasRole(['procurement', 'admin', 'finance', 'warehouse', 'requester']))
            @php
                $poQuery = \App\Models\PurchaseOrder::whereIn('status', ['issued', 'partially_received']);
                if (Auth::user()->hasRole('requester')) {
                    $poQuery->whereHas('purchaseRequest', fn($q) => $q->where('user_id', Auth::id()));
                }
                $activePoCount = $poQuery->count();
            @endphp
            <a href="{{ route('purchase-orders.index') }}"
                class="group flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                {{ request()->routeIs('purchase-orders.*') || request()->routeIs('goods-receipts.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/25 font-semibold' : 'text-slate-600 hover:bg-slate-100/90 hover:text-slate-900' }}">
                <svg class="w-5 h-5 flex-shrink-0 {{ (request()->routeIs('purchase-orders.*') || request()->routeIs('goods-receipts.*')) ? 'text-white' : 'text-slate-400 group-hover:text-emerald-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="flex-1">
                    @if(Auth::user()->hasRole('requester'))
                        Penerimaan Barang (BAST)
                    @elseif(Auth::user()->hasRole('warehouse'))
                        Penerimaan Barang &amp; Logistik
                    @else
                        Purchase Orders (PO)
                    @endif
                </span>
                @if($activePoCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ (request()->routeIs('purchase-orders.*') || request()->routeIs('goods-receipts.*')) ? 'bg-white text-slate-900' : 'bg-emerald-50 text-emerald-700 border border-emerald-200/80' }} font-mono shadow-xs">
                        {{ $activePoCount }}
                    </span>
                @elseif(request()->routeIs('purchase-orders.*'))
                    <span class="ms-auto w-1.5 h-1.5 rounded-full bg-emerald-400 flex-shrink-0"></span>
                @endif
            </a>
        @endif

        {{-- Faktur & 3-Way Match (Finance & Admin only) --}}
        @if(Auth::user()->hasRole(['finance', 'admin']))
            @php
                $unpaidInvoiceCount = \App\Models\Invoice::where('payment_status', 'unpaid')->count();
            @endphp
            <a href="{{ route('invoices.index') }}"
                class="group flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                {{ request()->routeIs('invoices.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/25 font-semibold' : 'text-slate-600 hover:bg-slate-100/90 hover:text-slate-900' }}">
                <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('invoices.*') ? 'text-white' : 'text-slate-400 group-hover:text-blue-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                </svg>
                <span class="flex-1">Faktur &amp; 3-Way Match</span>
                @if($unpaidInvoiceCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ request()->routeIs('invoices.*') ? 'bg-white text-slate-900' : 'bg-amber-50 text-amber-700 border border-amber-200/80' }} font-mono shadow-xs">
                        {{ $unpaidInvoiceCount }}
                    </span>
                @elseif(request()->routeIs('invoices.*'))
                    <span class="ms-auto w-1.5 h-1.5 rounded-full bg-white flex-shrink-0"></span>
                @endif
            </a>
        @endif

        <p class="px-3 pt-4 mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-400">Master &amp; Analitik</p>

        {{-- Direktori Vendor (Procurement & Admin only) --}}
        @if(Auth::user()->hasRole(['procurement', 'admin']))
            <a href="{{ route('vendors.index') }}"
                class="group flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                {{ request()->routeIs('vendors.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/25 font-semibold' : 'text-slate-600 hover:bg-slate-100/90 hover:text-slate-900' }}">
                <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('vendors.*') ? 'text-white' : 'text-slate-400 group-hover:text-indigo-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span>Direktori Vendor</span>
                @if(request()->routeIs('vendors.*'))
                    <span class="ms-auto w-1.5 h-1.5 rounded-full bg-white flex-shrink-0"></span>
                @endif
            </a>
        @endif

        {{-- Notifikasi Dalam Aplikasi (Semua Pengguna) --}}
        @php
            $unreadNotifCount = \App\Models\InAppNotification::where('user_id', Auth::id())->unread()->count();
        @endphp
        <a href="{{ route('notifications.index') }}"
            class="group flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
            {{ request()->routeIs('notifications.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/25 font-semibold' : 'text-slate-600 hover:bg-slate-100/90 hover:text-slate-900' }}">
            <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('notifications.*') ? 'text-white' : 'text-slate-400 group-hover:text-rose-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span class="flex-1">Notifikasi</span>
            @if($unreadNotifCount > 0)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500 text-white font-mono shadow-xs">
                    {{ $unreadNotifCount }}
                </span>
            @elseif(request()->routeIs('notifications.*'))
                <span class="ms-auto w-1.5 h-1.5 rounded-full bg-white flex-shrink-0"></span>
            @endif
        </a>

        {{-- Laporan & Analitik (Management, Finance, Procurement, Auditor, and Admin only) --}}
        @if(Auth::user()->hasRole(['admin', 'auditor', 'finance', 'procurement', 'manager', 'hod']))
            <a href="{{ route('reports.index') }}"
                class="group flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                {{ request()->routeIs('reports.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/25 font-semibold' : 'text-slate-600 hover:bg-slate-100/90 hover:text-slate-900' }}">
                <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('reports.*') ? 'text-white' : 'text-slate-400 group-hover:text-purple-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Laporan &amp; Analitik</span>
                @if(request()->routeIs('reports.*'))
                    <span class="ms-auto w-1.5 h-1.5 rounded-full bg-white flex-shrink-0"></span>
                @endif
            </a>
        @endif

        {{-- Audit Trail (Admin & Auditor only) --}}
        @if(Auth::user()->hasRole(['admin', 'auditor']))
            <a href="{{ route('audit-trail.index') }}"
                class="group flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                {{ request()->routeIs('audit-trail.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/25 font-semibold' : 'text-slate-600 hover:bg-slate-100/90 hover:text-slate-900' }}">
                <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('audit-trail.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-700' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                <span>Audit Trail GCG</span>
                @if(request()->routeIs('audit-trail.*'))
                    <span class="ms-auto w-1.5 h-1.5 rounded-full bg-white flex-shrink-0"></span>
                @endif
            </a>
        @endif
    </nav>

    <!-- User Profile at Bottom -->
    <div class="px-3 py-3 border-t border-slate-100 flex-shrink-0 bg-slate-50/70">
        <div x-data="{ dropup: false }" class="relative">
            <button @click="dropup = !dropup" @click.away="dropup = false"
                class="w-full flex items-center space-x-3 px-3 py-2 rounded-xl hover:bg-white hover:shadow-2xs transition-all duration-150 text-left border border-transparent hover:border-slate-200">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-slate-900 to-indigo-900 text-white flex items-center justify-center font-bold text-xs flex-shrink-0 shadow-xs">
                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-bold text-slate-800 truncate">{{ Auth::user()->name }}</div>
                    <div class="text-[10px] text-indigo-700 font-bold truncate">{{ Auth::user()->role_label }}</div>
                </div>
                <svg class="w-4 h-4 text-slate-400 flex-shrink-0 transition-transform duration-200"
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
                class="absolute bottom-full left-0 right-0 mb-2 bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden"
                style="display: none;">

                <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/50">
                    <div class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">Sesi Terautentikasi</div>
                    <div class="text-sm font-bold text-slate-900 mt-0.5">{{ Auth::user()->name }}</div>
                    <div class="text-xs text-slate-500 truncate">{{ Auth::user()->email }}</div>
                    <span class="inline-block mt-2 px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/60">
                        {{ Auth::user()->role_label }}
                    </span>
                </div>

                <a href="{{ route('profile.edit') }}"
                    class="flex items-center space-x-2.5 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>Pengaturan Akun</span>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center space-x-2.5 px-4 py-2.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 hover:text-rose-700 transition-colors border-t border-slate-100">
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
<div class="lg:hidden fixed top-0 left-0 right-0 h-14 bg-white border-b border-slate-200 flex items-center justify-between px-4 z-30">
    <button onclick="(function(){ var s=document.querySelector('aside'); var x=s._x_dataStack[0]; x.mobileOpen=!x.mobileOpen; })()"
        class="text-slate-600 hover:text-slate-900 transition p-2 rounded-lg hover:bg-slate-100">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>
    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
        <div class="w-7 h-7 rounded-lg bg-[#070D1E] border border-amber-500/70 p-0.5 flex items-center justify-center overflow-hidden">
            <img src="{{ asset('images/orderflow_emblem.jpg') }}" alt="OF" class="w-full h-full object-contain">
        </div>
        <span class="text-sm font-bold text-slate-900 tracking-tight">ORDERFLOW</span>
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
