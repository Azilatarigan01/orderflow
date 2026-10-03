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
                @if((Auth::user()->hasRole(['procurement', 'warehouse', 'admin']) || (Auth::user()->hasRole('requester') && $purchaseOrder->purchaseRequest?->user_id === Auth::id())) && in_array($purchaseOrder->status, ['issued', 'partially_received']))
                    <a href="{{ route('goods-receipts.create', ['purchase_order_id' => $purchaseOrder->id]) }}"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <span>+ Catat Penerimaan Barang / BAST</span>
                    </a>
                @endif
                @if(Auth::user()->hasRole(['procurement', 'admin']) && $purchaseOrder->status === 'partially_received')
                    <button type="button" onclick="document.getElementById('short-close-po-modal').classList.remove('hidden')"
                        class="px-3.5 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Tutup PO (Short Close)</span>
                    </button>
                @endif
                @if(Auth::user()->hasRole(['procurement', 'finance', 'admin']) && in_array($purchaseOrder->status, ['issued', 'partially_received', 'completed']))
                    <a href="{{ route('invoices.create', ['purchase_order_id' => $purchaseOrder->id]) }}"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/20 transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                        <span>+ Daftarkan Faktur Vendor</span>
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

            @if($purchaseOrder->is_short_closed)
                <!-- Short Closed PO Banner -->
                <div class="p-5 rounded-2xl bg-amber-50 border border-amber-300 text-amber-950 shadow-xs space-y-2">
                    <div class="flex items-center gap-2.5">
                        <span class="p-2 bg-amber-100 text-amber-800 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <div>
                            <h4 class="text-sm font-extrabold text-amber-950">Purchase Order Ditutup Resmi Sesuai Realisasi Fisik (Short Close)</h4>
                            <p class="text-xs text-amber-800">Ditutup pada {{ $purchaseOrder->short_closed_at ? $purchaseOrder->short_closed_at->format('d M Y H:i') : '-' }} oleh {{ $purchaseOrder->shortClosedByUser?->name ?? 'Tim Pengadaan' }} &bull; Sisa barang yang belum terkirim telah dibatalkan resmi dan tidak dapat ditagihkan lagi.</p>
                        </div>
                    </div>
                    <div class="bg-white/80 p-3.5 rounded-xl border border-amber-200 text-xs mt-2">
                        <span class="font-bold text-amber-950 block mb-0.5">Alasan Short Close:</span>
                        <p class="text-slate-800 leading-relaxed">{{ $purchaseOrder->short_close_reason }}</p>
                    </div>
                </div>
            @endif

            @if($purchaseOrder->status === 'cancelled')
                <!-- Cancelled PO Banner -->
                <div class="p-5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 shadow-xs space-y-2">
                    <div class="flex items-center gap-2.5">
                        <span class="p-2 bg-rose-100 text-rose-700 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        </span>
                        <div>
                            <h4 class="text-sm font-extrabold text-rose-900">Purchase Order Telah Dibatalkan Resmi (Wanprestasi / Default)</h4>
                            <p class="text-xs text-rose-700">Dibatalkan pada {{ $purchaseOrder->cancelled_at ? $purchaseOrder->cancelled_at->format('d M Y H:i') : '-' }} &bull; Alokasi penawaran pada pengajuan terkait telah dibuka kembali untuk pemilihan vendor pengganti.</p>
                        </div>
                    </div>
                    <div class="bg-white/80 p-3.5 rounded-xl border border-rose-200 text-xs mt-2">
                        <span class="font-bold text-rose-950 block mb-0.5">Alasan Resmi Pembatalan:</span>
                        <p class="text-slate-800 leading-relaxed">{{ $purchaseOrder->cancellation_reason }}</p>
                    </div>
                </div>
            @elseif($purchaseOrder->is_overdue)
                <!-- Overdue & Liquidated Damages Warning Banner -->
                <div class="p-6 rounded-2xl bg-amber-50/90 border-2 border-amber-300 text-slate-800 shadow-xs space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-amber-500 text-white rounded-xl shadow-xs">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h4 class="text-sm font-extrabold text-amber-950 uppercase tracking-wide">Peringatan Keterlambatan Pengiriman & Pinalti Denda</h4>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-600 text-white animate-pulse">
                                        Terlambat {{ $purchaseOrder->overdue_days }} Hari
                                    </span>
                                </div>
                                <p class="text-xs text-amber-800 mt-0.5">
                                    Target pengiriman: <strong>{{ $purchaseOrder->delivery_target_date ? $purchaseOrder->delivery_target_date->format('d M Y') : '-' }}</strong>. Vendor belum menyelesaikan pemenuhan fisik/jasa sesuai tenggat waktu kontrak.
                                </p>
                            </div>
                        </div>

                        @if(Auth::user()->hasRole(['procurement', 'admin']))
                            <div class="flex items-center gap-2 flex-wrap">
                                <form method="POST" action="{{ route('purchase-orders.default-notice', $purchaseOrder) }}" onsubmit="return confirm('Kirim Surat Teguran Resmi Wanprestasi ke vendor {{ $purchaseOrder->vendor?->name }}?')">
                                    @csrf
                                    <button type="submit" class="px-3.5 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        <span>Kirim Surat Teguran ({{ $purchaseOrder->default_notice_count }}x Terbit)</span>
                                    </button>
                                </form>

                                <button type="button" onclick="document.getElementById('cancel-po-modal').classList.remove('hidden')"
                                    class="px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Batalkan PO (Wanprestasi)</span>
                                </button>
                            </div>
                        @endif
                    </div>

                    <!-- Liquidated Damages Penalty Calculation Card -->
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 pt-3 border-t border-amber-200/80 text-xs">
                        <div class="bg-white/80 p-3 rounded-xl border border-amber-200">
                            <span class="text-slate-400 text-[11px] block">Klausul Denda Keterlambatan:</span>
                            <span class="font-bold text-slate-800">1‰ (0.1%) / hari kalender</span>
                            <span class="text-[10px] text-slate-500 block">Maksimal limit denda 5% nilai PO</span>
                        </div>
                        <div class="bg-white/80 p-3 rounded-xl border border-amber-200">
                            <span class="text-slate-400 text-[11px] block">Hari Keterlambatan:</span>
                            <span class="font-mono font-extrabold text-rose-700 text-sm">{{ $purchaseOrder->overdue_days }} Hari Kalender</span>
                            <span class="text-[10px] text-slate-500 block">Dihitung sejak target tiba lewat</span>
                        </div>
                        <div class="bg-white/80 p-3 rounded-xl border border-amber-200">
                            <span class="text-slate-400 text-[11px] block">Akumulasi Tarif Denda:</span>
                            <span class="font-mono font-extrabold text-amber-700 text-sm">{{ $purchaseOrder->penalty_percentage }}%</span>
                            <span class="text-[10px] text-slate-500 block">{{ $purchaseOrder->overdue_days >= 50 ? 'Sudah mencapai batas limit 5%' : 'Bertambah 0.1%/hari' }}</span>
                        </div>
                        <div class="bg-white/80 p-3 rounded-xl border border-amber-200">
                            <span class="text-slate-400 text-[11px] block">Estimasi Potongan Denda:</span>
                            <span class="font-mono font-extrabold text-rose-600 text-sm">{{ $purchaseOrder->formatted_estimated_penalty }}</span>
                            <span class="text-[10px] text-slate-500 block">Dipotong saat verifikasi faktur/invoice</span>
                        </div>
                    </div>

                    @if($purchaseOrder->default_notice_count > 0)
                        <div class="text-[11px] text-amber-900 bg-amber-100/70 p-2.5 rounded-lg border border-amber-200 flex items-center justify-between">
                            <span>Surat Teguran Wanprestasi terakhir diterbitkan pada: <strong>{{ $purchaseOrder->default_notice_sent_at?->format('d M Y H:i') }}</strong> (Total diterbitkan: {{ $purchaseOrder->default_notice_count }} kali).</span>
                            <span class="font-semibold text-amber-950 font-mono">Peringatan Aktif</span>
                        </div>
                    @endif
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

                <!-- 6 Highlights Grid -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 pt-3 border-t border-slate-100 text-xs">
                    <div>
                        <span class="text-slate-400 text-[11px] block">Rekanan Vendor:</span>
                        <span class="font-bold text-slate-900 truncate block">{{ $purchaseOrder->vendor?->name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[11px] block">Syarat Pembayaran:</span>
                        <span class="font-medium text-slate-900">{{ $purchaseOrder->payment_terms }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[11px] block">Target Tiba:</span>
                        <span class="font-medium text-slate-900">{{ $purchaseOrder->delivery_target_date ? $purchaseOrder->delivery_target_date->format('d M Y') : 'Sesuai Kontrak' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[11px] block">Kalkulasi PPN:</span>
                        <span class="font-bold text-indigo-700">{{ $purchaseOrder->formatted_tax_rate }} ({{ $purchaseOrder->tax_calculation_mode === 'header_subtotal' ? 'Header' : 'Line-Item' }})</span>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[11px] block">Toleransi Over-Delivery:</span>
                        <span class="font-bold text-emerald-700 font-mono">+{{ number_format($purchaseOrder->over_delivery_tolerance_percentage, 1) }}% Fisik</span>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[11px] block">Diterbitkan Oleh:</span>
                        <span class="font-medium text-slate-900 truncate block">{{ $purchaseOrder->issuer?->name ?? 'Tim Procurement' }}</span>
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
                                    <td class="px-4 py-3.5 font-bold text-slate-900">
                                        {{ $item->item_name }}
                                        @if($item->over_delivered_quantity > 0)
                                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800 ml-1.5 font-mono">
                                                Over-Delivery: +{{ $item->over_delivered_quantity }} {{ $item->unit }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-500">{{ $item->specification ?? '-' }}</td>
                                    <td class="px-4 py-3.5 text-center font-bold font-mono">{{ $item->quantity }} {{ $item->unit }}</td>
                                    <td class="px-4 py-3.5 text-center font-bold font-mono text-emerald-600">
                                        {{ $item->received_quantity }} {{ $item->unit }}
                                        @if($item->over_delivered_quantity > 0)
                                            <span class="block text-[10px] text-indigo-600 font-semibold">(+{{ $item->over_delivered_quantity }} di luar PO)</span>
                                        @endif
                                    </td>
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
                                <td colspan="7" class="px-4 py-2 text-right font-semibold text-slate-600">Pajak (PPN {{ $purchaseOrder->formatted_tax_rate }}):</td>
                                <td class="px-4 py-2 text-right font-mono text-slate-700">{{ $purchaseOrder->formatted_tax_amount }}</td>
                                <td></td>
                            </tr>
                            @if($purchaseOrder->tax_rounding_difference != 0)
                                <tr>
                                    <td colspan="7" class="px-4 py-1.5 text-right text-slate-500 text-[11px]">
                                        Toleransi Pembulatan Pajak (Rounding Adjustment):
                                    </td>
                                    <td class="px-4 py-1.5 text-right font-mono text-slate-500 text-[11px]">
                                        {{ $purchaseOrder->tax_rounding_difference > 0 ? '+' : '' }}{{ $purchaseOrder->formatted_rounding_difference }}
                                    </td>
                                    <td></td>
                                </tr>
                            @endif
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
                                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-purple-100 text-purple-800 border border-purple-200">
                                                {{ $gr->termin_name ?? 'Jasa (BAST)' }} &bull; Progres {{ $gr->formatted_progress_percentage }} (Kumulatif: {{ $gr->formatted_cumulative_progress }})
                                            </span>
                                            @if($gr->nominal_claimed > 0)
                                                <span class="text-xs font-mono font-bold text-purple-900">
                                                    {{ $gr->formatted_nominal_claimed }}
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-600">
                                        @if($gr->is_service)
                                            Diserahterimakan pada <strong>{{ $gr->received_date->format('d M Y') }}</strong> &bull; Pengawas: <strong>{{ $gr->acceptance_approver_name ?? $gr->receiver?->name }}</strong>
                                        @else
                                            Diterima pada <strong>{{ $gr->received_date->format('d M Y') }}</strong> oleh <strong>{{ $gr->receiver?->name ?? 'Tim Logistik' }}</strong> &bull; Kondisi: <em>{{ $gr->item_condition }}</em>
                                        @endif
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

            <!-- Vendor Invoices & 3-Way Matching Section -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900">Faktur Vendor &amp; Rekonsiliasi 3-Way Match</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Daftar tagihan yang diterbitkan vendor atas PO ini beserta status verifikasi 3-Way Match dan pembayaran.</p>
                    </div>
                    @if(Auth::user()->hasRole(['procurement', 'finance', 'admin']) && in_array($purchaseOrder->status, ['issued', 'partially_received', 'completed']))
                        <a href="{{ route('invoices.create', ['purchase_order_id' => $purchaseOrder->id]) }}"
                            class="px-3 py-1.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 font-bold text-xs rounded-lg transition border border-indigo-200">
                            + Daftarkan Faktur
                        </a>
                    @endif
                </div>

                @if($purchaseOrder->invoices->isEmpty())
                    <div class="p-8 text-center text-slate-400 text-xs">
                        <p>Belum ada faktur yang didaftarkan untuk Purchase Order ini.</p>
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($purchaseOrder->invoices as $inv)
                            <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50/50 transition">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold text-slate-900 text-sm">{{ $inv->invoice_number }}</span>
                                        <span class="font-mono text-xs text-slate-400">({{ $inv->internal_invoice_number }})</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $inv->matching_status_badge_class }}">
                                            {{ $inv->matching_status_label }}
                                        </span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $inv->payment_status_badge_class }}">
                                            {{ $inv->payment_status_label }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-600">
                                        Tagihan Bersih: <strong class="font-mono text-slate-900">{{ $inv->formatted_net_payable_amount }}</strong> &bull; Jatuh Tempo: <strong>{{ $inv->due_date ? $inv->due_date->format('d M Y') : '-' }}</strong>
                                        @if($inv->penalty_deduction > 0)
                                            &bull; <span class="text-rose-600 font-bold">Potongan Denda: -{{ $inv->formatted_penalty_deduction }}</span>
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <a href="{{ route('invoices.show', $inv) }}" class="px-3.5 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg shadow-2xs transition">
                                        Lihat Rincian 3-Way Match
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Delete Danger Zone (Disabled if receipts exist) -->
            @if(Auth::user()->hasRole(['procurement', 'admin']))
                <div class="p-4 rounded-xl border {{ $purchaseOrder->goodsReceipts->isNotEmpty() ? 'bg-slate-50 border-slate-200' : 'bg-rose-50 border-rose-200' }} flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-bold {{ $purchaseOrder->goodsReceipts->isNotEmpty() ? 'text-slate-600' : 'text-rose-800' }}">Hapus Purchase Order</h4>
                        <p class="text-[11px] {{ $purchaseOrder->goodsReceipts->isNotEmpty() ? 'text-slate-400' : 'text-rose-600' }}">
                            @if($purchaseOrder->goodsReceipts->isNotEmpty())
                                PO ini telah memiliki rekaman tanda terima barang/jasa dan <strong>tidak dapat dihapus</strong> demi kepatuhan audit.
                            @else
                                PO belum memiliki rekaman tanda terima dan dapat dibatalkan/dihapus jika ada kesalahan penerbitan.
                            @endif
                        </p>
                    </div>
                    @if(!$purchaseOrder->goodsReceipts->isNotEmpty())
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

    <!-- Modal Batalkan PO karena Wanprestasi -->
    <div id="cancel-po-modal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 border border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2 text-rose-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <h3 class="font-bold text-slate-900 text-sm">Batalkan PO karena Wanprestasi Vendor</h3>
                </div>
                <button type="button" onclick="document.getElementById('cancel-po-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-lg leading-none">&times;</button>
            </div>

            <p class="text-xs text-slate-600 leading-relaxed">
                Pembatalan ini akan mengubah status PO menjadi <strong class="text-rose-600">Dibatalkan</strong>, membebaskan alokasi penetapan penawaran (Quotation) pada pengajuan (PR), dan mencatat rekaman wanprestasi vendor ke riwayat audit.
            </p>

            <form method="POST" action="{{ route('purchase-orders.cancel', $purchaseOrder) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Pembatalan / Kronologi Wanprestasi *</label>
                    <textarea name="cancellation_reason" rows="4" required minlength="5"
                        placeholder="Contoh: Vendor telah melewati batas waktu pengiriman lebih dari 14 hari dan tidak memberikan kepastian respon setelah surat teguran wanprestasi diterbitkan."
                        class="w-full text-xs border border-slate-300 rounded-xl p-3 focus:ring-1 focus:ring-rose-500 focus:border-rose-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('cancel-po-modal').classList.add('hidden')"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-600/20 transition">
                        Konfirmasi Batalkan PO
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Short Close PO -->
    <div id="short-close-po-modal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 border border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2 text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <h3 class="font-bold text-slate-900 text-sm">Tutup Paksa PO Sesuai Realisasi Fisik (Short Close)</h3>
                </div>
                <button type="button" onclick="document.getElementById('short-close-po-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-lg leading-none">&times;</button>
            </div>

            <p class="text-xs text-slate-600 leading-relaxed">
                Tindakan ini digunakan ketika vendor tidak dapat mengirimkan sisa barang (misal: pabrik discontinue atau kehabisan stok permanen). Status PO akan diubah menjadi <strong class="text-emerald-700">Completed (Short Closed)</strong>, sisa kuantitas pesanan yang belum terkirim dibatalkan resmi, dan tim Keuangan hanya akan membayar atas barang yang telah benar-benar diterima di gudang/lokasi.
            </p>

            <form method="POST" action="{{ route('purchase-orders.short-close', $purchaseOrder) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Penutupan Paksa (Short Close) *</label>
                    <textarea name="short_close_reason" rows="4" required minlength="5"
                        placeholder="Contoh: Dari 5 unit laptop yang dipesan, 4 telah diterima dalam kondisi baik. Sisa 1 unit tidak dapat dikirim karena tipe ini telah discontinue oleh produsen dan vendor tidak memiliki stok pengganti yang setara. Sisa pesanan dibatalkan resmi dan PO ditutup."
                        class="w-full text-xs border border-slate-300 rounded-xl p-3 focus:ring-1 focus:ring-amber-500 focus:border-amber-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('short-close-po-modal').classList.add('hidden')"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shadow-md shadow-amber-600/20 transition">
                        Konfirmasi Tutup PO (Short Close)
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
