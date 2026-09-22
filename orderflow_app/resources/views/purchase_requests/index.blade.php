<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-slate-900 tracking-tight leading-tight">
                    Pengajuan Pembelian (Purchase Request)
                </h2>
                <p class="text-sm text-slate-500 mt-0.5">Kelola dan pantau seluruh permintaan barang/jasa internal divisi Anda.</p>
            </div>
            <div>
                <a href="{{ route('purchase-requests.create') }}" class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow-sm transition duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Buat Pengajuan PR Baru
                </a>
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

            @if(session('error'))
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <!-- Summary KPI Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                <a href="{{ route('purchase-requests.index') }}" class="p-4 rounded-xl bg-white border border-slate-200 hover:border-indigo-300 transition shadow-xs">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Semua PR</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ $metrics['total'] }}</p>
                </a>
                <a href="{{ route('purchase-requests.index', ['status' => 'draft']) }}" class="p-4 rounded-xl bg-white border border-slate-200 hover:border-slate-400 transition shadow-xs">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Draf</p>
                    <p class="text-2xl font-extrabold text-slate-700 mt-1">{{ $metrics['draft'] }}</p>
                </a>
                <a href="{{ route('purchase-requests.index', ['status' => 'submitted']) }}" class="p-4 rounded-xl bg-white border border-slate-200 hover:border-sky-300 transition shadow-xs">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-sky-600">Menunggu Review</p>
                    <p class="text-2xl font-extrabold text-sky-700 mt-1">{{ $metrics['submitted'] }}</p>
                </a>
                <a href="{{ route('purchase-requests.index', ['status' => 'revision_required']) }}" class="p-4 rounded-xl bg-white border border-slate-200 hover:border-amber-300 transition shadow-xs">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Perlu Revisi</p>
                    <p class="text-2xl font-extrabold text-amber-700 mt-1">{{ $metrics['revision_required'] }}</p>
                </a>
                <a href="{{ route('purchase-requests.index', ['status' => 'approved']) }}" class="p-4 rounded-xl bg-white border border-slate-200 hover:border-emerald-300 transition shadow-xs">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Disetujui</p>
                    <p class="text-2xl font-extrabold text-emerald-700 mt-1">{{ $metrics['approved'] }}</p>
                </a>
            </div>

            <!-- Filter & Search Bar -->
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                <form method="GET" action="{{ route('purchase-requests.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                    <div class="md:col-span-6 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari nomor PR atau judul pengadaan..."
                            class="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div class="md:col-span-4">
                        <select name="status" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Semua Status Pengajuan</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draf</option>
                            <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Menunggu Approval</option>
                            <option value="revision_required" {{ request('status') === 'revision_required' ? 'selected' : '' }}>Perlu Revisi</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                            <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Proses Pengadaan</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </div>

                    <div class="md:col-span-2 flex gap-2">
                        <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs rounded-lg transition">
                            Terapkan
                        </button>
                        @if(request()->hasAny(['search', 'status']))
                            <a href="{{ route('purchase-requests.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-xs flex items-center justify-center">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- PR Table -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600 divide-y divide-slate-200">
                        <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th scope="col" class="px-5 py-3.5">Nomor PR</th>
                                <th scope="col" class="px-5 py-3.5">Judul & Justifikasi</th>
                                <th scope="col" class="px-5 py-3.5">Pemohon / Divisi</th>
                                <th scope="col" class="px-5 py-3.5">Tgl Dibutuhkan</th>
                                <th scope="col" class="px-5 py-3.5 text-right">Total Estimasi</th>
                                <th scope="col" class="px-5 py-3.5 text-center">Status</th>
                                <th scope="col" class="px-5 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-normal">
                            @forelse($purchaseRequests as $pr)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <a href="{{ route('purchase-requests.show', $pr) }}" class="font-mono font-bold text-indigo-600 hover:text-indigo-800">
                                            {{ $pr->pr_number }}
                                        </a>
                                        <span class="block text-[10px] text-slate-400 mt-0.5">
                                            {{ $pr->items->count() }} item barang
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 max-w-xs">
                                        <a href="{{ route('purchase-requests.show', $pr) }}" class="font-bold text-slate-900 hover:text-indigo-600 transition block truncate">
                                            {{ $pr->title }}
                                        </a>
                                        <p class="text-slate-500 text-[11px] truncate mt-0.5">
                                            {{ $pr->description }}
                                        </p>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="font-medium text-slate-900">{{ $pr->user?->name ?? '-' }}</div>
                                        <div class="text-[11px] text-slate-500">{{ $pr->department?->name ?? 'Lintas Divisi' }}</div>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <span class="font-medium text-slate-700">
                                            {{ $pr->required_date->format('d M Y') }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-right font-mono font-bold text-slate-900">
                                        Rp {{ number_format($pr->estimated_total, 0, ',', '.') }}
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-semibold border {{ $pr->status_badge_class }}">
                                            {{ $pr->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-right space-x-2">
                                        <a href="{{ route('purchase-requests.show', $pr) }}" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium transition">
                                            Detail
                                        </a>
                                        @if($pr->canBeEditedBy(Auth::user()))
                                            <a href="{{ route('purchase-requests.edit', $pr) }}" class="px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-medium transition">
                                                Edit
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                        <div class="max-w-xs mx-auto space-y-3">
                                            <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center mx-auto text-slate-400">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            </div>
                                            <p class="text-sm font-medium text-slate-600">Belum ada pengajuan Purchase Request</p>
                                            <p class="text-xs text-slate-400">Klik tombol di bawah untuk membuat pengajuan kebutuhan barang baru.</p>
                                            <a href="{{ route('purchase-requests.create') }}" class="inline-block px-4 py-2 bg-indigo-600 text-white font-semibold text-xs rounded-lg shadow-sm hover:bg-indigo-700 transition">
                                                Buat PR Sekarang
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($purchaseRequests->hasPages())
                    <div class="p-4 border-t border-slate-100 bg-slate-50">
                        {{ $purchaseRequests->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
