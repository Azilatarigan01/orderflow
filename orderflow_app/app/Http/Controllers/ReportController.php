<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReportController extends Controller
{
    /**
     * Report index / selector page
     */
    public function index()
    {
        return view('reports.index');
    }

    /**
     * Resolve and validate dynamic date ranges, presets, and memory safeguard limit.
     * Prevents multi-year queries from causing memory exhaustion or DB timeout.
     */
    protected function resolveAndValidateDateRange(Request $request, string $defaultPreset = 'this_month'): array
    {
        $preset = $request->input('period_preset', $defaultPreset);
        $now = Carbon::now();

        $dateFrom = null;
        $dateTo = null;

        switch ($preset) {
            case 'today':
                $dateFrom = $now->toDateString();
                $dateTo = $now->toDateString();
                break;
            case 'this_week':
                $dateFrom = $now->copy()->startOfWeek()->toDateString();
                $dateTo = $now->copy()->endOfWeek()->toDateString();
                break;
            case 'this_month':
                $dateFrom = $now->copy()->startOfMonth()->toDateString();
                $dateTo = $now->copy()->endOfMonth()->toDateString();
                break;
            case 'this_quarter':
                $dateFrom = $now->copy()->firstOfQuarter()->toDateString();
                $dateTo = $now->copy()->lastOfQuarter()->toDateString();
                break;
            case 'this_year':
                $dateFrom = $now->copy()->startOfYear()->toDateString();
                $dateTo = $now->copy()->endOfYear()->toDateString();
                break;
            case 'all':
                // Only if explicitly selected, default date range of current year
                $dateFrom = $now->copy()->subMonths(11)->startOfMonth()->toDateString();
                $dateTo = $now->toDateString();
                break;
            case 'custom':
            default:
                if ($request->filled('date_from')) {
                    $dateFrom = $request->date_from;
                }
                if ($request->filled('date_to')) {
                    $dateTo = $request->date_to;
                }
                break;
        }

        // If custom inputs explicitly supplied
        if ($preset === 'custom') {
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');
        }

        // Safeguard validation: Check chronological order and 365-day maximum limit
        if ($dateFrom && $dateTo) {
            $carbonFrom = Carbon::parse($dateFrom);
            $carbonTo = Carbon::parse($dateTo);

            if ($carbonTo->lt($carbonFrom)) {
                throw ValidationException::withMessages([
                    'date_to' => 'Tanggal akhir penarikan tidak boleh lebih awal dari tanggal awal.',
                ]);
            }

            if ($carbonFrom->diffInDays($carbonTo) > 365) {
                throw ValidationException::withMessages([
                    'date_to' => 'Rentang penarikan laporan dibatasi maksimal 365 hari (1 tahun) per tarikan untuk menjaga kinerja dan kestabilan sistem.',
                ]);
            }
        }

        return [
            'preset' => $preset,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];
    }

    /**
     * PR Report — Perencanaan Belanja (Filter by period, department, status, posting date basis)
     */
    public function prReport(Request $request)
    {
        $dateFilter = $this->resolveAndValidateDateRange($request, 'this_month');
        $dateBasis = $request->input('date_basis', 'created_at'); // 'created_at' or 'required_date'

        $query = PurchaseRequest::with(['user', 'department'])->latest();

        // Filters
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $dateColumn = ($dateBasis === 'required_date') ? 'required_date' : 'created_at';
        if (!empty($dateFilter['date_from'])) {
            $query->whereDate($dateColumn, '>=', $dateFilter['date_from']);
        }
        if (!empty($dateFilter['date_to'])) {
            $query->whereDate($dateColumn, '<=', $dateFilter['date_to']);
        }

        $prs = $query->get();
        $departments = Department::orderBy('name')->get();
        $totalValue  = $prs->sum('estimated_total');

        // Group by status for summary
        $byStatus = $prs->groupBy('status')->map->count();

        if (in_array($request->get('export'), ['excel', 'xls'])) {
            return $this->exportExcelResponse('reports.excel_export', [
                'title'      => 'Laporan Rekapitulasi Purchase Request (Perencanaan Belanja)',
                'prs'        => $prs,
                'totalValue' => $totalValue,
                'byStatus'   => $byStatus,
                'filters'    => array_merge($request->only(['department_id', 'status']), [
                    'date_from' => $dateFilter['date_from'],
                    'date_to' => $dateFilter['date_to'],
                    'date_basis' => $dateBasis,
                    'period_preset' => $dateFilter['preset'],
                ]),
                'departments' => $departments,
                'type'       => 'pr',
            ], 'laporan_pr_' . now()->format('Ymd_His'));
        }

        if ($request->get('export') === 'csv') {
            return $this->exportPrCsv($prs);
        }

        if ($request->get('export') === 'pdf') {
            return view('reports.export_pdf', [
                'title'      => 'Laporan Purchase Request (Perencanaan Belanja)',
                'prs'        => $prs,
                'totalValue' => $totalValue,
                'byStatus'   => $byStatus,
                'filters'    => array_merge($request->only(['department_id', 'status']), [
                    'date_from' => $dateFilter['date_from'],
                    'date_to' => $dateFilter['date_to'],
                    'date_basis' => $dateBasis,
                    'period_preset' => $dateFilter['preset'],
                ]),
                'departments' => $departments,
                'type'       => 'pr',
            ]);
        }

        return view('reports.pr_report', compact('prs', 'departments', 'totalValue', 'byStatus', 'dateFilter', 'dateBasis'));
    }

    /**
     * PO Report — Realisasi Komitmen Anggaran & Kontrak (Posting Date: Tanggal PO Diterbitkan)
     */
    public function poReport(Request $request)
    {
        $dateFilter = $this->resolveAndValidateDateRange($request, 'this_month');
        $dateBasis = $request->input('date_basis', 'order_date'); // 'order_date' or 'delivery_date'

        $query = PurchaseOrder::with(['purchaseRequest.department', 'vendor', 'items'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        $dateColumn = ($dateBasis === 'delivery_date') ? 'delivery_date' : 'order_date';
        if (!empty($dateFilter['date_from'])) {
            $query->whereDate($dateColumn, '>=', $dateFilter['date_from']);
        }
        if (!empty($dateFilter['date_to'])) {
            $query->whereDate($dateColumn, '<=', $dateFilter['date_to']);
        }

        $pos        = $query->get();
        $vendors    = Vendor::where('is_active', true)->orderBy('name')->get();
        $totalValue = $pos->sum('grand_total');

        // Outstanding: POs not yet completed
        $outstanding = $pos->whereIn('status', ['issued', 'partially_received']);

        if (in_array($request->get('export'), ['excel', 'xls'])) {
            return $this->exportExcelResponse('reports.excel_export', [
                'title'       => 'Laporan Rekapitulasi Purchase Order (Komitmen Anggaran)',
                'pos'         => $pos,
                'totalValue'  => $totalValue,
                'outstanding' => $outstanding,
                'vendors'     => $vendors,
                'filters'     => array_merge($request->only(['status', 'vendor_id']), [
                    'date_from' => $dateFilter['date_from'],
                    'date_to' => $dateFilter['date_to'],
                    'date_basis' => $dateBasis,
                    'period_preset' => $dateFilter['preset'],
                ]),
                'type'        => 'po',
            ], 'laporan_po_' . now()->format('Ymd_His'));
        }

        if ($request->get('export') === 'csv') {
            return $this->exportPoCsv($pos);
        }

        if ($request->get('export') === 'pdf') {
            return view('reports.export_pdf', [
                'title'       => 'Laporan Purchase Order (Komitmen Anggaran)',
                'pos'         => $pos,
                'totalValue'  => $totalValue,
                'outstanding' => $outstanding,
                'vendors'     => $vendors,
                'filters'     => array_merge($request->only(['status', 'vendor_id']), [
                    'date_from' => $dateFilter['date_from'],
                    'date_to' => $dateFilter['date_to'],
                    'date_basis' => $dateBasis,
                    'period_preset' => $dateFilter['preset'],
                ]),
                'type'        => 'po',
            ]);
        }

        return view('reports.po_report', compact('pos', 'vendors', 'totalValue', 'outstanding', 'dateFilter', 'dateBasis'));
    }

    /**
     * GR Report — Realisasi Fisik & Logistik (Posting Date: Tanggal Penerimaan Fisik / BAST)
     */
    public function grReport(Request $request)
    {
        $dateFilter = $this->resolveAndValidateDateRange($request, 'this_month');

        $query = GoodsReceipt::with([
            'purchaseOrder.vendor',
            'purchaseOrder.purchaseRequest.department',
            'receiver',
            'items.poItem',
        ])->latest('received_date');

        if ($request->filled('receipt_type')) {
            $query->where('receipt_type', $request->receipt_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('vendor_id')) {
            $query->whereHas('purchaseOrder', function ($q) use ($request) {
                $q->where('vendor_id', $request->vendor_id);
            });
        }

        if (!empty($dateFilter['date_from'])) {
            $query->whereDate('received_date', '>=', $dateFilter['date_from']);
        }
        if (!empty($dateFilter['date_to'])) {
            $query->whereDate('received_date', '<=', $dateFilter['date_to']);
        }

        $receipts = $query->get();
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $totalItemsReceived = $receipts->sum(fn ($r) => $r->items->sum('quantity_received'));
        $totalItemsRejected = $receipts->sum(fn ($r) => $r->items->sum('quantity_rejected'));

        if (in_array($request->get('export'), ['excel', 'xls'])) {
            return $this->exportExcelResponse('reports.excel_export', [
                'title'              => 'Laporan Penerimaan Fisik Barang & BAST Jasa',
                'receipts'           => $receipts,
                'totalItemsReceived' => $totalItemsReceived,
                'totalItemsRejected' => $totalItemsRejected,
                'vendors'            => $vendors,
                'filters'            => array_merge($request->only(['receipt_type', 'status', 'vendor_id']), [
                    'date_from' => $dateFilter['date_from'],
                    'date_to' => $dateFilter['date_to'],
                    'period_preset' => $dateFilter['preset'],
                ]),
                'type'               => 'gr',
            ], 'laporan_penerimaan_barang_' . now()->format('Ymd_His'));
        }

        if ($request->get('export') === 'csv') {
            return $this->exportGrCsv($receipts);
        }

        if ($request->get('export') === 'pdf') {
            return view('reports.export_pdf', [
                'title'       => 'Laporan Penerimaan Fisik & BAST (Realisasi Barang/Jasa)',
                'receipts'    => $receipts,
                'totalItemsReceived' => $totalItemsReceived,
                'totalItemsRejected' => $totalItemsRejected,
                'vendors'     => $vendors,
                'filters'     => array_merge($request->only(['receipt_type', 'status', 'vendor_id']), [
                    'date_from' => $dateFilter['date_from'],
                    'date_to' => $dateFilter['date_to'],
                    'period_preset' => $dateFilter['preset'],
                ]),
                'type'        => 'gr',
            ]);
        }

        return view('reports.gr_report', compact('receipts', 'vendors', 'totalItemsReceived', 'totalItemsRejected', 'dateFilter'));
    }

    /**
     * Vendor Spend Report
     */
    public function vendorSpend(Request $request)
    {
        $dateFilter = $this->resolveAndValidateDateRange($request, 'this_year');

        $query = DB::table('purchase_orders as po')
            ->join('vendors as v', 'po.vendor_id', '=', 'v.id')
            ->whereIn('po.status', ['issued', 'partially_received', 'completed'])
            ->groupBy('v.id', 'v.name', 'v.code', 'v.category', 'v.rating')
            ->select(
                'v.id',
                'v.name',
                'v.code',
                'v.category',
                'v.rating',
                DB::raw('COUNT(po.id) as po_count'),
                DB::raw('SUM(po.grand_total) as total_spend'),
                DB::raw('MAX(po.order_date) as last_order_date')
            );

        if (!empty($dateFilter['date_from'])) {
            $query->whereDate('po.order_date', '>=', $dateFilter['date_from']);
        }
        if (!empty($dateFilter['date_to'])) {
            $query->whereDate('po.order_date', '<=', $dateFilter['date_to']);
        }
        if ($request->filled('category')) {
            $query->where('v.category', $request->category);
        }

        $vendorSpend = $query->orderByDesc('total_spend')->get();
        $grandTotal = $vendorSpend->sum('total_spend');
        $categories = Vendor::select('category')->distinct()->pluck('category');

        if (in_array($request->get('export'), ['excel', 'xls'])) {
            return $this->exportExcelResponse('reports.excel_export', [
                'title'       => 'Laporan Realisasi Pengeluaran Belanja Rekanan Vendor',
                'vendorSpend' => $vendorSpend,
                'grandTotal'  => $grandTotal,
                'categories'  => $categories,
                'filters'     => array_merge($request->only(['category']), [
                    'date_from' => $dateFilter['date_from'],
                    'date_to' => $dateFilter['date_to'],
                    'period_preset' => $dateFilter['preset'],
                ]),
                'type'        => 'vendor',
            ], 'laporan_vendor_spend_' . now()->format('Ymd_His'));
        }

        if ($request->get('export') === 'csv') {
            return $this->exportVendorCsv($vendorSpend);
        }

        if ($request->get('export') === 'pdf') {
            return view('reports.export_pdf', [
                'title'       => 'Laporan Pengeluaran Per Vendor',
                'vendorSpend' => $vendorSpend,
                'grandTotal'  => $grandTotal,
                'categories'  => $categories,
                'filters'     => array_merge($request->only(['category']), [
                    'date_from' => $dateFilter['date_from'],
                    'date_to' => $dateFilter['date_to'],
                    'period_preset' => $dateFilter['preset'],
                ]),
                'type'        => 'vendor',
            ]);
        }

        return view('reports.vendor_spend', compact('vendorSpend', 'grandTotal', 'categories', 'dateFilter'));
    }

    // ── Excel & CSV Exports (native PHP, memory-efficient) ─────────────────

    private function exportExcelResponse(string $view, array $data, string $filename)
    {
        $content = view($view, $data)->render();
        return response($content, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.xls\"",
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ]);
    }

    private function exportPrCsv($prs)
    {
        $filename = 'laporan_pr_' . now()->format('Ymd_His') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($prs) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, ['No. PR', 'Judul', 'Pemohon', 'Divisi', 'Nilai Estimasi', 'Status', 'Tanggal Dibuat', 'Target Kebutuhan']);
            foreach ($prs as $pr) {
                fputcsv($file, [
                    $pr->pr_number,
                    $pr->title,
                    $pr->user?->name,
                    $pr->department?->name,
                    $pr->estimated_total,
                    $pr->status,
                    $pr->created_at->format('d/m/Y'),
                    $pr->required_date ? Carbon::parse($pr->required_date)->format('d/m/Y') : '-',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportPoCsv($pos)
    {
        $filename = 'laporan_po_' . now()->format('Ymd_His') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($pos) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, ['No. PO', 'Vendor', 'Divisi Pemohon', 'Tanggal PO (Posting Date)', 'Subtotal', 'Pajak', 'Grand Total', 'Status']);
            foreach ($pos as $po) {
                fputcsv($file, [
                    $po->po_number,
                    $po->vendor?->name,
                    $po->purchaseRequest?->department?->name,
                    $po->order_date?->format('d/m/Y'),
                    $po->subtotal,
                    $po->tax_amount,
                    $po->grand_total,
                    $po->status,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportGrCsv($receipts)
    {
        $filename = 'laporan_penerimaan_barang_' . now()->format('Ymd_His') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($receipts) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, ['No. Penerimaan', 'No. PO', 'Tipe', 'Vendor', 'Tanggal Terima Fisik', 'Petugas Penerima', 'Surat Jalan / BAST', 'Kondisi', 'Status']);
            foreach ($receipts as $r) {
                fputcsv($file, [
                    $r->gr_number,
                    $r->purchaseOrder?->po_number,
                    $r->is_service ? 'Jasa (BAST)' : 'Barang Fisik',
                    $r->purchaseOrder?->vendor?->name,
                    $r->received_date?->format('d/m/Y'),
                    $r->receiver?->name ?? 'Gudang',
                    $r->delivery_note_no ?? '-',
                    $r->item_condition,
                    $r->status,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportVendorCsv($vendorSpend)
    {
        $filename = 'laporan_vendor_spend_' . now()->format('Ymd_His') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($vendorSpend) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, ['Kode Vendor', 'Nama Vendor', 'Kategori', 'Jumlah PO', 'Total Pengeluaran', 'Order Terakhir', 'Rating']);
            foreach ($vendorSpend as $v) {
                fputcsv($file, [
                    $v->code,
                    $v->name,
                    $v->category,
                    $v->po_count,
                    $v->total_spend,
                    $v->last_order_date,
                    $v->rating,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
