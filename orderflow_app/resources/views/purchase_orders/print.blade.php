<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Purchase Order - {{ $purchaseOrder->po_number }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                color: black !important;
                font-size: 11pt !important;
            }
            .page-break {
                page-break-after: always;
            }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased font-sans p-4 sm:p-8">

    <!-- Action Toolbar (Hidden during print) -->
    <div class="no-print max-w-4xl mx-auto mb-6 flex items-center justify-between bg-white p-4 rounded-xl border border-slate-300 shadow-sm">
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
            <span class="text-xs font-bold text-slate-700">Pratinjau Dokumen Resmi Purchase Order (PO)</span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition">
                &larr; Kembali ke Detail
            </a>
            <button onclick="window.print()" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold shadow-sm transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Dokumen / Simpan PDF</span>
            </button>
        </div>
    </div>

    <!-- Official Document Sheet -->
    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-12 rounded-2xl border border-slate-300 shadow-lg space-y-6">

        <!-- Header / Kop Surat Perusahaan -->
        <div class="flex items-start justify-between border-b-2 border-slate-900 pb-5">
            <div class="space-y-1">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-black text-sm">
                        OF
                    </div>
                    <span class="text-xl font-black tracking-tight text-slate-900">ORDERFLOW NUSANTARA</span>
                </div>
                <p class="text-xs text-slate-600 font-medium">Enterprise Procurement & Supply Chain Systems</p>
                <p class="text-[11px] text-slate-500">Jl. Jenderal Sudirman Kav. 52-53, SCBD, Jakarta Selatan 12190</p>
                <p class="text-[11px] text-slate-500">NPWP: 01.998.776.4-012.000 &bull; Telp: (021) 500-8899 &bull; Email: procurement@orderflow.com</p>
            </div>
            <div class="text-right space-y-1">
                <h1 class="text-xl font-extrabold text-slate-900 tracking-tight uppercase">PURCHASE ORDER</h1>
                <p class="font-mono font-extrabold text-indigo-700 text-base">{{ $purchaseOrder->po_number }}</p>
                <p class="text-xs text-slate-600">Tanggal: <strong>{{ $purchaseOrder->order_date->format('d F Y') }}</strong></p>
                <p class="text-xs text-slate-500 font-mono">Ref PR: {{ $purchaseOrder->purchaseRequest?->pr_number }}</p>
            </div>
        </div>

        <!-- Vendor & Delivery Information -->
        <div class="grid grid-cols-2 gap-8 text-xs border-b border-slate-200 pb-5">
            <div class="space-y-1">
                <p class="font-bold text-slate-400 uppercase text-[10px] tracking-wider">DIPESAN KEPADA (VENDOR):</p>
                <h3 class="text-sm font-extrabold text-slate-900">{{ $purchaseOrder->vendor?->name }}</h3>
                <p class="text-slate-600">{{ $purchaseOrder->vendor?->address }}</p>
                <p class="text-slate-600">Kontak: <strong>{{ $purchaseOrder->vendor?->contact_person }}</strong> ({{ $purchaseOrder->vendor?->phone }})</p>
                <p class="text-slate-600 font-mono">NPWP: {{ $purchaseOrder->vendor?->tax_number ?? '-' }}</p>
                <p class="text-slate-600">Email: {{ $purchaseOrder->vendor?->email }}</p>
            </div>

            <div class="space-y-1 bg-slate-50 p-4 rounded-xl border border-slate-200">
                <p class="font-bold text-slate-400 uppercase text-[10px] tracking-wider">INFORMASI PENGIRIMAN & PENAGIHAN:</p>
                <p class="text-slate-700">Alamat Pengiriman: <strong>Kantor Pusat OrderFlow Lt. 12 (Gudang Logistik)</strong></p>
                <p class="text-slate-700">Target Tiba: <strong>{{ $purchaseOrder->delivery_target_date ? $purchaseOrder->delivery_target_date->format('d F Y') : 'Sesuai Kesepakatan' }}</strong></p>
                <p class="text-slate-700">Syarat Pembayaran: <strong>{{ $purchaseOrder->payment_terms }}</strong></p>
                <p class="text-slate-700">Divisi Pemesan: <strong>{{ $purchaseOrder->purchaseRequest?->department?->name }}</strong></p>
            </div>
        </div>

        <!-- Items Table -->
        <div>
            <table class="w-full text-left text-xs border border-slate-200 border-collapse">
                <thead class="bg-slate-100 text-slate-700 uppercase font-bold text-[10px]">
                    <tr>
                        <th class="p-2.5 border border-slate-200 text-center w-8">No</th>
                        <th class="p-2.5 border border-slate-200">Nama Barang / Jasa</th>
                        <th class="p-2.5 border border-slate-200">Spesifikasi Detail</th>
                        <th class="p-2.5 border border-slate-200 text-center w-16">Jumlah</th>
                        <th class="p-2.5 border border-slate-200 text-center w-16">Satuan</th>
                        <th class="p-2.5 border border-slate-200 text-right w-36">Harga Satuan</th>
                        <th class="p-2.5 border border-slate-200 text-right w-36">Total Harga</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($purchaseOrder->items as $idx => $item)
                        <tr>
                            <td class="p-2.5 border border-slate-200 text-center font-mono">{{ $idx + 1 }}</td>
                            <td class="p-2.5 border border-slate-200 font-bold text-slate-900">{{ $item->item_name }}</td>
                            <td class="p-2.5 border border-slate-200 text-slate-600">{{ $item->specification ?? '-' }}</td>
                            <td class="p-2.5 border border-slate-200 text-center font-bold font-mono">{{ $item->quantity }}</td>
                            <td class="p-2.5 border border-slate-200 text-center">{{ $item->unit }}</td>
                            <td class="p-2.5 border border-slate-200 text-right font-mono">{{ $item->formatted_unit_price }}</td>
                            <td class="p-2.5 border border-slate-200 text-right font-mono font-bold">{{ $item->formatted_subtotal }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="font-bold">
                    <tr>
                        <td colspan="6" class="p-2 border border-slate-200 text-right uppercase text-[10px] text-slate-600">Subtotal Barang:</td>
                        <td class="p-2 border border-slate-200 text-right font-mono">{{ $purchaseOrder->formatted_subtotal }}</td>
                    </tr>
                    <tr>
                        <td colspan="6" class="p-2 border border-slate-200 text-right uppercase text-[10px] text-slate-600">Ongkos Kirim:</td>
                        <td class="p-2 border border-slate-200 text-right font-mono">{{ $purchaseOrder->formatted_shipping_fee }}</td>
                    </tr>
                    <tr>
                        <td colspan="6" class="p-2 border border-slate-200 text-right uppercase text-[10px] text-slate-600">Pajak (PPN/Tax):</td>
                        <td class="p-2 border border-slate-200 text-right font-mono">{{ $purchaseOrder->formatted_tax_amount }}</td>
                    </tr>
                    <tr class="bg-slate-100 text-sm">
                        <td colspan="6" class="p-3 border border-slate-200 text-right uppercase font-black text-slate-900">Total Akhir (Grand Total):</td>
                        <td class="p-3 border border-slate-200 text-right font-mono font-black text-slate-900">{{ $purchaseOrder->formatted_grand_total }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Notes / Instructions -->
        @if($purchaseOrder->notes)
            <div class="p-3 rounded-lg bg-slate-50 border border-slate-200 text-xs space-y-0.5">
                <span class="font-bold text-slate-800">Catatan Khusus & Instruksi Penagihan:</span>
                <p class="text-slate-600">{{ $purchaseOrder->notes }}</p>
            </div>
        @endif

        <!-- Legal 3 Signature Blocks -->
        <div class="pt-8 border-t border-slate-200">
            <div class="grid grid-cols-3 gap-6 text-center text-xs">
                <div class="space-y-16">
                    <p class="font-bold text-slate-700">Dibuat Oleh (Procurement):</p>
                    <div class="border-t border-slate-400 pt-1">
                        <p class="font-bold text-slate-900">{{ $purchaseOrder->issuer?->name ?? 'Tim Procurement' }}</p>
                        <p class="text-[10px] text-slate-500">Purchasing Department</p>
                    </div>
                </div>

                <div class="space-y-16">
                    <p class="font-bold text-slate-700">Disetujui Oleh (Manajemen):</p>
                    <div class="border-t border-slate-400 pt-1">
                        <p class="font-bold text-slate-900">Direksi / Head of Dept</p>
                        <p class="text-[10px] text-slate-500">Authorized Signatory</p>
                    </div>
                </div>

                <div class="space-y-16">
                    <p class="font-bold text-slate-700">Diterima & Dikonfirmasi Vendor:</p>
                    <div class="border-t border-slate-400 pt-1">
                        <p class="font-bold text-slate-900">{{ $purchaseOrder->vendor?->contact_person }}</p>
                        <p class="text-[10px] text-slate-500">{{ $purchaseOrder->vendor?->name }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Notice -->
        <div class="text-center pt-4 border-t border-slate-100 text-[10px] text-slate-400 font-mono">
            Dokumen ini diterbitkan secara sah melalui Sistem OrderFlow ERP. Lembar asli bertandatangan wajib dilampirkan bersama Faktur Tagihan.
        </div>

    </div>

</body>
</html>
