<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\InAppNotification;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\User;
use App\Services\ApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $requester;
    protected User $managerIt;
    protected User $finance;
    protected User $admin;
    protected Department $deptIt;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->deptIt = Department::where('code', 'IT')->first();
        $this->requester = User::where('role', 'requester')->where('department_id', $this->deptIt->id)->first();
        $this->managerIt = User::where('role', 'manager')->where('department_id', $this->deptIt->id)->first();
        $this->finance = User::where('role', 'finance')->first();
        $this->admin = User::where('role', 'admin')->first();
    }

    /**
     * Helper to create a submitted PR with specific total
     */
    protected function createSubmittedPr(float $totalAmount, User $creator): PurchaseRequest
    {
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR-' . date('Ymd') . '-' . rand(1000, 9999),
            'title' => 'Pengadaan Uji Coba Approval',
            'description' => 'Justifikasi kebutuhan pengadaan untuk testing.',
            'user_id' => $creator->id,
            'department_id' => $creator->department_id ?? $this->deptIt->id,
            'status' => 'submitted',
            'estimated_total' => $totalAmount,
            'required_date' => now()->addDays(7),
        ]);

        PurchaseRequestItem::create([
            'purchase_request_id' => $pr->id,
            'item_name' => 'Barang Uji Coba',
            'quantity' => 1,
            'unit' => 'Unit',
            'estimated_unit_price' => $totalAmount,
            'subtotal' => $totalAmount,
        ]);

        app(ApprovalService::class)->generateApprovalTiers($pr);

        return $pr;
    }

    public function test_pr_under_5m_generates_one_tier_manager_only(): void
    {
        $pr = $this->createSubmittedPr(4500000, $this->requester);

        $this->assertCount(1, $pr->approvals);
        $tier1 = $pr->approvals->first();
        $this->assertEquals(1, $tier1->tier_level);
        $this->assertEquals('manager', $tier1->role_required);
        $this->assertEquals($this->deptIt->id, $tier1->department_id);
    }

    public function test_pr_between_5m_and_25m_generates_two_tiers_manager_and_finance(): void
    {
        $pr = $this->createSubmittedPr(15000000, $this->requester);

        $this->assertCount(2, $pr->approvals);
        $tiers = $pr->approvals->sortBy('tier_level')->values();

        $this->assertEquals(1, $tiers[0]->tier_level);
        $this->assertEquals('manager', $tiers[0]->role_required);

        $this->assertEquals(2, $tiers[1]->tier_level);
        $this->assertEquals('finance', $tiers[1]->role_required);
    }

    public function test_pr_above_25m_generates_three_tiers_manager_finance_and_hod(): void
    {
        $pr = $this->createSubmittedPr(50000000, $this->requester);

        $this->assertCount(3, $pr->approvals);
        $tiers = $pr->approvals->sortBy('tier_level')->values();

        $this->assertEquals(1, $tiers[0]->tier_level);
        $this->assertEquals(2, $tiers[1]->tier_level);
        $this->assertEquals(3, $tiers[2]->tier_level);
        $this->assertEquals('hod', $tiers[2]->role_required);
    }

    public function test_anti_self_approval_requester_cannot_approve_their_own_pr(): void
    {
        // Manager creates their own PR
        $pr = $this->createSubmittedPr(3000000, $this->managerIt);

        // Manager tries to approve their own PR
        $response = $this->actingAs($this->managerIt)
            ->post(route('approvals.approve', $pr));

        $response->assertStatus(403);
        $this->assertEquals('submitted', $pr->fresh()->status);
    }

    public function test_sequential_approval_tier2_cannot_approve_while_tier1_is_pending(): void
    {
        $pr = $this->createSubmittedPr(15000000, $this->requester);

        // Finance attempts to approve while Tier 1 (Manager) is still pending
        $response = $this->actingAs($this->finance)
            ->post(route('approvals.approve', $pr));

        $response->assertStatus(403);

        // Now Manager IT approves Tier 1
        $managerResponse = $this->actingAs($this->managerIt)
            ->post(route('approvals.approve', $pr), [
                'notes' => 'Disetujui oleh Manager IT.',
            ]);

        $managerResponse->assertRedirect(route('purchase-requests.show', $pr));
        $this->assertEquals('approved', $pr->approvals()->where('tier_level', 1)->first()->status);
        $this->assertEquals('submitted', $pr->fresh()->status); // Still submitted waiting for Tier 2

        // Now Finance can approve Tier 2
        $financeResponse = $this->actingAs($this->finance)
            ->post(route('approvals.approve', $pr), [
                'notes' => 'Anggaran telah dialokasikan.',
            ]);

        $financeResponse->assertRedirect(route('purchase-requests.show', $pr));
        $this->assertEquals('approved', $pr->fresh()->status); // Final tier approved!
    }

    public function test_request_revision_requires_mandatory_notes_and_updates_status(): void
    {
        $pr = $this->createSubmittedPr(3000000, $this->requester);

        // Validation test: notes empty
        $responseEmpty = $this->actingAs($this->managerIt)
            ->post(route('approvals.revision', $pr), [
                'notes' => '',
            ]);
        $responseEmpty->assertSessionHasErrors('notes');

        // Validation test: notes too short (< 5 chars)
        $responseShort = $this->actingAs($this->managerIt)
            ->post(route('approvals.revision', $pr), [
                'notes' => 'ubah',
            ]);
        $responseShort->assertSessionHasErrors('notes');

        // Valid revision request
        $responseValid = $this->actingAs($this->managerIt)
            ->post(route('approvals.revision', $pr), [
                'notes' => 'Mohon lampirkan spesifikasi alternatif yang lebih hemat.',
            ]);

        $responseValid->assertRedirect(route('purchase-requests.show', $pr));
        $this->assertEquals('revision_required', $pr->fresh()->status);

        // Requester should have received an in-app notification
        $notification = InAppNotification::where('user_id', $this->requester->id)->latest()->first();
        $this->assertNotNull($notification);
        $this->assertStringContainsString('Revisi', $notification->title);
    }

    public function test_reject_requires_mandatory_notes_and_marks_pr_rejected(): void
    {
        $pr = $this->createSubmittedPr(3000000, $this->requester);

        // Validation test: notes empty
        $responseEmpty = $this->actingAs($this->managerIt)
            ->post(route('approvals.reject', $pr), [
                'notes' => '',
            ]);
        $responseEmpty->assertSessionHasErrors('notes');

        // Valid rejection
        $responseValid = $this->actingAs($this->managerIt)
            ->post(route('approvals.reject', $pr), [
                'notes' => 'Anggaran belanja modal dibekukan untuk periode ini.',
            ]);

        $responseValid->assertRedirect(route('purchase-requests.show', $pr));
        $this->assertEquals('rejected', $pr->fresh()->status);

        // Requester should have received an in-app notification
        $notification = InAppNotification::where('user_id', $this->requester->id)->latest()->first();
        $this->assertNotNull($notification);
        $this->assertStringContainsString('Ditolak', $notification->title);
    }

    public function test_approval_queue_displays_pending_prs_for_authorized_user(): void
    {
        $pr = $this->createSubmittedPr(3000000, $this->requester);

        $response = $this->actingAs($this->managerIt)->get(route('approvals.index'));
        $response->assertStatus(200);
        $response->assertSee($pr->pr_number);
    }
}
