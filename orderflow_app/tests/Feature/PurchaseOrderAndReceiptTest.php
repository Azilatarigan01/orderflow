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

        $this->warehouse = User::create([
            'name' => 'Staff Gudang',
            'email' => 'warehouse@orderflow.com',
            'password' => bcrypt('password'),
            'role' => 'warehouse',
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

    public function test_purchase_orders_index_can_be_rendered(): void
    {
        $response = $this->actingAs($this->procurement)->get(route('purchase-orders.index'));
        $response->assertStatus(200);
        $response->assertViewHas('purchaseOrders');
        $response->assertViewHas('metrics');
    }

    private function createApprovedPrWithSelectedQuotation(int $itemQty = 5, float $unitPrice = 1000000): array
    {
        $total = $itemQty * $unitPrice;

        $tax = round($total * 0.11, 2);

        $pr = PurchaseRequest::create([
            'pr_number' => 'PR-' . date('Ym') . '-1234',
            'user_id' => $this->requester->id,
            'department_id' => $this->dept->id,
            'title' => 'Pengadaan Laptop Kantor',
            'description' => 'Untuk karyawan baru departemen IT.',
            'required_date' => Carbon::tomorrow(),
            'estimated_total' => round($total * 1.25, 2),
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
            'tax_amount' => $tax,
            'grand_total' => $total + 50000 + $tax,
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
        $response = $this->actingAs($this->warehouse)->post(route('goods-receipts.store'), [
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
        $response = $this->actingAs($this->warehouse)->post(route('goods-receipts.store'), [
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
        $this->actingAs($this->warehouse)->post(route('goods-receipts.store'), [
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
        $this->actingAs($this->warehouse)->post(route('goods-receipts.store'), [
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

        $response = $this->actingAs($this->warehouse)->post(route('goods-receipts.store'), [
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
        [$pr] = $this->createApprovedPrWithSelectedQuotation(2, 1000000);

        $this->actingAs($this->procurement)->post(route('purchase-orders.store'), [
            'purchase_request_id' => $pr->id,
            'payment_terms' => 'Net 30 Days',
            'status' => 'issued',
        ]);

        $po = PurchaseOrder::first();
        $poItem = $po->items->first();

        // Create Goods Receipt
        $this->actingAs($this->warehouse)->post(route('goods-receipts.store'), [
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

    public function test_pr_multi_vendor_split_po_flow_and_completion(): void
    {
        $vendorFurniture = Vendor::create([
            'code' => 'VND-002',
            'name' => 'PT Furnitur Ergonomis Sejahtera',
            'category' => 'Furniture',
            'contact_person' => 'Dewi',
            'email' => 'sales@furnitur.com',
            'phone' => '021-998877',
            'address' => 'Surabaya',
            'rating' => 4.90,
            'is_active' => true,
        ]);

        // PR with 2 items: 5 Laptops (IT) + 2 Chairs (Furniture)
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR-' . date('Ym') . '-9999',
            'user_id' => $this->requester->id,
            'department_id' => $this->dept->id,
            'title' => 'Pengadaan Laptop dan Kursi Kerja',
            'description' => 'Multi-kategori: IT dan Perlengkapan Kantor.',
            'required_date' => Carbon::tomorrow(),
            'estimated_total' => 50000000 + 4000000,
            'status' => 'approved',
        ]);

        $itemLaptop = $pr->items()->create([
            'item_name' => 'Laptop ThinkPad X1',
            'specification' => 'Core i7, 32GB RAM',
            'quantity' => 5,
            'unit' => 'Unit',
            'estimated_unit_price' => 10000000,
            'subtotal' => 50000000,
        ]);

        $itemKursi = $pr->items()->create([
            'item_name' => 'Kursi Ergonomis Mesh',
            'specification' => 'Lumbar support adjustable',
            'quantity' => 2,
            'unit' => 'Unit',
            'estimated_unit_price' => 2000000,
            'subtotal' => 4000000,
        ]);

        // Quotation 1: IT Vendor (Laptop)
        $qLaptop = Quotation::create([
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->vendor->id,
            'quotation_number' => 'QTO-IT-01',
            'subtotal' => 48000000,
            'shipping_cost' => 100000,
            'tax_amount' => 0,
            'grand_total' => 48100000,
            'estimated_delivery_days' => 5,
            'is_selected' => false,
        ]);
        $qLaptop->items()->create([
            'purchase_request_item_id' => $itemLaptop->id,
            'item_name' => 'Laptop ThinkPad X1',
            'quantity' => 5,
            'unit' => 'Unit',
            'unit_price' => 9600000,
            'subtotal' => 48000000,
        ]);

        // Quotation 2: Furniture Vendor (Chair)
        $qKursi = Quotation::create([
            'purchase_request_id' => $pr->id,
            'vendor_id' => $vendorFurniture->id,
            'quotation_number' => 'QTO-FURN-01',
            'subtotal' => 3800000,
            'shipping_cost' => 50000,
            'tax_amount' => 0,
            'grand_total' => 3850000,
            'estimated_delivery_days' => 3,
            'is_selected' => false,
        ]);
        $qKursi->items()->create([
            'purchase_request_item_id' => $itemKursi->id,
            'item_name' => 'Kursi Ergonomis Mesh',
            'quantity' => 2,
            'unit' => 'Unit',
            'unit_price' => 1900000,
            'subtotal' => 3800000,
        ]);

        // Award Quotation 1 as Split Award
        $this->actingAs($this->procurement)->post(route('quotations.select', $pr), [
            'quotation_id' => $qLaptop->id,
            'is_split_award' => 1,
            'selection_reason' => 'Pemenang pengadaan kategori IT Hardware.',
        ]);

        // Award Quotation 2 as Split Award
        $this->actingAs($this->procurement)->post(route('quotations.select', $pr), [
            'quotation_id' => $qKursi->id,
            'is_split_award' => 1,
            'selection_reason' => 'Pemenang pengadaan kategori Kursi Ergonomis.',
        ]);

        // Verify both are selected
        $this->assertTrue($qLaptop->fresh()->is_selected);
        $this->assertTrue($qKursi->fresh()->is_selected);
        $this->assertEquals(2, $pr->fresh()->selectedQuotations()->count());
        $this->assertTrue($pr->fresh()->hasMultipleVendors());

        // Issue PO #1 for Laptop
        $this->actingAs($this->procurement)->post(route('purchase-orders.store'), [
            'purchase_request_id' => $pr->id,
            'quotation_id' => $qLaptop->id,
            'payment_terms' => 'Net 30 Days',
            'status' => 'issued',
        ]);
        $this->assertEquals(1, $pr->fresh()->purchaseOrders()->count());
        $po1 = $pr->fresh()->purchaseOrders()->first();
        $this->assertEquals($this->vendor->id, $po1->vendor_id);

        // Issue PO #2 for Kursi
        $this->actingAs($this->procurement)->post(route('purchase-orders.store'), [
            'purchase_request_id' => $pr->id,
            'quotation_id' => $qKursi->id,
            'payment_terms' => 'Net 14 Days',
            'status' => 'issued',
        ]);
        $this->assertEquals(2, $pr->fresh()->purchaseOrders()->count());
        $po2 = $pr->fresh()->purchaseOrders()->where('vendor_id', $vendorFurniture->id)->first();
        $this->assertNotNull($po2);

        // Deliver PO #1 fully (5 Laptops)
        $this->actingAs($this->warehouse)->post(route('goods-receipts.store'), [
            'purchase_order_id' => $po1->id,
            'receipt_type' => 'goods',
            'received_date' => Carbon::today()->format('Y-m-d'),
            'item_condition' => 'Baik (100% OK)',
            'items' => [
                [
                    'po_item_id' => $po1->items->first()->id,
                    'quantity_received' => 5,
                ]
            ],
        ]);

        // PO #1 is completed, but PR is STILL processing because PO #2 is not completed
        $this->assertEquals('completed', $po1->fresh()->status);
        $this->assertEquals('processing', $pr->fresh()->status);

        // Deliver PO #2 fully (2 Kursi)
        $this->actingAs($this->warehouse)->post(route('goods-receipts.store'), [
            'purchase_order_id' => $po2->id,
            'receipt_type' => 'goods',
            'received_date' => Carbon::today()->format('Y-m-d'),
            'item_condition' => 'Baik (100% OK)',
            'items' => [
                [
                    'po_item_id' => $po2->items->first()->id,
                    'quantity_received' => 2,
                ]
            ],
        ]);

        // NOW both POs are completed, so PR automatically transitions to completed!
        $this->assertEquals('completed', $po2->fresh()->status);
        $this->assertEquals('completed', $pr->fresh()->status);
    }

    /**
     * Test Poin 1: PR status strictly remains 'processing' if some PR items are not yet ordered in PO,
     * even if an existing PO is completed. PR only becomes 'completed' when all PR items are fulfilled.
     */
    public function test_pr_remains_processing_if_some_pr_items_not_yet_ordered(): void
    {
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/IT/2026/09/8888',
            'user_id' => $this->requester->id,
            'department_id' => $this->dept->id,
            'title' => 'Pengadaan Parsial Peralatan Lab',
            'description' => 'Item 1 dan Item 2',
            'required_date' => Carbon::tomorrow(),
            'estimated_total' => 20000000,
            'status' => 'approved',
        ]);

        $item1 = $pr->items()->create([
            'item_name' => 'Mikroskop Digital',
            'specification' => 'High Res',
            'quantity' => 2,
            'unit' => 'Unit',
            'estimated_unit_price' => 5000000,
            'subtotal' => 10000000,
        ]);

        $item2 = $pr->items()->create([
            'item_name' => 'Centrifuge Lab',
            'specification' => '4000 RPM',
            'quantity' => 2,
            'unit' => 'Unit',
            'estimated_unit_price' => 5000000,
            'subtotal' => 10000000,
        ]);

        // PO 1 only covers item1 (Mikroskop)
        $po1 = PurchaseOrder::create([
            'po_number' => 'PO/PROC/2026/09/8881',
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->vendor->id,
            'issued_by' => $this->procurement->id,
            'order_date' => Carbon::today(),
            'status' => 'issued',
            'subtotal' => 10000000,
            'grand_total' => 10000000,
            'payment_terms' => 'Net 30 Days',
        ]);

        $po1Item = $po1->items()->create([
            'purchase_request_item_id' => $item1->id,
            'item_name' => 'Mikroskop Digital',
            'specification' => 'High Res',
            'quantity' => 2,
            'unit' => 'Unit',
            'unit_price' => 5000000,
            'subtotal' => 10000000,
            'received_quantity' => 0,
        ]);

        $pr->update(['status' => 'processing']);

        // Goods Receipt arrives for PO 1 (2 Mikroskop received complete)
        $this->actingAs($this->warehouse)->post(route('goods-receipts.store'), [
            'purchase_order_id' => $po1->id,
            'receipt_type' => 'goods',
            'received_date' => Carbon::today()->format('Y-m-d'),
            'item_condition' => 'Baik (100% OK)',
            'items' => [
                [
                    'po_item_id' => $po1Item->id,
                    'quantity_received' => 2,
                ]
            ],
        ]);

        // PO 1 is completed
        $this->assertEquals('completed', $po1->fresh()->status);
        // BUT PR must REMAIN processing because Centrifuge (item2) has NOT been ordered or fulfilled!
        $this->assertEquals('processing', $pr->fresh()->status);

        // Now issue PO 2 for item2 (Centrifuge)
        $po2 = PurchaseOrder::create([
            'po_number' => 'PO/PROC/2026/09/8882',
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->vendor->id,
            'issued_by' => $this->procurement->id,
            'order_date' => Carbon::today(),
            'status' => 'issued',
            'subtotal' => 10000000,
            'grand_total' => 10000000,
            'payment_terms' => 'Net 30 Days',
        ]);

        $po2Item = $po2->items()->create([
            'purchase_request_item_id' => $item2->id,
            'item_name' => 'Centrifuge Lab',
            'specification' => '4000 RPM',
            'quantity' => 2,
            'unit' => 'Unit',
            'unit_price' => 5000000,
            'subtotal' => 10000000,
            'received_quantity' => 0,
        ]);

        // Goods Receipt arrives for PO 2 (2 Centrifuge received complete)
        $this->actingAs($this->warehouse)->post(route('goods-receipts.store'), [
            'purchase_order_id' => $po2->id,
            'receipt_type' => 'goods',
            'received_date' => Carbon::today()->format('Y-m-d'),
            'item_condition' => 'Baik (100% OK)',
            'items' => [
                [
                    'po_item_id' => $po2Item->id,
                    'quantity_received' => 2,
                ]
            ],
        ]);

        // Now both POs are completed AND all PR items are covered -> PR completed!
        $this->assertEquals('completed', $po2->fresh()->status);
        $this->assertEquals('completed', $pr->fresh()->status);

        // Status history is recorded for PR completion
        $this->assertDatabaseHas('status_histories', [
            'purchase_request_id' => $pr->id,
            'to_status' => 'completed',
        ]);

        // Requester notification was sent
        $this->assertDatabaseHas('in_app_notifications', [
            'user_id' => $this->requester->id,
            'type' => 'pr_completed',
        ]);
    }

    /**
     * Test Poin 3: Damaged/defective items at Goods Receipt do NOT count towards PO fulfillment.
     */
    public function test_goods_receipt_defective_items_keeps_po_open_and_marks_gr_disputed(): void
    {
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/IT/2026/09/0099',
            'user_id' => $this->requester->id,
            'department_id' => $this->dept->id,
            'title' => 'Pengadaan Monitor LED',
            'description' => 'Kebutuhan monitor designer & dev.',
            'required_date' => Carbon::tomorrow()->format('Y-m-d'),
            'status' => 'approved',
            'total_estimated_cost' => 20000000,
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO/PROC/2026/09/0099',
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->vendor->id,
            'order_date' => Carbon::today()->format('Y-m-d'),
            'subtotal' => 20000000,
            'tax_amount' => 2200000,
            'total_amount' => 22200000,
            'payment_terms' => 'Net 30 Days',
            'delivery_address' => 'Gedung IT Lantai 3',
            'delivery_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'issued_by' => $this->procurement->id,
            'status' => 'issued',
        ]);

        $poItem = $po->items()->create([
            'item_name' => 'Monitor LED 27 Inch 4K',
            'quantity' => 10,
            'unit' => 'Unit',
            'unit_price' => 2000000,
            'total_price' => 20000000,
            'received_quantity' => 0,
        ]);

        // 1. Warehouse receives physical 10 units, but 2 units have cracked screens
        $response = $this->actingAs($this->warehouse)->post(route('goods-receipts.store'), [
            'purchase_order_id' => $po->id,
            'receipt_type' => 'goods',
            'received_date' => Carbon::today()->format('Y-m-d'),
            'item_condition' => 'Sebagian Rusak / Cacat',
            'inspection_notes' => 'Terdapat 2 panel layar retap/pecah pada saat unboxing di gudang.',
            'items' => [
                [
                    'po_item_id' => $poItem->id,
                    'quantity_received' => 10,
                    'quantity_rejected' => 2,
                    'notes' => '2 unit panel LCD pecah benturan pengiriman',
                ],
            ],
        ]);

        $response->assertRedirect();
        
        // Assert GR status is 'disputed'
        $gr1 = GoodsReceipt::latest('id')->first();
        $this->assertEquals('disputed', $gr1->status);
        $this->assertEquals(10, $gr1->items->first()->quantity_received);
        $this->assertEquals(2, $gr1->items->first()->quantity_rejected);
        $this->assertEquals(8, $gr1->items->first()->quantity_accepted);

        // Assert PO received quantity incremented ONLY by 8 (usable), NOT 10!
        $po->refresh();
        $poItem->refresh();
        $this->assertEquals(8, $poItem->received_quantity);
        $this->assertEquals('partially_received', $po->status); // PO remains open!

        // 2. Vendor sends 2 replacement units
        $response2 = $this->actingAs($this->warehouse)->post(route('goods-receipts.store'), [
            'purchase_order_id' => $po->id,
            'receipt_type' => 'goods',
            'received_date' => Carbon::today()->format('Y-m-d'),
            'item_condition' => 'Baik (Pengganti)',
            'inspection_notes' => '2 unit pengganti tiba dalam kondisi prima lolos QC.',
            'items' => [
                [
                    'po_item_id' => $poItem->id,
                    'quantity_received' => 2,
                    'quantity_rejected' => 0,
                    'notes' => 'Barang pengganti retur garansi',
                ],
            ],
        ]);

        $response2->assertRedirect();
        $gr2 = GoodsReceipt::latest('id')->first();
        $this->assertEquals('completed', $gr2->status);

        // Now PO received quantity is full (10) and PO is completed!
        $po->refresh();
        $poItem->refresh();
        $this->assertEquals(10, $poItem->received_quantity);
        $this->assertEquals('completed', $po->status);
    }

    public function test_goods_receipt_with_uom_conversion_fulfills_po_accurately(): void
    {
        // 1. Setup PO with item in Box (2 Box ordered)
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/IT/2026/09/0099',
            'department_id' => $this->dept->id,
            'user_id' => $this->requester->id,
            'status' => 'approved',
            'priority' => 'medium',
            'title' => 'Pengadaan Kertas HVS Kantor',
            'description' => 'Pengadaan kertas untuk kebutuhan ATK bulanan',
            'estimated_total' => 500000,
            'required_date' => Carbon::now()->addDays(7),
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO/IT/2026/09/0099',
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->vendor->id,
            'created_by' => $this->procurement->id,
            'status' => 'issued',
            'subtotal' => 500000,
            'tax_amount' => 55000,
            'total_amount' => 555000,
            'order_date' => Carbon::today(),
        ]);

        $poItem = $po->items()->create([
            'item_name' => 'Kertas A4 80gr',
            'specification' => 'PaperOne 80 GSM',
            'quantity' => 2,
            'unit' => 'Box',
            'unit_price' => 250000,
            'subtotal' => 500000,
            'received_quantity' => 0,
        ]);

        // 2. Warehouse staff receives delivery in Rim (10 Rim delivered on Surat Jalan)
        $response = $this->actingAs($this->warehouse)->post(route('goods-receipts.store'), [
            'purchase_order_id' => $po->id,
            'receipt_type' => 'goods',
            'received_date' => Carbon::today()->format('Y-m-d'),
            'delivery_note_number' => 'SJ-RIM-1099',
            'item_condition' => 'Baik',
            'inspection_notes' => 'Tiba 10 Rim kertas sesuai konversi 1 Box = 5 Rim.',
            'items' => [
                [
                    'po_item_id' => $poItem->id,
                    'received_unit' => 'Rim',
                    'quantity_received' => 10, // 10 Rim
                    'quantity_rejected' => 0,
                    'notes' => 'Diterima dalam satuan Rim',
                ],
            ],
        ]);

        $response->assertRedirect();

        // 3. Verify GR Item has raw received data and converted normalized data
        $gr = GoodsReceipt::latest('id')->first();
        $this->assertNotNull($gr);
        $this->assertEquals('completed', $gr->status);

        $grItem = $gr->items()->first();
        $this->assertEquals('Rim', $grItem->received_unit);
        $this->assertEquals(10.0, (float) $grItem->raw_quantity_received);
        $this->assertEquals(0.0, (float) $grItem->raw_quantity_rejected);
        // Converted to base Box: 10 Rim * (1/5) = 2.0 Box
        $this->assertEquals(2.0, (float) $grItem->quantity_received);
        $this->assertEquals(2.0, (float) $grItem->quantity_accepted);

        // 4. Verify PO Item fulfilled in Box and PO marked completed
        $poItem->refresh();
        $po->refresh();
        $this->assertEquals(2.0, (float) $poItem->received_quantity);
        $this->assertEquals('completed', $po->status);
    }

    public function test_po_creation_accepts_tax_rounding_difference_within_tolerance(): void
    {
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/IT/2026/09/0101',
            'department_id' => $this->dept->id,
            'user_id' => $this->requester->id,
            'status' => 'approved',
            'priority' => 'medium',
            'title' => 'Pengadaan Lisensi Software',
            'description' => 'Pembelian lisensi tahunan',
            'estimated_total' => 15000000,
            'required_date' => Carbon::now()->addDays(7),
        ]);

        $q = Quotation::create([
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->vendor->id,
            'subtotal' => 10000000,
            'shipping_cost' => 0,
            // 11% PPN theoretically is 1.100.000, vendor tax invoice has Rp 1.100.025 (+25 rounding difference)
            'tax_amount' => 1100025,
            'grand_total' => 11100025,
            'estimated_delivery_days' => 1,
            'is_selected' => true,
            'selection_reason' => 'Pemenang pengadaan',
        ]);

        $q->items()->create([
            'item_name' => 'Lisensi Antivirus',
            'quantity' => 10,
            'unit' => 'Unit',
            'unit_price' => 1000000,
            'subtotal' => 10000000,
        ]);

        // Submit PO creation
        $response = $this->actingAs($this->procurement)->post(route('purchase-orders.store'), [
            'purchase_request_id' => $pr->id,
            'quotation_id' => $q->id,
            'payment_terms' => 'Net 30 Days',
            'status' => 'issued',
            'tax_rate' => 11,
            'tax_calculation_mode' => 'line_item',
            'tax_amount' => 1100025, // diff is +25, within <= 100 tolerance
            'over_delivery_tolerance_percentage' => 5,
        ]);

        $response->assertRedirect();
        $po = PurchaseOrder::latest('id')->first();
        $this->assertNotNull($po);
        $this->assertEquals(1100025.00, (float) $po->tax_amount);
        $this->assertEquals(25.00, (float) $po->tax_rounding_difference);
        $this->assertEquals(11100025.00, (float) $po->grand_total);
    }

    public function test_po_creation_rejects_tax_rounding_difference_exceeding_tolerance(): void
    {
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/IT/2026/09/0102',
            'department_id' => $this->dept->id,
            'user_id' => $this->requester->id,
            'status' => 'approved',
            'priority' => 'medium',
            'title' => 'Pengadaan Perangkat Jaringan',
            'description' => 'Pembelian switch',
            'estimated_total' => 15000000,
            'required_date' => Carbon::now()->addDays(7),
        ]);

        $q = Quotation::create([
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->vendor->id,
            'subtotal' => 10000000,
            'shipping_cost' => 0,
            'tax_amount' => 1100000,
            'grand_total' => 11100000,
            'estimated_delivery_days' => 1,
            'is_selected' => true,
            'selection_reason' => 'Pemenang pengadaan',
        ]);

        $q->items()->create([
            'item_name' => 'Switch 24 Port',
            'quantity' => 2,
            'unit' => 'Unit',
            'unit_price' => 5000000,
            'subtotal' => 10000000,
        ]);

        // Difference is Rp 250 (which exceeds tolerance max Rp 100)
        $response = $this->actingAs($this->procurement)->post(route('purchase-orders.store'), [
            'purchase_request_id' => $pr->id,
            'quotation_id' => $q->id,
            'payment_terms' => 'Net 30 Days',
            'status' => 'issued',
            'tax_rate' => 11,
            'tax_calculation_mode' => 'line_item',
            'tax_amount' => 1100250, // Diff = 250 > 100
        ]);

        $response->assertSessionHasErrors(['tax_amount']);
    }

    public function test_goods_receipt_within_over_delivery_tolerance_is_accepted_and_tracked(): void
    {
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/IT/2026/09/0103',
            'department_id' => $this->dept->id,
            'user_id' => $this->requester->id,
            'status' => 'approved',
            'priority' => 'medium',
            'title' => 'Pengadaan Kabel Fiber Optik',
            'description' => 'Roll kabel jaringan',
            'estimated_total' => 2000000,
            'required_date' => Carbon::now()->addDays(7),
        ]);

        // Order 100 Meter cable with 5% over-delivery tolerance (max allowed: 105 Meter)
        $po = PurchaseOrder::create([
            'po_number' => 'PO/IT/2026/09/0103',
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->vendor->id,
            'issued_by' => $this->procurement->id,
            'status' => 'issued',
            'subtotal' => 2000000,
            'tax_amount' => 220000,
            'grand_total' => 2220000,
            'payment_terms' => 'Net 30 Days',
            'over_delivery_tolerance_percentage' => 5.0,
            'order_date' => Carbon::today(),
        ]);

        $poItem = $po->items()->create([
            'item_name' => 'Kabel UTP Cat6',
            'quantity' => 100,
            'unit' => 'Meter',
            'unit_price' => 20000,
            'subtotal' => 2000000,
            'received_quantity' => 0,
            'over_delivery_tolerance_percentage' => 5.0,
            'over_delivered_quantity' => 0,
        ]);

        // Vendor delivers 103 Meter on Surat Jalan (within +5% tolerance max 105 Meter)
        $response = $this->actingAs($this->warehouse)->post(route('goods-receipts.store'), [
            'purchase_order_id' => $po->id,
            'receipt_type' => 'goods',
            'received_date' => Carbon::today()->format('Y-m-d'),
            'delivery_note_no' => 'SJ-CABLE-0103',
            'item_condition' => 'Baik',
            'inspection_notes' => 'Tiba 103 meter roll utuh pabrik (toleransi over-delivery lolos QC).',
            'items' => [
                [
                    'po_item_id' => $poItem->id,
                    'quantity_received' => 103, // 103 meter
                    'quantity_rejected' => 0,
                    'notes' => 'Kelebihan 3 meter diterima sesuai batas toleransi 5%',
                ],
            ],
        ]);

        $response->assertRedirect();

        // Verify GR Item tracked over-delivery
        $gr = GoodsReceipt::latest('id')->first();
        $this->assertNotNull($gr);
        $this->assertEquals('completed', $gr->status);

        $grItem = $gr->items()->first();
        $this->assertTrue((bool) $grItem->is_over_delivery);
        $this->assertEquals(3.0, (float) $grItem->over_delivery_quantity);
        $this->assertEquals(103, $grItem->quantity_received);

        // Verify PO Item fulfilled completely and tracked excess
        $poItem->refresh();
        $po->refresh();
        $this->assertEquals(100, $poItem->received_quantity); // Capped at contract 100
        $this->assertEquals(3.0, (float) $poItem->over_delivered_quantity); // Excess tracked
        $this->assertEquals('completed', $po->status); // PO is 100% finished
    }

    public function test_goods_receipt_exceeding_over_delivery_tolerance_is_blocked(): void
    {
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/IT/2026/09/0104',
            'department_id' => $this->dept->id,
            'user_id' => $this->requester->id,
            'status' => 'approved',
            'priority' => 'medium',
            'title' => 'Pengadaan Kabel Fiber Optik',
            'description' => 'Roll kabel jaringan',
            'estimated_total' => 2000000,
            'required_date' => Carbon::now()->addDays(7),
        ]);

        // Order 100 Meter cable with 5% over-delivery tolerance (max allowed: 105 Meter)
        $po = PurchaseOrder::create([
            'po_number' => 'PO/IT/2026/09/0104',
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->vendor->id,
            'issued_by' => $this->procurement->id,
            'status' => 'issued',
            'subtotal' => 2000000,
            'tax_amount' => 220000,
            'grand_total' => 2220000,
            'payment_terms' => 'Net 30 Days',
            'over_delivery_tolerance_percentage' => 5.0,
            'order_date' => Carbon::today(),
        ]);

        $poItem = $po->items()->create([
            'item_name' => 'Kabel UTP Cat6',
            'quantity' => 100,
            'unit' => 'Meter',
            'unit_price' => 20000,
            'subtotal' => 2000000,
            'received_quantity' => 0,
            'over_delivery_tolerance_percentage' => 5.0,
        ]);

        // Vendor attempts to deliver 112 Meter (exceeds max tolerance 105 Meter)
        $response = $this->actingAs($this->warehouse)->post(route('goods-receipts.store'), [
            'purchase_order_id' => $po->id,
            'receipt_type' => 'goods',
            'received_date' => Carbon::today()->format('Y-m-d'),
            'delivery_note_no' => 'SJ-CABLE-0104',
            'item_condition' => 'Baik',
            'items' => [
                [
                    'po_item_id' => $poItem->id,
                    'quantity_received' => 112, // 112 meter > 105 meter
                    'quantity_rejected' => 0,
                ],
            ],
        ]);

        $response->assertSessionHasErrors(['items']);
        $this->assertEquals(0, $poItem->fresh()->received_quantity);
        $this->assertEquals('issued', $po->fresh()->status);
    }
}
