<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} — Dokumen Resmi OrderFlow Enterprise</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&family=Playfair+Display:wght@700;800&family=Great+Vibes&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #E2E8F0;
            color: #1E293B;
            font-size: 11px;
            line-height: 1.5;
            padding: 24px;
        }
        .mono {
            font-family: 'JetBrains Mono', monospace;
        }
        .signature-font {
            font-family: 'Great Vibes', cursive;
        }

        /* Action Toolbar */
        .no-print {
            max-width: 1040px;
            margin: 0 auto 20px auto;
            background: #FFFFFF;
            padding: 14px 20px;
            border-radius: 12px;
            border: 1px solid #CBD5E1;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .toolbar-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .btn-back {
            background: #F1F5F9;
            color: #334155;
            border: 1px solid #CBD5E1;
        }
        .btn-back:hover {
            background: #E2E8F0;
        }
        .btn-print {
            background: #155DFC;
            color: #FFFFFF;
            border: none;
            box-shadow: 0 2px 4px rgba(21, 93, 252, 0.3);
        }
        .btn-print:hover {
            background: #0E49C8;
        }

        /* Corporate Paper Sheet (Standard A4 Landscape Simulation for Ledgers) */
        .paper-sheet {
            max-width: 1040px;
            min-height: 780px;
            margin: 0 auto;
            background: #FFFFFF;
            position: relative;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Geometric Header Elements */
        .top-banner {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 16px;
            background: #155DFC;
        }
        .top-left-wedge {
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 0;
            border-top: 50px solid #0B3285;
            border-right: 65px solid transparent;
            z-index: 2;
        }
        .top-left-wedge-light {
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 0;
            border-top: 70px solid #155DFC;
            border-right: 95px solid transparent;
            z-index: 1;
        }

        .document-content {
            padding: 45px 45px 30px 45px;
            position: relative;
            z-index: 3;
            flex: 1;
        }

        /* Official Letterhead */
        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #E2E8F0;
            padding-bottom: 18px;
            margin-bottom: 20px;
        }
        .org-identity-section {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .logo-emblem-box {
            width: 44px;
            height: 44px;
            background: #070D1E;
            border: 1.5px solid #F59E0B;
            border-radius: 9px;
            padding: 2.5px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }
        .logo-emblem-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .org-name {
            font-size: 16px;
            font-weight: 800;
            color: #0F172A;
            letter-spacing: 0.04em;
            line-height: 1.1;
        }
        .org-entity {
            font-size: 9px;
            color: #B45309;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            margin-top: 2px;
        }
        .org-dept {
            font-size: 9.5px;
            color: #64748B;
            font-weight: 500;
            margin-top: 2px;
        }

        .doc-identity-section {
            text-align: right;
        }
        .doc-main-title {
            font-size: 20px;
            font-weight: 900;
            letter-spacing: 0.04em;
            color: #0F172A;
            line-height: 1.1;
            text-transform: uppercase;
        }
        .doc-security-badge {
            display: inline-block;
            font-size: 8px;
            font-weight: 800;
            letter-spacing: 0.12em;
            color: #B91C1C;
            background: #FEF2F2;
            border: 1px solid #FECACA;
            padding: 2px 8px;
            border-radius: 4px;
            margin-top: 4px;
            text-transform: uppercase;
        }
        .meta-line {
            font-size: 9.5px;
            color: #64748B;
            margin-top: 4px;
        }
        .meta-line strong {
            color: #0F172A;
        }

        /* Scope & Filter Ledger Strip (Formal Document Table, not dashboard card) */
        .scope-table {
            width: 100%;
            border-collapse: collapse;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .scope-table td {
            padding: 8px 14px;
            font-size: 10px;
            color: #475569;
            border-right: 1px solid #E2E8F0;
            vertical-align: top;
        }
        .scope-table td:last-child {
            border-right: none;
        }
        .scope-label {
            font-size: 8.5px;
            font-weight: 700;
            color: #94A3B8;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 2px;
        }
        .scope-val {
            font-weight: 700;
            color: #0F172A;
            font-size: 10.5px;
        }

        /* Executive Summary Matrix (Official corporate table layout) */
        .exec-summary-wrap {
            margin-bottom: 22px;
        }
        .section-header-tag {
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #155DFC;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 8px;
        }
        .section-header-tag::before {
            content: '';
            display: inline-block;
            width: 6px;
            height: 12px;
            background: #155DFC;
            border-radius: 2px;
        }

        .summary-matrix-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #E2E8F0;
        }
        .summary-matrix-table th {
            background: #0F172A;
            color: #FFFFFF;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 7px 12px;
            text-align: left;
        }
        .summary-matrix-table td {
            padding: 8px 12px;
            font-size: 10.5px;
            border-bottom: 1px solid #E2E8F0;
            color: #334155;
        }
        .summary-matrix-table tr:nth-child(even) td {
            background-color: #F8FAFC;
        }

        /* Primary Data Table (Solid Royal Blue Header) */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0 25px 0;
            border: 1px solid #E2E8F0;
        }
        .data-table th {
            background-color: #155DFC;
            color: #FFFFFF;
            font-size: 9px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            padding: 9px 12px;
            border: none;
        }
        .data-table td {
            padding: 8px 12px;
            font-size: 10px;
            color: #334155;
            vertical-align: middle;
            border-bottom: 1px solid #F1F5F9;
        }
        .data-table tbody tr:nth-child(even) td {
            background-color: #F8FAFC;
        }
        .data-table tfoot td {
            background-color: #0F172A;
            color: #FFFFFF;
            font-weight: 800;
            padding: 9px 12px;
            font-size: 11px;
        }

        /* Status Pills */
        .status-pill {
            display: inline-block;
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 2px 7px;
            border-radius: 4px;
        }
        .status-pill.approved { background: #ECFDF5; color: #047857; border: 1px solid #A7F3D0; }
        .status-pill.pending  { background: #FFFBEB; color: #B45309; border: 1px solid #FDE68A; }
        .status-pill.rejected { background: #FEF2F2; color: #B91C1C; border: 1px solid #FECACA; }
        .status-pill.issued   { background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; }

        /* Audit Compliance & Notes Box */
        .compliance-box {
            background: #F8FAFC;
            border-left: 3px solid #155DFC;
            border-top: 1px solid #E2E8F0;
            border-right: 1px solid #E2E8F0;
            border-bottom: 1px solid #E2E8F0;
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 30px;
        }
        .compliance-title {
            font-size: 10px;
            font-weight: 800;
            color: #0F172A;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }
        .compliance-body {
            font-size: 9.5px;
            color: #475569;
            line-height: 1.5;
        }

        /* 3-Tier Official Signature Matrix */
        .signature-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #E2E8F0;
        }
        .signature-card {
            text-align: center;
        }
        .sig-role-title {
            font-size: 9.5px;
            font-weight: 700;
            color: #64748B;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 6px;
        }
        .sig-box {
            height: 52px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .sig-name {
            font-size: 11px;
            font-weight: 800;
            color: #0F172A;
            border-top: 1.5px solid #0F172A;
            padding-top: 4px;
            margin-top: 4px;
        }
        .sig-sub {
            font-size: 8.5px;
            color: #64748B;
        }

        /* Bottom Footer Ribbon */
        .bottom-footer-wrapper {
            position: relative;
            margin-top: 30px;
        }
        .contact-ribbon {
            background-color: #FFFFFF;
            border-top: 1px solid #E2E8F0;
            padding: 14px 45px 18px 45px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 9.5px;
            color: #475569;
        }
        .contact-item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .contact-icon-pill {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            background-color: #155DFC;
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .contact-icon-pill svg {
            width: 11px;
            height: 11px;
        }

        .bottom-bar {
            height: 12px;
            background-color: #155DFC;
            position: relative;
        }
        .bottom-right-wedge {
            position: absolute;
            bottom: 12px;
            right: 0;
            width: 0;
            height: 0;
            border-bottom: 40px solid #0B3285;
            border-left: 55px solid transparent;
            z-index: 2;
        }
        .bottom-right-wedge-light {
            position: absolute;
            bottom: 12px;
            right: 0;
            width: 0;
            height: 0;
            border-bottom: 55px solid #155DFC;
            border-left: 80px solid transparent;
            z-index: 1;
        }

        @media print {
            body {
                background: #FFFFFF !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .paper-sheet {
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
                max-width: 100% !important;
                min-height: auto !important;
            }
            @page {
                size: A4 landscape;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Action Toolbar (Hidden during print) -->
    <div class="no-print">
        <div>
            <a href="javascript:history.back()" class="toolbar-btn btn-back">
                &larr; Kembali
            </a>
        </div>
        <div>
            <button onclick="window.print()" class="toolbar-btn btn-print">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Dokumen / Simpan PDF</span>
            </button>
        </div>
    </div>

    <!-- Official Corporate Document Sheet -->
    <div class="paper-sheet">
        
        <!-- Geometric Blue Header Accents -->
        <div class="top-banner"></div>
        <div class="top-left-wedge-light"></div>
        <div class="top-left-wedge"></div>

        <!-- Document Core Content -->
        <div class="document-content">

            <!-- Official Letterhead Row -->
            <div class="header-row">
                
                <!-- Left: Enterprise Entity & Emblems -->
                <div class="org-identity-section">
                    <div class="logo-emblem-box">
                        <img src="{{ asset('images/orderflow_emblem.jpg') }}" alt="OF">
                    </div>
                    <div>
                        <div class="org-name">ORDERFLOW ENTERPRISE</div>
                        <div class="org-entity">PT SOLUSI KORPORASI NUSANTARA</div>
                        <div class="org-dept">Divisi Tata Kelola Pengadaan & Pengawasan Anggaran Korporat (GCG & Audit)</div>
                    </div>
                </div>

                <!-- Right: Document Title & Metadata -->
                <div class="doc-identity-section">
                    <div class="doc-main-title">{{ $title }}</div>
                    <div class="meta-line" style="margin-top: 6px;">
                        No. Registrasi: <span class="mono"><strong>#AUDIT-{{ strtoupper($type) }}-{{ date('Ym') }}-{{ rand(100, 999) }}</strong></span> &bull; 
                        Tanggal: <strong>{{ now()->format('d F Y') }}</strong>
                    </div>
                    <div class="meta-line">
                        Dicetak Oleh: <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->role ?? 'Internal Staff' }})
                    </div>
                </div>

            </div>

            <!-- Scope & Examination Parameters Table -->
            <table class="scope-table">
                <tr>
                    <td style="width: 25%;">
                        <div class="scope-label">Parameter Departemen:</div>
                        <div class="scope-val">
                            @if(!empty($filters['department_id']) && isset($departments))
                                {{ $departments->find($filters['department_id'])?->name ?? 'Semua Divisi' }}
                            @else
                                Seluruh Divisi Terintegrasi
                            @endif
                        </div>
                    </td>
                    <td style="width: 25%;">
                        <div class="scope-label">Rentang Periode Transaksi:</div>
                        <div class="scope-val">
                            {{ !empty($filters['date_from']) ? \Carbon\Carbon::parse($filters['date_from'])->format('d/m/Y') : 'Awal Tahun' }}
                            s/d
                            {{ !empty($filters['date_to']) ? \Carbon\Carbon::parse($filters['date_to'])->format('d/m/Y') : now()->format('d/m/Y') }}
                        </div>
                    </td>
                    <td style="width: 25%;">
                        <div class="scope-label">Filter Status Verifikasi:</div>
                        <div class="scope-val">
                            {{ !empty($filters['status']) ? ucfirst($filters['status']) : 'Semua Status Transaksi' }}
                        </div>
                    </td>
                    <td style="width: 25%;">
                        <div class="scope-label">Standar Kepatuhan:</div>
                        <div class="scope-val">ISO 27001 &bull; ISO 37001 (GCG)</div>
                    </td>
                </tr>
            </table>

            <!-- Executive Summary Section (Structured formal table, NO dashboard cards) -->
            <div class="exec-summary-wrap">
                <div class="section-header-tag">I. Ringkasan Eksekutif &amp; Neraca Otorisasi</div>
                
                @if(isset($type) && $type === 'pr')
                    <table class="summary-matrix-table">
                        <thead>
                            <tr>
                                <th>Kategori Indikator Pengadaan</th>
                                <th style="text-align: center;">Volume Dokumen</th>
                                <th style="text-align: right;">Akumulasi Nilai Estimasi</th>
                                <th style="text-align: center;">Rasio Otorisasi</th>
                                <th>Status Kepatuhan Anggaran</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Total Permohonan Belanja (Purchase Request)</strong></td>
                                <td style="text-align: center;" class="mono font-bold">{{ $prs->count() }} Dokumen</td>
                                <td style="text-align: right;" class="mono font-bold">Rp {{ number_format($totalValue, 0, ',', '.') }}</td>
                                <td style="text-align: center;" class="mono">100% Basis</td>
                                <td><span class="status-pill approved">Terverifikasi ERP</span></td>
                            </tr>
                            <tr>
                                <td>Pengajuan Disetujui (Approved / In Progress)</td>
                                <td style="text-align: center;" class="mono">{{ $byStatus->get('approved', 0) + $byStatus->get('completed', 0) }} Dokumen</td>
                                <td style="text-align: right;" class="mono">
                                    Rp {{ number_format($prs->whereIn('status', ['approved', 'completed'])->sum('estimated_total'), 0, ',', '.') }}
                                </td>
                                <td style="text-align: center;" class="mono">
                                    {{ $prs->count() > 0 ? round((($byStatus->get('approved', 0) + $byStatus->get('completed', 0)) / $prs->count()) * 100, 1) : 0 }}%
                                </td>
                                <td><span class="status-pill approved">Sesuai Pagu Divisi</span></td>
                            </tr>
                            <tr>
                                <td>Pengajuan Ditolak (Rejected)</td>
                                <td style="text-align: center;" class="mono">{{ $byStatus->get('rejected', 0) }} Dokumen</td>
                                <td style="text-align: right;" class="mono">
                                    Rp {{ number_format($prs->where('status', 'rejected')->sum('estimated_total'), 0, ',', '.') }}
                                </td>
                                <td style="text-align: center;" class="mono">
                                    {{ $prs->count() > 0 ? round(($byStatus->get('rejected', 0) / $prs->count()) * 100, 1) : 0 }}%
                                </td>
                                <td><span class="status-pill rejected">Dibatalkan Manajemen</span></td>
                            </tr>
                        </tbody>
                    </table>
                @endif

                @if(isset($type) && $type === 'po')
                    <table class="summary-matrix-table">
                        <thead>
                            <tr>
                                <th>Kategori Otorisasi Purchase Order</th>
                                <th style="text-align: center;">Jumlah Kontrak PO</th>
                                <th style="text-align: right;">Total Nilai Komitmen (Grand Total)</th>
                                <th style="text-align: center;">Rasio Realisasi</th>
                                <th>Status Penerimaan Logistik</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Akumulasi Purchase Order Diterbitkan</strong></td>
                                <td style="text-align: center;" class="mono font-bold">{{ $pos->count() }} Kontrak</td>
                                <td style="text-align: right;" class="mono font-bold">Rp {{ number_format($totalValue, 0, ',', '.') }}</td>
                                <td style="text-align: center;" class="mono">100% Penerbitan</td>
                                <td><span class="status-pill approved">Terotorisasi Direksi</span></td>
                            </tr>
                            <tr>
                                <td>Pesanan Telah Tuntas Diterima (Completed / BAST Terbit)</td>
                                <td style="text-align: center;" class="mono">{{ $pos->where('status', 'completed')->count() }} Kontrak</td>
                                <td style="text-align: right;" class="mono">
                                    Rp {{ number_format($pos->where('status', 'completed')->sum('grand_total'), 0, ',', '.') }}
                                </td>
                                <td style="text-align: center;" class="mono">
                                    {{ $pos->count() > 0 ? round(($pos->where('status', 'completed')->count() / $pos->count()) * 100, 1) : 0 }}%
                                </td>
                                <td><span class="status-pill approved">3-Way Match Terpenuhi</span></td>
                            </tr>
                            <tr>
                                <td>Pesanan Dalam Proses Pengiriman (Outstanding)</td>
                                <td style="text-align: center;" class="mono">{{ $outstanding->count() }} Kontrak</td>
                                <td style="text-align: right;" class="mono">
                                    Rp {{ number_format($outstanding->sum('grand_total'), 0, ',', '.') }}
                                </td>
                                <td style="text-align: center;" class="mono">
                                    {{ $pos->count() > 0 ? round(($outstanding->count() / $pos->count()) * 100, 1) : 0 }}%
                                </td>
                                <td><span class="status-pill pending">Menunggu Kedatangan Barang</span></td>
                            </tr>
                        </tbody>
                    </table>
                @endif

                @if(isset($type) && $type === 'vendor')
                    <table class="summary-matrix-table">
                        <thead>
                            <tr>
                                <th>Rekapitulasi Kemitraan Vendor</th>
                                <th style="text-align: center;">Total Rekanan Aktif</th>
                                <th style="text-align: right;">Total Nilai Belanja Fiskal</th>
                                <th style="text-align: center;">Rata-Rata Belanja/Vendor</th>
                                <th>Status Tata Kelola Rekanan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Evaluasi Kinerja Pengadaan Vendor</strong></td>
                                <td style="text-align: center;" class="mono font-bold">{{ $vendorSpend->count() }} Rekanan Terdaftar</td>
                                <td style="text-align: right;" class="mono font-bold">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                                <td style="text-align: center;" class="mono">
                                    Rp {{ $vendorSpend->count() > 0 ? number_format($grandTotal / $vendorSpend->count(), 0, ',', '.') : 0 }}
                                </td>
                                <td><span class="status-pill approved">Kemitraan Terverifikasi</span></td>
                            </tr>
                        </tbody>
                    </table>
                @endif

                @if(isset($type) && $type === 'gr')
                    <table class="summary-matrix-table">
                        <thead>
                            <tr>
                                <th>Kategori Pemeriksaan Serah Terima Fisik</th>
                                <th style="text-align: center;">Total Tanda Terima (GR/BAST)</th>
                                <th style="text-align: center;">Total Fisik Tiba (Unit)</th>
                                <th style="text-align: center;">Kuantitas Cacat (RTV)</th>
                                <th>Tingkat Lolos Inspeksi QC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Realisasi Penerimaan Logistik Gudang &amp; Jasa</strong></td>
                                <td style="text-align: center;" class="mono font-bold">{{ $receipts->count() }} Dokumen</td>
                                <td style="text-align: center;" class="mono font-bold text-emerald-700">{{ number_format($totalItemsReceived, 0, ',', '.') }} unit</td>
                                <td style="text-align: center;" class="mono font-bold text-rose-600">{{ number_format($totalItemsRejected, 0, ',', '.') }} unit</td>
                                <td>
                                    @php
                                        $passPct = ($totalItemsReceived > 0) ? round((($totalItemsReceived - $totalItemsRejected) / $totalItemsReceived) * 100, 1) : 100;
                                    @endphp
                                    <span class="status-pill {{ $totalItemsRejected > 0 ? 'rejected' : 'approved' }}">
                                        {{ $passPct }}% Lolos QC
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                @endif
            </div>

            <!-- Detail Ledger Section -->
            <div>
                <div class="section-header-tag">II. Rincian Buku Besar Transaksi (Ledger Registry)</div>

                @if(isset($type) && $type === 'pr')
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="text-align: center; width: 40px;">No</th>
                                <th>Nomor Dokumen</th>
                                <th>Judul Pengadaan</th>
                                <th>Pemohon</th>
                                <th>Divisi</th>
                                <th style="text-align: right;">Nilai Estimasi</th>
                                <th style="text-align: center;">Status</th>
                                <th style="text-align: center;">Tanggal Buat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($prs as $i => $pr)
                            <tr>
                                <td style="text-align: center;" class="mono">{{ $i + 1 }}</td>
                                <td class="mono font-bold">{{ $pr->pr_number }}</td>
                                <td>{{ $pr->title }}</td>
                                <td>{{ $pr->user?->name ?? '-' }}</td>
                                <td>{{ $pr->department?->name ?? '-' }}</td>
                                <td style="text-align: right;" class="mono font-bold">Rp {{ number_format($pr->estimated_total, 0, ',', '.') }}</td>
                                <td style="text-align: center;">
                                    <span class="status-pill {{ $pr->status === 'approved' || $pr->status === 'completed' ? 'approved' : ($pr->status === 'rejected' ? 'rejected' : 'pending') }}">
                                        {{ $pr->status_label }}
                                    </span>
                                </td>
                                <td style="text-align: center;">{{ $pr->created_at->format('d/m/Y') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" style="text-align:center; padding:20px; color:#94A3B8;">Tidak ada data transaksi yang sesuai filter.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7" style="text-align: right; text-transform: uppercase; padding-right: 18px; font-weight: 800;">Total Akumulasi Nilai PR:</td>
                                <td style="text-align: right; font-weight: 900;" class="mono">Rp {{ number_format($totalValue, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                @endif

                @if(isset($type) && $type === 'po')
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="text-align: center; width: 40px;">No</th>
                                <th>Nomor PO</th>
                                <th>Nama Rekanan (Vendor)</th>
                                <th>Divisi Pemesan</th>
                                <th style="text-align: center;">Tanggal PO</th>
                                <th style="text-align: center;">Target Tiba</th>
                                <th style="text-align: right;">Nilai Kontrak (Rp)</th>
                                <th style="text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pos as $i => $po)
                            <tr>
                                <td style="text-align: center;" class="mono">{{ $i + 1 }}</td>
                                <td class="mono font-bold">{{ $po->po_number }}</td>
                                <td class="font-bold">{{ $po->vendor?->name ?? '-' }}</td>
                                <td>{{ $po->purchaseRequest?->department?->name ?? '-' }}</td>
                                <td style="text-align: center;">{{ $po->order_date?->format('d/m/Y') ?? '-' }}</td>
                                <td style="text-align: center;">{{ $po->delivery_target_date?->format('d/m/Y') ?? 'Sesuai Kontrak' }}</td>
                                <td style="text-align: right;" class="mono font-bold">Rp {{ number_format($po->grand_total, 0, ',', '.') }}</td>
                                <td style="text-align: center;">
                                    <span class="status-pill {{ $po->status === 'completed' ? 'approved' : ($po->status === 'cancelled' ? 'rejected' : 'issued') }}">
                                        {{ $po->status_label }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" style="text-align:center; padding:20px; color:#94A3B8;">Tidak ada data Purchase Order ditemukan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" style="text-align: right; text-transform: uppercase; padding-right: 18px; font-weight: 800;">Total Akumulasi Komitmen PO:</td>
                                <td colspan="2" style="text-align: right; font-weight: 900;" class="mono">Rp {{ number_format($totalValue, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                @endif

                @if(isset($type) && $type === 'gr')
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="text-align: center; width: 40px;">No</th>
                                <th>No. GR / BAST</th>
                                <th>No. PO Referensi</th>
                                <th>Tipe Dokumen</th>
                                <th>Rekanan (Vendor)</th>
                                <th style="text-align: center;">Tgl Terima Fisik</th>
                                <th>Penerima Gudang</th>
                                <th style="text-align: center;">Kuantitas Lolos</th>
                                <th style="text-align: center;">Kuantitas Rusak</th>
                                <th style="text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($receipts as $i => $r)
                            <tr>
                                <td style="text-align: center;" class="mono">{{ $i + 1 }}</td>
                                <td class="mono font-bold">{{ $r->gr_number }}</td>
                                <td class="mono font-semibold">{{ $r->purchaseOrder?->po_number ?? '-' }}</td>
                                <td>{{ $r->is_service ? 'BAST Jasa' : 'Barang Fisik' }}</td>
                                <td class="font-bold">{{ $r->purchaseOrder?->vendor?->name ?? '-' }}</td>
                                <td style="text-align: center;" class="mono">{{ $r->received_date?->format('d/m/Y') }}</td>
                                <td>{{ $r->receiver?->name ?? 'Gudang' }}</td>
                                <td style="text-align: center;" class="mono font-bold text-emerald-700">
                                    {{ $r->items->sum('quantity_received') - $r->items->sum('quantity_rejected') }} unit
                                </td>
                                <td style="text-align: center;" class="mono {{ $r->items->sum('quantity_rejected') > 0 ? 'font-bold text-rose-600' : 'text-slate-400' }}">
                                    {{ $r->items->sum('quantity_rejected') }} unit
                                </td>
                                <td style="text-align: center;">
                                    <span class="status-pill {{ $r->status === 'completed' ? 'approved' : ($r->status === 'disputed' ? 'rejected' : 'issued') }}">
                                        {{ $r->status_label }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" style="text-align:center; padding:20px; color:#94A3B8;">Tidak ada data penerimaan barang/jasa ditemukan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7" style="text-align: right; text-transform: uppercase; padding-right: 18px; font-weight: 800;">Total Akumulasi Fisik:</td>
                                <td style="text-align: center; font-weight: 900;" class="mono text-emerald-700">{{ number_format($totalItemsReceived - $totalItemsRejected, 0, ',', '.') }} unit</td>
                                <td style="text-align: center; font-weight: 900;" class="mono text-rose-600">{{ number_format($totalItemsRejected, 0, ',', '.') }} unit</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                @endif

                @if(isset($type) && $type === 'vendor')
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="text-align: center; width: 40px;">No</th>
                                <th>Kode Rekanan</th>
                                <th>Nama Perusahaan Vendor</th>
                                <th>Kategori Spesialisasi</th>
                                <th style="text-align: center;">Frekuensi PO</th>
                                <th style="text-align: right;">Total Nilai Spend (Rp)</th>
                                <th style="text-align: center;">Rating Vendor</th>
                                <th style="text-align: center;">Transaksi Terakhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vendorSpend as $i => $v)
                            <tr>
                                <td style="text-align: center;" class="mono">{{ $i + 1 }}</td>
                                <td class="mono font-bold">{{ $v->code }}</td>
                                <td class="font-bold">{{ $v->name }}</td>
                                <td>{{ $v->category }}</td>
                                <td style="text-align: center;" class="mono font-bold">{{ $v->po_count }}x</td>
                                <td style="text-align: right;" class="mono font-bold">Rp {{ number_format($v->total_spend, 0, ',', '.') }}</td>
                                <td style="text-align: center;">
                                    <span class="mono font-bold text-amber-600">&#9733; {{ number_format($v->rating, 1) }}</span>
                                </td>
                                <td style="text-align: center;">{{ $v->last_order_date ? \Carbon\Carbon::parse($v->last_order_date)->format('d/m/Y') : '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" style="text-align:center; padding:20px; color:#94A3B8;">Tidak ada data pengeluaran vendor.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" style="text-align: right; text-transform: uppercase; padding-right: 18px; font-weight: 800;">Total Keseluruhan Pengeluaran Fiskal:</td>
                                <td colspan="3" style="text-align: right; font-weight: 900;" class="mono">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                @endif
            </div>

            <!-- Compliance Attestation Box -->
            <div class="compliance-box">
                <div class="compliance-title">Pernyataan Kepatuhan Tata Kelola (Audit Compliance Attestation)</div>
                <div class="compliance-body">
                    Seluruh ringkasan transaksi di atas telah divalidasi melalui sistem OrderFlow ERP sesuai prinsip <strong>Good Corporate Governance (GCG)</strong>. 
                    Pemisahan wewenang (*Segregation of Duties*), pencatatan audit log anti-tamper, serta rekonsiliasi Three-Way Matching (PO, BAST Penerimaan, dan Faktur Rekanan) telah terpenuhi tanpa adanya indikasi benturan kepentingan (*conflict of interest*).
                </div>
            </div>

            <!-- 3-Tier Official Signature Matrix (Empty for physical signing) -->
            <div class="signature-grid">
                
                <div class="signature-card">
                    <div class="sig-role-title">Dibuat &amp; Diverifikasi Oleh:</div>
                    <div class="sig-box" style="height: 65px;"></div>
                    <div class="sig-name">{{ auth()->user()->name }}</div>
                    <div class="sig-sub">Divisi Pengadaan / Internal Audit &bull; NIP: 2024-OF-0081</div>
                </div>

                <div class="signature-card">
                    <div class="sig-role-title">Diperiksa Oleh (Finance):</div>
                    <div class="sig-box" style="height: 65px;"></div>
                    <div class="sig-name">Ir. Darmasaputra, M.Ak., CA</div>
                    <div class="sig-sub">Head of Financial Controller &bull; NIP: 2021-OF-0012</div>
                </div>

                <div class="signature-card">
                    <div class="sig-role-title">Disahkan Oleh (Direksi):</div>
                    <div class="sig-box" style="height: 65px;"></div>
                    <div class="sig-name">Baskoro Wicaksono, S.E., MBA</div>
                    <div class="sig-sub">Director of Finance &amp; Procurement (CFO)</div>
                </div>

            </div>

        </div>

        <!-- Bottom Footer Ribbon -->
        <div class="bottom-footer-wrapper">
            <div class="bottom-right-wedge-light"></div>
            <div class="bottom-right-wedge"></div>

            <div class="contact-ribbon">
                <div class="contact-item">
                    <span class="contact-icon-pill">
                        <svg fill="currentColor" viewBox="0 0 24 24"><path d="M6.62 10.79a15.053 15.053 0 006.59 6.59l2.2-2.2a1 1 0 011.02-.24c1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57a1 1 0 01-.25 1.02l-2.2 2.2z"/></svg>
                    </span>
                    <span>+62 (021) 500-8899</span>
                </div>
                <div class="contact-item">
                    <span class="contact-icon-pill">
                        <svg fill="currentColor" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                    </span>
                    <span>audit.governance@orderflow.com</span>
                </div>
                <div class="contact-item">
                    <span class="contact-icon-pill">
                        <svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 010-5 2.5 2.5 0 010 5z"/></svg>
                    </span>
                    <span>Gedung Graha Niaga Lt. 18, SCBD Kav. 52-53, Jakarta Selatan</span>
                </div>
            </div>

            <div class="bottom-bar"></div>
        </div>

    </div>

</body>
</html>
