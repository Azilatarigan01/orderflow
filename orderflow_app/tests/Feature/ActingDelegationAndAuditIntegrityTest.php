<?php

namespace Tests\Feature;

use App\Models\ActingDelegation;
use App\Models\AuditTrail;
use App\Models\Department;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Services\ApprovalService;
use App\Services\AuditTrailService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ActingDelegationAndAuditIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $colleague;
    private User $requester;
    private Department $dept;
    private ApprovalService $approvalService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dept = Department::create([
            'name' => 'Information Technology',
            'code' => 'IT',
            'is_active' => true,
        ]);

        $this->admin = User::create([
            'name' => 'Admin System',
            'email' => 'admin@orderflow.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'department_id' => $this->dept->id,
            'is_active' => true,
        ]);

        $this->manager = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi.manager@orderflow.test',
            'password' => bcrypt('password'),
            'role' => 'manager',
            'department_id' => $this->dept->id,
            'is_active' => true,
        ]);

        $this->colleague = User::create([
            'name' => 'Ahmad Plt',
            'email' => 'ahmad.plt@orderflow.test',
            'password' => bcrypt('password'),
            'role' => 'requester', // Normally standard requester
            'department_id' => $this->dept->id,
            'is_active' => true,
        ]);

        $this->requester = User::create([
            'name' => 'Rina Requester',
            'email' => 'rina@orderflow.test',
            'password' => bcrypt('password'),
            'role' => 'requester',
            'department_id' => $this->dept->id,
            'is_active' => true,
        ]);

        $this->approvalService = app(ApprovalService::class);
    }

    public function test_manager_can_create_acting_plt_delegation_for_colleague(): void
    {
        $response = $this->actingAs($this->manager)->post(route('delegations.store'), [
            'delegatee_user_id' => $this->colleague->id,
            'role_delegated'    => 'manager',
            'start_date'        => Carbon::today()->format('Y-m-d'),
            'end_date'          => Carbon::today()->addDays(7)->format('Y-m-d'),
            'reason'            => 'Cuti tahunan selama 1 pekan',
        ]);

        $response->assertRedirect(route('delegations.index'));
        $this->assertDatabaseHas('acting_delegations', [
            'delegator_user_id' => $this->manager->id,
            'delegatee_user_id' => $this->colleague->id,
            'role_delegated'    => 'manager',
            'reason'            => 'Cuti tahunan selama 1 pekan',
            'is_active'         => true,
        ]);

        // Verify delegation appears in index
        $indexResponse = $this->actingAs($this->manager)->get(route('delegations.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee($this->colleague->name);
        $indexResponse->assertSee('Cuti tahunan selama 1 pekan');
    }

    public function test_delegation_cannot_be_granted_to_self(): void
    {
        $response = $this->actingAs($this->manager)->post(route('delegations.store'), [
            'delegatee_user_id' => $this->manager->id,
            'role_delegated'    => 'manager',
            'start_date'        => Carbon::today()->format('Y-m-d'),
            'end_date'          => Carbon::today()->addDays(7)->format('Y-m-d'),
            'reason'            => 'Delegasi diri sendiri',
        ]);

        $response->assertSessionHasErrors(['delegatee_user_id']);
        $this->assertDatabaseCount('acting_delegations', 0);
    }

    public function test_acting_plt_approver_can_approve_pr_on_behalf_of_manager(): void
    {
        // 1. Setup active Plt delegation from Manager to Colleague
        ActingDelegation::create([
            'delegator_user_id' => $this->manager->id,
            'delegatee_user_id' => $this->colleague->id,
            'role_delegated'    => 'manager',
            'start_date'        => Carbon::today(),
            'end_date'          => Carbon::today()->addDays(5),
            'reason'            => 'Dinas Luar Kota',
            'is_active'         => true,
        ]);

        // 2. Requester creates and submits PR
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/IT/2026/09/9991',
            'user_id' => $this->requester->id,
            'department_id' => $this->dept->id,
            'title' => 'Pengadaan Laptop Dev Plt Test',
            'description' => 'Laptop untuk engineering',
            'required_date' => Carbon::now()->addDays(7),
            'estimated_total' => 4500000, // <= 5jt, only 1 tier (manager) needed!
            'status' => 'submitted',
        ]);

        $this->approvalService->generateApprovalTiers($pr);
        $activeTier = $pr->currentPendingApproval();
        $this->assertNotNull($activeTier);

        // 3. Verify Colleague (as Plt) can approve
        $this->assertTrue($this->approvalService->canUserApprove($this->colleague, $pr));

        // 4. Colleague executes approval
        $this->approvalService->approve($this->colleague, $pr, 'Disetujui selaku Plt Manajer IT.');

        $activeTier->refresh();
        $pr->refresh();

        // 5. Assertions
        $this->assertEquals('approved', $activeTier->status);
        $this->assertEquals($this->colleague->id, $activeTier->approver_id);
        $this->assertTrue($activeTier->is_acting);
        $this->assertEquals($this->manager->id, $activeTier->acting_for_user_id);
        $this->assertEquals('approved', $pr->status);

        // Assert audit trail captured Plt acting
        $latestAudit = AuditTrail::where('action', 'pr_approved')->latest('id')->first();
        $this->assertNotNull($latestAudit);
        $this->assertStringContainsString('Plt Budi Santoso', $latestAudit->description);
    }

    public function test_expired_or_inactive_plt_cannot_approve(): void
    {
        // Expired delegation (ended yesterday)
        ActingDelegation::create([
            'delegator_user_id' => $this->manager->id,
            'delegatee_user_id' => $this->colleague->id,
            'role_delegated'    => 'manager',
            'start_date'        => Carbon::today()->subDays(5),
            'end_date'          => Carbon::today()->subDays(1),
            'reason'            => 'Cuti sudah selesai',
            'is_active'         => true,
        ]);

        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/IT/2026/09/9992',
            'user_id' => $this->requester->id,
            'department_id' => $this->dept->id,
            'title' => 'Pengadaan Printer Gudang',
            'description' => 'Kebutuhan cetak resi',
            'required_date' => Carbon::now()->addDays(7),
            'estimated_total' => 2000000,
            'status' => 'submitted',
        ]);

        $this->approvalService->generateApprovalTiers($pr);

        // Since delegation expired, colleague CANNOT approve
        $this->assertFalse($this->approvalService->canUserApprove($this->colleague, $pr));
    }

    public function test_sha256_audit_trail_verifies_valid_blockchain_chain(): void
    {
        AuditTrail::truncate();

        // Perform 3 consecutive audit entries
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/IT/2026/09/9993',
            'user_id' => $this->requester->id,
            'department_id' => $this->dept->id,
            'title' => 'Pengadaan Perangkat Audit',
            'description' => 'Testing audit hash',
            'required_date' => Carbon::now()->addDays(7),
            'estimated_total' => 3000000,
            'status' => 'submitted',
        ]);

        $this->actingAs($this->requester);
        AuditTrailService::record('pr_created', $pr, $pr->pr_number, beforeState: null, afterState: ['status' => 'draft']);
        AuditTrailService::record('pr_submitted', $pr, $pr->pr_number, beforeState: ['status' => 'draft'], afterState: ['status' => 'submitted']);
        
        $this->actingAs($this->manager);
        AuditTrailService::record('pr_approved', $pr, $pr->pr_number, beforeState: ['status' => 'submitted'], afterState: ['status' => 'approved']);

        $this->assertEquals(3, AuditTrail::count());

        // Verify chain integrity
        $integrity = AuditTrailService::verifyChainIntegrity();

        $this->assertTrue($integrity['is_valid']);
        $this->assertEquals(3, $integrity['total_records']);
        $this->assertEquals('verified', $integrity['status']);
        $this->assertNull($integrity['broken_id']);
    }

    public function test_sha256_audit_trail_detects_data_tampering(): void
    {
        AuditTrail::truncate();

        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/IT/2026/09/9994',
            'user_id' => $this->requester->id,
            'department_id' => $this->dept->id,
            'title' => 'Pengadaan Barang Uji Integritas',
            'description' => 'Testing tampering detection',
            'required_date' => Carbon::now()->addDays(7),
            'estimated_total' => 1000000,
            'status' => 'submitted',
        ]);

        $this->actingAs($this->requester);
        AuditTrailService::record('pr_created', $pr, $pr->pr_number, beforeState: null, afterState: ['status' => 'draft']);
        $second = AuditTrailService::record('pr_submitted', $pr, $pr->pr_number, beforeState: ['status' => 'draft'], afterState: ['status' => 'submitted']);

        // Maliciously tamper with the second record directly in database without updating record_hash
        DB::table('audit_trails')->where('id', $second->id)->update([
            'action' => 'pr_tampered_action',
        ]);

        $integrity = AuditTrailService::verifyChainIntegrity();

        $this->assertFalse($integrity['is_valid']);
        $this->assertEquals('tampered_payload', $integrity['status']);
        $this->assertEquals($second->id, $integrity['broken_id']);
    }

    public function test_sha256_audit_trail_detects_injected_chain_break(): void
    {
        AuditTrail::truncate();

        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/IT/2026/09/9995',
            'user_id' => $this->requester->id,
            'department_id' => $this->dept->id,
            'title' => 'Pengadaan Barang Uji Chain Break',
            'description' => 'Testing chain break detection',
            'required_date' => Carbon::now()->addDays(7),
            'estimated_total' => 1000000,
            'status' => 'submitted',
        ]);

        $this->actingAs($this->requester);
        AuditTrailService::record('pr_created', $pr, $pr->pr_number, beforeState: null, afterState: ['status' => 'draft']);
        
        // Maliciously inject a fake record with arbitrary previous hash
        $fakeId = DB::table('audit_trails')->insertGetId([
            'user_id'       => $this->requester->id,
            'action'        => 'fake_action',
            'entity_type'   => 'PurchaseRequest',
            'entity_id'     => $pr->id,
            'entity_label'  => 'PR-FAKE',
            'previous_hash' => 'fake_hash_1234567890abcdef1234567890abcdef1234567890abcdef1234567890ab',
            'record_hash'   => 'fake_hash_abcdef1234567890abcdef1234567890abcdef1234567890abcdef123456',
            'description'   => 'Injected fake record',
            'created_at'    => now(),
        ]);

        $integrity = AuditTrailService::verifyChainIntegrity();

        $this->assertFalse($integrity['is_valid']);
        $this->assertEquals('tampered_chain', $integrity['status']);
        $this->assertEquals($fakeId, $integrity['broken_id']);
    }

    public function test_multi_channel_notification_records_inapp_and_dispatches(): void
    {
        $notification = NotificationService::send(
            $this->requester,
            'Test Multi-Channel Title',
            'Test Multi-Channel Body Message',
            '/purchase-requests/1',
            'pr_approved'
        );

        $this->assertDatabaseHas('in_app_notifications', [
            'id' => $notification->id,
            'user_id' => $this->requester->id,
            'title' => 'Test Multi-Channel Title',
            'message' => 'Test Multi-Channel Body Message',
            'type' => 'pr_approved',
            'is_read' => false,
        ]);
    }
}
