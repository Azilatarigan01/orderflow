<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Services\ApprovalService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrRevisionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $requester;
    protected User $manager;
    protected User $finance;
    protected User $hod;
    protected Department $dept;
    protected ApprovalService $approvalService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->approvalService = app(ApprovalService::class);
        $this->dept = Department::create(['name' => 'Information Technology', 'code' => 'IT']);

        $this->requester = User::factory()->create([
            'role' => 'requester',
            'department_id' => $this->dept->id,
        ]);

        $this->manager = User::factory()->create([
            'role' => 'manager',
            'department_id' => $this->dept->id,
        ]);

        $this->finance = User::factory()->create([
            'role' => 'finance',
            'department_id' => null,
        ]);

        $this->hod = User::factory()->create([
            'role' => 'hod',
            'department_id' => null,
        ]);
    }

    public function test_non_material_revision_preserves_manager_approval_and_returns_directly_to_finance(): void
    {
        // 1. Create submitted PR for Rp 30,000,000 (Requires Tier 1 Manager, Tier 2 Finance, Tier 3 HoD)
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/IT/2026/09/5551',
            'user_id' => $this->requester->id,
            'department_id' => $this->dept->id,
            'title' => 'Pengadaan Server Backup Cloud',
            'description' => 'Server cadangan untuk disaster recovery.',
            'required_date' => Carbon::tomorrow(),
            'estimated_total' => 30000000,
            'status' => 'submitted',
        ]);

        $item = $pr->items()->create([
            'item_name' => 'Server Backup Unit',
            'specification' => 'Spesifikasi awal',
            'quantity' => 1,
            'unit' => 'Unit',
            'estimated_unit_price' => 30000000,
            'subtotal' => 30000000,
        ]);

        $this->approvalService->generateApprovalTiers($pr);

        $tier1 = $pr->approvals()->where('tier_level', 1)->first();
        $tier2 = $pr->approvals()->where('tier_level', 2)->first();
        $tier3 = $pr->approvals()->where('tier_level', 3)->first();

        $this->assertNotNull($tier1);
        $this->assertNotNull($tier2);
        $this->assertNotNull($tier3);

        // 2. Manager approves Tier 1
        $this->approvalService->approve($this->manager, $pr, 'Disetujui oleh Manajer IT.');
        $this->assertEquals('approved', $tier1->fresh()->status);
        $this->assertEquals('pending', $tier2->fresh()->status);

        // 3. Finance reviews Tier 2 and requests a non-financial revision (spesifikasi / lampiran)
        $this->approvalService->requestRevision($this->finance, $pr, 'Mohon lengkapi lampiran Terms of Reference (TOR) teknis server.');
        $this->assertEquals('revision_required', $pr->fresh()->status);
        $this->assertEquals('revision_required', $tier2->fresh()->status);

        // 4. Requester updates PR with updated specification WITHOUT changing price (still Rp 30,000,000)
        $response = $this->actingAs($this->requester)->put(route('purchase-requests.update', $pr), [
            'title' => 'Pengadaan Server Backup Cloud',
            'description' => 'Server cadangan untuk disaster recovery (sudah dilengkapi TOR).',
            'required_date' => Carbon::tomorrow()->format('Y-m-d'),
            'action' => 'submit',
            'items' => [
                [
                    'item_name' => 'Server Backup Unit',
                    'specification' => 'Spesifikasi disempurnakan sesuai TOR Teknis Server v2.0',
                    'quantity' => 1,
                    'unit' => 'Unit',
                    'estimated_unit_price' => 30000000,
                ]
            ],
        ]);

        $response->assertRedirect(route('purchase-requests.show', $pr));

        // 5. VERIFY ENTERPRISE ROUTING RULE:
        // - PR status returns to 'submitted'
        $this->assertEquals('submitted', $pr->fresh()->status);

        // - Tier 1 Manager approval is PRESERVED (NOT deleted, NOT reset to pending)
        $this->assertEquals('approved', $tier1->fresh()->status);
        $this->assertEquals($this->manager->id, $tier1->fresh()->approver_id);

        // - Tier 2 Finance is directly active again (pending)
        $this->assertEquals('pending', $tier2->fresh()->status);
        $this->assertEquals(2, $pr->fresh()->currentPendingApproval()->tier_level);

        // - Manager does NOT need to approve again!
        $this->assertFalse($this->approvalService->canUserApprove($this->manager, $pr->fresh()));

        // - Finance CAN immediately approve!
        $this->assertTrue($this->approvalService->canUserApprove($this->finance, $pr->fresh()));

        // 6. Finance approves Tier 2, PR moves cleanly to Tier 3 (HoD)
        $this->approvalService->approve($this->finance, $pr->fresh(), 'TOR teknis sudah sesuai dan terverifikasi.');
        $this->assertEquals('approved', $tier2->fresh()->status);
        $this->assertEquals('pending', $tier3->fresh()->status);
        $this->assertEquals(3, $pr->fresh()->currentPendingApproval()->tier_level);
    }

    public function test_material_revision_with_price_increase_resets_approvals_back_to_tier_1_manager(): void
    {
        // 1. Create submitted PR for Rp 20,000,000
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/IT/2026/09/5552',
            'user_id' => $this->requester->id,
            'department_id' => $this->dept->id,
            'title' => 'Pengadaan Laptop Staff',
            'description' => '2 unit laptop kantor.',
            'required_date' => Carbon::tomorrow(),
            'estimated_total' => 20000000,
            'status' => 'submitted',
        ]);

        $pr->items()->create([
            'item_name' => 'Laptop Core i5',
            'specification' => 'RAM 8GB',
            'quantity' => 2,
            'unit' => 'Unit',
            'estimated_unit_price' => 10000000,
            'subtotal' => 20000000,
        ]);

        $this->approvalService->generateApprovalTiers($pr);

        // Manager approves Tier 1
        $this->approvalService->approve($this->manager, $pr, 'Disetujui.');
        $tier1 = $pr->approvals()->where('tier_level', 1)->first();
        $this->assertEquals('approved', $tier1->fresh()->status);

        // Finance requests revision
        $this->approvalService->requestRevision($this->finance, $pr, 'Harga satuan terlalu rendah, gunakan tipe spesifikasi resmi.');
        $this->assertEquals('revision_required', $pr->fresh()->status);

        // Requester updates and INCREASES budget to Rp 28,000,000 (Material Change: > Rp 20,000,000 and crosses 25M HoD tier)
        $response = $this->actingAs($this->requester)->put(route('purchase-requests.update', $pr), [
            'title' => 'Pengadaan Laptop Staff',
            'description' => 'Spesifikasi dinaikkan ke Core i7.',
            'required_date' => Carbon::tomorrow()->format('Y-m-d'),
            'action' => 'submit',
            'items' => [
                [
                    'item_name' => 'Laptop Core i7',
                    'specification' => 'RAM 16GB',
                    'quantity' => 2,
                    'unit' => 'Unit',
                    'estimated_unit_price' => 14000000, // Total: 28,000,000
                ]
            ],
        ]);

        $response->assertRedirect(route('purchase-requests.show', $pr));

        // VERIFY MATERIAL RESET RULE:
        // Because budget increased from 20M to 28M, Manager signature MUST be revoked/reset!
        $newTier1 = $pr->fresh()->approvals()->where('tier_level', 1)->first();
        $this->assertEquals('pending', $newTier1->status);
        $this->assertEquals(1, $pr->fresh()->currentPendingApproval()->tier_level);

        // Manager MUST re-approve the higher budget
        $this->assertTrue($this->approvalService->canUserApprove($this->manager, $pr->fresh()));
        // Finance CANNOT approve yet until Manager approves
        $this->assertFalse($this->approvalService->canUserApprove($this->finance, $pr->fresh()));

        // Also verify HoD tier was newly generated because total is now 28M (> 25M)
        $this->assertEquals(3, $pr->fresh()->approvals()->count());
    }
}
