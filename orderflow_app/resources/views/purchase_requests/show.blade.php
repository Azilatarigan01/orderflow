<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="font-mono font-extrabold text-2xl text-slate-900 tracking-tight leading-tight">
                        {{ $purchaseRequest->pr_number }}
                    </h2>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $purchaseRequest->status_badge_class }}">
                        {{ $purchaseRequest->status_label }}
                    </span>
                </div>
                <p class="text-sm text-slate-500 mt-0.5">Dibuat pada {{ $purchaseRequest->created_at->format('d M Y, H:i') }} WIB</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('purchase-requests.index') }}" class="px-3.5 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-semibold transition">
                    Kembali ke Daftar
                </a>
                @if($purchaseRequest->canBeEditedBy(Auth::user()))
                    <a href="{{ route('purchase-requests.edit', $purchaseRequest) }}" class="px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-lg text-xs font-semibold transition">
                        ✏️ Edit Pengajuan
                    </a>
                @endif
                @if($purchaseRequest->canBeSubmittedBy(Auth::user()))
                    <form method="POST" action="{{ route('purchase-requests.submit', $purchaseRequest) }}" onsubmit="return confirm('Apakah Anda yakin ingin mengajukan Purchase Request ini ke atasan divisi?')">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                            🚀 Ajukan Persetujuan (Submit)
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Message -->
            @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <!-- PR Header Document Card -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6 space-y-6">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 tracking-tight">{{ $purchaseRequest->title }}</h3>
                    <p class="text-xs text-slate-500 mt-1">Dokumen Resmi Pengajuan Pembelian Korporat</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-4 rounded-lg bg-slate-50 border border-slate-200 text-xs">
                    <div>
                        <span class="block text-slate-500 font-semibold uppercase tracking-wider text-[10px]">Pemohon (Requester)</span>
                        <p class="font-bold text-slate-900 text-sm mt-0.5">{{ $purchaseRequest->user?->name ?? '-' }}</p>
                        <p class="text-slate-500">{{ $purchaseRequest->user?->email ?? '-' }}</p>
                    </div>

                    <div>
                        <span class="block text-slate-500 font-semibold uppercase tracking-wider text-[10px]">Divisi / Departemen</span>
                        <p class="font-bold text-slate-900 text-sm mt-0.5">{{ $purchaseRequest->department?->name ?? 'Lintas Divisi' }}</p>
                        <p class="text-slate-500">Kode Divisi: <span class="font-mono font-bold">{{ $purchaseRequest->department?->code ?? '-' }}</span></p>
                    </div>

                    <div>
                        <span class="block text-slate-500 font-semibold uppercase tracking-wider text-[10px]">Target Kedatangan Barang</span>
                        <p class="font-bold text-indigo-700 text-sm mt-0.5">{{ $purchaseRequest->required_date->format('d F Y') }}</p>
                        <p class="text-slate-500">({{ $purchaseRequest->required_date->diffForHumans() }})</p>
                    </div>
                </div>

                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Alasan & Justifikasi Kebutuhan Bisnis:</h4>
                    <div class="p-3.5 bg-slate-50/80 rounded-lg border border-slate-200 text-xs text-slate-700 whitespace-pre-line leading-relaxed">
                        {{ $purchaseRequest->description }}
                    </div>
                </div>
            </div>

            <!-- Items Table Card -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900">
                        Rincian Barang / Jasa ({{ $purchaseRequest->items->count() }} Item)
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600 divide-y divide-slate-200">
                        <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-4 py-3 text-center w-12">#</th>
                                <th class="px-4 py-3">Nama Barang / Jasa</th>
                                <th class="px-4 py-3">Spesifikasi Teknis</th>
                                <th class="px-4 py-3 text-center w-24">Jumlah</th>
                                <th class="px-4 py-3 text-center w-24">Satuan</th>
                                <th class="px-4 py-3 text-right w-36">Harga Satuan</th>
                                <th class="px-4 py-3 text-right w-40">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-normal">
                            @foreach($purchaseRequest->items as $index => $item)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-3 text-center text-slate-400 font-mono">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 font-semibold text-slate-900">{{ $item->item_name }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $item->specification ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center font-bold text-slate-800">{{ $item->quantity }}</td>
                                    <td class="px-4 py-3 text-center text-slate-600">{{ $item->unit }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-slate-700">Rp {{ number_format($item->estimated_unit_price, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right font-mono font-bold text-slate-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50 border-t-2 border-slate-200">
                            <tr>
                                <td colspan="6" class="px-4 py-3.5 text-right font-bold text-slate-700 uppercase tracking-wider text-xs">
                                    Total Estimasi Anggaran:
                                </td>
                                <td class="px-4 py-3.5 text-right font-mono font-extrabold text-lg text-indigo-700">
                                    Rp {{ number_format($purchaseRequest->estimated_total, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Attachments & Documents -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6 space-y-3">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-3">
                    Berkas Lampiran Pendukung
                </h3>

                @if($purchaseRequest->attachments->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($purchaseRequest->attachments as $att)
                            <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg flex items-center justify-between">
                                <div class="flex items-center gap-3 overflow-hidden">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                    </div>
                                    <div class="truncate">
                                        <p class="text-xs font-semibold text-slate-900 truncate">{{ $att->file_name }}</p>
                                        <p class="text-[10px] text-slate-400">{{ $att->formatted_size }} &bull; Diunggah oleh {{ $att->uploader?->name ?? 'Pengguna' }}</p>
                                    </div>
                                </div>
                                <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank" class="px-2.5 py-1 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 text-xs font-medium rounded-md flex-shrink-0 ml-2">
                                    Buka File
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-400 italic">Tidak ada berkas lampiran pendukung yang diunggah.</p>
                @endif
            </div>

            <!-- Status History Audit Trail (Timeline) -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6 space-y-4">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-3">
                    Linimasa Jejak Audit & Riwayat Status (Status History)
                </h3>

                <div class="flow-root">
                    <ul class="-mb-8">
                        @foreach($purchaseRequest->histories as $index => $history)
                            <li>
                                <div class="relative pb-8">
                                    @if(!$loop->last)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-slate-200" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-slate-900 text-white flex items-center justify-center ring-8 ring-white text-xs font-bold font-mono">
                                                {{ $purchaseRequest->histories->count() - $index }}
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-xs font-bold text-slate-900">
                                                    Status: <span class="text-indigo-600">{{ $history->to_status_label }}</span>
                                                    @if($history->from_status)
                                                        <span class="text-slate-400 font-normal">(sebelumnya: {{ $history->from_status }})</span>
                                                    @endif
                                                </p>
                                                @if($history->notes)
                                                    <p class="text-xs text-slate-600 mt-1 bg-slate-50 p-2 rounded-lg border border-slate-200">
                                                        {{ $history->notes }}
                                                    </p>
                                                @endif
                                                <p class="text-[11px] text-slate-400 mt-1">
                                                    Oleh: <strong class="text-slate-600">{{ $history->user?->name ?? 'Sistem' }}</strong> ({{ $history->user?->role_label ?? '-' }})
                                                </p>
                                            </div>
                                            <div class="text-right text-[11px] whitespace-nowrap text-slate-400">
                                                {{ $history->created_at->format('d M Y, H:i') }} WIB
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Danger Zone / Delete Draft Option -->
            @if($purchaseRequest->status === 'draft' && ($purchaseRequest->user_id === Auth::id() || Auth::user()->hasRole('admin')))
                <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-bold text-rose-800">Hapus Draf Pengajuan</h4>
                        <p class="text-[11px] text-rose-600">Draf ini belum diserahkan ke atasan dan dapat dihapus secara permanen.</p>
                    </div>
                    <form method="POST" action="{{ route('purchase-requests.destroy', $purchaseRequest) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus draf PR ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-semibold transition">
                            Hapus Draf
                        </button>
                    </form>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
