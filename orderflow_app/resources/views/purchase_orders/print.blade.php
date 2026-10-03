<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Purchase Order - {{ $purchaseOrder->po_number }} — OrderFlow Enterprise</title>
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
        .font-serif-prestige {
            font-family: 'Playfair Display', Georgia, serif;
        }
        .signature-font {
            font-family: 'Great Vibes', cursive;
        }

        /* Action Toolbar */
        .no-print {
            max-width: 840px;
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

        /* Corporate Paper Sheet (Standard A4 Simulation) */
        .paper-sheet {
            max-width: 840px;
            min-height: 1100px;
            margin: 0 auto;
            background: #FFFFFF;
            position: relative;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Geometric Header Elements (Matching reference template) */
        .top-banner {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 18px;
            background: #155DFC;
        }
        .top-left-wedge {
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 0;
            border-top: 60px solid #0B3285;
            border-right: 75px solid transparent;
            z-index: 2;
        }
        .top-left-wedge-light {
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 0;
            border-top: 85px solid #155DFC;
            border-right: 110px solid transparent;
            z-index: 1;
        }

        .document-content {
            padding: 55px 45px 35px 45px;
            position: relative;
            z-index: 3;
            flex: 1;
        }

        /* Letterhead layout */
        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 28px;
        }
        .bill-to-section {
            max-width: 320px;
        }
        .bill-to-label {
            font-size: 11px;
            font-weight: 700;
            color: #64748B;
            margin-bottom: 4px;
        }
        .bill-to-name {
            font-size: 16px;
            font-weight: 800;
            color: #0F172A;
            line-height: 1.2;
            margin-bottom: 3px;
        }
        .bill-to-sub {
            font-size: 11px;
            color: #334155;
            font-weight: 600;
            margin-bottom: 2px;
        }
        .bill-to-detail {
            font-size: 10.5px;
            color: #64748B;
            line-height: 1.4;
        }

        .brand-doc-section {
            text-align: right;
        }
        .company-brand-row {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            margin-bottom: 12px;
        }
        .logo-emblem-box {
            width: 38px;
            height: 38px;
            background: #070D1E;
            border: 1.5px solid #F59E0B;
            border-radius: 8px;
            padding: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .logo-emblem-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .company-name-text {
            font-size: 14px;
            font-weight: 800;
            color: #0F172A;
            letter-spacing: 0.04em;
            line-height: 1;
        }
        .company-tagline-text {
            font-size: 8px;
            color: #B45309;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            margin-top: 3px;
        }

        .doc-title-text {
            font-size: 26px;
            font-weight: 900;
            letter-spacing: 0.05em;
            color: #0F172A;
            line-height: 1;
            margin-bottom: 8px;
        }
        .doc-meta-table {
            margin-left: auto;
            font-size: 10px;
            color: #334155;
        }
        .doc-meta-table td {
            padding: 1.5px 0 1.5px 12px;
        }
        .doc-meta-label {
            color: #64748B;
            font-weight: 600;
        }
        .doc-meta-value {
            font-weight: 700;
            color: #0F172A;
            text-align: right;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0 25px 0;
        }
        .items-table th {
            background-color: #155DFC;
            color: #FFFFFF;
            font-size: 9.5px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            padding: 10px 14px;
            border: none;
        }
        .items-table th:first-child {
            border-top-left-radius: 6px;
            border-bottom-left-radius: 6px;
            text-align: center;
            width: 44px;
        }
        .items-table th:last-child {
            border-top-right-radius: 6px;
            border-bottom-right-radius: 6px;
            text-align: right;
            width: 140px;
        }
        .items-table td {
            padding: 12px 14px;
            font-size: 10.5px;
            color: #334155;
            vertical-align: middle;
            border-bottom: 1px solid #F1F5F9;
        }
        .items-table tbody tr:nth-child(even) td {
            background-color: #F8FAFC;
        }
        .item-main-title {
            font-weight: 700;
            color: #0F172A;
            font-size: 11px;
            margin-bottom: 2px;
        }
        .item-sub-spec {
            font-size: 9.5px;
            color: #64748B;
            line-height: 1.3;
        }

        /* Summary & Signatures Row */
        .summary-signature-grid {
            display: grid;
            grid-template-columns: 1.25fr 1fr;
            gap: 30px;
            align-items: flex-start;
            margin-top: 10px;
        }

        .policy-section {
            padding-right: 15px;
        }
        .policy-heading {
            font-size: 13px;
            font-weight: 800;
            color: #0F172A;
            margin-bottom: 10px;
        }
        .payment-info-box {
            font-size: 10px;
            color: #334155;
            margin-bottom: 16px;
            line-height: 1.6;
        }
        .payment-info-box strong {
            color: #0F172A;
        }
        .terms-box {
            font-size: 9.5px;
            color: #64748B;
            line-height: 1.45;
        }
        .terms-title {
            font-weight: 700;
            color: #0F172A;
            margin-bottom: 3px;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.05em;
        }

        /* Totals Calculation Column */
        .totals-section {
            margin-left: auto;
            width: 100%;
        }
        .calc-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 10.5px;
            color: #64748B;
        }
        .calc-row strong {
            color: #0F172A;
            font-weight: 700;
        }
        .total-box-card {
            background-color: #155DFC;
            color: #FFFFFF;
            border-radius: 6px;
            padding: 10px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
            box-shadow: 0 4px 6px -1px rgba(21, 93, 252, 0.25);
        }
        .total-box-label {
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.04em;
        }
        .total-box-value {
            font-size: 15px;
            font-weight: 800;
        }

        /* Corporate 3-Column Official Signature Matrix */
        .po-signatures-block {
            margin-top: 30px;
            padding-top: 18px;
            border-top: 1px dashed #CBD5E1;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        .po-sig-column {
            text-align: center;
        }
        .po-sig-header {
            font-size: 9.5px;
            font-weight: 800;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }
        .po-sig-blank-box {
            height: 70px;
        }
        .po-sig-name {
            font-size: 11px;
            font-weight: 700;
            color: #0F172A;
            border-bottom: 1.5px solid #0F172A;
            display: inline-block;
            min-width: 170px;
            padding-bottom: 2px;
            margin-bottom: 4px;
        }
        .po-sig-role {
            font-size: 9.5px;
            font-weight: 600;
            color: #334155;
        }
        .po-sig-date {
            font-size: 8.5px;
            color: #64748B;
            margin-top: 2px;
        }

        /* Bottom Footer Ribbon */
        .bottom-footer-wrapper {
            position: relative;
            margin-top: 40px;
        }
        .contact-ribbon {
            background-color: #FFFFFF;
            border-top: 1px solid #E2E8F0;
            padding: 16px 45px 22px 45px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 10px;
            color: #475569;
        }
        .contact-item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .contact-icon-pill {
            width: 22px;
            height: 22px;
            border-radius: 5px;
            background-color: #155DFC;
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .contact-icon-pill svg {
            width: 12px;
            height: 12px;
        }

        /* Bottom Wedge & Bar */
        .bottom-bar {
            height: 14px;
            background-color: #155DFC;
            position: relative;
        }
        .bottom-right-wedge {
            position: absolute;
            bottom: 14px;
            right: 0;
            width: 0;
            height: 0;
            border-bottom: 50px solid #0B3285;
            border-left: 70px solid transparent;
            z-index: 2;
        }
        .bottom-right-wedge-light {
            position: absolute;
            bottom: 14px;
            right: 0;
            width: 0;
            height: 0;
            border-bottom: 70px solid #155DFC;
            border-left: 100px solid transparent;
            z-index: 1;
        }

        /* Print Media Styles */
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
                size: A4 portrait;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Action Toolbar (Hidden on print) -->
    <div class="no-print">
        <div style="display:flex; align-items:center; gap:8px;">
            <span style="width:10px; height:10px; border-radius:50%; background:#10B981; display:inline-block;"></span>
            <span style="font-size:12px; font-weight:700; color:#1E293B;">Purchase Order Resmi &bull; #{{ $purchaseOrder->po_number }}</span>
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
            <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="toolbar-btn btn-back">
                &larr; Kembali ke Detail
            </a>
            <button onclick="window.print()" class="toolbar-btn btn-print">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Lembar Resmi / Simpan PDF</span>
            </button>
        </div>
    </div>

    <!-- Official Document Paper -->
    <div class="paper-sheet">
        
        <!-- Top Geometric Accent Elements -->
        <div class="top-banner"></div>
        <div class="top-left-wedge-light"></div>
        <div class="top-left-wedge"></div>

        <!-- Main Document Body -->
        <div class="document-content">

            <!-- Letterhead Row -->
            <div class="header-row">
                
                <!-- Left: Bill To / Vendor Destination -->
                <div class="bill-to-section">
                    <div class="bill-to-label">DIPESAN KEPADA (VENDOR):</div>
                    <div class="bill-to-name">{{ $purchaseOrder->vendor?->name ?? 'Rekanan Korporat' }}</div>
                    <div class="bill-to-sub">U.P: {{ $purchaseOrder->vendor?->contact_person ?? 'Bagian Penjualan & Otorisasi' }}</div>
                    <div class="bill-to-detail">
                        {{ $purchaseOrder->vendor?->address ?? 'Alamat Terdaftar Rekanan' }}<br>
                        Telp: {{ $purchaseOrder->vendor?->phone ?? '-' }} &bull; Email: {{ $purchaseOrder->vendor?->email ?? '-' }}<br>
                        NPWP: <span class="mono">{{ $purchaseOrder->vendor?->tax_number ?? '-' }}</span>
                    </div>
                </div>

                <!-- Right: Company Logo & Document Identity -->
                <div class="brand-doc-section">
                    <div class="company-brand-row">
                        <div>
                            <div class="company-name-text">ORDERFLOW</div>
                            <div class="company-tagline-text">PT SOLUSI KORPORASI NUSANTARA</div>
                        </div>
                        <div class="logo-emblem-box">
                            <img src="{{ asset('images/orderflow_emblem.jpg') }}" alt="OF">
                        </div>
                    </div>

                    <div class="doc-title-text">PURCHASE ORDER</div>

                    <table class="doc-meta-table">
                        <tr>
                            <td class="doc-meta-label">Nomor Dokumen:</td>
                            <td class="doc-meta-value mono">#{{ $purchaseOrder->po_number }}</td>
                        </tr>
                        <tr>
                            <td class="doc-meta-label">Tanggal Terbit:</td>
                            <td class="doc-meta-value">{{ $purchaseOrder->order_date ? $purchaseOrder->order_date->format('d F Y') : now()->format('d F Y') }}</td>
                        </tr>
                        <tr>
                            <td class="doc-meta-label">Target Penerimaan:</td>
                            <td class="doc-meta-value">{{ $purchaseOrder->delivery_target_date ? $purchaseOrder->delivery_target_date->format('d F Y') : 'Sesuai Kesepakatan' }}</td>
                        </tr>
                        @if($purchaseOrder->purchaseRequest)
                        <tr>
                            <td class="doc-meta-label">Ref. PR Pemohon:</td>
                            <td class="doc-meta-value mono">{{ $purchaseOrder->purchaseRequest->pr_number }}</td>
                        </tr>
                        @endif
                    </table>
                </div>

            </div>

            <!-- Items Table (Solid Royal Blue Header + Alternating Zebra Rows) -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="text-align: center;">NO.</th>
                        <th style="text-align: left;">DESKRIPSI BARANG / LAYANAN JASA</th>
                        <th style="text-align: right;">HARGA SATUAN</th>
                        <th style="text-align: center;">QTY</th>
                        <th style="text-align: right;">TOTAL HARGA</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseOrder->items as $index => $item)
                        <tr>
                            <td style="text-align: center;" class="mono">{{ sprintf('%02d', $index + 1) }}</td>
                            <td>
                                <div class="item-main-title">{{ $item->item_name }}</div>
                                @if($item->specification)
                                    <div class="item-sub-spec">{{ $item->specification }}</div>
                                @endif
                            </td>
                            <td style="text-align: right;" class="mono">{{ $item->formatted_unit_price }}</td>
                            <td style="text-align: center;" class="mono font-bold">{{ $item->quantity }} <span style="font-size:9px; color:#64748B;">{{ $item->unit }}</span></td>
                            <td style="text-align: right;" class="mono font-bold">{{ $item->formatted_subtotal }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: #94A3B8; padding: 24px;">Tidak ada item tercatat dalam pesanan ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Summary Breakdown & Signatures Section -->
            <div class="summary-signature-grid">
                
                <!-- Left Column: Thank you note, Payment info, Terms -->
                <div class="policy-section">
                    <div class="policy-heading">Terima Kasih Atas Kerjasama Anda</div>
                    
                    <div class="payment-info-box">
                        <strong>Ketentuan Pembayaran & Pengiriman:</strong><br>
                        Syarat Pembayaran: <strong>{{ $purchaseOrder->payment_terms ?? 'Net 30 Hari' }}</strong><br>
                        Alamat Pengiriman: <strong>Gudang Logistik OrderFlow Lt. 12, SCBD Jakarta</strong><br>
                        Divisi Pemesan: <strong>{{ $purchaseOrder->purchaseRequest?->department?->name ?? 'Kantor Pusat Korporat' }}</strong>
                    </div>

                    <div class="terms-box">
                        <div class="terms-title">Syarat &amp; Ketentuan Hukum (Terms &amp; Conditions):</div>
                        1. Faktur penagihan wajib melampirkan lembar asli PO bertandatangan dan Surat BAST/Goods Receipt.<br>
                        2. Seluruh spesifikasi barang akan diverifikasi melalui sistem 3-Way Match sebelum persetujuan pembayaran.<br>
                        3. Keterlambatan penyerahan tanpa konfirmasi resmi dapat dikenakan denda sesuai SOP pengadaan perusahaan.
                    </div>
                </div>

                <!-- Right Column: Calculation Breakdown & Total Box -->
                <div class="totals-section">
                    <div class="calc-row">
                        <span>Subtotal Item:</span>
                        <strong class="mono">{{ $purchaseOrder->formatted_subtotal }}</strong>
                    </div>
                    @if($purchaseOrder->shipping_fee > 0)
                        <div class="calc-row">
                            <span>Ongkos Kirim (Logistik):</span>
                            <strong class="mono">{{ $purchaseOrder->formatted_shipping_fee }}</strong>
                        </div>
                    @endif
                    <div class="calc-row">
                        <span>Pajak Pertambahan Nilai (PPN):</span>
                        <strong class="mono">{{ $purchaseOrder->formatted_tax_amount }}</strong>
                    </div>

                    <!-- Solid Royal Blue Total Box Card -->
                    <div class="total-box-card">
                        <span class="total-box-label">TOTAL AKHIR:</span>
                        <span class="total-box-value mono">{{ $purchaseOrder->formatted_grand_total }}</span>
                    </div>

                </div>

            </div>

            <!-- Official Corporate 3-Column Signature Matrix (Clean Blank Line with Explicit Names & Positions) -->
            <div class="po-signatures-block">
                <!-- Column 1: Issuer -->
                <div class="po-sig-column">
                    <div class="po-sig-header">Dibuat &amp; Diterbitkan Oleh:</div>
                    <div class="po-sig-blank-box"></div>
                    <div class="po-sig-name">{{ $purchaseOrder->issuer?->name ?? 'Tim Procurement' }}</div>
                    <div class="po-sig-role">Spesialis Pengadaan / Procurement Officer</div>
                    <div class="po-sig-date">Tgl: {{ $purchaseOrder->order_date ? $purchaseOrder->order_date->format('d/m/Y') : date('d/m/Y') }}</div>
                </div>

                <!-- Column 2: Approver -->
                <div class="po-sig-column">
                    <div class="po-sig-header">Disetujui Oleh (Otorisasi):</div>
                    <div class="po-sig-blank-box"></div>
                    <div class="po-sig-name">( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
                    <div class="po-sig-role">Direksi Pengadaan &amp; Otorisasi Keuangan</div>
                    <div class="po-sig-date">Tgl: ..... / ..... / 20....</div>
                </div>

                <!-- Column 3: Vendor -->
                <div class="po-sig-column">
                    <div class="po-sig-header">Diterima &amp; Dikonfirmasi Rekanan:</div>
                    <div class="po-sig-blank-box"></div>
                    <div class="po-sig-name">( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
                    <div class="po-sig-role">{{ $purchaseOrder->vendor?->name ?? 'Pimpinan / Kuasa Sah Rekanan' }}</div>
                    <div class="po-sig-date">Tgl &amp; Cap: ..... / ..... / 20....</div>
                </div>
            </div>

        </div>

        <!-- Bottom Footer Ribbon & Geometric Accents -->
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
                    <span>procurement@orderflow.com</span>
                </div>
                <div class="contact-item">
                    <span class="contact-icon-pill">
                        <svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 010-5 2.5 2.5 0 010 5z"/></svg>
                    </span>
                    <span>SCBD Lot 52-53, SCBD, Jakarta Selatan 12190</span>
                </div>
            </div>

            <div class="bottom-bar"></div>
        </div>

    </div>

</body>
</html>
