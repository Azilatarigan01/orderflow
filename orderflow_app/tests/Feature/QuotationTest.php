<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\PurchaseRequest;
use App\Models\Quotation;
use App\Models\User;
use App\Models\Vendor;
use App\Services\QuotationScoringService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $procurement;
    protected User $requester;
    protected Department $dept;
    protected Vendor $activeVendor1;
    protected Vendor $activeVendor2;
    protected Vendor $inactiveVendor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dept = Department::create([
            'code' => 'IT',
            'name' => 'Information Technology',
            'is_active' => true,
        ]);

        $this->admin = User::create([
            'name' => 'Super Administrator',
            'email' => 'admin@orderflow.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'department_id' => $this->dept->id,
            'is_active' => true,
        ]);

        $this->procurement = User::create([
            'name' => 'Procurement Officer',
            'email' => 'procurement@orderflow.com',
            'password' => bcrypt('password'),
            'role' => 'procurement',
            'department_id' => $this->dept->id,
            'is_active' => true,
        ]);

        $this->requester = User::create([
            'name' => 'Budi Santoso',
            'email' => 'requester@orderflow.com',
            'password' => bcrypt('password'),
            'role' => 'requester',
            'department_id' => $this->dept->id,
            'is_active' => true,
        ]);

        $this->activeVendor1 = Vendor::create([
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

        $this->activeVendor2 = Vendor::create([
            'code' => 'VND-002',
            'name' => 'CV Mega Bintang',
            'category' => 'Hardware',
            'contact_person' => 'Joko',
            'email' => 'sales@megabintang.com',
            'phone' => '021-654321',
            'address' => 'Surabaya',
            'rating' => 4.50,
            'is_active' => true,
        ]);

        $this->inactiveVendor = Vendor::create([
            'code' => 'VND-999',
            'name' => 'CV Nonaktif Sejahtera',
            'category' => 'Hardware',
            'contact_person' => 'Rudi',
            'email' => 'rudi@nonaktif.com',
            'phone' => '021-999999',
            'address' => 'Bandung',
            'rating' => 3.00,
            'is_active' => false,
        ]);
    }

    private function createApprovedPr(float $estimatedTotal = 15000000): PurchaseRequest
    {
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR-' . date('Ym') . '-9999',
            'user_id' => $this->requester->id,
            'department_id' => $this->dept->id,
            'title' => 'Pengadaan Laptop dan Monitor Developer',
            'description' => 'Kebutuhan perangkat keras untuk tim software development.',
            'required_date' => Carbon::tomorrow(),
            'estimated_total' => $estimatedTotal,
            'status' => 'approved',
        ]);

        $pr->items()->create([
            'item_name' => 'Laptop ThinkPad X1',
            'specification' => 'Core i7, 32GB RAM, 1TB SSD',
            'quantity' => 1,
            'unit' => 'Unit',
            'estimated_unit_price' => $estimatedTotal,
            'subtotal' => $estimatedTotal,
        ]);

        return $pr;
    }

    public function test_procurement_can_view_quotations_index_and_compare_screen(): void
    {
        $pr = $this->createApprovedPr();

        $response = $this->actingAs($this->procurement)->get(route('quotations.index'));
        $response->assertStatus(200);
        $response->assertSee($pr->pr_number);

        $compareResponse = $this->actingAs($this->procurement)->get(route('quotations.compare', $pr));
        $compareResponse->assertStatus(200);
        $compareResponse->assertSee('Matriks Perbandingan Vendor');
    }

    public function test_inactive_vendor_cannot_be_selected_for_quotation(): void
    {
        $pr = $this->createApprovedPr();

        $response = $this->actingAs($this->procurement)->post(route('quotations.store', $pr), [
            'vendor_id' => $this->inactiveVendor->id,
            'quotation_number' => 'QTO-999',
            'shipping_cost' => 50000,
            'tax_amount' => 0,
            'estimated_delivery_days' => 3,
            'items' => [
                [
                    'item_name' => 'Laptop ThinkPad X1',
                    'quantity' => 1,
                    'unit' => 'Unit',
                    'unit_price' => '14.000.000',
                ]
            ],
        ]);

        $response->assertSessionHasErrors(['vendor_id']);
        $this->assertDatabaseCount('quotations', 0);
    }

    public function test_procurement_can_store_quotation_with_correct_totals(): void
    {
        $pr = $this->createApprovedPr();

        $response = $this->actingAs($this->procurement)->post(route('quotations.store', $pr), [
            'vendor_id' => $this->activeVendor1->id,
            'quotation_number' => 'QTO-2026-001',
            'shipping_cost' => '150.000',
            'tax_amount' => '1.540.000',
            'estimated_delivery_days' => 2,
            'warranty_months' => 24,
            'warranty_info' => 'Garansi Resmi Lenovo 2 Tahun',
            'valid_until' => Carbon::now()->addDays(14)->format('Y-m-d'),
            'notes' => 'Franco kantor pusat Jakarta.',
            'items' => [
                [
                    'item_name' => 'Laptop ThinkPad X1',
                    'quantity' => 1,
                    'unit' => 'Unit',
                    'unit_price' => '14.000.000',
                ]
            ],
        ]);

        $response->assertRedirect(route('quotations.compare', $pr));
        $this->assertDatabaseHas('quotations', [
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->activeVendor1->id,
            'subtotal' => 14000000,
            'shipping_cost' => 150000,
            'tax_amount' => 1540000,
            'grand_total' => 15690000,
            'estimated_delivery_days' => 2,
            'warranty_months' => 24,
        ]);
    }

    public function test_expired_quotation_is_detected_by_model(): void
    {
        $pr = $this->createApprovedPr();

        $quotation = Quotation::create([
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->activeVendor1->id,
            'quotation_number' => 'QTO-EXP',
            'subtotal' => 10000000,
            'shipping_cost' => 0,
            'tax_amount' => 0,
            'grand_total' => 10000000,
            'estimated_delivery_days' => 3,
            'valid_until' => Carbon::yesterday(),
        ]);

        $this->assertTrue($quotation->is_expired);
    }

    public function test_weighted_scoring_engine_computes_recommendation(): void
    {
        $pr = $this->createApprovedPr();

        // Vendor 1: Cheaper (10M), 3 days, 12 months, rating 4.8
        $q1 = Quotation::create([
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->activeVendor1->id,
            'subtotal' => 10000000,
            'shipping_cost' => 0,
            'tax_amount' => 0,
            'grand_total' => 10000000,
            'estimated_delivery_days' => 3,
            'warranty_months' => 12,
        ]);

        // Vendor 2: More expensive (12M), 1 day (faster), 24 months (longer), rating 4.5
        $q2 = Quotation::create([
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->activeVendor2->id,
            'subtotal' => 12000000,
            'shipping_cost' => 0,
            'tax_amount' => 0,
            'grand_total' => 12000000,
            'estimated_delivery_days' => 1,
            'warranty_months' => 24,
        ]);

        $service = app(QuotationScoringService::class);
        $result = $service->evaluateQuotations($pr);

        $this->assertCount(2, $result['scored_quotations']);
        $this->assertNotNull($result['best_score_id']);

        $evaluatedQ1 = $result['scored_quotations']->firstWhere('id', $q1->id);
        $evaluatedQ2 = $result['scored_quotations']->firstWhere('id', $q2->id);

        $this->assertGreaterThan(0, $evaluatedQ1->score);
        $this->assertGreaterThan(0, $evaluatedQ2->score);
        $this->assertTrue($evaluatedQ1->is_recommended || $evaluatedQ2->is_recommended);
    }

    public function test_pr_above_10m_requires_at_least_two_quotations_without_single_source(): void
    {
        $pr = $this->createApprovedPr(20000000); // 20 Juta (> 10 Juta)

        // Only 1 quotation entered
        $q1 = Quotation::create([
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->activeVendor1->id,
            'subtotal' => 19000000,
            'grand_total' => 19000000,
            'estimated_delivery_days' => 2,
        ]);

        $response = $this->actingAs($this->procurement)->post(route('quotations.select', $pr), [
            'quotation_id' => $q1->id,
            'selection_reason' => 'Harga terbaik dari vendor pertama.',
            'is_single_source' => false,
        ]);

        $response->assertSessionHasErrors(['selection']);
        $this->assertEquals('approved', $pr->fresh()->status);
        $this->assertFalse((bool) $q1->fresh()->is_selected);
    }

    public function test_pr_above_10m_allows_single_quotation_if_single_source_justified(): void
    {
        $pr = $this->createApprovedPr(20000000); // 20 Juta (> 10 Juta)

        $q1 = Quotation::create([
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->activeVendor1->id,
            'subtotal' => 19000000,
            'grand_total' => 19000000,
            'estimated_delivery_days' => 2,
        ]);

        $response = $this->actingAs($this->procurement)->post(route('quotations.select', $pr), [
            'quotation_id' => $q1->id,
            'selection_reason' => 'Dipilih karena satu-satunya distributor resmi berlisensi.',
            'is_single_source' => true,
            'single_source_reason' => 'Perangkat ini memiliki lisensi eksklusif OEM dan tidak dijual oleh vendor lain.',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals('processing', $pr->fresh()->status);
        $this->assertTrue((bool) $q1->fresh()->is_selected);
        $this->assertTrue((bool) $q1->fresh()->is_single_source);
    }

    public function test_awarding_vendor_updates_status_and_records_audit_trail(): void
    {
        $pr = $this->createApprovedPr(5000000); // 5 Juta (≤ 10 Juta)

        $q1 = Quotation::create([
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->activeVendor1->id,
            'quotation_number' => 'QTO-WINNER-01',
            'subtotal' => 4800000,
            'grand_total' => 4800000,
            'estimated_delivery_days' => 2,
        ]);

        $response = $this->actingAs($this->procurement)->post(route('quotations.select', $pr), [
            'quotation_id' => $q1->id,
            'selection_reason' => 'Harga di bawah budget PR dan garansi terjamin.',
        ]);

        $response->assertRedirect(route('quotations.compare', $pr));
        $this->assertEquals('processing', $pr->fresh()->status);
        $this->assertTrue((bool) $q1->fresh()->is_selected);

        // Verify status history audit
        $this->assertDatabaseHas('status_histories', [
            'purchase_request_id' => $pr->id,
            'from_status' => 'approved',
            'to_status' => 'processing',
            'user_id' => $this->procurement->id,
        ]);

        // Verify in-app notification sent to requester
        $this->assertDatabaseHas('in_app_notifications', [
            'user_id' => $pr->user_id,
            'type' => 'vendor_awarded',
        ]);
    }
}
