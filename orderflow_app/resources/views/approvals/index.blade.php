<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">
                    <span>Otorisasi &amp; Kepatuhan</span>
                    <span>&bull;</span>
                    <span class="text-slate-700 font-bold">Good Corporate Governance (GCG)</span>
                </div>
                <h2 class="font-extrabold text-2xl text-slate-900 tracking-tight leading-tight">
                    Antrean Persetujuan (Approval Queue)
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Daftar pengajuan belanja internal divisi yang memerlukan telaah spesifikasi dan otorisasi anggaran Anda.</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-bold {{ $pendingPrs->count() > 0 ? 'bg-amber-50 text-amber-800 border border-amber-200/80 shadow-2xs' : 'bg-emerald-50 text-emerald-800 border border-emerald-200/80' }}">
                    <span class="w-2 h-2 rounded-full {{ $pendingPrs->count() > 0 ? 'bg-amber-500 animate-pulse' : 'bg-emerald-500' }}"></span>
                    <span>{{ $pendingPrs->count() }} Pengajuan Menunggu Tindakan</span>
                </span>
                <a href="{{ route('purchase-requests.index') }}" class="px-3.5 py-1.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-bold rounded-xl shadow-2xs transition">
                    Semua Pengajuan PR
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto space-y-6">

            <!-- Flash Message -->
            @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm flex items-center justify-between shadow-2xs">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="font-semibold">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <!-- Approval Queue Table / Zero-State Card -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
                <div class="px-6 py-4.5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white">
                    <div>
                        <h4 class="font-extrabold text-sm text-slate-900">Pengajuan Dalam Antrean Anda</h4>
                        <p class="text-xs text-slate-500">Hanya menampilkan berkas PR yang telah melewati tahap sebelumnya dan saat ini menunggu otorisasi Anda</p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 font-mono border border-slate-200 self-start sm:self-auto">
                        Total: {{ $pendingPrs->count() }} Dokumen
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600 divide-y divide-slate-100">
                        <thead class="bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-6 py-3.5">Nomor PR</th>
                                <th class="px-6 py-3.5">Judul Pengadaan</th>
                                <th class="px-6 py-3.5">Pemohon / Divisi</th>
                                <th class="px-6 py-3.5">Target Kebutuhan</th>
                                <th class="px-6 py-3.5 text-right">Total Anggaran</th>
                                <th class="px-6 py-3.5 text-center">Tahapan Anda</th>
                                <th class="px-6 py-3.5 text-right">Aksi Otorisasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-normal">
                            @forelse($pendingPrs as $pr)
                                @php
                                    $activeTier = $pr->currentPendingApproval();
                                @endphp
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('purchase-requests.show', $pr) }}" class="font-mono font-bold text-slate-900 hover:text-indigo-600 hover:underline">
                                            {{ $pr->pr_number }}
                                        </a>
                                        <span class="block text-[10px] text-slate-400 mt-0.5">
                                            Diajukan {{ $pr->created_at->format('d M Y') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 max-w-xs">
                                        <a href="{{ route('purchase-requests.show', $pr) }}" class="font-bold text-slate-900 hover:text-indigo-600 block truncate">
                                            {{ $pr->title }}
                                        </a>
                                        <p class="text-slate-500 text-[11px] truncate mt-0.5">
                                            {{ $pr->description }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-semibold text-slate-900">{{ $pr->user?->name ?? '-' }}</div>
                                        <div class="text-[11px] text-slate-500">{{ $pr->department?->name ?? 'Lintas Divisi' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-medium text-slate-700">
                                            {{ $pr->required_date->format('d M Y') }}
                                        </span>
                                        <span class="block text-[10px] text-slate-500 font-medium">
                                            {{ $pr->required_date->diffForHumans() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-mono font-extrabold text-slate-900 text-sm">
                                        Rp {{ number_format($pr->estimated_total, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border bg-amber-50 text-amber-800 border-amber-200">
                                            {{ $activeTier?->tier_label ?? 'Persetujuan' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <a href="{{ route('purchase-requests.show', $pr) }}" class="inline-flex items-center px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs rounded-xl shadow-xs transition">
                                            <span>Telaah &amp; Otorisasi</span>
                                            <svg class="w-3.5 h-3.5 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-14 text-center">
                                        <div class="max-w-md mx-auto space-y-4">
                                            <!-- Success Zero-State Ring -->
                                            <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto border border-emerald-200/80 shadow-2xs">
                                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </div>

                                            <div class="space-y-1">
                                                <span class="inline-block px-3 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                                    Seluruh Berkas Selesai Ditinjau
                                                </span>
                                                <h4 class="text-base font-extrabold text-slate-900 pt-1">Antrean Persetujuan Anda Bersih</h4>
                                                <p class="text-xs text-slate-500 leading-relaxed max-w-sm mx-auto">
                                                    Saat ini tidak ada permohonan belanja internal yang tertahan atau memerlukan tindakan otorisasi dari akun wewenang Anda.
                                                </p>
                                            </div>

                                            <!-- Operational Metadata Strip -->
                                            <div class="pt-2 flex flex-wrap items-center justify-center gap-2 text-[11px] text-slate-500 font-medium">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    Standar SLA: &lt; 24 Jam
                                                </span>
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                                    Kepatuhan Audit: Terverifikasi
                                                </span>
                                            </div>

                                            <div class="pt-2 flex items-center justify-center gap-3">
                                                <a href="{{ route('purchase-requests.index') }}" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold shadow-xs transition">
                                                    Buka Semua Pengajuan PR &rarr;
                                                </a>
                                                <a href="{{ route('reports.index') }}" class="px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-xl text-xs font-bold transition shadow-2xs">
                                                    Lihat Laporan
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Riwayat Persetujuan Anda (Approved / Rejected / Revision Required) -->
            @if(isset($historyApprovals) && $historyApprovals->count() > 0)
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-slate-50/50">
                        <div>
                            <h4 class="font-bold text-sm text-slate-900">Riwayat Keputusan Otorisasi Anda</h4>
                            <p class="text-xs text-slate-500">Daftar berkas PR yang telah Anda berikan keputusan (Disetujui, Ditolak, atau Diminta Revisi)</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-white text-slate-700 font-mono border border-slate-200 self-start sm:self-auto shadow-2xs">
                            {{ $historyApprovals->count() }} Berkas Terakhir
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-600 divide-y divide-slate-100">
                            <thead class="bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <tr>
                                    <th class="px-6 py-3">Nomor PR</th>
                                    <th class="px-6 py-3">Judul Pengadaan</th>
                                    <th class="px-6 py-3">Pemohon</th>
                                    <th class="px-6 py-3">Waktu Keputusan</th>
                                    <th class="px-6 py-3 text-center">Keputusan Anda</th>
                                    <th class="px-6 py-3">Catatan Otorisasi</th>
                                    <th class="px-6 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($historyApprovals as $hist)
                                    <tr class="hover:bg-slate-50/70 transition">
                                        <td class="px-6 py-3.5 font-mono font-bold text-slate-900 whitespace-nowrap">
                                            {{ $hist->purchaseRequest?->pr_number ?? '-' }}
                                        </td>
                                        <td class="px-6 py-3.5 max-w-xs truncate font-medium text-slate-800">
                                            {{ $hist->purchaseRequest?->title ?? '-' }}
                                        </td>
                                        <td class="px-6 py-3.5 whitespace-nowrap">
                                            {{ $hist->purchaseRequest?->user?->name ?? '-' }}
                                        </td>
                                        <td class="px-6 py-3.5 whitespace-nowrap text-slate-500">
                                            {{ $hist->acted_at ? $hist->acted_at->format('d M Y H:i') : '-' }}
                                        </td>
                                        <td class="px-6 py-3.5 text-center whitespace-nowrap">
                                            @if($hist->status === 'approved')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    Disetujui
                                                </span>
                                            @elseif($hist->status === 'rejected')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    Ditolak
                                                </span>
                                            @elseif($hist->status === 'revision_required')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                    Minta Revisi
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-3.5 text-slate-500 italic max-w-xs truncate">
                                            {{ $hist->notes ?? '-' }}
                                        </td>
                                        <td class="px-6 py-3.5 text-right whitespace-nowrap">
                                            @if($hist->purchaseRequest)
                                                <a href="{{ route('purchase-requests.show', $hist->purchaseRequest) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold hover:underline">
                                                    Detail &rarr;
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
