<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }
    public function test_authenticated_user_can_view_vendors_index(): void
    {
        $user = User::where('role', 'requester')->first();
        $this->assertNotNull($user, 'User seed exists');

        $response = $this->actingAs($user)->get(route('vendors.index'));

        $response->assertStatus(200);
        $response->assertSee('Direktori Rekanan Vendor');
    }

    public function test_procurement_can_access_create_vendor_page(): void
    {
        $procurement = User::where('role', 'procurement')->first();
        $this->assertNotNull($procurement, 'Procurement seed exists');

        $response = $this->actingAs($procurement)->get(route('vendors.create'));

        $response->assertStatus(200);
        $response->assertSee('Registrasi Rekanan Vendor Baru');
    }

    public function test_requester_is_forbidden_from_accessing_create_vendor_page(): void
    {
        $requester = User::where('role', 'requester')->first();
        $this->assertNotNull($requester, 'Requester seed exists');

        $response = $this->actingAs($requester)->get(route('vendors.create'));

        $response->assertStatus(403);
    }

    public function test_procurement_can_store_new_vendor(): void
    {
        $procurement = User::where('role', 'procurement')->first();

        $code = 'VND-TEST-' . rand(100, 999);
        $response = $this->actingAs($procurement)->post(route('vendors.store'), [
            'code' => $code,
            'name' => 'PT Vendor Pengujian Unit',
            'category' => 'Testing Services',
            'contact_person' => 'Budi Tester',
            'email' => 'testing@vendor.com',
            'phone' => '081299990000',
            'address' => 'Gedung Uji Lantai 3, Jakarta',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('vendors.index'));
        $this->assertDatabaseHas('vendors', ['code' => $code]);
    }

    public function test_user_can_switch_role_via_demo_switcher(): void
    {
        $requester = User::where('role', 'requester')->first();

        $response = $this->actingAs($requester)->post(route('switch-role', 'manager'));

        $response->assertRedirect(route('dashboard'));
        $this->assertEquals('manager', auth()->user()->role);
    }
}
