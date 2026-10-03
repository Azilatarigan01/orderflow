<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $manager;
    protected User $requester;
    protected Department $dept;
    protected Vendor $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dept = Department::create([
            'code' => 'FIN',
            'name' => 'Finance & Accounting',
            'is_active' => true,
        ]);

        $this->admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@orderflow.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'department_id' => $this->dept->id,
            'is_active' => true,
        ]);

        $this->manager = User::create([
            'name' => 'Finance Manager',
            'email' => 'manager@orderflow.com',
            'password' => bcrypt('password'),
            'role' => 'manager',
            'department_id' => $this->dept->id,
            'is_active' => true,
        ]);

        $this->requester = User::create([
            'name' => 'Staff Biasa',
            'email' => 'staff@orderflow.com',
            'password' => bcrypt('password'),
            'role' => 'requester',
            'department_id' => $this->dept->id,
            'is_active' => true,
        ]);

        $this->vendor = Vendor::create([
            'code' => 'VND-001',
            'name' => 'PT Mitra Solusindo',
            'category' => 'IT & Hardware',
            'contact_person' => 'Budi',
            'email' => 'sales@mitrasolusindo.com',
            'phone' => '021-998877',
            'address' => 'Jakarta Barat',
            'rating' => 4.90,
            'is_active' => true,
        ]);
    }

    /**
     * Test authorized management can access reports hub
     */
    public function test_authorized_user_can_access_reports_index(): void
    {
        $response = $this->actingAs($this->manager)->get(route('reports.index'));
        $response->assertStatus(200);
        $response->assertSee('Pusat Laporan', false);
        $response->assertSee('Posting Date', false);
    }

    /**
     * Test unauthorized regular requester cannot access reports
     */
    public function test_regular_requester_is_forbidden_from_reports(): void
    {
        $response = $this->actingAs($this->requester)->get(route('reports.index'));
        $response->assertStatus(403);
    }

    /**
     * Test safeguard: Date range exceeding 365 days triggers validation error (anti-OOM)
     */
    public function test_report_safeguard_blocks_date_range_exceeding_365_days(): void
    {
        $response = $this->actingAs($this->admin)->get(route('reports.po', [
            'period_preset' => 'custom',
            'date_from' => '2023-01-01',
            'date_to' => '2025-01-01', // 2 years difference
        ]));

        $response->assertSessionHasErrors('date_to');
    }

    /**
     * Test safeguard: Inverted dates (date_to < date_from) triggers validation error
     */
    public function test_report_safeguard_blocks_inverted_dates(): void
    {
        $response = $this->actingAs($this->admin)->get(route('reports.po', [
            'period_preset' => 'custom',
            'date_from' => '2026-09-30',
            'date_to' => '2026-09-01',
        ]));

        $response->assertSessionHasErrors('date_to');
    }

    /**
     * Test dynamic presets correctly filter by this_month
     */
    public function test_po_report_dynamic_presets_and_date_basis(): void
    {
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/FIN/2026/09/0001',
            'user_id' => $this->admin->id,
            'department_id' => $this->dept->id,
            'title' => 'Pengadaan Server Akuntansi',
            'description' => 'Kebutuhan server pembukuan',
            'required_date' => Carbon::now()->addDays(7)->toDateString(),
            'status' => 'approved',
            'total_estimated_cost' => 15000000,
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO/PROC/2026/09/0001',
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->vendor->id,
            'order_date' => Carbon::now()->toDateString(), // this month
            'delivery_date' => Carbon::now()->addDays(14)->toDateString(),
            'subtotal' => 15000000,
            'tax_amount' => 1650000,
            'total_amount' => 16650000,
            'payment_terms' => 'Net 30 Days',
            'issued_by' => $this->admin->id,
            'status' => 'issued',
        ]);

        $response = $this->actingAs($this->admin)->get(route('reports.po', [
            'period_preset' => 'this_month',
            'date_basis' => 'order_date',
        ]));

        $response->assertStatus(200);
        $response->assertSee($po->po_number);
    }

    /**
     * Test Goods Receipt report (Realisasi Fisik) filters by received_date
     */
    public function test_gr_physical_fulfillment_report_filters_by_received_date(): void
    {
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR/FIN/2026/09/0002',
            'user_id' => $this->admin->id,
            'department_id' => $this->dept->id,
            'title' => 'Pengadaan Meja Kerja',
            'description' => 'Meja kerja tim akuntansi',
            'required_date' => Carbon::now()->addDays(7)->toDateString(),
            'status' => 'approved',
            'total_estimated_cost' => 5000000,
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO/PROC/2026/09/0002',
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->vendor->id,
            'order_date' => Carbon::now()->subMonths(1)->toDateString(), // ordered last month
            'subtotal' => 5000000,
            'tax_amount' => 550000,
            'total_amount' => 5550000,
            'payment_terms' => 'Net 14 Days',
            'issued_by' => $this->admin->id,
            'status' => 'partially_received',
        ]);

        $gr = GoodsReceipt::create([
            'gr_number' => 'GR/WH/2026/09/0001',
            'purchase_order_id' => $po->id,
            'received_by' => $this->admin->id,
            'receipt_type' => 'goods',
            'received_date' => Carbon::now()->toDateString(), // arrived physically today!
            'item_condition' => 'Baik',
            'status' => 'completed',
        ]);

        // When requesting GR report for this month, physical receipt is captured!
        $response = $this->actingAs($this->admin)->get(route('reports.gr', [
            'period_preset' => 'this_month',
        ]));

        $response->assertStatus(200);
        $response->assertSee($gr->gr_number);
        $response->assertSee($po->po_number);

        // Test CSV export for GR report
        $csvResponse = $this->actingAs($this->admin)->get(route('reports.gr', [
            'period_preset' => 'this_month',
            'export' => 'csv',
        ]));

        $csvResponse->assertHeader('content-type', 'text/csv; charset=utf-8');
    }

    public function test_report_exports_excel_with_formatted_table(): void
    {
        $response = $this->actingAs($this->admin)->get(route('reports.po', [
            'period_preset' => 'this_month',
            'export' => 'excel',
        ]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.ms-excel; charset=utf-8');
        $this->assertStringContainsString('PT ORDERFLOW NUSANTARA', $response->getContent());
        $this->assertStringContainsString('LAPORAN REKAPITULASI PURCHASE ORDER', $response->getContent());
        $this->assertStringContainsString('mso-number-format', $response->getContent());
    }

    public function test_report_exports_pdf_renders_cleanly(): void
    {
        $response = $this->actingAs($this->admin)->get(route('reports.po', [
            'period_preset' => 'this_month',
            'export' => 'pdf',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Laporan Purchase Order', false);
        $response->assertSee('ORDERFLOW ENTERPRISE', false);
    }
}
