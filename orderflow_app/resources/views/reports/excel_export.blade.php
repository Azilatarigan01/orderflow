<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="ProgId" content="Excel.Sheet">
    <meta name="Generator" content="OrderFlow Enterprise ERP">
    <style>
        body, table {
            font-family: Calibri, 'Segoe UI', Arial, sans-serif;
            font-size: 11pt;
            color: #1E293B;
        }
        .header-title-box {
            margin-bottom: 15px;
        }
        .title-company {
            font-size: 15pt;
            font-weight: bold;
            color: #0F172A;
        }
        .title-report {
            font-size: 13pt;
            font-weight: bold;
            color: #1E40AF;
        }
        .meta-table td {
            border: none;
            padding: 3px 6px;
            font-size: 10pt;
        }
        .meta-label {
            font-weight: bold;
            color: #475569;
            width: 140px;
        }
        .meta-val {
            color: #0F172A;
            font-weight: 600;
        }
        .data-table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 15px;
        }
        .data-table th {
            background-color: #1E3A8A;
            color: #FFFFFF;
            font-weight: bold;
            border: 1px solid #0F172A;
            padding: 8px 10px;
            text-align: center;
            font-size: 10.5pt;
        }
        .data-table td {
            border: 1px solid #CBD5E1;
            padding: 6px 10px;
            vertical-align: middle;
            font-size: 10pt;
        }
        .row-even {
            background-color: #F8FAFC;
        }
        .row-odd {
            background-color: #FFFFFF;
        }
        .num {
            mso-number-format: "\#\,\#\#0";
            text-align: right;
        }
        .currency {
            mso-number-format: "\Rp\#\,\#\#0";
            text-align: right;
            font-weight: 600;
        }
        .center {
            text-align: center;
        }
        .bold {
            font-weight: bold;
        }
        .tfoot-total td {
            background-color: #E2E8F0;
            font-weight: bold;
            border-top: 2px solid #0F172A;
            border-bottom: 2px double #0F172A;
            padding: 8px 10px;
            font-size: 11pt;
        }
    </style>
</head>
<body>

    <!-- Header Letterhead -->
    <table class="meta-table">
        <tr>
            <td colspan="6" class="title-company">PT ORDERFLOW NUSANTARA — SISTEM PENGADAAN KORPORAT</td>
        </tr>
        <tr>
            <td colspan="6" class="title-report">{{ strtoupper($title) }}</td>
        </tr>
        <tr><td colspan="6" style="height: 6px;"></td></tr>
        <tr>
            <td class="meta-label">Rentang Periode:</td>
            <td class="meta-val" colspan="5">
                {{ !empty($filters['date_from']) ? \Carbon\Carbon::parse($filters['date_from'])->format('d/m/Y') : 'Awal' }} 
                s/d 
                {{ !empty($filters['date_to']) ? \Carbon\Carbon::parse($filters['date_to'])->format('d/m/Y') : 'Sekarang' }}
                ({{ ucfirst(str_replace('_', ' ', $filters['period_preset'] ?? 'Bulan Ini')) }})
            </td>
        </tr>
        @if(!empty($filters['date_basis']))
        <tr>
            <td class="meta-label">Basis Tanggal:</td>
            <td class="meta-val" colspan="5">
                {{ $filters['date_basis'] === 'order_date' ? 'Posting Date (Tanggal Penerbitan PO)' : 'Target Delivery Date (Batas Waktu Pengiriman)' }}
            </td>
        </tr>
        @endif
        <tr>
            <td class="meta-label">Tanggal Cetak:</td>
            <td class="meta-val" colspan="5">{{ now()->format('d/m/Y H:i:s') }} WIB</td>
        </tr>
        <tr>
            <td class="meta-label">Operator:</td>
            <td class="meta-val" colspan="5">{{ auth()->user()?->name ?? 'Administrator' }} ({{ strtoupper(auth()->user()?->role ?? 'Admin') }})</td>
        </tr>
    </table>

    <br>

    <!-- Data Tables By Report Type -->
    @if($type === 'pr')
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 40px;">No</th>
                    <th style="width: 140px;">Nomor PR</th>
                    <th style="width: 220px;">Judul Pengadaan</th>
                    <th style="width: 140px;">Pemohon (Requester)</th>
                    <th style="width: 130px;">Divisi / Dept</th>
                    <th style="width: 100px;">Tgl Pengajuan</th>
                    <th style="width: 100px;">Target Kebutuhan</th>
                    <th style="width: 110px;">Status</th>
                    <th style="width: 150px;">Estimasi Biaya (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($prs as $idx => $pr)
                    <tr class="{{ $idx % 2 === 0 ? 'row-even' : 'row-odd' }}">
                        <td class="center">{{ $idx + 1 }}</td>
                        <td class="bold">{{ $pr->pr_number }}</td>
                        <td>{{ $pr->title }}</td>
                        <td>{{ $pr->user?->name ?? '-' }}</td>
                        <td>{{ $pr->department?->name ?? '-' }}</td>
                        <td class="center">{{ $pr->created_at->format('d/m/Y') }}</td>
                        <td class="center">{{ $pr->required_date ? \Carbon\Carbon::parse($pr->required_date)->format('d/m/Y') : '-' }}</td>
                        <td class="center bold">{{ strtoupper($pr->status) }}</td>
                        <td class="num">{{ (float) $pr->estimated_total }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="center">Tidak ada data Purchase Request untuk filter yang dipilih.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="tfoot-total">
                    <td colspan="8" style="text-align: right;">TOTAL ESTIMASI ANGGARAN PR:</td>
                    <td class="num bold">{{ (float) $totalValue }}</td>
                </tr>
            </tfoot>
        </table>

    @elseif($type === 'po')
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 40px;">No</th>
                    <th style="width: 150px;">Nomor PO</th>
                    <th style="width: 140px;">Nomor PR Terkait</th>
                    <th style="width: 180px;">Nama Vendor Rekanan</th>
                    <th style="width: 130px;">Divisi Pemohon</th>
                    <th style="width: 100px;">Tanggal PO</th>
                    <th style="width: 100px;">Target Tiba</th>
                    <th style="width: 110px;">Status PO</th>
                    <th style="width: 130px;">Subtotal (Rp)</th>
                    <th style="width: 110px;">PPN (Rp)</th>
                    <th style="width: 140px;">Grand Total (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pos as $idx => $po)
                    <tr class="{{ $idx % 2 === 0 ? 'row-even' : 'row-odd' }}">
                        <td class="center">{{ $idx + 1 }}</td>
                        <td class="bold">{{ $po->po_number }}</td>
                        <td>{{ $po->purchaseRequest?->pr_number ?? '-' }}</td>
                        <td class="bold">{{ $po->vendor?->name ?? '-' }}</td>
                        <td>{{ $po->purchaseRequest?->department?->name ?? '-' }}</td>
                        <td class="center">{{ $po->order_date ? \Carbon\Carbon::parse($po->order_date)->format('d/m/Y') : '-' }}</td>
                        <td class="center">{{ $po->delivery_target_date ? \Carbon\Carbon::parse($po->delivery_target_date)->format('d/m/Y') : '-' }}</td>
                        <td class="center bold">{{ strtoupper($po->status) }}</td>
                        <td class="num">{{ (float) $po->subtotal }}</td>
                        <td class="num">{{ (float) $po->tax_amount }}</td>
                        <td class="num bold">{{ (float) $po->grand_total }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="center">Tidak ada data Purchase Order untuk filter yang dipilih.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="tfoot-total">
                    <td colspan="8" style="text-align: right;">TOTAL REALISASI KOMITMEN PO:</td>
                    <td class="num bold">{{ (float) $pos->sum('subtotal') }}</td>
                    <td class="num bold">{{ (float) $pos->sum('tax_amount') }}</td>
                    <td class="num bold">{{ (float) $totalValue }}</td>
                </tr>
            </tfoot>
        </table>

    @elseif($type === 'gr')
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 40px;">No</th>
                    <th style="width: 150px;">Nomor Penerimaan</th>
                    <th style="width: 140px;">Nomor PO</th>
                    <th style="width: 110px;">Tipe Dokumen</th>
                    <th style="width: 180px;">Nama Vendor</th>
                    <th style="width: 100px;">Tanggal Terima Fisik</th>
                    <th style="width: 140px;">Petugas Penerima</th>
                    <th style="width: 130px;">Surat Jalan / BAST</th>
                    <th style="width: 130px;">Kondisi Barang</th>
                    <th style="width: 110px;">Status QC</th>
                </tr>
            </thead>
            <tbody>
                @forelse($receipts as $idx => $r)
                    <tr class="{{ $idx % 2 === 0 ? 'row-even' : 'row-odd' }}">
                        <td class="center">{{ $idx + 1 }}</td>
                        <td class="bold">{{ $r->gr_number }}</td>
                        <td>{{ $r->purchaseOrder?->po_number ?? '-' }}</td>
                        <td class="center">{{ $r->is_service ? 'JASA (BAST)' : 'BARANG FISIK' }}</td>
                        <td class="bold">{{ $r->purchaseOrder?->vendor?->name ?? '-' }}</td>
                        <td class="center">{{ $r->received_date ? \Carbon\Carbon::parse($r->received_date)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $r->receiver?->name ?? 'Petugas Gudang' }}</td>
                        <td>{{ $r->delivery_note_no ?? '-' }}</td>
                        <td>{{ $r->item_condition ?? 'Baik (100% OK)' }}</td>
                        <td class="center bold">{{ strtoupper($r->status) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="center">Tidak ada data Tanda Terima Barang / BAST untuk filter yang dipilih.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="tfoot-total">
                    <td colspan="6" style="text-align: right;">TOTAL FISIK DITERIMA:</td>
                    <td colspan="4" class="bold">{{ number_format($totalItemsReceived, 0, ',', '.') }} Unit (Lolos QC) &bull; {{ number_format($totalItemsRejected, 0, ',', '.') }} Unit (Cacat/Retur)</td>
                </tr>
            </tfoot>
        </table>

    @elseif($type === 'vendor')
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 40px;">No</th>
                    <th style="width: 110px;">Kode Vendor</th>
                    <th style="width: 220px;">Nama Rekanan</th>
                    <th style="width: 150px;">Kategori Rekanan</th>
                    <th style="width: 90px;">Rating</th>
                    <th style="width: 90px;">Jumlah PO</th>
                    <th style="width: 110px;">Order Terakhir</th>
                    <th style="width: 160px;">Total Belanja Fiskal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vendorSpend as $idx => $v)
                    <tr class="{{ $idx % 2 === 0 ? 'row-even' : 'row-odd' }}">
                        <td class="center">{{ $idx + 1 }}</td>
                        <td class="center bold">{{ $v->code }}</td>
                        <td class="bold">{{ $v->name }}</td>
                        <td>{{ $v->category }}</td>
                        <td class="center">⭐ {{ number_format($v->rating, 1) }}</td>
                        <td class="center">{{ $v->po_count }} PO</td>
                        <td class="center">{{ $v->last_order_date ? \Carbon\Carbon::parse($v->last_order_date)->format('d/m/Y') : '-' }}</td>
                        <td class="num bold">{{ (float) $v->total_spend }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="center">Tidak ada data pengeluaran vendor untuk filter yang dipilih.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="tfoot-total">
                    <td colspan="7" style="text-align: right;">TOTAL PENGELUARAN KESELURUHAN REKANAN:</td>
                    <td class="num bold">{{ (float) $grandTotal }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

</body>
</html>
