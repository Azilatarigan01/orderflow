<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="font-mono font-extrabold text-2xl text-slate-900 tracking-tight leading-tight">
                        {{ $purchaseOrder->po_number }}
                    </h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $purchaseOrder->status_badge_class }}">
                        {{ $purchaseOrder->status_label }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Diterbitkan pada {{ $purchaseOrder->order_date->format('d M Y') }} &bull; Referensi PR: <a href="{{ route('purchase-requests.show', $purchaseOrder->purchaseRequest) }}" class="font-mono text-indigo-600 font-bold hover:underline">{{ $purchaseOrder->purchaseRequest?->pr_number }}</a>
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('purchase-orders.index') }}" class="px-3.5 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold shadow-xs transition">
                    Kembali ke Daftar PO
                </a>
                <a href="{{ route('purchase-orders.print', $purchaseOrder) }}" target="_blank"
                    class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-800 rounded-xl text-xs font-bold shadow-xs transition flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Cetak / PDF Resmi</span>
                </a>
                @if(Auth::user()->hasRole(['procurement', 'admin']) && in_array($purchaseOrder->status, ['issued', 'partially_received']))
                    <a href="{{ route('goods-receipts.create', ['purchase_order_id' => $purchaseOrder->id]) }}"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <span>+ Catat Penerimaan Barang / BAST</span>
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Progress & Key Facts Banner -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Progres Penerimaan Barang / Deliverables</span>
                        <div class="flex items-center gap-3 mt-1">
                            <h3 class="text-2xl font-extrabold text-slate-900 font-mono">
                                {{ $purchaseOrder->total_received_quantity }} / {{ $purchaseOrder->total_ordered_quantity }} Unit
                            </h3>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $purchaseOrder->receipt_progress_percentage == 100 ? 'bg-emerald-100 text-emerald-800' : 'bg-indigo-100 text-indigo-800' }}">
                                {{ $purchaseOrder->receipt_progress_percentage }}% Selesai
                            </span>
                        </div>
                    </div>
                    <div class="text-left sm:text-right">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Nilai Kontrak</span>
                        <p class="text-2xl font-mono font-extrabold text-indigo-700 mt-1">{{ $purchaseOrder->formatted_grand_total }}</p>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
                    <div class="bg-gradient-to-r from-indigo-500 to-emerald-500 h-3 rounded-full transition-all duration-500" style="width: {{ $purchaseOrder->receipt_progress_percentage }}%"></div>
                </div>

                <!-- 4 Highlights -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-3 border-t border-slate-100 text-xs">
                    <div>
                        <span class="text-slate-400 text-[11px] block">Rekanan Vendor:</span>
                        <span class="font-bold text-slate-900">{{ $purchaseOrder->vendor?->name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[11px] block">Syarat Pembayaran:</span>
                        <span class="font-medium text-slate-900">{{ $purchaseOrder->payment_terms }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[11px] block">Target Tiba di Kantor:</span>
                        <span class="font-medium text-slate-900">{{ $purchaseOrder->delivery_target_date ? $purchaseOrder->delivery_target_date->format('d M Y') : 'Sesuai Kesepakatan' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[11px] block">Diterbitkan Oleh:</span>
                        <span class="font-medium text-slate-900">{{ $purchaseOrder->issuer?->name ?? 'Tim Procurement' }}</span>
                    </div>
                </div>
            </div>

            <!-- Ordered Line Items & Progress Table -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900">Rincian Item Pesanan & Pemenuhan Fisik</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Pemantauan kuantitas yang dipesan vs jumlah yang sudah tiba di kantor.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] font-bold border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3 text-center w-10">No</th>
                                <th class="px-4 py-3">Nama Barang / Jasa</th>
                                <th class="px-4 py-3">Spesifikasi</th>
                                <th class="px-4 py-3 text-center">Jumlah Dipesan</th>
                                <th class="px-4 py-3 text-center">Telah Diterima</th>
                                <th class="px-4 py-3 text-center">Sisa Belum Tiba</th>
                                <th class="px-4 py-3 text-right">Harga Satuan</th>
                                <th class="px-4 py-3 text-right">Subtotal</th>
                                <th class="px-4 py-3 text-center">Status Item</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($purchaseOrder->items as $idx => $item)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-3.5 text-center text-slate-400 font-mono font-bold">{{ $idx + 1 }}</td>
                                    <td class="px-4 py-3.5 font-bold text-slate-900">{{ $item->item_name }}</td>
                                    <td class="px-4 py-3.5 text-slate-500">{{ $item->specification ?? '-' }}</td>
                                    <td class="px-4 py-3.5 text-center font-bold font-mono">{{ $item->quantity }} {{ $item->unit }}</td>
                                    <td class="px-4 py-3.5 text-center font-bold font-mono text-emerald-600">{{ $item->received_quantity }} {{ $item->unit }}</td>
                                    <td class="px-4 py-3.5 text-center font-bold font-mono {{ $item->remaining_quantity > 0 ? 'text-amber-600' : 'text-slate-400' }}">
                                        {{ $item->remaining_quantity }} {{ $item->unit }}
                                    </td>
                                    <td class="px-4 py-3.5 text-right font-mono">{{ $item->formatted_unit_price }}</td>
                                    <td class="px-4 py-3.5 text-right font-mono font-bold text-slate-900">{{ $item->formatted_subtotal }}</td>
                                    <td class="px-4 py-3.5 text-center">
                                        @if($item->received_quantity >= $item->quantity)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                ✓ Lengkap
                                            </span>
                                        @elseif($item->received_quantity > 0)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                                Sebagian
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                                Menunggu
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50 border-t border-slate-200">
                            <tr>
                                <td colspan="7" class="px-4 py-2.5 text-right font-semibold text-slate-600">Subtotal Barang:</td>
                                <td class="px-4 py-2.5 text-right font-mono font-bold text-slate-900">{{ $purchaseOrder->formatted_subtotal }}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="7" class="px-4 py-2 text-right font-semibold text-slate-600">Ongkos Kirim:</td>
                                <td class="px-4 py-2 text-right font-mono text-slate-700">{{ $purchaseOrder->formatted_shipping_fee }}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="7" class="px-4 py-2 text-right font-semibold text-slate-600">Pajak (PPN/Tax):</td>
                                <td class="px-4 py-2 text-right font-mono text-slate-700">{{ $purchaseOrder->formatted_tax_amount }}</td>
                                <td></td>
                            </tr>
                            <tr class="border-t-2 border-slate-300 bg-indigo-50/40">
                                <td colspan="7" class="px-4 py-3.5 text-right font-extrabold text-slate-900 uppercase tracking-wider">Total Nilai PO:</td>
                                <td class="px-4 py-3.5 text-right font-mono font-extrabold text-base text-indigo-700">{{ $purchaseOrder->formatted_grand_total }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Goods Receipts / BAST History Timeline -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                            <span>Riwayat Penerimaan Fisik (Goods Receipts & BAST)</span>
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-xs font-mono font-bold">
                                {{ $purchaseOrder->goodsReceipts->count() }} Rekaman
                            </span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Bukti tanda terima barang, surat jalan, dan berita acara serah terima pekerjaan.</p>
                    </div>
                </div>

                @if($purchaseOrder->goodsReceipts->isEmpty())
                    <div class="p-8 rounded-xl bg-slate-50 border border-slate-200 text-center space-y-2">
                        <svg class="w-10 h-10 text-slate-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <p class="font-bold text-slate-700 text-xs">Belum ada barang atau jasa yang diterima untuk PO ini.</p>
                        <p class="text-[11px] text-slate-400">Setelah kurir vendor mengantar barang, gunakan tombol "+ Catat Penerimaan Barang / BAST" di kanan atas.</p>
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($purchaseOrder->goodsReceipts as $gr)
                            <div class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-extrabold text-sm text-indigo-700">{{ $gr->gr_number }}</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border {{ $gr->status_badge_class }}">
                                            {{ $gr->status_label }}
                                        </span>
                                        @if($gr->is_service)
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800">
                                                Jasa (BAST)
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-600">
                                        Diterima pada <strong>{{ $gr->received_date->format('d M Y') }}</strong> oleh <strong>{{ $gr->receiver?->name ?? 'Tim Logistik' }}</strong> &bull; Kondisi: <em>{{ $gr->item_condition }}</em>
                                    </p>
                                    @if($gr->delivery_note_no)
                                        <p class="text-[11px] text-slate-500 font-mono">No Surat Jalan: {{ $gr->delivery_note_no }}</p>
                                    @endif
                                    @if($gr->inspection_notes)
                                        <p class="text-[11px] text-slate-500 bg-slate-50 p-2 rounded-lg border border-slate-200 mt-1">
                                            Catatan Inspeksi: "{{ $gr->inspection_notes }}"
                                        </p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <a href="{{ route('goods-receipts.show', $gr) }}" class="px-3 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg shadow-2xs transition">
                                        Lihat Rincian Tanda Terima
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Delete Danger Zone (Disabled if receipts exist) -->
            @if(Auth::user()->hasRole(['procurement', 'admin']))
                <div class="p-4 rounded-xl border {{ $purchaseOrder->goodsReceipts->exists() ? 'bg-slate-50 border-slate-200' : 'bg-rose-50 border-rose-200' }} flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-bold {{ $purchaseOrder->goodsReceipts->exists() ? 'text-slate-600' : 'text-rose-800' }}">Hapus Purchase Order</h4>
                        <p class="text-[11px] {{ $purchaseOrder->goodsReceipts->exists() ? 'text-slate-400' : 'text-rose-600' }}">
                            @if($purchaseOrder->goodsReceipts->exists())
                                PO ini telah memiliki rekaman tanda terima barang/jasa dan <strong>tidak dapat dihapus</strong> demi kepatuhan audit.
                            @else
                                PO belum memiliki rekaman tanda terima dan dapat dibatalkan/dihapus jika ada kesalahan penerbitan.
                            @endif
                        </p>
                    </div>
                    @if(!$purchaseOrder->goodsReceipts->exists())
                        <form method="POST" action="{{ route('purchase-orders.destroy', $purchaseOrder) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus Purchase Order ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-semibold transition">
                                Hapus PO
                            </button>
                        </form>
                    @endif
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
