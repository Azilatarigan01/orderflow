<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                        Tata Kelola Otorisasi Korporat
                    </span>
                </div>
                <h2 class="font-bold text-2xl text-slate-900 tracking-tight mt-1">Delegasi Wewenang Pejabat (Plt)</h2>
                <p class="text-xs text-slate-500 mt-0.5">Pelimpahan wewenang persetujuan (Acting Approver) saat pejabat berhalangan hadir atau cuti kerja.</p>
            </div>
            <div>
                <a href="{{ route('delegations.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-xs transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Delegasi Plt Baru
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Success Message -->
            @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Active Delegation Highlight Alert -->
            @if($myActiveDelegation)
                <div class="p-5 rounded-2xl bg-amber-50 border border-amber-300 text-amber-950 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-xs">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-amber-200 text-amber-900 flex items-center justify-center font-bold text-lg flex-shrink-0">
                            ⏳
                        </div>
                        <div>
                            <div class="text-xs font-bold uppercase tracking-wider text-amber-800">Status Delegasi Anda Saat Ini Aktif</div>
                            <div class="text-sm font-bold text-amber-950 mt-0.5">
                                Wewenang <span class="uppercase tracking-wider underline">{{ $myActiveDelegation->role_delegated }}</span> sedang dilimpahkan kepada <span class="text-indigo-900">{{ $myActiveDelegation->delegatee?->name }}</span>
                            </div>
                            <p class="text-xs text-amber-800 mt-1">
                                Berlaku: {{ $myActiveDelegation->start_date->format('d M Y') }} s/d {{ $myActiveDelegation->end_date->format('d M Y') }} &bull; Alasan: "{{ $myActiveDelegation->reason }}"
                            </p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('delegations.toggle', $myActiveDelegation) }}">
                        @csrf
                        <button type="submit" class="px-3.5 py-1.5 text-xs font-bold bg-amber-800 hover:bg-amber-900 text-white rounded-lg transition whitespace-nowrap">
                            Cabut Wewenang Sekarang
                        </button>
                    </form>
                </div>
            @endif

            <!-- Governance Rule Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
                <div class="flex items-center gap-2 mb-2 text-indigo-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <h3 class="text-xs font-bold uppercase tracking-wider">Aturan Tata Kelola Plt (Standard Operating Procedure)</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs text-slate-600 mt-3 pt-3 border-t border-slate-100">
                    <div class="space-y-1">
                        <span class="font-bold text-slate-800 block">1. Masa Berlaku Otomatis</span>
                        <p class="text-slate-500">Kewenangan Plt otomatis aktif pada tanggal mulai dan kedaluwarsa pada pukul 23:59 tanggal berakhir tanpa perlu intervensi manual.</p>
                    </div>
                    <div class="space-y-1">
                        <span class="font-bold text-slate-800 block">2. Jejak Audit Kriptografis</span>
                        <p class="text-slate-500">Setiap persetujuan oleh Plt dicatat transparan: <em>"Disetujui oleh [Plt] atas nama Plt [Pejabat Asli]"</em> dan diikat hash SHA-256 anti-tamper.</p>
                    </div>
                    <div class="space-y-1">
                        <span class="font-bold text-slate-800 block">3. Segregation of Duties</span>
                        <p class="text-slate-500">Plt tidak dapat menyetujui pengajuan PR miliknya sendiri (aturan anti-self approval tetap berlaku secara mutlak).</p>
                    </div>
                </div>
            </div>

            <!-- Delegations Table -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Daftar Rekam Jejak Pelimpahan Wewenang (Plt)</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Menampilkan seluruh penugasan wewenang resmi dalam organisasi.</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-slate-100 text-slate-700">
                        {{ $delegations->total() }} Data
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600 divide-y divide-slate-100">
                        <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-6 py-3.5">Pejabat Pemberi (Delegator)</th>
                                <th class="px-6 py-3.5">Pelaksana Tugas (Plt)</th>
                                <th class="px-6 py-3.5 text-center">Peran Didelegasikan</th>
                                <th class="px-6 py-3.5 text-center">Rentang Periode</th>
                                <th class="px-6 py-3.5">Alasan Pelimpahan</th>
                                <th class="px-6 py-3.5 text-center">Status</th>
                                <th class="px-6 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($delegations as $d)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-900">{{ $d->delegator?->name }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $d->delegator?->email }} &bull; {{ $d->delegator?->department?->code ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-indigo-900 flex items-center gap-1.5">
                                            <span>{{ $d->delegatee?->name }}</span>
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-mono bg-indigo-100 text-indigo-800">Plt</span>
                                        </div>
                                        <div class="text-[11px] text-slate-400">{{ $d->delegatee?->email }} &bull; {{ $d->delegatee?->department?->code ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold uppercase tracking-wider bg-slate-100 text-slate-800 border border-slate-200">
                                            {{ $d->role_delegated }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="font-mono font-semibold text-slate-800">
                                            {{ $d->start_date->format('d/m/Y') }} — {{ $d->end_date->format('d/m/Y') }}
                                        </div>
                                        @if($d->isEffectiveToday())
                                            <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                ● Sedang Berlangsung
                                            </span>
                                        @elseif($d->end_date < now())
                                            <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-500">
                                                Kedaluwarsa
                                            </span>
                                        @else
                                            <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-800">
                                                Mendatang
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-slate-700 italic">
                                        "{{ $d->reason }}"
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($d->is_active)
                                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                                Dicabut / Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <form method="POST" action="{{ route('delegations.toggle', $d) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="text-xs font-semibold px-2.5 py-1 rounded-lg border border-slate-300 hover:bg-slate-100 text-slate-700 transition">
                                                    {{ $d->is_active ? 'Cabut' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('delegations.destroy', $d) }}" class="inline" onsubmit="return confirm('Hapus riwayat delegasi ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs font-semibold px-2 py-1 text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-slate-400">
                                        Belum ada pelimpahan wewenang Plt yang terdaftar.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($delegations->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100">
                        {{ $delegations->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
