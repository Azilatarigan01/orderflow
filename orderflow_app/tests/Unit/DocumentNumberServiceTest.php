<?php

namespace Tests\Unit;

use App\Models\Department;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Services\DocumentNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentNumberServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_pr_number_follows_enterprise_format(): void
    {
        $dept = Department::create([
            'name' => 'Information Technology',
            'code' => 'IT',
            'is_active' => true,
        ]);

        $year = date('Y');
        $month = str_pad(date('n'), 2, '0', STR_PAD_LEFT);

        $number1 = DocumentNumberService::generatePrNumber($dept);
        $number2 = DocumentNumberService::generatePrNumber($dept);

        $this->assertEquals("PR/IT/{$year}/{$month}/0001", $number1);
        $this->assertEquals("PR/IT/{$year}/{$month}/0002", $number2);
    }

    public function test_departments_have_isolated_sequences(): void
    {
        $deptIT = Department::create([
            'name' => 'Information Technology',
            'code' => 'IT',
            'is_active' => true,
        ]);

        $deptHR = Department::create([
            'name' => 'Human Resources',
            'code' => 'HR',
            'is_active' => true,
        ]);

        $year = date('Y');
        $month = str_pad(date('n'), 2, '0', STR_PAD_LEFT);

        $numIT1 = DocumentNumberService::generatePrNumber($deptIT);
        $numIT2 = DocumentNumberService::generatePrNumber($deptIT);

        $numHR1 = DocumentNumberService::generatePrNumber($deptHR);

        $this->assertEquals("PR/IT/{$year}/{$month}/0001", $numIT1);
        $this->assertEquals("PR/IT/{$year}/{$month}/0002", $numIT2);
        $this->assertEquals("PR/HR/{$year}/{$month}/0001", $numHR1);
    }

    public function test_po_and_gr_number_formats(): void
    {
        $year = date('Y');
        $month = str_pad(date('n'), 2, '0', STR_PAD_LEFT);

        $po1 = DocumentNumberService::generatePoNumber('PROC');
        $po2 = DocumentNumberService::generatePoNumber('PROC');

        $gr1 = DocumentNumberService::generateGrNumber('goods', 'WH');
        $bast1 = DocumentNumberService::generateGrNumber('service', 'IT');

        $this->assertEquals("PO/PROC/{$year}/{$month}/0001", $po1);
        $this->assertEquals("PO/PROC/{$year}/{$month}/0002", $po2);
        $this->assertEquals("GR/WH/{$year}/{$month}/0001", $gr1);
        $this->assertEquals("BAST/IT/{$year}/{$month}/0001", $bast1);
    }

    public function test_model_delegates_to_document_number_service(): void
    {
        $dept = Department::create([
            'name' => 'Finance & Accounting',
            'code' => 'FIN',
            'is_active' => true,
        ]);

        $year = date('Y');
        $month = str_pad(date('n'), 2, '0', STR_PAD_LEFT);

        $prNum = PurchaseRequest::generatePrNumber($dept);
        $poNum = PurchaseOrder::generatePoNumber('PROC');
        $grNum = GoodsReceipt::generateGrNumber('goods', 'WH');

        $this->assertEquals("PR/FIN/{$year}/{$month}/0001", $prNum);
        $this->assertEquals("PO/PROC/{$year}/{$month}/0001", $poNum);
        $this->assertEquals("GR/WH/{$year}/{$month}/0001", $grNum);
    }
}
