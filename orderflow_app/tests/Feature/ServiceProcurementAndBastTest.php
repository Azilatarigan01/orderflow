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

class ServiceProcurementAndBastTest extends TestCase
{
    use RefreshDatabase;

    protected User $requester;
    protected User $procurement;
    protected User $warehouse;
    protected Department $dept;
    protected Vendor $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dept = Department::create(['name' => 'Information Technology', 'code' => 'IT']);

        $this->requester = User::factory()->create([
            'role' => 'requester',
            'department_id' => $this->dept->id,
        ]);

        $this->procurement = User::factory()->create([
            'role' => 'procurement',
            'department_id' => $this->dept->id,
        ]);

        $this->warehouse = User::factory()->create([
            'role' => 'warehouse',
            'department_id' => $this->dept->id,
        ]);

        $this->vendor = Vendor::create([
            'code' => 'VND-CONSULT-01',
            'name' => 'PT Konsultan Solusi Digital',
            'category' => 'Konsultan IT',
            'contact_person' => 'Rahmat',
            'email' => 'rahmat@solusidigital.com',
            'phone' => '021-778899',
            'address' => 'Jakarta Selatan',
            'rating' => 4.95,
            'is_active' => true,
        ]);
    }

    public function test_service_po_can_be_received_in_multiple_termins_with_progress_percentage(): void
    {
        // 1. Create PR for IT Service (1 Paket Rp 50.000.000)
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/IT/2026/09/7001',
            'user_id' => $this->requester->id,
            'department_id' => $this->dept->id,
            'title' => 'Pengadaan Jasa Audit Keamanan Siber & Penetrasi Web',
            'description' => 'Jasa konsultan profesional untuk pengujian keamanan sistem korporat.',
            'required_date' => Carbon::now()->addDays(30),
            'estimated_total' => 50000000,
            'status' => 'approved',
        ]);

        $prItem = $pr->items()->create([
            'item_name' => 'Jasa Penetration Testing & Vulnerability Assessment',
            'specification' => 'SLA 30 hari kalender, mencakup web apps, mobile apps, dan API server.',
            'quantity' => 1,
            'unit' => 'Paket',
            'estimated_unit_price' => 50000000,
            'subtotal' => 50000000,
        ]);

        // 2. Issue PO for the service
        $po = PurchaseOrder::create([
            'po_number' => 'PO/PROC/2026/09/7001',
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->vendor->id,
            'issued_by' => $this->procurement->id,
            'order_date' => Carbon::today(),
            'status' => 'issued',
            'subtotal' => 50000000,
            'grand_total' => 50000000,
            'payment_terms' => 'Termin 30% - 50% - 20%',
        ]);

        $poItem = $po->items()->create([
            'purchase_request_item_id' => $prItem->id,
            'item_name' => $prItem->item_name,
            'specification' => $prItem->specification,
            'quantity' => 1,
            'unit' => 'Paket',
            'unit_price' => 50000000,
            'subtotal' => 50000000,
            'received_quantity' => 0,
        ]);

        $pr->update(['status' => 'processing']);

        // 3. Record Termin 1: DP / Kickoff (30%)
        $resp1 = $this->actingAs($this->warehouse)->post(route('goods-receipts.store'), [
            'purchase_order_id' => $po->id,
            'receipt_type' => 'service',
            'termin_name' => 'Termin 1 (DP Kick-off 30%)',
            'progress_percentage' => 30.0,
            'nominal_claimed' => 15000000,
            'received_date' => Carbon::today()->format('Y-m-d'),
            'item_condition' => 'Baik (100% OK)',
            'service_period_start' => Carbon::today()->subDays(5)->format('Y-m-d'),
            'service_period_end' => Carbon::today()->format('Y-m-d'),
            'acceptance_approver_name' => 'Budi Santoso (Lead IT Security)',
            'service_deliverables' => 'Kickoff meeting, perumusan scope of work, dan penandatanganan NDA.',
            'items' => [
                [
                    'po_item_id' => $poItem->id,
                    'raw_quantity_received' => 0,
                ]
            ],
        ]);

        $resp1->assertRedirect(route('purchase-orders.show', $po));
        $gr1 = GoodsReceipt::latest('id')->first();
        $this->assertEquals('Termin 1 (DP Kick-off 30%)', $gr1->termin_name);
        $this->assertEquals(30.0, (float) $gr1->progress_percentage);
        $this->assertEquals(30.0, (float) $gr1->cumulative_progress_percentage);
        $this->assertEquals(15000000, (float) $gr1->nominal_claimed);

        // PO is on progress
        $this->assertEquals('partially_received', $po->fresh()->status);
        $this->assertEquals(30, $po->fresh()->receipt_progress_percentage);
        // PR remains processing
        $this->assertEquals('processing', $pr->fresh()->status);

        // 4. Record Termin 2: Intermediate Progress (50%)
        $resp2 = $this->actingAs($this->warehouse)->post(route('goods-receipts.store'), [
            'purchase_order_id' => $po->id,
            'receipt_type' => 'service',
            'termin_name' => 'Termin 2 (Pengujian Lapangan 50%)',
            'progress_percentage' => 50.0,
            'nominal_claimed' => 25000000,
            'received_date' => Carbon::today()->format('Y-m-d'),
            'item_condition' => 'Baik (100% OK)',
            'acceptance_approver_name' => 'Budi Santoso (Lead IT Security)',
            'service_deliverables' => 'Eksekusi penetration testing server dan vulnerability report draft diserahkan.',
            'items' => [
                [
                    'po_item_id' => $poItem->id,
                    'raw_quantity_received' => 0,
                ]
            ],
        ]);

        $resp2->assertRedirect(route('purchase-orders.show', $po));
        $gr2 = GoodsReceipt::latest('id')->first();
        $this->assertEquals(50.0, (float) $gr2->progress_percentage);
        $this->assertEquals(80.0, (float) $gr2->cumulative_progress_percentage);

        // PO progress is now 80%, still partially received
        $this->assertEquals('partially_received', $po->fresh()->status);
        $this->assertEquals(80, $po->fresh()->receipt_progress_percentage);
        $this->assertEquals('processing', $pr->fresh()->status);

        // 5. Record Termin 3: Final Handover & Retest (20%)
        $resp3 = $this->actingAs($this->warehouse)->post(route('goods-receipts.store'), [
            'purchase_order_id' => $po->id,
            'receipt_type' => 'service',
            'termin_name' => 'Termin 3 (Pelunasan & Final Report 20%)',
            'progress_percentage' => 20.0,
            'nominal_claimed' => 10000000,
            'received_date' => Carbon::today()->format('Y-m-d'),
            'item_condition' => 'Baik (100% OK)',
            'acceptance_approver_name' => 'Budi Santoso & Anton Wijaya (CTO)',
            'service_deliverables' => 'Retesting selesai, perbaikan celah keamanan terverifikasi, dan sertifikat diserahkan.',
            'items' => [
                [
                    'po_item_id' => $poItem->id,
                    'raw_quantity_received' => 0,
                ]
            ],
        ]);

        $resp3->assertRedirect(route('purchase-orders.show', $po));
        $gr3 = GoodsReceipt::latest('id')->first();
        $this->assertEquals(20.0, (float) $gr3->progress_percentage);
        $this->assertEquals(100.0, (float) $gr3->cumulative_progress_percentage);

        // With 100% cumulative progress reached:
        // PO automatically completed
        $this->assertEquals('completed', $po->fresh()->status);
        $this->assertEquals(100, $po->fresh()->receipt_progress_percentage);

        // PR automatically completed!
        $this->assertEquals('completed', $pr->fresh()->status);

        // Audit & notifications logged
        $this->assertDatabaseHas('status_histories', [
            'purchase_request_id' => $pr->id,
            'to_status' => 'completed',
        ]);
        $this->assertDatabaseHas('in_app_notifications', [
            'user_id' => $this->requester->id,
            'type' => 'pr_completed',
        ]);
    }

    public function test_service_bast_blocks_exceeding_100_percent_cumulative_progress(): void
    {
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/IT/2026/09/7002',
            'user_id' => $this->requester->id,
            'department_id' => $this->dept->id,
            'title' => 'Pengadaan Jasa Maintenance Server',
            'description' => 'Pemeliharaan server 1 tahun.',
            'required_date' => Carbon::now()->addDays(30),
            'estimated_total' => 20000000,
            'status' => 'approved',
        ]);

        $prItem = $pr->items()->create([
            'item_name' => 'Maintenance Server',
            'quantity' => 1,
            'unit' => 'Paket',
            'estimated_unit_price' => 20000000,
            'subtotal' => 20000000,
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO/PROC/2026/09/7002',
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->vendor->id,
            'issued_by' => $this->procurement->id,
            'order_date' => Carbon::today(),
            'status' => 'issued',
            'subtotal' => 20000000,
            'grand_total' => 20000000,
            'payment_terms' => 'Termin',
        ]);

        $poItem = $po->items()->create([
            'purchase_request_item_id' => $prItem->id,
            'item_name' => $prItem->item_name,
            'quantity' => 1,
            'unit' => 'Paket',
            'unit_price' => 20000000,
            'subtotal' => 20000000,
            'received_quantity' => 0,
        ]);

        // First BAST: 70%
        $this->actingAs($this->warehouse)->post(route('goods-receipts.store'), [
            'purchase_order_id' => $po->id,
            'receipt_type' => 'service',
            'termin_name' => 'Termin 1 (70%)',
            'progress_percentage' => 70.0,
            'received_date' => Carbon::today()->format('Y-m-d'),
            'item_condition' => 'Baik (100% OK)',
            'items' => [
                ['po_item_id' => $poItem->id, 'raw_quantity_received' => 0]
            ],
        ]);

        // Attempting to record 40% (total 110%) must be blocked with error
        $response = $this->actingAs($this->warehouse)->post(route('goods-receipts.store'), [
            'purchase_order_id' => $po->id,
            'receipt_type' => 'service',
            'termin_name' => 'Termin 2 (40%)',
            'progress_percentage' => 40.0,
            'received_date' => Carbon::today()->format('Y-m-d'),
            'item_condition' => 'Baik (100% OK)',
            'items' => [
                ['po_item_id' => $poItem->id, 'raw_quantity_received' => 0]
            ],
        ]);

        $response->assertSessionHasErrors(['progress_percentage']);
        $this->assertEquals(1, GoodsReceipt::count());
    }
}
