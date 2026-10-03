<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\MeasurementUnit;
use App\Models\PrApproval;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Quotation;
use App\Models\User;
use App\Models\Vendor;
use App\Services\ApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnterpriseWorkflowEdgeCasesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $managerIt;
    protected User $managerSuccessor;
    protected User $finance;
    protected User $procurement;
    protected User $requester;
    protected Department $deptIt;
    protected Vendor $activeVendor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deptIt = Department::create([
            'name' => 'Information Technology',
            'code' => 'IT',
        ]);

        $this->admin = User::factory()->create([
            'name' => 'System Admin',
            'email' => 'admin@orderflow.local',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->managerIt = User::factory()->create([
            'name' => 'Manager IT Lama',
            'email' => 'manager.old@orderflow.local',
            'role' => 'manager',
            'department_id' => $this->deptIt->id,
            'is_active' => true,
        ]);

        $this->managerSuccessor = User::factory()->create([
            'name' => 'Manager IT Baru (Pengganti)',
            'email' => 'manager.new@orderflow.local',
            'role' => 'manager',
            'department_id' => $this->deptIt->id,
            'is_active' => true,
        ]);

        $this->deptIt->update(['manager_id' => $this->managerIt->id]);

        $this->finance = User::factory()->create([
            'name' => 'Finance Officer',
            'email' => 'finance@orderflow.local',
            'role' => 'finance',
            'is_active' => true,
        ]);

        $this->procurement = User::factory()->create([
            'name' => 'Procurement Officer',
            'email' => 'proc@orderflow.local',
            'role' => 'procurement',
            'is_active' => true,
        ]);

        $this->requester = User::factory()->create([
            'name' => 'Requester Staff',
            'email' => 'requester@orderflow.local',
            'role' => 'requester',
            'department_id' => $this->deptIt->id,
            'is_active' => true,
        ]);

        $this->activeVendor = Vendor::create([
            'code' => 'VND-001',
            'name' => 'PT Mitra Solusi Prima',
            'category' => 'Hardware & IT',
            'contact_person' => 'Budi Santoso',
            'email' => 'budi@mitraprima.local',
            'phone' => '08123456789',
            'address' => 'Jl. Sudirman Kav 21',
            'is_active' => true,
        ]);
    }

    /**
     * Case 1: Approver Resign / Mutasi - Reassign pending approval & batch transfer
     */
    public function test_approver_reassign_by_admin_on_resignation_or_mutation(): void
    {
        $approvalService = app(ApprovalService::class);

        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/IT/2026/09/0001',
            'user_id' => $this->requester->id,
            'department_id' => $this->deptIt->id,
            'title' => 'Pengadaan Laptop DevOps',
            'description' => 'Laptop spek tinggi',
            'required_date' => now()->addDays(7)->toDateString(),
            'estimated_total' => 15000000,
            'status' => 'submitted',
        ]);

        $approvalService->generateApprovalTiers($pr);
        $tier1 = $pr->approvals()->where('tier_level', 1)->first();
        $this->assertNotNull($tier1);
        $this->assertEquals('pending', $tier1->status);

        // Former manager resigns and account is deactivated
        $this->managerIt->update(['is_active' => false]);

        // Attempting to approve as deactivated manager fails
        $this->assertFalse($approvalService->canUserApprove($this->managerIt, $pr));

        // Admin reassigns the pending tier to the successor manager
        $response = $this->actingAs($this->admin)->post(route('approvals.reassign', $tier1), [
            'new_approver_id' => $this->managerSuccessor->id,
            'reassign_reason' => 'Manager IT sebelumnya telah mengundurkan diri (resign). Mandat dialihkan ke pejabat baru.',
        ]);

        $response->assertSessionHas('success');
        $tier1->refresh();

        $this->assertEquals($this->managerSuccessor->id, $tier1->assigned_approver_id);
        $this->assertEquals($this->admin->id, $tier1->reassigned_by);
        $this->assertStringContainsString('mengundurkan diri', $tier1->reassign_reason);

        // Successor manager can now approve the PR
        $this->assertTrue($approvalService->canUserApprove($this->managerSuccessor, $pr));

        // Successor approves
        $approvalService->approve($this->managerSuccessor, $pr, 'Disetujui oleh Manager Pengganti');
        $tier1->refresh();
        $this->assertEquals('approved', $tier1->status);
        $this->assertEquals($this->managerSuccessor->id, $tier1->approver_id);
    }

    /**
     * Case 2: Tender Gagal (All Vendors Decline / Over Budget)
     * Procurement cancels tender, returns PR to requester for revision
     */
    public function test_procurement_can_cancel_rfq_and_return_pr_when_all_vendors_fail_or_over_budget(): void
    {
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/IT/2026/09/0002',
            'user_id' => $this->requester->id,
            'department_id' => $this->deptIt->id,
            'title' => 'Pengadaan Server Rackmount',
            'description' => 'Server 2U',
            'required_date' => now()->addDays(7)->toDateString(),
            'estimated_total' => 20000000,
            'status' => 'approved',
        ]);

        // Vendors submit quotation that exceeds budget or out of stock
        $q1 = Quotation::create([
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->activeVendor->id,
            'quotation_number' => 'QTO-OVER-01',
            'subtotal' => 35000000, // 35M vs 20M budget
            'grand_total' => 35000000,
            'estimated_delivery_days' => 7,
            'warranty_months' => 12,
        ]);

        // Procurement cancels tender and returns PR
        $response = $this->actingAs($this->procurement)->post(route('quotations.fail-tender', $pr), [
            'failure_category' => 'over_budget',
            'failure_reason' => 'Semua penawaran vendor yang masuk melebihi pagu anggaran sebesar Rp 35.000.000 (Pagu PR hanya Rp 20.000.000). Mohon disesuaikan estimasi biaya atau kurangi spesifikasi hardware.',
        ]);

        $response->assertRedirect(route('purchase-requests.show', $pr));
        $response->assertSessionHas('success');

        $pr->refresh();
        $this->assertEquals('revision_required', $pr->status);

        // Status history is recorded with clear category and audit note
        $latestHistory = $pr->statusHistories()->latest()->first();
        $this->assertNotNull($latestHistory);
        $this->assertEquals('revision_required', $latestHistory->to_status);
        $this->assertStringContainsString('Seluruh Penawaran Melampaui Pagu Anggaran', $latestHistory->notes);
        $this->assertStringContainsString('35.000.000', $latestHistory->notes);

        // Requester receives notification to revise PR
        $this->assertDatabaseHas('in_app_notifications', [
            'user_id' => $this->requester->id,
            'type' => 'rfq_failed',
        ]);
    }

    /**
     * Case 3: Deadlock Partial Receipt - Short Close PO
     * PO with 5 items ordered, 4 received, remainder discontinued -> Short Close resolves PO to completed
     */
    public function test_short_close_po_resolves_deadlock_partial_receipt(): void
    {
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/IT/2026/09/0003',
            'user_id' => $this->requester->id,
            'department_id' => $this->deptIt->id,
            'title' => 'Pengadaan 5 Unit Workstation',
            'description' => 'Workstation',
            'required_date' => now()->addDays(7)->toDateString(),
            'estimated_total' => 25000000,
            'status' => 'processing',
        ]);

        $prItem = $pr->items()->create([
            'item_name' => 'Workstation Pro',
            'quantity' => 5,
            'unit' => 'Unit',
            'estimated_price' => 5000000,
            'subtotal' => 25000000,
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-2026-0003',
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->activeVendor->id,
            'issued_by' => $this->procurement->id,
            'order_date' => now()->toDateString(),
            'subtotal' => 25000000,
            'tax_rate' => 11,
            'tax_calculation_mode' => 'line_item',
            'tax_rounding_tolerance' => 100,
            'shipping_fee' => 0,
            'tax_amount' => 2750000,
            'tax_rounding_difference' => 0,
            'grand_total' => 27750000,
            'over_delivery_tolerance_percentage' => 5,
            'payment_terms' => 'Net 30 Hari',
            'status' => 'partially_received',
        ]);

        $poItem = $po->items()->create([
            'purchase_request_item_id' => $prItem->id,
            'item_name' => 'Workstation Pro',
            'quantity' => 5,
            'unit' => 'Unit',
            'unit_price' => 5000000,
            'subtotal' => 25000000,
            'received_quantity' => 4, // 4 received, 1 will never arrive
            'over_delivery_tolerance_percentage' => 5,
            'over_delivered_quantity' => 0,
        ]);

        // Tim Procurement executes Short Close
        $response = $this->actingAs($this->procurement)->post(route('purchase-orders.short-close', $po), [
            'short_close_reason' => 'Produsen menyatakan sisa 1 unit telah discontinued secara global. Vendor tidak memiliki stok pengganti. Sisa 1 unit dibatalkan resmi dan PO ditutup berdasarkan 4 unit yang diterima.',
        ]);

        $response->assertRedirect(route('purchase-orders.show', $po));
        $response->assertSessionHas('success');

        $po->refresh();
        $this->assertEquals('completed', $po->status);
        $this->assertTrue((bool) $po->is_short_closed);
        $this->assertEquals($this->procurement->id, $po->short_closed_by);
        $this->assertNotNull($po->short_closed_at);
        $this->assertStringContainsString('discontinued secara global', $po->short_close_reason);

        // Parent PR automatically completes because all POs are resolved
        $pr->refresh();
        $this->assertEquals('completed', $pr->status);

        // Finance received notification of short close
        $this->assertDatabaseHas('in_app_notifications', [
            'user_id' => $this->finance->id,
            'type' => 'po_short_closed',
        ]);
    }

    /**
     * Case 4: Data Master Integrity Safeguards & Soft Deletes
     * Vendor with active transactions CANNOT be deleted; Inactive vendor can be safely soft-deleted
     */
    public function test_master_data_integrity_blocks_deletion_of_vendor_with_active_transactions(): void
    {
        // 1. Create active PO for vendor
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/IT/2026/09/0004',
            'user_id' => $this->requester->id,
            'department_id' => $this->deptIt->id,
            'title' => 'Pengadaan Aktif Vendor',
            'description' => 'Test active vendor deletion block',
            'required_date' => now()->addDays(7)->toDateString(),
            'estimated_total' => 10000000,
            'status' => 'processing',
        ]);

        PurchaseOrder::create([
            'po_number' => 'PO-2026-0004',
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->activeVendor->id,
            'issued_by' => $this->procurement->id,
            'order_date' => now()->toDateString(),
            'subtotal' => 10000000,
            'tax_rate' => 11,
            'tax_calculation_mode' => 'line_item',
            'tax_rounding_tolerance' => 100,
            'shipping_fee' => 0,
            'tax_amount' => 1100000,
            'tax_rounding_difference' => 0,
            'grand_total' => 11100000,
            'over_delivery_tolerance_percentage' => 5,
            'payment_terms' => 'Net 30 Hari',
            'status' => 'issued', // Active PO!
        ]);

        // Attempting to delete vendor with active PO must be BLOCKED
        $response = $this->actingAs($this->admin)->delete(route('vendors.destroy', $this->activeVendor));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('tidak dapat dihapus karena masih terikat', session('error'));

        // Vendor still exists in database
        $this->assertDatabaseHas('vendors', ['id' => $this->activeVendor->id, 'deleted_at' => null]);

        // 2. An idle vendor without transactions can be safely Soft-Deleted
        $idleVendor = Vendor::create([
            'code' => 'VND-IDLE',
            'name' => 'PT Vendor Baru Belum Transaksi',
            'category' => 'Konsultasi',
            'contact_person' => 'Rian',
            'email' => 'rian@idle.local',
            'phone' => '089999999',
            'address' => 'Jl. Gatot Subroto',
            'is_active' => true,
        ]);

        $deleteResponse = $this->actingAs($this->admin)->delete(route('vendors.destroy', $idleVendor));
        $deleteResponse->assertSessionHas('success');

        // Soft deleted: record still exists in table but deleted_at is not null
        $this->assertSoftDeleted('vendors', ['id' => $idleVendor->id]);
    }

    /**
     * Case 5: MeasurementUnit & Department models support SoftDeletes
     */
    public function test_measurement_unit_and_department_support_soft_deletes(): void
    {
        $unit = MeasurementUnit::create([
            'code' => 'TEST_BOX',
            'name' => 'Kotak Khusus',
            'category' => 'Kemasan',
            'is_active' => true,
        ]);

        $unit->delete();
        $this->assertSoftDeleted('measurement_units', ['id' => $unit->id]);

        $dept = Department::create([
            'name' => 'Divisi Riset Baru',
            'code' => 'RND',
        ]);

        $dept->delete();
        $this->assertSoftDeleted('departments', ['id' => $dept->id]);
    }
}
