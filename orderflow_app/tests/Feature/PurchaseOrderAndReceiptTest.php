<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Quotation;
use App\Models\User;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderAndReceiptTest extends TestCase
{
    use RefreshDatabase;

    protected User $procurement;
    protected User $requester;
    protected Department $dept;
    protected Vendor $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dept = Department::create([
            'code' => 'IT',
            'name' => 'Information Technology',
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

    private function createApprovedPrWithSelectedQuotation(int $itemQty = 5, float $unitPrice = 1000000): array
    {
        $total = $itemQty * $unitPrice;

        $pr = PurchaseRequest::create([
            'pr_number' => 'PR-' . date('Ym') . '-1234',
            'user_id' => $this->requester->id,
            'department_id' => $this->dept->id,
            'title' => 'Pengadaan Laptop Kantor',
            'description' => 'Untuk karyawan baru departemen IT.',
            'required_date' => Carbon::tomorrow(),
            'estimated_total' => $total,
            'status' => 'approved',
        ]);

        $prItem = $pr->items()->create([
            'item_name' => 'Laptop ThinkPad',
            'specification' => '16GB RAM, 512GB SSD',
            'quantity' => $itemQty,
            'unit' => 'Unit',
            'estimated_unit_price' => $unitPrice,
            'subtotal' => $total,
        ]);

        $quotation = Quotation::create([
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->vendor->id,
            'quotation_number' => 'QTO-WIN-01',
            'subtotal' => $total,
            'shipping_cost' => 50000,
            'tax_amount' => 110000,
            'grand_total' => $total + 50000 + 110000,
            'estimated_delivery_days' => 3,
            'warranty_months' => 24,
            'is_selected' => true,
            'selection_reason' => 'Harga terbaik dan garansi resmi paling memadai.',
        ]);

        $qItem = $quotation->items()->create([
            'purchase_request_item_id' => $prItem->id,
            'item_name' => 'Laptop ThinkPad',
            'specification' => '16GB RAM, 512GB SSD',
            'quantity' => $itemQty,
            'unit' => 'Unit',
            'unit_price' => $unitPrice,
            'subtotal' => $total,
        ]);

        return [$pr, $quotation, $prItem, $qItem];
    }

    public function test_po_cannot_be_created_from_unapproved_pr(): void
    {
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR-' . date('Ym') . '-0002',
            'user_id' => $this->requester->id,
            'department_id' => $this->dept->id,
            'title' => 'Draf PR Tanpa Approval',
            'description' => 'Pengajuan belum disetujui.',
            'required_date' => Carbon::tomorrow(),
            'estimated_total' => 5000000,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->procurement)->post(route('purchase-orders.store'), [
            'purchase_request_id' => $pr->id,
            'payment_terms' => 'Net 30 Days',
            'status' => 'issued',
        ]);

        $response->assertSessionHasErrors(['purchase_request_id']);
        $this->assertDatabaseCount('purchase_orders', 0);
    }

    public function test_po_created_successfully_with_quotation_prices_and_items(): void
    {
        [$pr, $quotation] = $this->createApprovedPrWithSelectedQuotation(5, 2000000);

        $response = $this->actingAs($this->procurement)->post(route('purchase-orders.store'), [
            'purchase_request_id' => $pr->id,
            'payment_terms' => 'Net 30 Days',
            'delivery_target_date' => Carbon::tomorrow()->format('Y-m-d'),
            'status' => 'issued',
            'notes' => 'Harap konfirmasi sebelum pengiriman.',
        ]);

        $this->assertDatabaseCount('purchase_orders', 1);
        $po = PurchaseOrder::first();

        $response->assertRedirect(route('purchase-orders.show', $po));
        $this->assertEquals('issued', $po->status);
        $this->assertEquals($quotation->subtotal, $po->subtotal);
        $this->assertEquals($quotation->grand_total, $po->grand_total);
        $this->assertCount(1, $po->items);
        $this->assertEquals(5, $po->items->first()->quantity);
        $this->assertEquals(0, $po->items->first()->received_quantity);
    }

    public function test_quantity_received_cannot_exceed_po_quantity(): void
    {
        [$pr, $quotation] = $this->createApprovedPrWithSelectedQuotation(5, 1000000);

        $this->actingAs($this->procurement)->post(route('purchase-orders.store'), [
            'purchase_request_id' => $pr->id,
            'payment_terms' => 'Net 30 Days',
            'status' => 'issued',
        ]);

        $po = PurchaseOrder::first();
        $poItem = $po->items->first();

        // Attempt to receive 6 units when PO is only 5 units
        $response = $this->actingAs($this->procurement)->post(route('goods-receipts.store'), [
            'purchase_order_id' => $po->id,
            'receipt_type' => 'goods',
            'received_date' => Carbon::today()->format('Y-m-d'),
            'item_condition' => 'Baik (100% OK)',
            'delivery_note_no' => 'SJ-OVER-01',
            'items' => [
                [
                    'po_item_id' => $poItem->id,
                    'quantity_received' => 6,
                ]
            ],
        ]);

        $response->assertSessionHasErrors(['items']);
        $this->assertDatabaseCount('goods_receipts', 0);
        $this->assertEquals(0, $poItem->fresh()->received_quantity);
    }

    public function test_receiving_four_out_of_five_items_results_in_partially_received(): void
    {
        [$pr] = $this->createApprovedPrWithSelectedQuotation(5, 1000000);

        $this->actingAs($this->procurement)->post(route('purchase-orders.store'), [
            'purchase_request_id' => $pr->id,
            'payment_terms' => 'Net 30 Days',
            'status' => 'issued',
        ]);

        $po = PurchaseOrder::first();
        $poItem = $po->items->first();

        // Receive 4 units
        $response = $this->actingAs($this->procurement)->post(route('goods-receipts.store'), [
            'purchase_order_id' => $po->id,
            'receipt_type' => 'goods',
            'received_date' => Carbon::today()->format('Y-m-d'),
            'item_condition' => 'Baik (100% OK)',
            'delivery_note_no' => 'SJ-PARTIAL-01',
            'items' => [
                [
                    'po_item_id' => $poItem->id,
                    'quantity_received' => 4,
                ]
            ],
        ]);

        $response->assertRedirect(route('purchase-orders.show', $po));
        $this->assertEquals('partially_received', $po->fresh()->status);
        $this->assertEquals(4, $poItem->fresh()->received_quantity);
        $this->assertEquals(1, $poItem->fresh()->remaining_quantity);
    }

    public function test_receiving_all_remaining_items_results_in_completed_po(): void
    {
        [$pr] = $this->createApprovedPrWithSelectedQuotation(5, 1000000);

        $this->actingAs($this->procurement)->post(route('purchase-orders.store'), [
            'purchase_request_id' => $pr->id,
            'payment_terms' => 'Net 30 Days',
            'status' => 'issued',
        ]);

        $po = PurchaseOrder::first();
        $poItem = $po->items->first();

        // First delivery: 4 units
        $this->actingAs($this->procurement)->post(route('goods-receipts.store'), [
            'purchase_order_id' => $po->id,
            'receipt_type' => 'goods',
            'received_date' => Carbon::today()->format('Y-m-d'),
            'item_condition' => 'Baik (100% OK)',
            'delivery_note_no' => 'SJ-BATCH-1',
            'items' => [
                [
                    'po_item_id' => $poItem->id,
                    'quantity_received' => 4,
                ]
            ],
        ]);

        $this->assertEquals('partially_received', $po->fresh()->status);

        // Second delivery: remaining 1 unit
        $this->actingAs($this->procurement)->post(route('goods-receipts.store'), [
            'purchase_order_id' => $po->id,
            'receipt_type' => 'goods',
            'received_date' => Carbon::today()->format('Y-m-d'),
            'item_condition' => 'Baik (100% OK)',
            'delivery_note_no' => 'SJ-BATCH-2',
            'items' => [
                [
                    'po_item_id' => $poItem->id,
                    'quantity_received' => 1,
                ]
            ],
        ]);

        $this->assertEquals('completed', $po->fresh()->status);
        $this->assertEquals('completed', $pr->fresh()->status);
        $this->assertEquals(5, $poItem->fresh()->received_quantity);
        $this->assertEquals(0, $poItem->fresh()->remaining_quantity);
    }

    public function test_service_acceptance_bast_records_service_fields(): void
    {
        [$pr] = $this->createApprovedPrWithSelectedQuotation(1, 5000000);

        $this->actingAs($this->procurement)->post(route('purchase-orders.store'), [
            'purchase_request_id' => $pr->id,
            'payment_terms' => 'Net 30 Days',
            'status' => 'issued',
        ]);

        $po = PurchaseOrder::first();
        $poItem = $po->items->first();

        $response = $this->actingAs($this->procurement)->post(route('goods-receipts.store'), [
            'purchase_order_id' => $po->id,
            'receipt_type' => 'service',
            'received_date' => Carbon::today()->format('Y-m-d'),
            'item_condition' => 'Baik (100% OK)',
            'service_period_start' => Carbon::yesterday()->format('Y-m-d'),
            'service_period_end' => Carbon::today()->format('Y-m-d'),
            'acceptance_approver_name' => 'Budi Santoso (Lead IT)',
            'service_deliverables' => 'Pekerjaan migrasi cloud dan konfigurasi server telah diselesaikan.',
            'items' => [
                [
                    'po_item_id' => $poItem->id,
                    'quantity_received' => 1,
                ]
            ],
        ]);

        $response->assertRedirect(route('purchase-orders.show', $po));
        $gr = GoodsReceipt::first();
        $this->assertEquals('service', $gr->receipt_type);
        $this->assertStringContainsString('BAST', $gr->gr_number);
        $this->assertEquals('Budi Santoso (Lead IT)', $gr->acceptance_approver_name);
        $this->assertEquals('completed', $po->fresh()->status);
    }

    public function test_po_with_goods_receipt_cannot_be_deleted(): void
    {
        [$pr] = $this->createApprovedPrWithSelectedQuotation(1, 1000000);

        $this->actingAs($this->procurement)->post(route('purchase-orders.store'), [
            'purchase_request_id' => $pr->id,
            'payment_terms' => 'Net 30 Days',
            'status' => 'issued',
        ]);

        $po = PurchaseOrder::first();
        $poItem = $po->items->first();

        // Create Goods Receipt
        $this->actingAs($this->procurement)->post(route('goods-receipts.store'), [
            'purchase_order_id' => $po->id,
            'receipt_type' => 'goods',
            'received_date' => Carbon::today()->format('Y-m-d'),
            'item_condition' => 'Baik (100% OK)',
            'items' => [
                [
                    'po_item_id' => $poItem->id,
                    'quantity_received' => 1,
                ]
            ],
        ]);

        // Attempt deletion
        $response = $this->actingAs($this->procurement)->delete(route('purchase-orders.destroy', $po));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('purchase_orders', ['id' => $po->id]);
    }
}
