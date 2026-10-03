<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\GoodsReceipt;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Quotation;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceThreeWayMatchingAndPaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $procurementUser;
    private User $financeUser;
    private User $requesterUser;
    private Department $department;
    private Vendor $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->department = Department::create([
            'name' => 'Finance & Accounting',
            'code' => 'FIN',
            'budget_limit' => 500000000,
        ]);

        $this->procurementUser = User::factory()->create([
            'name' => 'Procurement Specialist',
            'role' => 'procurement',
            'department_id' => $this->department->id,
        ]);

        $this->financeUser = User::factory()->create([
            'name' => 'Finance Officer',
            'role' => 'finance',
            'department_id' => $this->department->id,
        ]);

        $this->requesterUser = User::factory()->create([
            'name' => 'Requester Staff',
            'role' => 'requester',
            'department_id' => $this->department->id,
        ]);

        $this->vendor = Vendor::create([
            'code' => 'VND-TECH-01',
            'name' => 'PT Teknologi Nusa Mandiri',
            'category' => 'IT Equipment',
            'contact_person' => 'Irawan',
            'email' => 'sales@nusatech.com',
            'phone' => '021-55443322',
            'address' => 'Jakarta Selatan',
        ]);
    }

    private function createSamplePoWithReceipt(int $orderedQty = 10, int $receivedQty = 10, float $unitPrice = 1000000, array $poOverrides = []): array
    {
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/FIN/2026/09/0088',
            'user_id' => $this->requesterUser->id,
            'department_id' => $this->department->id,
            'title' => 'Pengadaan Laptop Staff Akuntansi',
            'description' => 'Laptop kerja untuk pembukuan akhir tahun.',
            'category' => 'goods',
            'priority' => 'high',
            'required_date' => now()->addDays(30),
            'status' => 'processing',
            'estimated_total' => $orderedQty * $unitPrice,
        ]);

        $prItem = $pr->items()->create([
            'item_name' => 'Laptop ThinkPad i7',
            'quantity' => $orderedQty,
            'unit' => 'Unit',
            'estimated_unit_price' => $unitPrice,
            'subtotal' => $orderedQty * $unitPrice,
        ]);

        $quotation = Quotation::create([
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->vendor->id,
            'quotation_number' => 'QUO-TECH-2026-099',
            'subtotal' => $orderedQty * $unitPrice,
            'shipping_cost' => 0,
            'tax_amount' => round(($orderedQty * $unitPrice) * 0.11, 2),
            'grand_total' => round(($orderedQty * $unitPrice) * 1.11, 2),
            'estimated_delivery_days' => 7,
            'is_selected' => true,
        ]);

        $poSubtotal = $orderedQty * $unitPrice;
        $poTax = round($poSubtotal * 0.11, 2);
        $poGrandTotal = $poSubtotal + $poTax;

        $po = PurchaseOrder::create(array_merge([
            'po_number' => 'PO/PROC/2026/09/0088',
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->vendor->id,
            'quotation_id' => $quotation->id,
            'issued_by' => $this->procurementUser->id,
            'order_date' => now()->subDays(10),
            'delivery_target_date' => now()->addDays(5),
            'subtotal' => $poSubtotal,
            'tax_rate' => 11,
            'shipping_fee' => 0,
            'tax_amount' => $poTax,
            'grand_total' => $poGrandTotal,
            'status' => 'issued',
            'payment_terms' => 'Net 30 Days',
        ], $poOverrides));

        $poItem = $po->items()->create([
            'purchase_request_item_id' => $prItem->id,
            'item_name' => 'Laptop ThinkPad i7',
            'quantity' => $orderedQty,
            'received_quantity' => $receivedQty,
            'unit' => 'Unit',
            'unit_price' => $unitPrice,
            'subtotal' => $poSubtotal,
        ]);

        if ($receivedQty > 0) {
            $gr = GoodsReceipt::create([
                'gr_number' => 'GR/WH/2026/09/0088',
                'purchase_order_id' => $po->id,
                'received_by' => $this->procurementUser->id,
                'received_date' => now()->subDays(2),
                'receipt_type' => 'goods',
                'item_condition' => 'good',
            ]);

            $gr->items()->create([
                'po_item_id' => $poItem->id,
                'received_quantity' => $receivedQty,
            ]);

            $po->recalculateStatus();
        }

        return [$pr, $po, $poItem];
    }

    public function test_three_way_match_succeeds_when_invoice_matches_gr_and_po(): void
    {
        [$pr, $po, $poItem] = $this->createSamplePoWithReceipt(10, 10, 1000000);

        $response = $this->actingAs($this->procurementUser)
            ->post(route('invoices.store'), [
                'purchase_order_id' => $po->id,
                'invoice_number' => 'INV-NUSA-2026-001',
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'items' => [
                    [
                        'po_item_id' => $poItem->id,
                        'quantity_invoiced' => 10,
                        'unit_price' => 1000000,
                    ],
                ],
            ]);

        $response->assertSessionHas('success');

        $invoice = Invoice::where('invoice_number', 'INV-NUSA-2026-001')->first();
        $this->assertNotNull($invoice);
        $this->assertEquals('matched', $invoice->matching_status);
        $this->assertEquals('unpaid', $invoice->payment_status);
        $this->assertEquals(10000000.0, $invoice->subtotal);
        $this->assertEquals(1100000.0, $invoice->tax_amount);
        $this->assertEquals(11100000.0, $invoice->net_payable_amount);
        $this->assertStringStartsWith('INV/FIN/', $invoice->internal_invoice_number);
    }

    public function test_three_way_match_flags_mismatch_quantity_when_invoiced_exceeds_gr(): void
    {
        // PO ordered 10 units, but warehouse only received 5 units so far
        [$pr, $po, $poItem] = $this->createSamplePoWithReceipt(10, 5, 1000000);

        // Vendor attempts to invoice the full 10 units prematurely
        $response = $this->actingAs($this->procurementUser)
            ->post(route('invoices.store'), [
                'purchase_order_id' => $po->id,
                'invoice_number' => 'INV-NUSA-2026-002',
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'items' => [
                    [
                        'po_item_id' => $poItem->id,
                        'quantity_invoiced' => 10, // Exceeds 5 units received
                        'unit_price' => 1000000,
                    ],
                ],
            ]);

        $response->assertSessionHas('warning');

        $invoice = Invoice::where('invoice_number', 'INV-NUSA-2026-002')->first();
        $this->assertNotNull($invoice);
        $this->assertEquals('mismatch_quantity', $invoice->matching_status);
        $this->assertStringContainsString('melebihi fisik diterima', $invoice->matching_notes);
    }

    public function test_three_way_match_flags_mismatch_price_when_unit_price_exceeds_po(): void
    {
        [$pr, $po, $poItem] = $this->createSamplePoWithReceipt(10, 10, 1000000);

        // Vendor raises price on invoice to 1,200,000 without contract amendment
        $response = $this->actingAs($this->procurementUser)
            ->post(route('invoices.store'), [
                'purchase_order_id' => $po->id,
                'invoice_number' => 'INV-NUSA-2026-003',
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'items' => [
                    [
                        'po_item_id' => $poItem->id,
                        'quantity_invoiced' => 10,
                        'unit_price' => 1200000, // Exceeds agreed 1,000,000
                    ],
                ],
            ]);

        $response->assertSessionHas('warning');

        $invoice = Invoice::where('invoice_number', 'INV-NUSA-2026-003')->first();
        $this->assertNotNull($invoice);
        $this->assertEquals('mismatch_price', $invoice->matching_status);
        $this->assertStringContainsString('lebih tinggi dari PO', $invoice->matching_notes);
    }

    public function test_invoice_applies_liquidated_damages_penalty_deduction_for_overdue_po(): void
    {
        // PO delivery target was 10 days ago with partial delivery (10 ordered, 8 received -> partially_received, 1.0% penalty)
        [$pr, $po, $poItem] = $this->createSamplePoWithReceipt(10, 8, 1000000, [
            'delivery_target_date' => now()->subDays(10)->toDateString(),
        ]);

        $this->assertTrue($po->is_overdue);
        $this->assertEquals(1.0, $po->penalty_percentage);

        $response = $this->actingAs($this->procurementUser)
            ->post(route('invoices.store'), [
                'purchase_order_id' => $po->id,
                'invoice_number' => 'INV-NUSA-2026-004',
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'items' => [
                    [
                        'po_item_id' => $poItem->id,
                        'quantity_invoiced' => 8,
                        'unit_price' => 1000000,
                    ],
                ],
            ]);

        $invoice = Invoice::where('invoice_number', 'INV-NUSA-2026-004')->first();
        $this->assertNotNull($invoice);
        $this->assertEquals('matched', $invoice->matching_status);
        $this->assertEquals(111000.0, $invoice->penalty_deduction); // 1% of 11,100,000 grand total
        $this->assertEquals(8880000.0 - 111000.0, $invoice->net_payable_amount);
        $this->assertStringContainsString('klausul denda keterlambatan', $invoice->matching_notes);
    }

    public function test_finance_can_record_payment_and_complete_settlement(): void
    {
        [$pr, $po, $poItem] = $this->createSamplePoWithReceipt(10, 10, 1000000);

        $this->actingAs($this->procurementUser)
            ->post(route('invoices.store'), [
                'purchase_order_id' => $po->id,
                'invoice_number' => 'INV-NUSA-2026-005',
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'items' => [
                    [
                        'po_item_id' => $poItem->id,
                        'quantity_invoiced' => 10,
                        'unit_price' => 1000000,
                    ],
                ],
            ]);

        $invoice = Invoice::where('invoice_number', 'INV-NUSA-2026-005')->first();

        // Finance records full payment disbursement
        $response = $this->actingAs($this->financeUser)
            ->post(route('invoices.payment', $invoice), [
                'paid_amount' => 11100000.0,
                'payment_date' => now()->toDateString(),
                'payment_method' => 'Bank Transfer',
                'payment_reference' => 'TRF-BCA-20260927-7788',
                'notes' => 'Pembayaran lunas transfer via RTGS BCA.',
            ]);

        $response->assertRedirect(route('invoices.show', $invoice));
        $response->assertSessionHas('success');

        $invoice->refresh();
        $this->assertEquals('paid', $invoice->payment_status);
        $this->assertEquals(11100000.0, $invoice->paid_amount);
        $this->assertEquals(0.0, $invoice->remaining_amount);
        $this->assertEquals('TRF-BCA-20260927-7788', $invoice->payment_reference);

        // Check requester notified
        $this->assertDatabaseHas('in_app_notifications', [
            'user_id' => $pr->user_id,
            'type' => 'invoice_payment',
        ]);
    }

    public function test_non_finance_user_cannot_record_payment(): void
    {
        [$pr, $po, $poItem] = $this->createSamplePoWithReceipt(10, 10, 1000000);

        $this->actingAs($this->procurementUser)
            ->post(route('invoices.store'), [
                'purchase_order_id' => $po->id,
                'invoice_number' => 'INV-NUSA-2026-006',
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'items' => [
                    [
                        'po_item_id' => $poItem->id,
                        'quantity_invoiced' => 10,
                        'unit_price' => 1000000,
                    ],
                ],
            ]);

        $invoice = Invoice::where('invoice_number', 'INV-NUSA-2026-006')->first();

        // Requester cannot record payment
        $this->actingAs($this->requesterUser)
            ->post(route('invoices.payment', $invoice), [
                'paid_amount' => 11100000.0,
                'payment_date' => now()->toDateString(),
                'payment_method' => 'Bank Transfer',
                'payment_reference' => 'TRF-BCA-20260927-7788',
            ])
            ->assertForbidden();
    }
}
