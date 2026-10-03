<?php

namespace Tests\Feature\Api;

use App\Models\Department;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\User;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderFlowRestApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $requesterIt;
    protected User $requesterHr;
    protected User $managerIt;
    protected Department $deptIt;
    protected Department $deptHr;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deptIt = Department::create(['name' => 'Information Technology', 'code' => 'IT']);
        $this->deptHr = Department::create(['name' => 'Human Resources', 'code' => 'HR']);

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'department_id' => $this->deptIt->id,
        ]);

        $this->requesterIt = User::factory()->create([
            'role' => 'requester',
            'department_id' => $this->deptIt->id,
        ]);

        $this->requesterHr = User::factory()->create([
            'role' => 'requester',
            'department_id' => $this->deptHr->id,
        ]);

        $this->managerIt = User::factory()->create([
            'role' => 'manager',
            'department_id' => $this->deptIt->id,
        ]);
    }

    /**
     * 1. API Testing: Respons sukses (200 & 201)
     */
    public function test_api_returns_success_200_and_201_responses(): void
    {
        Sanctum::actingAs($this->requesterIt);

        // POST /api/purchase-requests -> 201 Created
        $payload = [
            'title' => 'Pengadaan Monitor LED 27 Inci',
            'description' => 'Kebutuhan monitor tim developer',
            'required_date' => Carbon::now()->addDays(7)->toDateString(),
            'items' => [
                [
                    'item_name' => 'Monitor LED 27"',
                    'specification' => 'IPS 144Hz',
                    'quantity' => 2,
                    'unit' => 'Unit',
                    'estimated_unit_price' => 3000000,
                ],
            ],
        ];

        $postResponse = $this->postJson(route('api.purchase-requests.store'), $payload);
        $postResponse->assertStatus(201)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'pr_number',
                    'title',
                    'estimated_total',
                    'status',
                    'items',
                ],
            ]);

        $prId = $postResponse->json('data.id');

        // GET /api/purchase-requests/{id} -> 200 OK
        $showResponse = $this->getJson(route('api.purchase-requests.show', $prId));
        $showResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $prId,
                    'title' => 'Pengadaan Monitor LED 27 Inci',
                ],
            ]);
    }

    /**
     * 2. API Testing: Validation error 422
     */
    public function test_api_returns_validation_error_422_when_payload_is_invalid(): void
    {
        Sanctum::actingAs($this->requesterIt);

        // Missing required fields (title, required_date, items)
        $invalidPayload = [
            'description' => 'Tanpa judul dan tanpa items',
        ];

        $response = $this->postJson(route('api.purchase-requests.store'), $invalidPayload);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonValidationErrors(['title', 'required_date', 'items']);
    }

    /**
     * 3. API Testing: Unauthenticated 401
     */
    public function test_api_returns_unauthenticated_401_without_token(): void
    {
        // Calling protected endpoint without Sanctum actingAs / Bearer token
        $response = $this->getJson(route('api.purchase-requests.index'));

        $response->assertStatus(401);
    }

    /**
     * 4. API Testing: Forbidden 403
     */
    public function test_api_returns_forbidden_403_when_accessing_unauthorized_department_pr(): void
    {
        // Create PR owned by Requester HR
        $prHr = PurchaseRequest::create([
            'pr_number' => 'PR-HR-0001',
            'user_id' => $this->requesterHr->id,
            'department_id' => $this->deptHr->id,
            'title' => 'Training HR',
            'description' => 'Pelatihan kepemimpinan HR',
            'required_date' => Carbon::now()->addDays(7)->toDateString(),
            'estimated_total' => 5000000,
            'status' => 'submitted',
        ]);

        // Requester IT tries to access Requester HR's PR
        Sanctum::actingAs($this->requesterIt);

        $response = $this->getJson(route('api.purchase-requests.show', $prHr->id));

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * 5. API Testing: Not found 404
     */
    public function test_api_returns_not_found_404_for_non_existent_resource(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson(route('api.purchase-requests.show', 999999));

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * 6. API Testing: Pagination dan Filter
     */
    public function test_api_handles_pagination_and_filtering_parameters(): void
    {
        Sanctum::actingAs($this->admin);

        // Seed 15 PRs with different statuses & departments
        for ($i = 1; $i <= 8; $i++) {
            PurchaseRequest::create([
                'pr_number' => "PR-IT-PAG-$i",
                'user_id' => $this->requesterIt->id,
                'department_id' => $this->deptIt->id,
                'title' => "Pengadaan IT Hardware $i",
                'description' => "Deskripsi kebutuhan IT hardware $i",
                'required_date' => Carbon::now()->addDays(7)->toDateString(),
                'estimated_total' => 1000000 * $i,
                'status' => 'submitted',
            ]);
        }

        for ($j = 1; $j <= 7; $j++) {
            PurchaseRequest::create([
                'pr_number' => "PR-HR-PAG-$j",
                'user_id' => $this->requesterHr->id,
                'department_id' => $this->deptHr->id,
                'title' => "Kebutuhan HR Umum $j",
                'description' => "Deskripsi pengadaan kebutuhan HR $j",
                'required_date' => Carbon::now()->addDays(7)->toDateString(),
                'estimated_total' => 500000 * $j,
                'status' => 'draft',
            ]);
        }

        // Test Pagination: per_page=5
        $pagedResponse = $this->getJson(route('api.purchase-requests.index', ['per_page' => 5]));
        $pagedResponse->assertStatus(200)
            ->assertJsonPath('data.pagination.per_page', 5)
            ->assertJsonPath('data.pagination.total', 15)
            ->assertJsonPath('data.pagination.last_page', 3);

        $this->assertCount(5, $pagedResponse->json('data.items'));

        // Test Filter by status='submitted'
        $filteredStatusResponse = $this->getJson(route('api.purchase-requests.index', ['status' => 'submitted']));
        $filteredStatusResponse->assertStatus(200)
            ->assertJsonPath('data.pagination.total', 8);

        // Test Filter by department_id
        $filteredDeptResponse = $this->getJson(route('api.purchase-requests.index', ['department_id' => $this->deptHr->id]));
        $filteredDeptResponse->assertStatus(200)
            ->assertJsonPath('data.pagination.total', 7);

        // Test Search query filter
        $searchResponse = $this->getJson(route('api.purchase-requests.index', ['search' => 'Hardware 3']));
        $searchResponse->assertStatus(200)
            ->assertJsonPath('data.pagination.total', 1);
    }
}
