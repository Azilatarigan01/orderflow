<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h2 class="font-mono font-extrabold text-2xl text-slate-900 tracking-tight leading-tight">
                    {{ $goodsReceipt->gr_number }}
                </h2>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $goodsReceipt->status_badge_class }}">
                    {{ $goodsReceipt->status_label }}
                </span>
                @if($goodsReceipt->is_service)
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-800">
                        Berita Acara Serah Terima (BAST)
                    </span>
                @endif
            </div>
            <a href="{{ route('purchase-orders.show', $goodsReceipt->purchaseOrder) }}" class="px-3.5 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-semibold transition">
                Kembali ke Purchase Order
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Detail Header Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-5">
                <div class="border-b border-slate-100 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Bukti Tanda Terima Resmi</span>
                        <h3 class="text-xl font-extrabold text-slate-900 mt-0.5">
                            {{ $goodsReceipt->is_service ? 'Berita Acara Serah Terima Jasa' : 'Surat Tanda Terima Barang Fisik' }}
                        </h3>
                        <p class="text-xs text-slate-500 mt-1">
                            Referensi PO: <a href="{{ route('purchase-orders.show', $goodsReceipt->purchaseOrder) }}" class="font-mono font-bold text-indigo-600 hover:underline">{{ $goodsReceipt->purchaseOrder?->po_number }}</a>
                            &bull; Vendor: <strong>{{ $goodsReceipt->purchaseOrder?->vendor?->name }}</strong>
                        </p>
                    </div>
                    <div class="text-left sm:text-right">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tanggal Diterima</span>
                        <p class="text-base font-bold text-slate-900 mt-0.5">{{ $goodsReceipt->received_date->format('d F Y') }}</p>
                        <p class="text-[11px] text-slate-500">Penerima: {{ $goodsReceipt->receiver?->name ?? 'Tim Logistik' }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 text-[11px] block">Kondisi Fisik / Hasil Kerja:</span>
                        <span class="font-bold text-emerald-700">{{ $goodsReceipt->item_condition }}</span>
                    </div>
                    @if(!$goodsReceipt->is_service && $goodsReceipt->delivery_note_no)
                        <div>
                            <span class="text-slate-400 text-[11px] block">Nomor Surat Jalan Vendor:</span>
                            <span class="font-mono font-bold text-slate-900">{{ $goodsReceipt->delivery_note_no }}</span>
                        </div>
                    @endif
                    @if($goodsReceipt->delivery_note_doc)
                        <div>
                            <span class="text-slate-400 text-[11px] block">Berkas Surat Jalan / Foto:</span>
                            <a href="{{ asset('storage/' . $goodsReceipt->delivery_note_doc) }}" target="_blank" class="font-bold text-indigo-600 underline flex items-center gap-1 mt-0.5">
                                <span>Lihat Berkas Surat Jalan 📄</span>
                            </a>
                        </div>
                    @endif
                    @if($goodsReceipt->bast_document_path)
                        <div>
                            <span class="text-slate-400 text-[11px] block">Dokumen BAST Resmi:</span>
                            <a href="{{ asset('storage/' . $goodsReceipt->bast_document_path) }}" target="_blank" class="font-bold text-purple-700 underline flex items-center gap-1 mt-0.5">
                                <span>Buka Dokumen BAST 📄</span>
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Service BAST Details if applicable -->
                @if($goodsReceipt->is_service)
                    <div class="bg-purple-50/50 p-4 rounded-xl border border-purple-100 space-y-2 text-xs">
                        <h4 class="font-bold text-purple-950 uppercase tracking-wider text-[11px]">Rincian Serah Terima Jasa (BAST)</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-purple-900">
                            <div>
                                <span class="text-purple-600 block text-[11px]">Periode Pelaksanaan:</span>
                                <span class="font-medium">
                                    {{ $goodsReceipt->service_period_start ? $goodsReceipt->service_period_start->format('d M Y') : '-' }} s/d {{ $goodsReceipt->service_period_end ? $goodsReceipt->service_period_end->format('d M Y') : '-' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-purple-600 block text-[11px]">Penanggung Jawab Pemeriksa:</span>
                                <span class="font-bold">{{ $goodsReceipt->acceptance_approver_name ?? '-' }}</span>
                            </div>
                            <div class="sm:col-span-2">
                                <span class="text-purple-600 block text-[11px]">Hasil Pekerjaan (Deliverables):</span>
                                <p class="mt-0.5 bg-white p-2.5 rounded-lg border border-purple-200 text-slate-800">
                                    {{ $goodsReceipt->service_deliverables ?? 'Pekerjaan telah diselesaikan sesuai target dan spesifikasi.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                @if($goodsReceipt->inspection_notes)
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs">
                        <span class="font-bold text-slate-700 block mb-0.5">Catatan Pemeriksaan:</span>
                        <p class="text-slate-600">{{ $goodsReceipt->inspection_notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Items Received Table -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900">Daftar Barang & Kuantitas Diterima</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Rincian barang yang diserahterimakan pada tanda terima ini.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] font-bold border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-2.5 text-center w-10">No</th>
                                <th class="px-4 py-2.5">Item Barang / Jasa</th>
                                <th class="px-4 py-2.5 text-center">Jumlah Dipesan</th>
                                <th class="px-4 py-2.5 text-center">Jumlah Diterima Hari Ini</th>
                                <th class="px-4 py-2.5 text-center">Jumlah Rusak/Cacat</th>
                                <th class="px-4 py-2.5">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($goodsReceipt->items as $idx => $item)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-3 text-center text-slate-400 font-mono font-bold">{{ $idx + 1 }}</td>
                                    <td class="px-4 py-3 font-bold text-slate-900">
                                        {{ $item->poItem?->item_name }}
                                        <span class="block text-[11px] text-slate-400 font-normal">{{ $item->poItem?->specification ?? '-' }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center font-mono font-bold text-slate-500">
                                        {{ $item->poItem?->quantity }} {{ $item->poItem?->unit }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-mono font-extrabold text-emerald-700 text-sm">
                                        + {{ $item->quantity_received }} {{ $item->poItem?->unit }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-mono {{ $item->quantity_rejected > 0 ? 'text-rose-600 font-bold' : 'text-slate-400' }}">
                                        {{ $item->quantity_rejected }} {{ $item->poItem?->unit }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-500">
                                        {{ $item->notes ?? '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
