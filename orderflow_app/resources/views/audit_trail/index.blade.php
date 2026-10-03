<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                        Blockchain-Lite Integrity Ledger
                    </span>
                </div>
                <h2 class="font-bold text-2xl text-slate-900 tracking-tight mt-1">Audit Trail & Integritas Kriptografis</h2>
                <p class="text-sm text-slate-500 mt-0.5">Log audit transaksi immutable dengan pengikatan rantai hash SHA-256 anti-manipulasi.</p>
            </div>
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                Hanya Dapat Dibaca — Mutlak Tanpa Modifikasi
            </span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- SHA-256 Ledger Cryptographic Integrity Attestation Card -->
            <div class="bg-white rounded-2xl border {{ $integrityCheck['is_valid'] ? 'border-emerald-200' : 'border-rose-300' }} shadow-xs p-6 space-y-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl {{ $integrityCheck['is_valid'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }} flex items-center justify-center font-bold text-lg flex-shrink-0">
                            {{ $integrityCheck['is_valid'] ? '🛡️' : '⚠️' }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900">
                                    Sertifikat Integritas Rantai Audit (SHA-256 Chain Attestation)
                                </h3>
                                @if($integrityCheck['is_valid'])
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        ● 100% VALID & SAH
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300 animate-pulse">
                                        ● TERDETEKSI MANIPULASI
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $integrityCheck['message'] }}</p>
                        </div>
                    </div>
                    <div class="text-right text-xs">
                        <span class="text-slate-400 block text-[11px]">Terakhir Diverifikasi Otomatis:</span>
                        <span class="font-mono font-bold text-slate-700">{{ $integrityCheck['verified_at']->format('d/m/Y H:i:s') }} WIB</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Total Log Tervalidasi</span>
                        <span class="text-base font-mono font-bold text-slate-900">{{ number_format($integrityCheck['total_records'], 0, ',', '.') }} Rekod</span>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Status Rantai Hash</span>
                        <span class="text-xs font-mono font-bold {{ $integrityCheck['is_valid'] ? 'text-emerald-700' : 'text-rose-700' }}">
                            {{ strtoupper($integrityCheck['status']) }} (SEALED)
                        </span>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Genesis Block Hash</span>
                        <span class="text-[11px] font-mono text-slate-600 truncate block" title="{{ $integrityCheck['genesis_hash'] }}">
                            {{ substr($integrityCheck['genesis_hash'], 0, 16) }}...
                        </span>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Latest Head Hash</span>
                        <span class="text-[11px] font-mono text-indigo-700 truncate block" title="{{ $integrityCheck['latest_hash'] }}">
                            {{ $integrityCheck['latest_hash'] ? substr($integrityCheck['latest_hash'], 0, 16) . '...' : 'Genesis Only' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                <form method="GET" action="{{ route('audit-trail.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Aktor (User)</label>
                        <select name="user_id" id="at_user" class="w-full text-xs border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-slate-500">
                            <option value="">Semua User</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Jenis Entitas</label>
                        <select name="entity_type" id="at_entity" class="w-full text-xs border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-slate-500">
                            <option value="">Semua</option>
                            @foreach($entityTypes as $et)
                                <option value="{{ $et }}" @selected(request('entity_type') === $et)>{{ $et }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Aksi</label>
                        <select name="action" id="at_action" class="w-full text-xs border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-slate-500">
                            <option value="">Semua Aksi</option>
                            @foreach($actions as $act)
                                <option value="{{ $act }}" @selected(request('action') === $act)>{{ ucfirst(str_replace('_',' ',$act)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nomor Dokumen</label>
                        <input type="text" name="entity_label" id="at_label" value="{{ request('entity_label') }}" placeholder="PR-202609-0001"
                               class="w-full text-xs border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-slate-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Dari Tanggal</label>
                        <input type="date" name="date_from" id="at_date_from" value="{{ request('date_from') }}"
                               class="w-full text-xs border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-slate-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Sampai Tanggal</label>
                        <input type="date" name="date_to" id="at_date_to" value="{{ request('date_to') }}"
                               class="w-full text-xs border border-slate-300 rounded-xl px-3 py-2 focus:ring-1 focus:ring-slate-500">
                    </div>
                    <div class="lg:col-span-6 flex gap-3">
                        <button type="submit" id="at_filter_btn" class="px-5 py-2 bg-slate-900 hover:bg-slate-700 text-white text-xs font-semibold rounded-xl transition">Terapkan Filter</button>
                        <a href="{{ route('audit-trail.index') }}" class="px-5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">Reset</a>
                    </div>
                </form>
            </div>

            {{-- Total count --}}
            <div class="flex items-center justify-between">
                <p class="text-xs text-slate-600">
                    Menampilkan <strong>{{ $trails->count() }}</strong> dari <strong>{{ $trails->total() }}</strong> entri audit trail
                </p>
                <p class="text-[11px] text-slate-400">Log ini diikat rantai hash SHA-256 yang divalidasi secara real-time.</p>
            </div>

            {{-- Audit Trail Table --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600 divide-y divide-slate-100">
                        <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-5 py-3">Waktu</th>
                                <th class="px-5 py-3">Aktor</th>
                                <th class="px-5 py-3">Aksi</th>
                                <th class="px-5 py-3">Entitas</th>
                                <th class="px-5 py-3">Keterangan / Status</th>
                                <th class="px-5 py-3">Kriptografi Hash SHA-256</th>
                                <th class="px-5 py-3">Komentar</th>
                                <th class="px-5 py-3">IP Address</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($trails as $trail)
                                <tr class="hover:bg-slate-50 transition" id="at_row_{{ $trail->id }}">
                                    <td class="px-5 py-3 whitespace-nowrap text-slate-500">
                                        <div class="font-semibold text-slate-700">{{ $trail->created_at->format('d/m/Y') }}</div>
                                        <div class="text-[10px]">{{ $trail->created_at->format('H:i:s') }}</div>
                                    </td>
                                    <td class="px-5 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-slate-900">{{ $trail->user?->name ?? 'System' }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $trail->user?->role_label }}</div>
                                    </td>
                                    <td class="px-5 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $trail->action_badge_class }}">
                                            {{ $trail->action_label }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 whitespace-nowrap">
                                        <div class="font-mono font-bold text-slate-700 text-xs">{{ $trail->entity_label }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $trail->entity_type }}</div>
                                    </td>
                                    <td class="px-5 py-3">
                                        <div class="text-slate-800 font-medium">{{ $trail->description }}</div>
                                        @if($trail->before_state || $trail->after_state)
                                            <div class="flex items-center gap-1.5 mt-1">
                                                @if(isset($trail->before_state['status']))
                                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">{{ $trail->before_state['status'] }}</span>
                                                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                                @endif
                                                @if(isset($trail->after_state['status']))
                                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 font-semibold">{{ $trail->after_state['status'] }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 whitespace-nowrap">
                                        <div class="flex items-center gap-1 font-mono text-[10px] text-indigo-700 bg-indigo-50 border border-indigo-100 rounded px-1.5 py-0.5"
                                             title="Record Hash: {{ $trail->record_hash }} | Prev: {{ $trail->previous_hash }}">
                                            <svg class="w-3 h-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            <span>{{ $trail->record_hash ? substr($trail->record_hash, 0, 10) . '...' : 'Genesis' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 max-w-xs truncate text-slate-500 italic">
                                        {{ $trail->comment ?: '-' }}
                                    </td>
                                    <td class="px-5 py-3 whitespace-nowrap font-mono text-[11px] text-slate-400">
                                        {{ $trail->ip_address ?: '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-8 text-center text-slate-400">
                                        Tidak ada catatan audit trail yang cocok dengan filter yang dipilih.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($trails->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100">
                        {{ $trails->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
