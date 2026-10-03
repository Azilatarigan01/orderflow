<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Quotation;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderOverdueAndCancellationTest extends TestCase
{
    use RefreshDatabase;

    private User $procurementUser;
    private User $requesterUser;
    private User $warehouseUser;
    private Department $department;
    private Vendor $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->department = Department::create([
            'name' => 'Engineering',
            'code' => 'ENG',
            'budget_limit' => 500000000,
        ]);

        $this->procurementUser = User::factory()->create([
            'name' => 'Procurement Lead',
            'role' => 'procurement',
            'department_id' => $this->department->id,
        ]);

        $this->requesterUser = User::factory()->create([
            'name' => 'Requester Engineer',
            'role' => 'requester',
            'department_id' => $this->department->id,
        ]);

        $this->warehouseUser = User::factory()->create([
            'name' => 'Warehouse Receiver',
            'role' => 'warehouse',
            'department_id' => $this->department->id,
        ]);

        $this->vendor = Vendor::create([
            'code' => 'VEND-MSP-01',
            'name' => 'PT Mitra Solusi Prima',
            'category' => 'Hardware',
            'contact_person' => 'Budi Hendrawan',
            'email' => 'sales@mitrasolusi.com',
            'phone' => '021-99887766',
            'address' => 'Jakarta Timur',
        ]);
    }

    private function createSamplePrAndPo(array $poOverrides = []): array
    {
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/ENG/2026/09/0099',
            'user_id' => $this->requesterUser->id,
            'department_id' => $this->department->id,
            'title' => 'Pengadaan Server Rak Data Center',
            'description' => 'Server rackmount untuk kebutuhan data center.',
            'category' => 'goods',
            'priority' => 'high',
            'required_date' => now()->addDays(30),
            'status' => 'processing',
            'estimated_total' => 50000000,
        ]);

        $item = $pr->items()->create([
            'item_name' => 'Server 2U Rackmount',
            'quantity' => 2,
            'unit' => 'Unit',
            'estimated_unit_price' => 25000000,
            'subtotal' => 50000000,
        ]);

        $quotation = Quotation::create([
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->vendor->id,
            'quotation_number' => 'QUO-MSP-2026-001',
            'subtotal' => 50000000,
            'shipping_cost' => 0,
            'tax_amount' => 5500000,
            'grand_total' => 55500000,
            'estimated_delivery_days' => 14,
            'is_selected' => true,
        ]);

        $poData = array_merge([
            'po_number' => 'PO/PROC/2026/09/0099',
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->vendor->id,
            'quotation_id' => $quotation->id,
            'issued_by' => $this->procurementUser->id,
            'order_date' => now()->subDays(20),
            'delivery_target_date' => now()->subDays(5)->toDateString(),
            'subtotal' => 50000000,
            'tax_rate' => 11,
            'shipping_fee' => 0,
            'tax_amount' => 5500000,
            'grand_total' => 55500000,
            'status' => 'issued',
            'payment_terms' => 'Net 30 Days',
        ], $poOverrides);

        $po = PurchaseOrder::create($poData);

        $po->items()->create([
            'purchase_request_item_id' => $item->id,
            'item_name' => 'Server 2U Rackmount',
            'quantity' => 2,
            'received_quantity' => 0,
            'unit' => 'Unit',
            'unit_price' => 25000000,
            'subtotal' => 50000000,
        ]);

        return [$pr, $quotation, $po];
    }

    public function test_po_overdue_and_liquidated_damages_penalty_calculation(): void
    {
        [$pr, $quotation, $po] = $this->createSamplePrAndPo([
            'delivery_target_date' => now()->subDays(5)->toDateString(),
        ]);

        $this->assertTrue($po->is_overdue);
        $this->assertEquals(5, $po->overdue_days);
        $this->assertEquals(0.5, $po->penalty_percentage);
        $this->assertEquals(277500.0, $po->estimated_penalty_amount);
        $this->assertEquals('Rp 277.500', $po->formatted_estimated_penalty);

        // Cap at 5.0% maximum after 50+ days
        $po->update(['delivery_target_date' => now()->subDays(60)->toDateString()]);
        $po->refresh();

        $this->assertTrue($po->is_overdue);
        $this->assertEquals(60, $po->overdue_days);
        $this->assertEquals(5.0, $po->penalty_percentage);
        $this->assertEquals(2775000.0, $po->estimated_penalty_amount);

        // Completed PO should NOT be considered overdue
        $po->update(['status' => 'completed']);
        $this->assertFalse($po->is_overdue);
        $this->assertEquals(0, $po->overdue_days);
        $this->assertEquals(0.0, $po->penalty_percentage);
    }

    public function test_procurement_can_send_default_notice_to_vendor(): void
    {
        [$pr, $quotation, $po] = $this->createSamplePrAndPo();

        $response = $this->actingAs($this->procurementUser)
            ->post(route('purchase-orders.default-notice', $po));

        $response->assertRedirect(route('purchase-orders.show', $po));
        $response->assertSessionHas('success');

        $po->refresh();
        $this->assertEquals(1, $po->default_notice_count);
        $this->assertNotNull($po->default_notice_sent_at);

        $this->assertDatabaseHas('status_histories', [
            'purchase_request_id' => $pr->id,
            'user_id' => $this->procurementUser->id,
        ]);

        $this->assertDatabaseHas('in_app_notifications', [
            'user_id' => $pr->user_id,
            'type' => 'po_default_notice',
        ]);
    }

    public function test_procurement_can_cancel_po_due_to_default_and_unaward_quotation(): void
    {
        [$pr, $quotation, $po] = $this->createSamplePrAndPo();

        $this->assertTrue($quotation->is_selected);

        $response = $this->actingAs($this->procurementUser)
            ->post(route('purchase-orders.cancel', $po), [
                'cancellation_reason' => 'Vendor tidak mengirimkan barang setelah lewat 10 hari dan tidak merespon teguran resmi.',
            ]);

        $response->assertRedirect(route('purchase-orders.show', $po));
        $response->assertSessionHas('success');

        $po->refresh();
        $this->assertEquals('cancelled', $po->status);
        $this->assertNotNull($po->cancelled_at);
        $this->assertStringContainsString('Vendor tidak mengirimkan barang', $po->cancellation_reason);

        // Quotation selection must be released
        $quotation->refresh();
        $this->assertFalse($quotation->is_selected);
        $this->assertStringContainsString('wanprestasi', $quotation->selection_reason);

        // PR status remains processing/ready for re-award
        $pr->refresh();
        $this->assertNotEquals('completed', $pr->status);

        $this->assertDatabaseHas('in_app_notifications', [
            'user_id' => $pr->user_id,
            'type' => 'po_cancelled',
        ]);
    }

    public function test_non_procurement_user_cannot_cancel_or_send_default_notice(): void
    {
        [$pr, $quotation, $po] = $this->createSamplePrAndPo();

        $this->actingAs($this->requesterUser)
            ->post(route('purchase-orders.default-notice', $po))
            ->assertForbidden();

        $this->actingAs($this->requesterUser)
            ->post(route('purchase-orders.cancel', $po), [
                'cancellation_reason' => 'Ingin dibatalkan',
            ])
            ->assertForbidden();
    }
}
