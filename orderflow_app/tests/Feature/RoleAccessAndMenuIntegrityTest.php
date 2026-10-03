<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Models\Vendor;
use App\Services\ApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessAndMenuIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $managerIt;
    protected User $finance;
    protected User $hod;
    protected User $procurement;
    protected User $warehouse;
    protected User $requester;
    protected Department $deptIt;
    protected Department $deptFin;
    protected Vendor $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deptIt = Department::create(['name' => 'Information Technology', 'code' => 'IT']);
        $this->deptFin = Department::create(['name' => 'Finance & Accounting', 'code' => 'FIN']);

        $this->admin = User::factory()->create([
            'name' => 'Super Administrator',
            'email' => 'admin@test.local',
            'role' => 'admin',
            'department_id' => $this->deptIt->id,
        ]);

        $this->managerIt = User::factory()->create([
            'name' => 'Manager IT',
            'email' => 'manager.it@test.local',
            'role' => 'manager',
            'department_id' => $this->deptIt->id,
        ]);

        $this->finance = User::factory()->create([
            'name' => 'Finance Officer',
            'email' => 'finance@test.local',
            'role' => 'finance',
            'department_id' => $this->deptFin->id,
        ]);

        $this->hod = User::factory()->create([
            'name' => 'Head of Department',
            'email' => 'hod@test.local',
            'role' => 'hod',
            'department_id' => $this->deptIt->id,
        ]);

        $this->procurement = User::factory()->create([
            'name' => 'Procurement Officer',
            'email' => 'proc@test.local',
            'role' => 'procurement',
            'department_id' => $this->deptIt->id,
        ]);

        $this->warehouse = User::factory()->create([
            'name' => 'Warehouse Staff',
            'email' => 'warehouse@test.local',
            'role' => 'warehouse',
            'department_id' => $this->deptIt->id,
        ]);

        $this->requester = User::factory()->create([
            'name' => 'Requester IT',
            'email' => 'requester@test.local',
            'role' => 'requester',
            'department_id' => $this->deptIt->id,
        ]);

        $this->vendor = Vendor::create([
            'name' => 'PT Mitra Solusi Prima',
            'code' => 'V-MSP-01',
            'contact_person' => 'Budi Santoso',
            'email' => 'vendor@msp.com',
            'phone' => '021-5551234',
            'address' => 'Jl. Jendral Sudirman Kav 20, Jakarta',
            'tax_number' => '01.234.567.8-012.000',
            'category' => 'hardware',
            'is_active' => true,
        ]);
    }

    /**
     * Test 1: PO Show page opens without BadMethodCallException (exists() on collection bug fixed)
     */
    public function test_purchase_order_show_opens_without_error_for_roles(): void
    {
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/IT/2026/09/0001',
            'user_id' => $this->requester->id,
            'department_id' => $this->deptIt->id,
            'title' => 'Pengadaan Laptop Kantor',
            'description' => 'Laptop untuk tim IT',
            'required_date' => now()->addDays(7)->toDateString(),
            'estimated_total' => 15000000,
            'status' => 'processing',
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-2026-0001',
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->vendor->id,
            'issued_by' => $this->procurement->id,
            'order_date' => now()->toDateString(),
            'delivery_target_date' => now()->addDays(14)->toDateString(),
            'subtotal' => 15000000,
            'tax_rate' => 11,
            'tax_calculation_mode' => 'header_subtotal',
            'tax_rounding_tolerance' => 100,
            'shipping_fee' => 0,
            'tax_amount' => 1650000,
            'tax_rounding_difference' => 0,
            'grand_total' => 16650000,
            'over_delivery_tolerance_percentage' => 5,
            'payment_terms' => 'Net 30 Hari',
            'status' => 'issued',
        ]);

        $po->items()->create([
            'item_name' => 'ThinkPad T14',
            'quantity' => 1,
            'unit' => 'Unit',
            'unit_price' => 15000000,
            'subtotal' => 15000000,
            'received_quantity' => 0,
            'over_delivery_tolerance_percentage' => 5,
            'over_delivered_quantity' => 0,
        ]);

        // Procurement can open
        $resProc = $this->actingAs($this->procurement)->get(route('purchase-orders.show', $po));
        $resProc->assertOk();
        $resProc->assertSee('PO-2026-0001');

        // Admin can open
        $resAdmin = $this->actingAs($this->admin)->get(route('purchase-orders.show', $po));
        $resAdmin->assertOk();

        // Warehouse can open
        $resWh = $this->actingAs($this->warehouse)->get(route('purchase-orders.show', $po));
        $resWh->assertOk();
    }

    /**
     * Test 2: Goods Receipt Show page opens without error and displays received items
     */
    public function test_goods_receipt_show_opens_without_error(): void
    {
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/IT/2026/09/0002',
            'user_id' => $this->requester->id,
            'department_id' => $this->deptIt->id,
            'title' => 'Pengadaan PC Server',
            'description' => 'Server mini rack',
            'required_date' => now()->addDays(7)->toDateString(),
            'estimated_total' => 20000000,
            'status' => 'processing',
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-2026-0002',
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->vendor->id,
            'issued_by' => $this->procurement->id,
            'order_date' => now()->toDateString(),
            'subtotal' => 20000000,
            'tax_rate' => 11,
            'tax_calculation_mode' => 'header_subtotal',
            'tax_rounding_tolerance' => 100,
            'shipping_fee' => 0,
            'tax_amount' => 2200000,
            'tax_rounding_difference' => 0,
            'grand_total' => 22200000,
            'over_delivery_tolerance_percentage' => 5,
            'payment_terms' => 'Net 30 Hari',
            'status' => 'partially_received',
        ]);

        $poItem = $po->items()->create([
            'item_name' => 'Server Dell PowerEdge',
            'quantity' => 2,
            'unit' => 'Unit',
            'unit_price' => 10000000,
            'subtotal' => 20000000,
            'received_quantity' => 1,
            'over_delivery_tolerance_percentage' => 5,
            'over_delivered_quantity' => 0,
        ]);

        $gr = GoodsReceipt::create([
            'gr_number' => 'GR-2026-0001',
            'purchase_order_id' => $po->id,
            'received_by' => $this->warehouse->id,
            'received_date' => now()->toDateString(),
            'item_condition' => 'good',
            'delivery_note_no' => 'SJ-VENDOR-991',
            'is_service' => false,
            'status' => 'partially_received',
        ]);

        $gr->items()->create([
            'po_item_id' => $poItem->id,
            'received_unit' => 'Unit',
            'raw_quantity_received' => 1,
            'raw_quantity_rejected' => 0,
            'conversion_multiplier' => 1,
            'quantity_received' => 1,
            'quantity_rejected' => 0,
            'is_over_delivery' => false,
            'over_delivery_quantity' => 0,
            'notes' => 'Tiba dalam kondisi baik',
        ]);

        $response = $this->actingAs($this->warehouse)->get(route('goods-receipts.show', $gr));
        $response->assertOk();
        $response->assertSee('GR-2026-0001');
        $response->assertSee('Server Dell PowerEdge');
    }

    /**
     * Test 3: PO Print has 3-column signature block and no "Format Dokumen Resmi Korporat • Standar Enterprise A4"
     */
    public function test_purchase_order_print_has_clean_signature_and_no_unwanted_text(): void
    {
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/IT/2026/09/0003',
            'user_id' => $this->requester->id,
            'department_id' => $this->deptIt->id,
            'title' => 'Pengadaan Monitor',
            'description' => 'Monitor 4K',
            'required_date' => now()->addDays(7)->toDateString(),
            'estimated_total' => 5000000,
            'status' => 'processing',
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-2026-0003',
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->vendor->id,
            'issued_by' => $this->procurement->id,
            'order_date' => now()->toDateString(),
            'subtotal' => 5000000,
            'tax_rate' => 11,
            'tax_calculation_mode' => 'header_subtotal',
            'tax_rounding_tolerance' => 100,
            'shipping_fee' => 0,
            'tax_amount' => 550000,
            'tax_rounding_difference' => 0,
            'grand_total' => 5550000,
            'over_delivery_tolerance_percentage' => 5,
            'payment_terms' => 'Net 30 Hari',
            'status' => 'issued',
        ]);

        $response = $this->actingAs($this->procurement)->get(route('purchase-orders.print', $po));
        $response->assertOk();

        // Must NOT contain the unwanted watermark text
        $response->assertDontSee('Standar Enterprise A4');
        $response->assertDontSee('Format Dokumen Resmi Korporat &bull; Standar Enterprise A4', false);

        // Must contain official 3-column signature block with blank signature area
        $response->assertSee('Dibuat &amp; Diterbitkan Oleh:', false);
        $response->assertSee('Disetujui Oleh (Otorisasi):', false);
        $response->assertSee('Diterima &amp; Dikonfirmasi Rekanan:', false);
        $response->assertSee($this->procurement->name);
        $response->assertSee($this->vendor->name);
    }

    /**
     * Test 4: HoD can access /approvals without 403 Forbidden
     */
    public function test_hod_role_can_access_approval_queue(): void
    {
        $response = $this->actingAs($this->hod)->get(route('approvals.index'));
        $response->assertOk();
        $response->assertSee('Antrean Persetujuan');
    }

    /**
     * Test 5: Sequential approval: PR > 25 Jt must NOT enter Finance or HoD until Manager approves
     */
    public function test_sequential_approval_prevents_premature_queuing_for_higher_tiers(): void
    {
        $approvalService = app(ApprovalService::class);

        // Create high-value PR (> 25M, e.g. 50M)
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/IT/2026/09/0004',
            'user_id' => $this->requester->id,
            'department_id' => $this->deptIt->id,
            'title' => 'Pengadaan Infrastruktur Jaringan Korporat',
            'description' => 'Core switch dan firewall',
            'required_date' => now()->addDays(14)->toDateString(),
            'estimated_total' => 50000000,
            'status' => 'submitted',
        ]);

        $approvalService->generateApprovalTiers($pr);

        // 1. Initial State: Only Tahap 1 (Manager IT) should have it in queue!
        $managerQueue = $approvalService->getPendingQueueForUser($this->managerIt);
        $financeQueue = $approvalService->getPendingQueueForUser($this->finance);
        $hodQueue = $approvalService->getPendingQueueForUser($this->hod);

        $this->assertTrue($managerQueue->contains('id', $pr->id), 'Manager IT must see PR at Tahap 1');
        $this->assertFalse($financeQueue->contains('id', $pr->id), 'Finance must NOT see PR while waiting for Manager');
        $this->assertFalse($hodQueue->contains('id', $pr->id), 'HoD must NOT see PR while waiting for Manager');

        // 2. Manager IT approves Tahap 1
        $approvalService->approve($this->managerIt, $pr, 'Disetujui spesifikasi teknis.');
        $pr->refresh();

        $managerQueueAfter = $approvalService->getPendingQueueForUser($this->managerIt);
        $financeQueueAfter = $approvalService->getPendingQueueForUser($this->finance);
        $hodQueueAfter = $approvalService->getPendingQueueForUser($this->hod);

        $this->assertFalse($managerQueueAfter->contains('id', $pr->id), 'Manager IT has completed their stage');
        $this->assertTrue($financeQueueAfter->contains('id', $pr->id), 'Finance now sees PR at Tahap 2');
        $this->assertFalse($hodQueueAfter->contains('id', $pr->id), 'HoD must NOT see PR until Finance approves');

        // 3. Finance approves Tahap 2
        $approvalService->approve($this->finance, $pr, 'Pagu anggaran tersedia dan lolos uji.');
        $pr->refresh();

        $financeQueueFinal = $approvalService->getPendingQueueForUser($this->finance);
        $hodQueueFinal = $approvalService->getPendingQueueForUser($this->hod);

        $this->assertFalse($financeQueueFinal->contains('id', $pr->id), 'Finance has completed their stage');
        $this->assertTrue($hodQueueFinal->contains('id', $pr->id), 'HoD now sees PR at Tahap 3 (> 25M)');

        // 4. HoD approves Tahap 3 -> PR completely approved
        $approvalService->approve($this->hod, $pr, 'Otorisasi Direksi diberikan.');
        $pr->refresh();

        $this->assertEquals('approved', $pr->status);
        $this->assertFalse($approvalService->getPendingQueueForUser($this->hod)->contains('id', $pr->id));
    }

    /**
     * Test 6: PR <= 25 Jt NEVER enters HoD approval queue
     */
    public function test_pr_below_25m_never_enters_hod_queue(): void
    {
        $approvalService = app(ApprovalService::class);

        // PR of 15M (between 5M and 25M: Manager -> Finance only)
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/IT/2026/09/0005',
            'user_id' => $this->requester->id,
            'department_id' => $this->deptIt->id,
            'title' => 'Pengadaan UPS Ruang Server',
            'description' => 'UPS 10kVA',
            'required_date' => now()->addDays(7)->toDateString(),
            'estimated_total' => 15000000,
            'status' => 'submitted',
        ]);

        $approvalService->generateApprovalTiers($pr);

        // Manager approves
        $approvalService->approve($this->managerIt, $pr, 'OK');
        $pr->refresh();

        // HoD must not see it
        $this->assertFalse($approvalService->getPendingQueueForUser($this->hod)->contains('id', $pr->id));

        // Finance approves
        $approvalService->approve($this->finance, $pr, 'OK Anggaran');
        $pr->refresh();

        // PR is approved directly without HoD
        $this->assertEquals('approved', $pr->status);
        $this->assertFalse($approvalService->getPendingQueueForUser($this->hod)->contains('id', $pr->id));
    }

    /**
     * Test 7: Warehouse role can access Goods Receipts creation but not PR approval
     */
    public function test_warehouse_role_permissions(): void
    {
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/IT/2026/09/0006',
            'user_id' => $this->requester->id,
            'department_id' => $this->deptIt->id,
            'title' => 'Pengadaan Switch Rack',
            'description' => 'Switch',
            'required_date' => now()->addDays(7)->toDateString(),
            'estimated_total' => 10000000,
            'status' => 'processing',
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-2026-0006',
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->vendor->id,
            'issued_by' => $this->procurement->id,
            'order_date' => now()->toDateString(),
            'subtotal' => 10000000,
            'tax_rate' => 11,
            'tax_calculation_mode' => 'header_subtotal',
            'tax_rounding_tolerance' => 100,
            'shipping_fee' => 0,
            'tax_amount' => 1100000,
            'tax_rounding_difference' => 0,
            'grand_total' => 11100000,
            'over_delivery_tolerance_percentage' => 5,
            'payment_terms' => 'Net 30 Hari',
            'status' => 'issued',
        ]);

        $po->items()->create([
            'item_name' => 'Switch 24P',
            'quantity' => 1,
            'unit' => 'Unit',
            'unit_price' => 10000000,
            'subtotal' => 10000000,
            'received_quantity' => 0,
            'over_delivery_tolerance_percentage' => 5,
            'over_delivered_quantity' => 0,
        ]);

        // Warehouse can access goods receipts creation with valid PO
        $resGrCreate = $this->actingAs($this->warehouse)->get(route('goods-receipts.create', ['purchase_order_id' => $po->id]));
        $resGrCreate->assertOk();

        // Warehouse cannot access approval queue
        $resApprovals = $this->actingAs($this->warehouse)->get(route('approvals.index'));
        $resApprovals->assertForbidden();

        // Requester cannot access approval queue
        $resReqApprovals = $this->actingAs($this->requester)->get(route('approvals.index'));
        $resReqApprovals->assertForbidden();
    }

    /**
     * Test 8: Notification targeting strictly routes to current tier approvers only
     */
    public function test_notification_targeting_per_tier(): void
    {
        $approvalService = app(ApprovalService::class);

        // PR 30 Juta (Requires Manager -> Finance -> HoD)
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/IT/2026/09/0099',
            'user_id' => $this->requester->id,
            'department_id' => $this->deptIt->id,
            'title' => 'Pengadaan Server DC 30M',
            'description' => 'Server Capex',
            'required_date' => now()->addDays(7)->toDateString(),
            'estimated_total' => 30000000,
            'status' => 'draft',
        ]);

        $pr->items()->create([
            'item_name' => 'Server DC',
            'quantity' => 1,
            'unit' => 'Unit',
            'estimated_price' => 30000000,
            'subtotal' => 30000000,
        ]);

        // 1. Submit PR (Tier 1: Manager)
        $pr->update(['status' => 'submitted']);
        $approvalService->generateApprovalTiers($pr);
        $pr->refresh();

        // Manager must receive notification
        $managerNotif = \App\Models\InAppNotification::where('user_id', $this->managerIt->id)->latest()->first();
        $this->assertNotNull($managerNotif);
        $this->assertStringContainsString('Otorisasi Manager', $managerNotif->title);
        $this->assertStringContainsString('#approval-action', $managerNotif->link);

        // Finance and HoD and Admin must NOT have notification for this PR
        $this->assertNull(\App\Models\InAppNotification::where('user_id', $this->finance->id)->where('link', 'like', "%{$pr->id}%")->first());
        $this->assertNull(\App\Models\InAppNotification::where('user_id', $this->hod->id)->where('link', 'like', "%{$pr->id}%")->first());
        $this->assertNull(\App\Models\InAppNotification::where('user_id', $this->admin->id)->where('link', 'like', "%{$pr->id}%")->first());

        // 2. Manager Approves -> Tier 2: Finance
        $approvalService->approve($this->managerIt, $pr, 'Setuju Manager');
        $pr->refresh();

        $financeNotif = \App\Models\InAppNotification::where('user_id', $this->finance->id)->latest()->first();
        $this->assertNotNull($financeNotif);
        $this->assertStringContainsString('Otorisasi Keuangan', $financeNotif->title);

        // HoD and Admin still must NOT have notification
        $this->assertNull(\App\Models\InAppNotification::where('user_id', $this->hod->id)->where('link', 'like', "%{$pr->id}%")->first());
        $this->assertNull(\App\Models\InAppNotification::where('user_id', $this->admin->id)->where('link', 'like', "%{$pr->id}%")->first());

        // 3. Finance Approves -> Tier 3: HoD
        $approvalService->approve($this->finance, $pr, 'Setuju Finance');
        $pr->refresh();

        $hodNotif = \App\Models\InAppNotification::where('user_id', $this->hod->id)->latest()->first();
        $this->assertNotNull($hodNotif);
        $this->assertStringContainsString('Otorisasi Direksi/HoD', $hodNotif->title);

        // Admin must NOT have notification (Admin is not HoD)
        $this->assertNull(\App\Models\InAppNotification::where('user_id', $this->admin->id)->where('link', 'like', "%{$pr->id}%")->first());
    }

    /**
     * Test 9: Navigation menu role segregation and removal of GCG matrix card
     */
    public function test_navigation_menu_role_segregation_and_clean_approvals(): void
    {
        // HoD sees dashboard: NO "Faktur & 3-Way Match", NO GCG matrix
        $resHod = $this->actingAs($this->hod)->get(route('dashboard'));
        $resHod->assertOk();
        $resHod->assertDontSee('Faktur &amp; 3-Way Match', false);
        $resHod->assertDontSee('Tata Kelola GCG', false);

        // Approvals index for HoD: NO GCG matrix card
        $resApprovals = $this->actingAs($this->hod)->get(route('approvals.index'));
        $resApprovals->assertOk();
        $resApprovals->assertDontSee('Tata Kelola GCG', false);
        $resApprovals->assertDontSee('Matriks Ambang Batas Otorisasi Bertingkat', false);

        // Requester: NO "Faktur & 3-Way Match", NO "Antrean Approval", NO "Delegasi Plt"
        $resReq = $this->actingAs($this->requester)->get(route('dashboard'));
        $resReq->assertOk();
        $resReq->assertDontSee('Faktur &amp; 3-Way Match', false);
        $resReq->assertDontSee('Antrean Approval', false);
        $resReq->assertDontSee('Delegasi Plt', false);

        // Finance: DOES see "Faktur & 3-Way Match", but DOES NOT see "Direktori Vendor" or "RFQ & Komparasi"
        $resFin = $this->actingAs($this->finance)->get(route('dashboard'));
        $resFin->assertOk();
        $resFin->assertSee('Faktur &amp; 3-Way Match', false);
        $resFin->assertDontSee('Direktori Vendor', false);
        $resFin->assertDontSee('RFQ &amp; Komparasi', false);

        // HoD: DOES NOT see "Direktori Vendor" or "RFQ & Komparasi"
        $resHod->assertDontSee('Direktori Vendor', false);
        $resHod->assertDontSee('RFQ &amp; Komparasi', false);
    }

    /**
     * Test 10: Finance can reject PR from another department without 403 forbidden error
     */
    public function test_finance_can_reject_pr_from_other_department_without_403(): void
    {
        $approvalService = app(ApprovalService::class);

        // PR from IT department (10M -> Manager IT -> Finance)
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/IT/2026/09/0100',
            'user_id' => $this->requester->id,
            'department_id' => $this->deptIt->id,
            'title' => 'Pengadaan Lisensi Software IT',
            'description' => 'Lisensi tahunan',
            'required_date' => now()->addDays(7)->toDateString(),
            'estimated_total' => 10000000,
            'status' => 'submitted',
        ]);

        $pr->items()->create([
            'item_name' => 'Software License',
            'quantity' => 1,
            'unit' => 'Lisensi',
            'estimated_price' => 10000000,
            'subtotal' => 10000000,
        ]);

        $approvalService->generateApprovalTiers($pr);

        // Manager IT approves Tier 1
        $approvalService->approve($this->managerIt, $pr, 'Disetujui Manager IT');
        $pr->refresh();

        // Active tier is now Tier 2: Finance
        $activeTier = $pr->currentPendingApproval();
        $this->assertEquals(2, $activeTier->tier_level);
        $this->assertEquals('finance', $activeTier->role_required);

        // Finance officer rejects the PR
        $rejectResponse = $this->actingAs($this->finance)
            ->post(route('approvals.reject', $pr), [
                'notes' => 'Alokasi anggaran IT kuartal ini telah habis, mohon tunda.',
            ]);

        // Must redirect to PR show
        $rejectResponse->assertRedirect(route('purchase-requests.show', $pr));
        $rejectResponse->assertSessionHas('success');

        // Following redirect MUST NOT throw 403 (was throwing 403 previously!)
        $showResponse = $this->actingAs($this->finance)->get(route('purchase-requests.show', $pr));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Ditolak');
        $showResponse->assertSee('Alokasi anggaran IT kuartal ini telah habis');

        $pr->refresh();
        $this->assertEquals('rejected', $pr->status);
    }
}
