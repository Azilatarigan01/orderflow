<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Attachment;
use App\Models\Quotation;
use App\Models\Vendor;
use App\Services\ApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class OperationalVulnerabilitiesAndSafeguardsTest extends TestCase
{
    use RefreshDatabase;

    protected User $requester;
    protected User $manager;
    protected User $finance;
    protected User $hod;
    protected User $admin;
    protected Department $dept;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dept = Department::create([
            'name' => 'Information Technology',
            'code' => 'IT',
        ]);

        $this->requester = User::factory()->create([
            'name' => 'Zila Requester',
            'role' => 'requester',
            'department_id' => $this->dept->id,
            'is_active' => true,
        ]);

        $this->manager = User::factory()->create([
            'name' => 'Budi Manager',
            'role' => 'manager',
            'department_id' => $this->dept->id,
            'is_active' => true,
        ]);

        $this->dept->update(['manager_id' => $this->manager->id]);

        $this->finance = User::factory()->create([
            'name' => 'Siti Finance',
            'role' => 'finance',
            'is_active' => true,
        ]);

        $this->hod = User::factory()->create([
            'name' => 'Direktur Utama',
            'role' => 'hod',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create([
            'name' => 'Super Admin',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    protected function createPr(array $overrides = []): PurchaseRequest
    {
        static $counter = 1;
        $prNum = sprintf('PR/IT/2026/09/%04d', $counter++);

        return PurchaseRequest::create(array_merge([
            'pr_number' => $prNum,
            'user_id' => $this->requester->id,
            'department_id' => $this->dept->id,
            'title' => 'Pengadaan Laptop Unit Baru',
            'description' => 'Kebutuhan tim teknis',
            'required_date' => now()->addDays(7)->toDateString(),
            'estimated_total' => 2000000,
            'status' => 'submitted',
        ], $overrides));
    }

    /**
     * Issue 1: Double-Approval Race Condition (Concurrency Locking)
     */
    public function test_concurrent_double_approval_triggers_conflict_exception(): void
    {
        $pr = $this->createPr(['estimated_total' => 2000000]);

        app(ApprovalService::class)->generateApprovalTiers($pr);

        // First approval completes
        app(ApprovalService::class)->approve($this->manager, $pr, 'Persetujuan pertama');

        $this->assertEquals('approved', $pr->fresh()->status);

        // Second concurrent approval attempt (e.g. from parallel tab or second approver)
        // Must throw 409 Conflict HttpException
        try {
            app(ApprovalService::class)->approve($this->manager, $pr, 'Persetujuan kedua tidak sah');
            $this->fail('Expected 409 Conflict HttpException was not thrown.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(409, $e->getStatusCode());
        }
    }

    public function test_controller_handles_409_conflict_and_redirects_with_friendly_error(): void
    {
        $pr = $this->createPr(['estimated_total' => 2000000]);

        app(ApprovalService::class)->generateApprovalTiers($pr);

        // Pre-approve to simulate a race condition where status has shifted
        app(ApprovalService::class)->approve($this->manager, $pr, 'Telah disetujui');

        // Manager sends POST to approve via HTTP
        $response = $this->actingAs($this->manager)->post(route('approvals.approve', $pr), [
            'notes' => 'Mencoba klik approve ulang',
        ]);

        $response->assertRedirect(route('purchase-requests.show', $pr));
        $response->assertSessionHas('error');
    }

    /**
     * Issue 2: Price & Specification Snapshot Immutability
     */
    public function test_pr_item_retains_concrete_snapshot_values_independently(): void
    {
        $pr = $this->createPr(['estimated_total' => 15000000]);

        $item = PurchaseRequestItem::create([
            'purchase_request_id' => $pr->id,
            'item_name' => 'MacBook Pro M3 Pro',
            'specification' => '16GB RAM, 512GB SSD, Silver',
            'quantity' => 1,
            'unit' => 'Unit',
            'estimated_unit_price' => 15000000,
            'subtotal' => 15000000,
        ]);

        $this->assertEquals(15000000, (float) $item->subtotal);

        // Approve the PR
        app(ApprovalService::class)->generateApprovalTiers($pr);
        app(ApprovalService::class)->approve($this->manager, $pr);
        $pr->update(['status' => 'approved']);

        // Attempting to alter snapshot data of an approved PR must throw DomainException
        $this->expectException(\DomainException::class);
        $item->update([
            'estimated_unit_price' => 18000000,
        ]);
    }

    public function test_pr_item_cannot_be_deleted_when_pr_is_already_approved(): void
    {
        $pr = $this->createPr(['status' => 'approved']);

        $item = PurchaseRequestItem::create([
            'purchase_request_id' => $pr->id,
            'item_name' => 'Dell Server Rack',
            'specification' => 'Dual Xeon',
            'quantity' => 1,
            'unit' => 'Unit',
            'estimated_unit_price' => 30000000,
            'subtotal' => 30000000,
        ]);

        $this->expectException(\DomainException::class);
        $item->delete();
    }

    /**
     * Issue 3: Orphaned Files & Storage Cleanup
     */
    public function test_deleting_attachment_removes_physical_file_from_storage(): void
    {
        Storage::fake('public');

        $uploaded = UploadedFile::fake()->create('dokumen_penawaran.pdf', 500, 'application/pdf');
        $storedPath = $uploaded->store('attachments', 'public');

        $pr = $this->createPr();

        $attachment = Attachment::create([
            'purchase_request_id' => $pr->id,
            'file_name' => 'dokumen_penawaran.pdf',
            'file_path' => $storedPath,
            'file_type' => 'application/pdf',
            'file_size' => 512000,
            'uploaded_by' => $this->requester->id,
        ]);

        Storage::disk('public')->assertExists($storedPath);

        // Delete attachment model
        $attachment->delete();

        // Physical file must be automatically removed by model hook
        Storage::disk('public')->assertMissing($storedPath);
    }

    public function test_deleting_quotation_removes_physical_file_from_storage(): void
    {
        Storage::fake('public');

        $uploaded = UploadedFile::fake()->create('vendor_quote_official.pdf', 300, 'application/pdf');
        $storedPath = $uploaded->store('quotations', 'public');

        $vendor = Vendor::create([
            'code' => 'VND-099',
            'name' => 'PT Mitra Solusi Prima',
            'category' => 'Hardware & IT',
            'contact_person' => 'Budi Santoso',
            'email' => 'budi@mitraprima.local',
            'phone' => '08123456789',
            'address' => 'Jl. Sudirman Kav 21',
            'is_active' => true,
        ]);

        $pr = $this->createPr();

        $quotation = Quotation::create([
            'purchase_request_id' => $pr->id,
            'vendor_id' => $vendor->id,
            'quotation_number' => 'QTO-TEST-99',
            'grand_total' => 5000000,
            'file_path' => $storedPath,
            'created_by' => $this->admin->id,
        ]);

        Storage::disk('public')->assertExists($storedPath);

        $quotation->delete();

        Storage::disk('public')->assertMissing($storedPath);
    }

    public function test_storage_cleanup_orphans_artisan_command(): void
    {
        Storage::fake('public');

        // 1. Create a legitimate attachment
        $legitFile = UploadedFile::fake()->create('legit_spec.pdf', 200);
        $legitPath = $legitFile->store('attachments', 'public');

        $pr = $this->createPr();

        Attachment::create([
            'purchase_request_id' => $pr->id,
            'file_name' => 'legit_spec.pdf',
            'file_path' => $legitPath,
            'file_type' => 'application/pdf',
            'file_size' => 204800,
            'uploaded_by' => $this->requester->id,
        ]);

        // 2. Create an unreferenced / orphaned file left behind
        $orphanFile = UploadedFile::fake()->create('abandoned_draft.pdf', 800);
        $orphanPath = $orphanFile->store('attachments', 'public');

        Storage::disk('public')->assertExists($legitPath);
        Storage::disk('public')->assertExists($orphanPath);

        // Run cleanup command
        $this->artisan('storage:cleanup-orphans')
            ->expectsOutputToContain('Successfully deleted 1 orphaned file(s)')
            ->assertExitCode(0);

        // Legit file is preserved, orphan file is purged
        Storage::disk('public')->assertExists($legitPath);
        Storage::disk('public')->assertMissing($orphanPath);
    }

    /**
     * Issue 4: Session Keep-Alive & CSRF Refresh Endpoint
     */
    public function test_session_keepalive_endpoint_returns_fresh_csrf_when_authenticated(): void
    {
        $response = $this->actingAs($this->requester)->get(route('session.keepalive'));

        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'authenticated',
            'csrf_token',
            'timestamp',
        ]);
        $response->assertJson([
            'status' => 'alive',
            'authenticated' => true,
        ]);
    }

    public function test_session_keepalive_redirects_unauthenticated_requests(): void
    {
        $response = $this->get(route('session.keepalive'));
        $response->assertRedirect('/login');
    }
}
