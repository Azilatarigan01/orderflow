<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\PrApproval;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\User;
use App\Models\Vendor;
use App\Services\ApprovalService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoreWorkflowAutomatedTest extends TestCase
{
    use RefreshDatabase;

    protected Department $deptIt;
    protected User $requester;
    protected User $manager;
    protected User $finance;
    protected User $procurement;
    protected User $warehouse;
    protected Vendor $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deptIt = Department::create(['name' => 'Information Technology', 'code' => 'IT']);

        $this->requester = User::factory()->create([
            'name' => 'Requester User',
            'email' => 'requester@orderflow.test',
            'role' => 'requester',
            'department_id' => $this->deptIt->id,
            'password' => bcrypt('password123'),
        ]);

        $this->manager = User::factory()->create([
            'name' => 'IT Manager',
            'email' => 'manager@orderflow.test',
            'role' => 'manager',
            'department_id' => $this->deptIt->id,
            'password' => bcrypt('password123'),
        ]);

        $this->finance = User::factory()->create([
            'name' => 'Finance Officer',
            'email' => 'finance@orderflow.test',
            'role' => 'finance',
            'department_id' => $this->deptIt->id,
            'password' => bcrypt('password123'),
        ]);

        $this->procurement = User::factory()->create([
            'name' => 'Procurement Officer',
            'email' => 'procurement@orderflow.test',
            'role' => 'procurement',
            'department_id' => $this->deptIt->id,
            'password' => bcrypt('password123'),
        ]);

        $this->warehouse = User::factory()->create([
            'name' => 'Warehouse Staff',
            'email' => 'warehouse@orderflow.test',
            'role' => 'warehouse',
            'department_id' => $this->deptIt->id,
            'password' => bcrypt('password123'),
        ]);

        $this->vendor = Vendor::create([
            'code' => 'VND-001',
            'name' => 'PT Mitra Komputindo Solusindo',
            'category' => 'hardware',
            'contact_person' => 'Budi Santoso',
            'phone' => '081234567890',
            'email' => 'sales@mitrakomputindo.co.id',
            'address' => 'Jl. Sudirman No. 45, Jakarta',
            'is_active' => true,
        ]);
    }

    /**
     * 1. Automated Test: Login dan Logout
     */
    public function test_login_and_logout_flow(): void
    {
        // Login attempt with valid credentials
        $loginResponse = $this->post(route('login'), [
            'email' => 'requester@orderflow.test',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($this->requester);
        $loginResponse->assertRedirect(route('dashboard'));

        // Logout attempt
        $logoutResponse = $this->post(route('logout'));
        $this->assertGuest();
        $logoutResponse->assertRedirect('/');
    }

    /**
     * 2. Automated Test: Role dan Permission
     */
    public function test_role_and_permission_enforcement(): void
    {
        // Requester cannot access vendor creation (restricted to procurement/admin)
        $this->actingAs($this->requester);
        $response = $this->get(route('vendors.create'));
        $response->assertStatus(403);

        // Procurement can access vendor creation
        $this->actingAs($this->procurement);
        $allowedResponse = $this->get(route('vendors.create'));
        $allowedResponse->assertStatus(200);
    }

    /**
     * 3. Automated Test: Membuat PR dan Item
     */
    public function test_create_pr_and_items(): void
    {
        $this->actingAs($this->requester);

        $payload = [
            'title' => 'Pengadaan Laptop Workstation IT',
            'description' => 'Penggantian workstation lama tim developer',
            'purpose' => 'Kebutuhan komputasi developer AI',
            'required_date' => Carbon::now()->addDays(14)->toDateString(),
            'items' => [
                [
                    'item_name' => 'Laptop Workstation 32GB',
                    'specification' => 'Core i7, 32GB RAM, 1TB NVMe',
                    'quantity' => 2,
                    'unit' => 'Unit',
                    'estimated_unit_price' => '15.000.000',
                ],
                [
                    'item_name' => 'Mouse Wireless Ergonomic',
                    'specification' => 'Bluetooth Silent',
                    'quantity' => 2,
                    'unit' => 'Pcs',
                    'estimated_unit_price' => '500.000',
                ],
            ],
        ];

        $response = $this->post(route('purchase-requests.store'), $payload);

        $this->assertDatabaseHas('purchase_requests', [
            'title' => 'Pengadaan Laptop Workstation IT',
            'user_id' => $this->requester->id,
            'department_id' => $this->deptIt->id,
        ]);

        $pr = PurchaseRequest::where('title', 'Pengadaan Laptop Workstation IT')->first();
        $this->assertCount(2, $pr->items);
        $this->assertDatabaseHas('purchase_request_items', [
            'purchase_request_id' => $pr->id,
            'item_name' => 'Laptop Workstation 32GB',
            'quantity' => 2,
        ]);
    }

    /**
     * 4. Automated Test: Perhitungan Total
     */
    public function test_automatic_calculation_of_totals(): void
    {
        $this->actingAs($this->requester);

        $payload = [
            'title' => 'Uji Perhitungan Otomatis PR',
            'description' => 'Verifikasi akurasi rumus kalkulasi',
            'purpose' => 'Pengujian akurasi perkalian dan penjumlahan',
            'required_date' => Carbon::now()->addDays(7)->toDateString(),
            'items' => [
                [
                    'item_name' => 'Item A',
                    'quantity' => 3,
                    'unit' => 'Unit',
                    'estimated_unit_price' => '2.500.000', // Subtotal: 7.500.000
                ],
                [
                    'item_name' => 'Item B',
                    'quantity' => 4,
                    'unit' => 'Unit',
                    'estimated_unit_price' => '1.250.000', // Subtotal: 5.000.000
                ],
            ],
        ];

        $this->post(route('purchase-requests.store'), $payload);

        $pr = PurchaseRequest::where('title', 'Uji Perhitungan Otomatis PR')->first();
        $this->assertNotNull($pr);

        // Expected Grand Total = 7.500.000 + 5.000.000 = 12.500.000
        $this->assertEquals(12500000, (float) $pr->estimated_total);

        $items = $pr->items()->orderBy('item_name')->get();
        $this->assertEquals(7500000, (float) $items[0]->subtotal);
        $this->assertEquals(5000000, (float) $items[1]->subtotal);
    }

    /**
     * 5. Automated Test: Approval Sesuai Urutan (Sequential Approval Flow)
     */
    public function test_approval_in_strict_sequential_order(): void
    {
        $approvalService = app(ApprovalService::class);

        // Create PR with total 15.000.000 -> Requires Tier 1 (Manager) & Tier 2 (Finance)
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR-SEQ-001',
            'user_id' => $this->requester->id,
            'department_id' => $this->deptIt->id,
            'title' => 'Pengadaan Server Ramping',
            'description' => 'Server database',
            'required_date' => Carbon::now()->addDays(10)->toDateString(),
            'estimated_total' => 15000000,
            'status' => 'submitted',
        ]);

        $approvalService->generateApprovalTiers($pr);

        $tiers = $pr->approvals()->orderBy('tier_level')->get();
        $this->assertCount(2, $tiers);
        $this->assertEquals(1, $tiers[0]->tier_level);
        $this->assertEquals(2, $tiers[1]->tier_level);

        // Finance (Tier 2) tries to approve prematurely while Tier 1 is still pending -> Must fail (403 Forbidden)
        $this->actingAs($this->finance);
        $prematureResponse = $this->post(route('approvals.approve', $pr->id), [
            'notes' => 'Persetujuan prematur sebelum Manager',
        ]);
        $prematureResponse->assertStatus(403);
        $this->assertEquals('pending', $tiers[1]->fresh()->status);
        $this->assertEquals('submitted', $pr->fresh()->status);

        // Manager (Tier 1) approves first -> Success
        $this->actingAs($this->manager);
        $this->post(route('approvals.approve', $pr->id), [
            'notes' => 'Disetujui Manager IT',
        ]);
        $this->assertEquals('approved', $tiers[0]->fresh()->status);

        // Now Finance (Tier 2) approves second -> Success and PR status becomes 'approved'
        $this->actingAs($this->finance);
        $this->post(route('approvals.approve', $pr->id), [
            'notes' => 'Anggaran diverifikasi Finance',
        ]);
        $this->assertEquals('approved', $tiers[1]->fresh()->status);
        $this->assertEquals('approved', $pr->fresh()->status);
    }

    /**
     * 6. Automated Test: Requester Tidak Dapat Approve PR Sendiri (Segregation of Duties)
     */
    public function test_requester_cannot_approve_own_pr(): void
    {
        $approvalService = app(ApprovalService::class);

        $pr = PurchaseRequest::create([
            'pr_number' => 'PR-SOD-001',
            'user_id' => $this->requester->id,
            'department_id' => $this->deptIt->id,
            'title' => 'Pengajuan Sendiri',
            'description' => 'Coba approve sendiri',
            'required_date' => Carbon::now()->addDays(7)->toDateString(),
            'estimated_total' => 5000000,
            'status' => 'submitted',
        ]);

        $approvalService->generateApprovalTiers($pr);

        // Requester attempts to trigger approval endpoint on their own PR
        $this->actingAs($this->requester);
        $response = $this->post(route('approvals.approve', $pr->id), [
            'notes' => 'Saya approve PR saya sendiri',
        ]);

        // Access must be denied or blocked
        $response->assertStatus(403);
        $this->assertEquals('submitted', $pr->fresh()->status);
    }

    /**
     * 7. Automated Test: PO Hanya Dari PR Approved
     */
    public function test_po_can_only_be_issued_from_approved_pr(): void
    {
        $this->actingAs($this->procurement);

        // Draft PR
        $prDraft = PurchaseRequest::create([
            'pr_number' => 'PR-DRAFT-001',
            'user_id' => $this->requester->id,
            'department_id' => $this->deptIt->id,
            'title' => 'PR Masih Draft',
            'description' => 'Draft description',
            'required_date' => Carbon::now()->addDays(5)->toDateString(),
            'estimated_total' => 2000000,
            'status' => 'draft',
        ]);

        // Submitted PR (not yet approved)
        $prSubmitted = PurchaseRequest::create([
            'pr_number' => 'PR-SUBMITTED-001',
            'user_id' => $this->requester->id,
            'department_id' => $this->deptIt->id,
            'title' => 'PR Sedang Menunggu Approval',
            'description' => 'Submitted description',
            'required_date' => Carbon::now()->addDays(5)->toDateString(),
            'estimated_total' => 2000000,
            'status' => 'submitted',
        ]);

        // Attempting to access PO creation for unapproved PRs must fail or redirect with validation error
        $responseDraft = $this->get(route('purchase-orders.create', ['purchase_request_id' => $prDraft->id]));
        $responseDraft->assertSessionHas('error');

        $responseSubmitted = $this->get(route('purchase-orders.create', ['purchase_request_id' => $prSubmitted->id]));
        $responseSubmitted->assertSessionHas('error');
    }

    /**
     * 8. Automated Test: Jumlah Receipt Tidak Melebihi PO
     */
    public function test_receipt_quantity_cannot_exceed_po_quantity(): void
    {
        $this->actingAs($this->warehouse);

        $pr = PurchaseRequest::create([
            'pr_number' => 'PR-RCV-001',
            'user_id' => $this->requester->id,
            'department_id' => $this->deptIt->id,
            'title' => 'Pengadaan Printer Gudang',
            'description' => 'Printer barcode',
            'required_date' => Carbon::now()->addDays(7)->toDateString(),
            'estimated_total' => 5000000,
            'status' => 'approved',
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-RCV-001',
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->vendor->id,
            'created_by' => $this->procurement->id,
            'subtotal' => 5000000,
            'tax_amount' => 550000,
            'grand_total' => 5550000,
            'status' => 'issued',
            'order_date' => Carbon::now()->toDateString(),
            'issued_at' => Carbon::now(),
            'delivery_target_date' => Carbon::now()->addDays(5),
            'over_delivery_tolerance_percentage' => 5.0,
        ]);

        $poItem = \App\Models\PoItem::create([
            'purchase_order_id' => $po->id,
            'item_name' => 'Printer Thermal Barcode',
            'quantity' => 10,
            'received_quantity' => 0,
            'unit' => 'Unit',
            'unit_price' => 500000,
            'subtotal' => 5000000,
            'over_delivery_tolerance_percentage' => 5.0,
        ]);

        // Attempt to receive 20 units (exceeding PO quantity of 10 and max tolerance of 10.5)
        $invalidPayload = [
            'purchase_order_id' => $po->id,
            'receipt_type' => 'goods',
            'delivery_note_no' => 'DO-EXCEED-001',
            'received_date' => Carbon::now()->toDateString(),
            'item_condition' => 'good',
            'notes' => 'Penerimaan melebihi pesanan',
            'items' => [
                [
                    'po_item_id' => $poItem->id,
                    'quantity_received' => 20, // 20 > 10.5
                ],
            ],
        ];

        $response = $this->post(route('goods-receipts.store'), $invalidPayload);
        $response->assertSessionHasErrors('items');

        // Assert no goods receipt created
        $this->assertDatabaseMissing('goods_receipts', [
            'delivery_note_no' => 'DO-EXCEED-001',
        ]);
    }
}
