<?php

namespace App\Http\Controllers\Api;

use App\Models\Department;
use App\Models\GoodsReceipt;
use App\Models\PrApproval;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardApiController extends BaseApiController
{
    /**
     * Get dashboard summary and KPI metrics tailored for the user's role
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();

        // 1. Role-specific PR counts (Optimized single-query aggregation)
        $prQuery = PurchaseRequest::query();
        if ($user->hasRole('requester')) {
            $prQuery->where('user_id', $user->id);
        } elseif ($user->hasRole(['manager', 'hod']) && !$user->hasRole('admin')) {
            $prQuery->where('department_id', $user->department_id);
        }

        $prAggregate = $prQuery->selectRaw("
            COUNT(*) as total_pr,
            SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) as submitted_pr,
            SUM(CASE WHEN status IN ('approved', 'processing', 'completed') THEN 1 ELSE 0 END) as approved_pr,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_pr,
            COALESCE(SUM(estimated_total), 0) as total_val
        ")->first();

        $totalPrCount     = (int) ($prAggregate->total_pr ?? 0);
        $submittedPrCount = (int) ($prAggregate->submitted_pr ?? 0);
        $approvedPrCount  = (int) ($prAggregate->approved_pr ?? 0);
        $rejectedPrCount  = (int) ($prAggregate->rejected_pr ?? 0);
        $totalPrValue     = (float) ($prAggregate->total_val ?? 0);

        // 2. Pending Approvals awaiting current user's specific tier
        $pendingApprovalsForUser = 0;
        if ($user->hasRole(['manager', 'finance', 'hod', 'admin'])) {
            $pendingQuery = PrApproval::where('status', 'pending')
                ->whereHas('purchaseRequest', function ($q) {
                    $q->where('status', 'submitted');
                });

            if ($user->hasRole('manager') && !$user->hasRole('admin')) {
                $pendingQuery->where('role_required', 'manager')
                    ->where('department_id', $user->department_id);
            } elseif ($user->hasRole('finance') && !$user->hasRole('admin')) {
                $pendingQuery->where('role_required', 'finance');
            } elseif ($user->hasRole('hod') && !$user->hasRole('admin')) {
                $pendingQuery->where('role_required', 'hod');
            }

            $pendingApprovalsForUser = $pendingQuery->count();
        }

        // 3. Purchase Order metrics (Optimized single-query aggregation)
        $poMetrics = null;
        if ($user->hasRole(['procurement', 'finance', 'admin', 'auditor', 'manager', 'hod'])) {
            $poQuery = PurchaseOrder::query();
            if ($user->hasRole(['manager', 'hod']) && !$user->hasRole('admin')) {
                $poQuery->whereHas('purchaseRequest', function ($q) use ($user) {
                    $q->where('department_id', $user->department_id);
                });
            }

            $poAggregate = $poQuery->selectRaw("
                COUNT(*) as total_orders,
                SUM(CASE WHEN status = 'issued' THEN 1 ELSE 0 END) as issued,
                SUM(CASE WHEN status = 'partially_received' THEN 1 ELSE 0 END) as partially_received,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                COALESCE(SUM(CASE WHEN status IN ('issued', 'partially_received', 'completed') THEN grand_total ELSE 0 END), 0) as total_spend
            ")->first();

            $totalSpend = (float) ($poAggregate->total_spend ?? 0);
            $poMetrics = [
                'total_orders'       => (int) ($poAggregate->total_orders ?? 0),
                'issued'             => (int) ($poAggregate->issued ?? 0),
                'partially_received' => (int) ($poAggregate->partially_received ?? 0),
                'completed'          => (int) ($poAggregate->completed ?? 0),
                'total_spend'        => $totalSpend,
                'formatted_spend'    => 'Rp ' . number_format($totalSpend, 0, ',', '.'),
            ];
        }

        // 4. Vendor counts (procurement, finance, admin, auditor)
        $vendorMetrics = null;
        if ($user->hasRole(['procurement', 'finance', 'admin', 'auditor'])) {
            $vendorMetrics = [
                'total_active_vendors' => Vendor::where('is_active', true)->count(),
            ];
        }

        $data = [
            'role' => $user->role,
            'department' => $user->department?->name,
            'kpis' => [
                'total_requests'     => $totalPrCount,
                'submitted_requests' => $submittedPrCount,
                'approved_requests'  => $approvedPrCount,
                'rejected_requests'  => $rejectedPrCount,
                'total_estimated_value' => (float) $totalPrValue,
                'formatted_estimated_value' => 'Rp ' . number_format($totalPrValue, 0, ',', '.'),
                'pending_approvals_actionable' => $pendingApprovalsForUser,
            ],
            'purchase_orders' => $poMetrics,
            'vendors' => $vendorMetrics,
        ];

        return $this->sendResponse($data, 'Ringkasan metriks dashboard berhasil diambil');
    }

    /**
     * Get comprehensive management dashboard dataset with dynamic period & department filtering
     * Used by the React Management Dashboard module
     */
    public function managementDashboardData(Request $request): JsonResponse
    {
        $user = $request->user();
        $period = $request->input('period', 'this_month');
        $departmentId = $request->input('department_id');

        $now = Carbon::now();
        $startDate = null;
        $endDate = $now->copy()->endOfDay();

        switch ($period) {
            case 'this_month':
                $startDate = $now->copy()->startOfMonth();
                break;
            case 'last_month':
                $startDate = $now->copy()->subMonth()->startOfMonth();
                $endDate = $now->copy()->subMonth()->endOfMonth();
                break;
            case 'this_quarter':
                $startDate = $now->copy()->firstOfQuarter();
                break;
            case 'this_year':
                $startDate = $now->copy()->startOfYear();
                break;
            case 'all':
            default:
                $startDate = $now->copy()->subMonths(11)->startOfMonth();
                break;
        }

        // Departments for filter
        $departments = Department::select('id', 'name', 'code')->orderBy('name')->get();

        // Base PR Query (Optimized single-query aggregation)
        $prQuery = PurchaseRequest::query();
        if ($departmentId) {
            $prQuery->where('department_id', $departmentId);
        }
        if ($startDate) {
            $prQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        $prAggregate = $prQuery->selectRaw("
            COUNT(*) as total_pr,
            SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) as pending_pr,
            SUM(CASE WHEN status IN ('approved', 'po_created', 'completed') THEN 1 ELSE 0 END) as approved_pr,
            COALESCE(SUM(estimated_total), 0) as total_budget
        ")->first();

        $totalPrCount    = (int) ($prAggregate->total_pr ?? 0);
        $pendingPrCount  = (int) ($prAggregate->pending_pr ?? 0);
        $approvedPrCount = (int) ($prAggregate->approved_pr ?? 0);
        $totalPrBudget   = (float) ($prAggregate->total_budget ?? 0);

        // Base PO Query for period spend (Optimized single-query aggregation)
        $poQuery = PurchaseOrder::query();
        if ($departmentId) {
            $poQuery->whereHas('purchaseRequest', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
        if ($startDate) {
            $poQuery->whereBetween('order_date', [$startDate->toDateString(), $endDate->toDateString()]);
        }

        $poAggregate = $poQuery->selectRaw("
            COALESCE(SUM(CASE WHEN status IN ('issued', 'partially_received', 'completed') THEN grand_total ELSE 0 END), 0) as total_spend,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count
        ")->first();

        $totalPoSpend     = (float) ($poAggregate->total_spend ?? 0);
        $completedPoCount = (int) ($poAggregate->completed_count ?? 0);

        // Active POs represent current open commitments in the pipeline
        $activePoQuery = PurchaseOrder::whereIn('status', ['issued', 'partially_received']);
        if ($departmentId) {
            $activePoQuery->whereHas('purchaseRequest', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
        $activePoCount = $activePoQuery->count();

        // Overdue PO Query (Active overdue POs for the selected department)
        $overduePoQuery = PurchaseOrder::overdue()->with(['vendor', 'purchaseRequest.department']);
        if ($departmentId) {
            $overduePoQuery->whereHas('purchaseRequest', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
        $overduePoCount = (clone $overduePoQuery)->count();
        $overduePoList = $overduePoQuery->latest()->take(10)->get()->map(function ($po) {
            return [
                'id' => $po->id,
                'po_number' => $po->po_number,
                'vendor_name' => $po->vendor?->name ?? 'Vendor Tidak Dikenal',
                'department_name' => $po->purchaseRequest?->department?->name ?? '-',
                'grand_total' => (float) $po->grand_total,
                'formatted_grand_total' => 'Rp ' . number_format($po->grand_total, 0, ',', '.'),
                'delivery_target_date' => $po->delivery_target_date ? $po->delivery_target_date->format('d M Y') : '-',
                'overdue_days' => $po->overdue_days,
                'estimated_penalty' => (float) $po->estimated_penalty_amount,
                'formatted_penalty' => $po->formatted_estimated_penalty,
                'status' => $po->status,
                'status_label' => PurchaseOrder::STATUS_LABELS[$po->status] ?? $po->status,
            ];
        });

        // Pending Approvals List
        $approvalsQuery = PurchaseRequest::where('status', 'submitted')
            ->with(['user', 'department', 'approvals']);
        if ($departmentId) {
            $approvalsQuery->where('department_id', $departmentId);
        }
        $pendingApprovalsList = $approvalsQuery->latest()->take(10)->get()->map(function ($pr) {
            $currentAppr = $pr->approvals->where('status', 'pending')->sortBy('tier_level')->first();
            return [
                'id' => $pr->id,
                'pr_number' => $pr->pr_number,
                'title' => $pr->title,
                'requester_name' => $pr->user?->name ?? '-',
                'department_name' => $pr->department?->name ?? '-',
                'estimated_total' => (float) $pr->estimated_total,
                'formatted_total' => 'Rp ' . number_format($pr->estimated_total, 0, ',', '.'),
                'created_at' => $pr->created_at->format('d M Y H:i'),
                'current_role' => $currentAppr?->role_required ?? 'approver',
            ];
        });

        // Spend Trend (Optimized 2-query batch fetch instead of 12-loop queries)
        $sixMonthsAgo = Carbon::now()->subMonths(5)->startOfMonth();
        $nowEnd = Carbon::now()->endOfMonth();

        $trendPrQ = PurchaseRequest::whereBetween('created_at', [$sixMonthsAgo, $nowEnd]);
        $trendPoQ = PurchaseOrder::whereIn('status', ['issued', 'partially_received', 'completed'])
            ->whereBetween('order_date', [$sixMonthsAgo->toDateString(), $nowEnd->toDateString()]);

        if ($departmentId) {
            $trendPrQ->where('department_id', $departmentId);
            $trendPoQ->whereHas('purchaseRequest', fn($q) => $q->where('department_id', $departmentId));
        }

        $prSumsByMonth = $trendPrQ->get(['created_at', 'estimated_total'])
            ->groupBy(fn ($pr) => $pr->created_at->format('Y-m'))
            ->map(fn ($group) => $group->sum('estimated_total'));

        $poSumsByMonth = $trendPoQ->get(['order_date', 'grand_total'])
            ->groupBy(fn ($po) => $po->order_date ? $po->order_date->format('Y-m') : '')
            ->map(fn ($group) => $group->sum('grand_total'));

        $trendData = [];
        for ($i = 5; $i >= 0; $i--) {
            $mStart = Carbon::now()->subMonths($i)->startOfMonth();
            $mKey   = $mStart->format('Y-m');
            $trendData[] = [
                'month' => $mStart->format('M Y'),
                'pr_total' => (float) ($prSumsByMonth[$mKey] ?? 0),
                'po_total' => (float) ($poSumsByMonth[$mKey] ?? 0),
            ];
        }

        // Recent PR Activities
        $recentPrsQuery = PurchaseRequest::with(['user', 'department']);
        if ($departmentId) {
            $recentPrsQuery->where('department_id', $departmentId);
        }
        $recentPrsList = $recentPrsQuery->latest()->take(6)->get()->map(function ($pr) {
            return [
                'id' => $pr->id,
                'pr_number' => $pr->pr_number,
                'title' => $pr->title,
                'requester_name' => $pr->user?->name ?? '-',
                'department_code' => $pr->department?->code ?? '-',
                'estimated_total' => (float) $pr->estimated_total,
                'formatted_total' => 'Rp ' . number_format($pr->estimated_total, 0, ',', '.'),
                'status' => $pr->status,
                'status_label' => $pr->status_label,
                'status_badge_class' => $pr->status_badge_class,
                'created_at' => $pr->created_at->format('d M Y'),
            ];
        });

        $data = [
            'user' => [
                'name' => $user->name,
                'role' => $user->role,
                'role_label' => $user->role_label,
                'department' => $user->department?->name ?? 'Semua Divisi',
            ],
            'filters' => [
                'period' => $period,
                'department_id' => $departmentId,
                'start_date' => $startDate ? $startDate->toDateString() : null,
                'end_date' => $endDate ? $endDate->toDateString() : null,
            ],
            'departments' => $departments,
            'kpis' => [
                'total_spend' => $totalPoSpend,
                'formatted_spend' => 'Rp ' . number_format($totalPoSpend, 0, ',', '.'),
                'total_pr' => $totalPrCount,
                'pending_approval_pr' => $pendingPrCount,
                'approved_pr' => $approvedPrCount,
                'total_pr_budget' => $totalPrBudget,
                'formatted_pr_budget' => 'Rp ' . number_format($totalPrBudget, 0, ',', '.'),
                'active_po' => $activePoCount,
                'completed_po' => $completedPoCount,
                'overdue_po' => $overduePoCount,
            ],
            'trend' => $trendData,
            'pending_approvals' => $pendingApprovalsList,
            'overdue_pos' => $overduePoList,
            'recent_prs' => $recentPrsList,
        ];

        return $this->sendResponse($data, 'Data management dashboard berhasil dimuat');
    }
}
