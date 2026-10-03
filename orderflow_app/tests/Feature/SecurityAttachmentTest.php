<?php

namespace Tests\Feature;

use App\Models\Attachment;
use App\Models\Department;
use App\Models\PurchaseRequest;
use App\Models\Quotation;
use App\Models\User;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityAttachmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $procurement;
    protected User $ownerRequester;
    protected User $unauthorizedRequester;
    protected Department $deptIT;
    protected Department $deptHR;
    protected Vendor $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->deptIT = Department::create([
            'code' => 'IT',
            'name' => 'Information Technology',
            'is_active' => true,
        ]);

        $this->deptHR = Department::create([
            'code' => 'HR',
            'name' => 'Human Resources',
            'is_active' => true,
        ]);

        $this->procurement = User::create([
            'name' => 'Procurement Officer',
            'email' => 'procurement@orderflow.com',
            'password' => bcrypt('password'),
            'role' => 'procurement',
            'department_id' => $this->deptIT->id,
            'is_active' => true,
        ]);

        $this->ownerRequester = User::create([
            'name' => 'Owner Requester',
            'email' => 'owner@orderflow.com',
            'password' => bcrypt('password'),
            'role' => 'requester',
            'department_id' => $this->deptIT->id,
            'is_active' => true,
        ]);

        $this->unauthorizedRequester = User::create([
            'name' => 'Snoop Requester',
            'email' => 'snoop@orderflow.com',
            'password' => bcrypt('password'),
            'role' => 'requester',
            'department_id' => $this->deptHR->id,
            'is_active' => true,
        ]);

        $this->vendor = Vendor::create([
            'code' => 'VND-001',
            'name' => 'PT Sinar Mega Solusindo',
            'category' => 'Hardware',
            'contact_person' => 'Bambang',
            'email' => 'sales@sinarmega.com',
            'phone' => '021-123456',
            'address' => 'Jakarta',
            'rating' => 4.80,
            'is_active' => true,
        ]);
    }

    /**
     * Test Poin 5: Unauthenticated guest cannot access quotation attachments.
     */
    public function test_guest_cannot_access_quotation_attachment(): void
    {
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/IT/2026/09/0001',
            'user_id' => $this->ownerRequester->id,
            'department_id' => $this->deptIT->id,
            'title' => 'Pengadaan Server Cloud',
            'description' => 'Server kebutuhan dev',
            'required_date' => Carbon::tomorrow()->format('Y-m-d'),
            'status' => 'approved',
            'total_estimated_cost' => 50000000,
        ]);

        $fakeFile = UploadedFile::fake()->create('secret_quote.pdf', 100, 'application/pdf');
        $storedPath = $fakeFile->store('private/quotations', 'local');

        $quotation = Quotation::create([
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->vendor->id,
            'quotation_number' => 'Q-VEND-001',
            'subtotal' => 45000000,
            'grand_total' => 49950000,
            'file_path' => $storedPath,
            'created_by' => $this->procurement->id,
        ]);

        $response = $this->get(route('quotations.attachment', $quotation));
        $response->assertRedirect(route('login'));
    }

    /**
     * Test Poin 5: Unauthorized user from different department cannot access confidential vendor quote.
     */
    public function test_unauthorized_user_is_forbidden_from_viewing_confidential_quotation(): void
    {
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/IT/2026/09/0002',
            'user_id' => $this->ownerRequester->id,
            'department_id' => $this->deptIT->id,
            'title' => 'Pengadaan Server Cloud',
            'description' => 'Server kebutuhan dev',
            'required_date' => Carbon::tomorrow()->format('Y-m-d'),
            'status' => 'approved',
            'total_estimated_cost' => 50000000,
        ]);

        $fakeFile = UploadedFile::fake()->create('secret_quote.pdf', 100, 'application/pdf');
        $storedPath = $fakeFile->store('private/quotations', 'local');

        $quotation = Quotation::create([
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->vendor->id,
            'quotation_number' => 'Q-VEND-002',
            'subtotal' => 45000000,
            'grand_total' => 49950000,
            'file_path' => $storedPath,
            'created_by' => $this->procurement->id,
        ]);

        // Unauthorized requester from HR attempts to access IT vendor quote
        $response = $this->actingAs($this->unauthorizedRequester)
            ->get(route('quotations.attachment', $quotation));

        $response->assertStatus(403);
    }

    /**
     * Test Poin 5: Procurement can stream/download confidential quotation document securely.
     */
    public function test_procurement_can_download_quotation_attachment(): void
    {
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/IT/2026/09/0003',
            'user_id' => $this->ownerRequester->id,
            'department_id' => $this->deptIT->id,
            'title' => 'Pengadaan Server Cloud',
            'description' => 'Server kebutuhan dev',
            'required_date' => Carbon::tomorrow()->format('Y-m-d'),
            'status' => 'approved',
            'total_estimated_cost' => 50000000,
        ]);

        $fakeFile = UploadedFile::fake()->create('secret_quote.pdf', 100, 'application/pdf');
        $storedPath = $fakeFile->store('private/quotations', 'local');

        $quotation = Quotation::create([
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->vendor->id,
            'quotation_number' => 'Q-VEND-003',
            'subtotal' => 45000000,
            'grand_total' => 49950000,
            'file_path' => $storedPath,
            'created_by' => $this->procurement->id,
        ]);

        $response = $this->actingAs($this->procurement)
            ->get(route('quotations.attachment', $quotation));

        $response->assertStatus(200);
    }

    /**
     * Test Poin 5: Requester from different department cannot access private PR attachment.
     */
    public function test_unauthorized_requester_cannot_download_private_pr_attachment(): void
    {
        $fakeFile = UploadedFile::fake()->create('it_architecture_spec.pdf', 100, 'application/pdf');
        $storedPath = $fakeFile->store('private/pr_attachments', 'local');

        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/IT/2026/09/0004',
            'user_id' => $this->ownerRequester->id,
            'department_id' => $this->deptIT->id,
            'title' => 'Spesifikasi Khusus IT',
            'description' => 'Dokumen rahasia jaringan internal',
            'required_date' => Carbon::tomorrow()->format('Y-m-d'),
            'status' => 'submitted',
            'attachment_path' => $storedPath,
        ]);

        $response = $this->actingAs($this->unauthorizedRequester)
            ->get(route('purchase-requests.attachment', $pr));

        $response->assertStatus(403);
    }
}
