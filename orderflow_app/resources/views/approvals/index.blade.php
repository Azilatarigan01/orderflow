<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-slate-900 tracking-tight leading-tight">
                    Antrean Persetujuan (Approval Queue)
                </h2>
                <p class="text-sm text-slate-500 mt-0.5">Daftar pengajuan belanja internal yang membutuhkan telaah dan otorisasi Anda.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                    <span class="w-2 h-2 rounded-full bg-amber-500 mr-2 animate-pulse"></span>
                    {{ $pendingPrs->count() }} Pengajuan Menunggu Tindakan
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Message -->
            @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <!-- Approval Matrix Guidelines Banner -->
            <div class="p-5 rounded-xl bg-slate-900 text-white border border-slate-800 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <span class="text-[10px] uppercase font-bold tracking-widest text-indigo-400">Prinsip Tata Kelola Otoritas Anggaran</span>
                    <h3 class="text-sm font-bold">Matriks Otorisasi Persetujuan Bertingkat (Approval Matrix)</h3>
                    <p class="text-xs text-slate-300 max-w-2xl leading-relaxed">
                        &le; Rp5 Juta (Cukup Manager Divisi) &bull; Rp5 Jt - Rp25 Jt (Manager Divisi &rarr; Tim Finance) &bull; &gt; Rp25 Juta (+Direksi / HoD).
                    </p>
                </div>
                <div class="flex-shrink-0">
                    <span class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-300 border border-slate-700 text-xs font-mono">
                        Peran Anda: <strong class="text-white">{{ Auth::user()->role_label }}</strong>
                    </span>
                </div>
            </div>

            <!-- Approval Queue Table -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h4 class="font-bold text-sm text-slate-900">Pengajuan Dalam Antrean Anda</h4>
                        <p class="text-xs text-slate-500">Hanya menampilkan PR yang sudah disetujui tier sebelumnya dan menunggu giliran Anda</p>
                    </div>
                    <span class="text-xs font-medium text-slate-500 font-mono">
                        Total: {{ $pendingPrs->count() }} Dokumen
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600 divide-y divide-slate-100">
                        <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
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
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('purchase-requests.show', $pr) }}" class="font-mono font-bold text-indigo-600 hover:text-indigo-800">
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
                                        <span class="block text-[10px] text-indigo-600 font-medium">
                                            {{ $pr->required_date->diffForHumans() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-mono font-extrabold text-slate-900 text-sm">
                                        Rp {{ number_format($pr->estimated_total, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border bg-amber-50 text-amber-700 border-amber-200">
                                            {{ $activeTier?->tier_label ?? 'Persetujuan' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <a href="{{ route('purchase-requests.show', $pr) }}" class="inline-flex items-center px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-lg shadow-xs transition">
                                            <span>Telaah & Otorisasi</span>
                                            <svg class="w-3.5 h-3.5 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-16 text-center text-slate-400">
                                        <div class="max-w-xs mx-auto space-y-3">
                                            <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto border border-emerald-100">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            </div>
                                            <p class="text-sm font-bold text-slate-800">Antrean Bersih!</p>
                                            <p class="text-xs text-slate-500 leading-relaxed">
                                                Tidak ada pengajuan pembelian yang memerlukan tindakan persetujuan Anda saat ini.
                                            </p>
                                            <a href="{{ route('purchase-requests.index') }}" class="inline-block px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition">
                                                Buka Semua Pengajuan PR &rarr;
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
