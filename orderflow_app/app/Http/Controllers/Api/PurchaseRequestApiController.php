<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\PurchaseRequestResource;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\StatusHistory;
use App\Services\ApprovalService;
use App\Services\AuditTrailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseRequestApiController extends BaseApiController
{
    protected ApprovalService $approvalService;

    public function __construct(ApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    /**
     * List purchase requests with filtering, pagination, and role-based access
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = PurchaseRequest::with(['user', 'department', 'items', 'approvals.approver'])->latest();

        // Segregation & Access Scope based on Role
        if ($user->hasRole('requester')) {
            $query->where('user_id', $user->id);
        } elseif ($user->hasRole(['manager', 'hod']) && !$user->hasRole('admin')) {
            $query->where('department_id', $user->department_id);
        }

        // Filtering
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('pr_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        $perPage = min((int) $request->input('per_page', 10), 100);
        $paginated = $query->paginate($perPage);

        $data = [
            'items' => PurchaseRequestResource::collection($paginated->items()),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
                'last_page'    => $paginated->lastPage(),
                'has_more'     => $paginated->hasMorePages(),
            ],
        ];

        return $this->sendResponse($data, 'Daftar permohonan pengadaan (Purchase Request) berhasil diambil');
    }

    /**
     * Create a new Purchase Request
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title'                      => ['required', 'string', 'max:255'],
            'description'                => ['nullable', 'string'],
            'required_date'              => ['required', 'date', 'after_or_equal:today'],
            'department_id'              => ['nullable', 'exists:departments,id'],
            'items'                      => ['required', 'array', 'min:1'],
            'items.*.item_name'          => ['required', 'string', 'max:255'],
            'items.*.specification'      => ['nullable', 'string'],
            'items.*.quantity'           => ['required', 'integer', 'min:1'],
            'items.*.unit'               => ['required', 'string', 'max:50'],
            'items.*.estimated_unit_price' => ['required', 'numeric', 'min:0'],
        ], [
            'title.required'             => 'Judul pengadaan wajib diisi.',
            'required_date.required'     => 'Tanggal kebutuhan barang wajib diisi.',
            'required_date.after_or_equal' => 'Tanggal kebutuhan tidak boleh di masa lalu.',
            'items.required'             => 'Minimal sertakan 1 rincian item barang.',
            'items.min'                  => 'Minimal sertakan 1 rincian item barang.',
            'items.*.item_name.required' => 'Nama barang pada setiap baris wajib diisi.',
            'items.*.quantity.min'       => 'Jumlah kuantitas barang minimal 1.',
            'items.*.estimated_unit_price.min' => 'Estimasi harga satuan minimal 0.',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi pengajuan gagal', $validator->errors(), 422);
        }

        $user = $request->user();
        $deptId = $request->department_id ?? $user->department_id;

        if (!$deptId) {
            return $this->sendError('Departemen pemohon tidak ditemukan', [
                'department_id' => ['Departemen pemohon tidak valid atau belum diatur pada profil Anda.']
            ], 422);
        }

        $pr = DB::transaction(function () use ($request, $user, $deptId) {
            $totalEstimate = 0;
            foreach ($request->items as $item) {
                $totalEstimate += ((int) $item['quantity']) * ((float) $item['estimated_unit_price']);
            }

            $purchaseRequest = PurchaseRequest::create([
                'pr_number'       => PurchaseRequest::generatePrNumber($deptId),
                'user_id'         => $user->id,
                'department_id'   => $deptId,
                'title'           => $request->title,
                'description'     => $request->description,
                'required_date'   => $request->required_date,
                'estimated_total' => $totalEstimate,
                'status'          => 'submitted',
            ]);

            foreach ($request->items as $item) {
                $subtotal = ((int) $item['quantity']) * ((float) $item['estimated_unit_price']);
                PurchaseRequestItem::create([
                    'purchase_request_id'  => $purchaseRequest->id,
                    'item_name'            => $item['item_name'],
                    'specification'        => $item['specification'] ?? null,
                    'quantity'             => $item['quantity'],
                    'unit'                 => $item['unit'],
                    'estimated_unit_price' => $item['estimated_unit_price'],
                    'estimated_subtotal'   => $subtotal,
                ]);
            }

            // Create initial status history
            StatusHistory::create([
                'purchase_request_id' => $purchaseRequest->id,
                'from_status'         => 'draft',
                'to_status'           => 'submitted',
                'user_id'             => $user->id,
                'notes'               => 'Pengajuan dibuat dan diajukan via REST API',
            ]);

            // Generate sequential approval tiers based on total amount
            $this->approvalService->generateApprovalTiers($purchaseRequest);

            // Audit Trail
            AuditTrailService::record(
                'pr_created_api',
                $purchaseRequest,
                $purchaseRequest->pr_number,
                null,
                $purchaseRequest->toArray(),
                "Membuat PR #{$purchaseRequest->pr_number} senilai Rp " . number_format($totalEstimate, 0, ',', '.') . " via REST API"
            );

            return $purchaseRequest;
        });

        $pr->load(['user', 'department', 'items', 'approvals.approver']);

        return $this->sendResponse(
            new PurchaseRequestResource($pr),
            "Permohonan pengadaan #{$pr->pr_number} berhasil diajukan dan diteruskan ke alur persetujuan.",
            201
        );
    }

    /**
     * Show single Purchase Request
     */
    public function show(Request $request, int|string $id): JsonResponse
    {
        $user = $request->user();
        $pr = PurchaseRequest::with(['user', 'department', 'items', 'approvals.approver'])->find($id);

        if (!$pr) {
            return $this->sendError('Purchase Request tidak ditemukan', [], 404);
        }

        // Authorization check: requesters cannot view PRs belonging to others
        if ($user->hasRole('requester') && $pr->user_id !== $user->id) {
            return $this->sendError('Akses Ditolak: Anda tidak memiliki wewenang untuk melihat pengajuan ini.', [], 403);
        }

        if ($user->hasRole(['manager', 'hod']) && !$user->hasRole('admin') && $pr->department_id !== $user->department_id) {
            return $this->sendError('Akses Ditolak: Anda hanya berwenang melihat pengajuan dari divisi Anda sendiri.', [], 403);
        }

        return $this->sendResponse(new PurchaseRequestResource($pr), 'Detail permohonan pengadaan berhasil diambil');
    }

    /**
     * Approve the active approval tier
     */
    public function approve(Request $request, int|string $id): JsonResponse
    {
        $user = $request->user();
        $pr = PurchaseRequest::with(['approvals', 'items', 'user', 'department'])->find($id);

        if (!$pr) {
            return $this->sendError('Purchase Request tidak ditemukan', [], 404);
        }

        if ($pr->status !== 'submitted') {
            return $this->sendError(
                "Pengajuan ini berstatus '{$pr->status_label}' dan tidak dapat diproses persetujuannya.",
                [],
                422
            );
        }

        if (!$this->approvalService->canUserApprove($user, $pr)) {
            return $this->sendError(
                'Akses Ditolak: Anda tidak memiliki wewenang pada tahap persetujuan ini, atau melanggar prinsip anti self-approval.',
                [],
                403
            );
        }

        $activeTier = $pr->currentPendingApproval();
        $notes = $request->input('notes', 'Disetujui via REST API');

        try {
            $this->approvalService->approve($user, $pr, $notes);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return $this->sendError($e->getMessage(), [], $e->getStatusCode());
        }

        $pr->refresh()->load(['user', 'department', 'items', 'approvals.approver']);

        return $this->sendResponse(
            new PurchaseRequestResource($pr),
            "Persetujuan tahap {$activeTier->tier_level} ({$activeTier->role_required}) berhasil disahkan."
        );
    }

    /**
     * Reject the Purchase Request
     */
    public function reject(Request $request, int|string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'reason.required' => 'Alasan penolakan pengadaan wajib diisi.',
            'reason.min'      => 'Alasan penolakan minimal 5 karakter.',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi penolakan gagal', $validator->errors(), 422);
        }

        $user = $request->user();
        $pr = PurchaseRequest::with(['approvals', 'items', 'user', 'department'])->find($id);

        if (!$pr) {
            return $this->sendError('Purchase Request tidak ditemukan', [], 404);
        }

        if ($pr->status !== 'submitted') {
            return $this->sendError(
                "Pengajuan ini berstatus '{$pr->status_label}' dan tidak dapat ditolak.",
                [],
                422
            );
        }

        if (!$this->approvalService->canUserApprove($user, $pr)) {
            return $this->sendError(
                'Akses Ditolak: Anda tidak memiliki wewenang pada tahap persetujuan ini untuk menolak pengajuan.',
                [],
                403
            );
        }

        try {
            $this->approvalService->reject($user, $pr, $request->reason);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return $this->sendError($e->getMessage(), [], $e->getStatusCode());
        }

        $pr->refresh()->load(['user', 'department', 'items', 'approvals.approver']);

        return $this->sendResponse(
            new PurchaseRequestResource($pr),
            "Pengajuan #{$pr->pr_number} telah ditolak dengan alasan yang tercatat."
        );
    }
}
