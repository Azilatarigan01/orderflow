<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\StatusHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_authenticated_user_can_view_pr_index(): void
    {
        $requester = User::where('role', 'requester')->first();
        $this->assertNotNull($requester);

        $response = $this->actingAs($requester)->get(route('purchase-requests.index'));

        $response->assertStatus(200);
        $response->assertSee('Pengajuan Pembelian');
    }

    public function test_pr_cannot_be_created_without_items(): void
    {
        $requester = User::where('role', 'requester')->first();

        $response = $this->actingAs($requester)->post(route('purchase-requests.store'), [
            'title' => 'Pengadaan Tanpa Item',
            'description' => 'Justifikasi kebutuhan pengadaan tanpa barang.',
            'required_date' => now()->addDays(7)->toDateString(),
            'items' => [], // Empty items
        ]);

        $response->assertSessionHasErrors('items');
        $this->assertDatabaseMissing('purchase_requests', ['title' => 'Pengadaan Tanpa Item']);
    }

    public function test_pr_total_is_calculated_automatically_from_items(): void
    {
        $requester = User::where('role', 'requester')->first();

        $response = $this->actingAs($requester)->post(route('purchase-requests.store'), [
            'title' => 'Pengadaan Kursi Kerja Ergonomis',
            'description' => 'Penggantian kursi kerja tim engineering yang sudah rusak.',
            'required_date' => now()->addDays(10)->toDateString(),
            'action' => 'draft',
            'items' => [
                [
                    'item_name' => 'Kursi Ergonomis Mesh',
                    'specification' => 'Lumbar support, 3D armrest',
                    'quantity' => 5,
                    'unit' => 'Unit',
                    'estimated_unit_price' => 2000000, // 5 * 2jt = 10jt
                ],
                [
                    'item_name' => 'Footrest Ergonomis',
                    'specification' => 'Adjustable height',
                    'quantity' => 5,
                    'unit' => 'Unit',
                    'estimated_unit_price' => 300000, // 5 * 300rb = 1.5jt
                ],
            ],
        ]);

        $response->assertRedirect();
        
        $pr = PurchaseRequest::where('title', 'Pengadaan Kursi Kerja Ergonomis')->first();
        $this->assertNotNull($pr);

        // Expected total = 10,000,000 + 1,500,000 = 11,500,000
        $this->assertEquals(11500000, (float) $pr->estimated_total);
        $this->assertCount(2, $pr->items);
        $this->assertEquals('draft', $pr->status);

        // Verify status history was created
        $this->assertDatabaseHas('status_histories', [
            'purchase_request_id' => $pr->id,
            'to_status' => 'draft',
        ]);
    }

    public function test_pr_accepts_dot_formatted_prices(): void
    {
        $requester = User::where('role', 'requester')->first();

        $response = $this->actingAs($requester)->post(route('purchase-requests.store'), [
            'title' => 'Pengadaan Laptop 15 Juta',
            'description' => 'Laptop spek tinggi untuk programming.',
            'required_date' => now()->addDays(14)->toDateString(),
            'action' => 'draft',
            'items' => [
                [
                    'item_name' => 'Laptop Flagship',
                    'specification' => 'Core i7',
                    'quantity' => 1,
                    'unit' => 'Unit',
                    'estimated_unit_price' => '15.000.000', // Dot formatted input
                ],
            ],
        ]);

        $response->assertRedirect();
        $pr = PurchaseRequest::where('title', 'Pengadaan Laptop 15 Juta')->first();
        $this->assertNotNull($pr);
        $this->assertEquals(15000000, (float) $pr->estimated_total);
    }

    public function test_submitting_pr_changes_status_and_logs_history(): void
    {
        $requester = User::where('role', 'requester')->first();

        // Find a draft PR
        $draftPr = PurchaseRequest::where('user_id', $requester->id)
            ->where('status', 'draft')
            ->first();

        $this->assertNotNull($draftPr);

        $response = $this->actingAs($requester)->post(route('purchase-requests.submit', $draftPr), [
            'notes' => 'Tolong segera diproses karena mendesak.',
        ]);

        $response->assertRedirect(route('purchase-requests.show', $draftPr));
        $this->assertEquals('submitted', $draftPr->fresh()->status);

        // Verify status history audit trail
        $this->assertDatabaseHas('status_histories', [
            'purchase_request_id' => $draftPr->id,
            'from_status' => 'draft',
            'to_status' => 'submitted',
            'user_id' => $requester->id,
        ]);
    }

    public function test_requester_cannot_view_pr_from_another_department(): void
    {
        $hrDept = Department::where('code', 'HRD')->first();
        $itDept = Department::where('code', 'IT')->first();

        // Create HR user
        $hrUser = User::create([
            'name' => 'Siti HRD',
            'email' => 'siti.hrd@orderflow.com',
            'password' => bcrypt('password'),
            'role' => 'requester',
            'department_id' => $hrDept->id,
            'is_active' => true,
        ]);

        // Create HR PR
        $hrPr = PurchaseRequest::create([
            'pr_number' => 'PR-202609-9999',
            'user_id' => $hrUser->id,
            'department_id' => $hrDept->id,
            'title' => 'Pengadaan Pelatihan Karyawan',
            'description' => 'Training kepemimpinan kuartal 4',
            'required_date' => now()->addDays(14)->toDateString(),
            'estimated_total' => 5000000,
            'status' => 'submitted',
        ]);

        // IT requester should NOT be able to view HR PR
        $itRequester = User::where('email', 'requester@orderflow.com')->first();
        $response = $this->actingAs($itRequester)->get(route('purchase-requests.show', $hrPr));

        $response->assertStatus(403);
    }

    public function test_requester_cannot_edit_submitted_pr(): void
    {
        $requester = User::where('role', 'requester')->first();

        $submittedPr = PurchaseRequest::where('user_id', $requester->id)
            ->where('status', 'submitted')
            ->first();

        $this->assertNotNull($submittedPr);

        $response = $this->actingAs($requester)->get(route('purchase-requests.edit', $submittedPr));

        $response->assertStatus(403);
    }
}
