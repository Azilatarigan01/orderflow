<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReactManagementDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $manager;
    protected User $requester;
    protected Department $deptIt;
    protected Department $deptOps;
    protected Vendor $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deptIt = Department::create([
            'name' => 'Information Technology',
            'code' => 'IT',
        ]);

        $this->deptOps = Department::create([
            'name' => 'Operations',
            'code' => 'OPS',
        ]);

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'department_id' => $this->deptIt->id,
        ]);

        $this->manager = User::factory()->create([
            'role' => 'manager',
            'department_id' => $this->deptIt->id,
        ]);

        $this->requester = User::factory()->create([
            'role' => 'requester',
            'department_id' => $this->deptIt->id,
        ]);

        $this->vendor = Vendor::create([
            'name' => 'PT Mitra Solusi',
            'code' => 'VND-001',
            'category' => 'Hardware & IT',
            'contact_person' => 'Budi Santoso',
            'email' => 'vendor@example.com',
            'phone' => '081234567890',
            'address' => 'Jl. Jenderal Sudirman',
            'is_active' => true,
        ]);
    }

    public function test_unauthenticated_user_cannot_access_react_dashboard_view(): void
    {
        $response = $this->get(route('management.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authorized_roles_can_render_react_dashboard_view(): void
    {
        $response = $this->actingAs($this->admin)->get(route('management.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('id="react-management-dashboard-root"', false);
        $response->assertSee('Dashboard Eksekutif');
    }

    public function test_web_api_endpoint_returns_json_with_kpis_and_departments(): void
    {
        // Create PR
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR-2026-10-0001',
            'user_id' => $this->requester->id,
            'department_id' => $this->deptIt->id,
            'title' => 'Pengadaan Laptop Developer',
            'description' => 'Kebutuhan laptop tim',
            'purpose' => 'Kebutuhan developer baru',
            'required_date' => Carbon::now()->addDays(14),
            'status' => 'submitted',
            'estimated_total' => 25000000,
        ]);

        // Create Overdue PO
        $po = PurchaseOrder::create([
            'po_number' => 'PO-2026-10-0001',
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->vendor->id,
            'issued_by' => $this->admin->id,
            'order_date' => Carbon::now()->subDays(20),
            'delivery_target_date' => Carbon::now()->subDays(5),
            'subtotal' => 25000000,
            'tax_rate' => 11,
            'tax_amount' => 2750000,
            'grand_total' => 27750000,
            'status' => 'issued',
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('web.management.dashboard-data'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'kpis' => [
                        'total_pr' => 1,
                        'pending_approval_pr' => 1,
                        'active_po' => 1,
                        'overdue_po' => 1,
                    ],
                ],
            ]);

        $this->assertNotEmpty($response->json('data.departments'));
        $this->assertNotEmpty($response->json('data.trend'));
        $this->assertCount(1, $response->json('data.pending_approvals'));
        $this->assertCount(1, $response->json('data.overdue_pos'));
    }

    public function test_dashboard_api_filters_by_department(): void
    {
        // IT PR
        PurchaseRequest::create([
            'pr_number' => 'PR-2026-10-0010',
            'user_id' => $this->requester->id,
            'department_id' => $this->deptIt->id,
            'title' => 'Server Rack IT',
            'description' => 'Rack server data center',
            'purpose' => 'Upgrade server',
            'required_date' => Carbon::now()->addDays(14),
            'status' => 'submitted',
            'estimated_total' => 50000000,
        ]);

        // OPS PR
        $opsUser = User::factory()->create([
            'role' => 'requester',
            'department_id' => $this->deptOps->id,
        ]);

        PurchaseRequest::create([
            'pr_number' => 'PR-2026-10-0020',
            'user_id' => $opsUser->id,
            'department_id' => $this->deptOps->id,
            'title' => 'Genset Kantor',
            'description' => 'Unit genset solar',
            'purpose' => 'Backup listrik',
            'required_date' => Carbon::now()->addDays(14),
            'status' => 'submitted',
            'estimated_total' => 15000000,
        ]);

        // Filter by IT
        $responseIt = $this->actingAs($this->admin)->getJson(route('web.management.dashboard-data', [
            'department_id' => $this->deptIt->id,
        ]));

        $responseIt->assertStatus(200);
        $this->assertEquals(1, $responseIt->json('data.kpis.total_pr'));
        $this->assertEquals(50000000, $responseIt->json('data.kpis.total_pr_budget'));

        // Filter by OPS
        $responseOps = $this->actingAs($this->admin)->getJson(route('web.management.dashboard-data', [
            'department_id' => $this->deptOps->id,
        ]));

        $responseOps->assertStatus(200);
        $this->assertEquals(1, $responseOps->json('data.kpis.total_pr'));
        $this->assertEquals(15000000, $responseOps->json('data.kpis.total_pr_budget'));
    }

    public function test_dashboard_api_filters_by_period(): void
    {
        // PR created 2 months ago
        $oldPr = PurchaseRequest::create([
            'pr_number' => 'PR-2026-08-0001',
            'user_id' => $this->requester->id,
            'department_id' => $this->deptIt->id,
            'title' => 'PR Lama 2 Bulan Lalu',
            'description' => 'Pengadaan lama 2 bulan',
            'purpose' => 'Pengadaan lama',
            'required_date' => Carbon::now()->subMonths(2)->addDays(14),
            'status' => 'completed',
            'estimated_total' => 10000000,
        ]);
        PurchaseRequest::where('id', $oldPr->id)->update([
            'created_at' => Carbon::now()->subMonths(2)->startOfMonth(),
        ]);

        // PR created this month
        $newPr = PurchaseRequest::create([
            'pr_number' => 'PR-2026-10-0099',
            'user_id' => $this->requester->id,
            'department_id' => $this->deptIt->id,
            'title' => 'PR Baru Bulan Ini',
            'description' => 'Pengadaan baru bulan ini',
            'purpose' => 'Pengadaan terkini',
            'required_date' => Carbon::now()->addDays(14),
            'status' => 'submitted',
            'estimated_total' => 20000000,
            'created_at' => Carbon::now(),
        ]);

        // Query for this_month
        $responseThisMonth = $this->actingAs($this->admin)->getJson(route('web.management.dashboard-data', [
            'period' => 'this_month',
        ]));

        $responseThisMonth->assertStatus(200);
        $this->assertEquals(1, $responseThisMonth->json('data.kpis.total_pr'));
        $this->assertEquals(20000000, $responseThisMonth->json('data.kpis.total_pr_budget'));

        // Query for all
        $responseAll = $this->actingAs($this->admin)->getJson(route('web.management.dashboard-data', [
            'period' => 'all',
        ]));

        $responseAll->assertStatus(200);
        $this->assertEquals(2, $responseAll->json('data.kpis.total_pr'));
    }

    public function test_sanctum_bearer_token_access_to_management_dashboard_api(): void
    {
        Sanctum::actingAs($this->manager, ['*']);

        $response = $this->getJson(route('api.management.dashboard-data'));
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }
}
