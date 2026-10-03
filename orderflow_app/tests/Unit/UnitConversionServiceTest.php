<?php

namespace Tests\Unit;

use App\Models\MeasurementUnit;
use App\Models\UnitConversion;
use App\Services\UnitConversionService;
use Database\Seeders\MeasurementUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class UnitConversionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UnitConversionService $converter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MeasurementUnitSeeder::class);
        $this->converter = app(UnitConversionService::class);
    }

    public function test_same_unit_conversion_returns_identical_quantity(): void
    {
        $result = $this->converter->convertQuantity(15.5, 'Unit', 'Unit');
        $this->assertEquals(15.5, $result);

        $resultBox = $this->converter->convertQuantity(10, 'Box', 'Box');
        $this->assertEquals(10, $resultBox);
    }

    public function test_direct_packaging_conversion(): void
    {
        // 1 Box = 5 Rim (seeded) -> 2 Box = 10 Rim
        $rims = $this->converter->convertQuantity(2, 'Box', 'Rim');
        $this->assertEquals(10.0, $rims);

        // 1 Rim = 500 Lembar -> 3 Rim = 1500 Lembar (seeded in test if needed, or check seeded)
        // Check 1 Dus = 12 Pack
        $pack = $this->converter->convertQuantity(3, 'Dus', 'Pack');
        $this->assertEquals(36.0, $pack);

        // 1 Karton = 24 Pcs -> 2 Karton = 48 Pcs
        $pcs = $this->converter->convertQuantity(2, 'Karton', 'Pcs');
        $this->assertEquals(48.0, $pcs);
    }

    public function test_inverse_packaging_conversion(): void
    {
        // 1 Box = 5 Rim -> 10 Rim received converts to 2 Box
        $boxes = $this->converter->convertQuantity(10, 'Rim', 'Box');
        $this->assertEquals(2.0, $boxes);

        // 25 Rim received converts to 5 Box
        $boxes2 = $this->converter->convertQuantity(25, 'Rim', 'Box');
        $this->assertEquals(5.0, $boxes2);

        // 48 Pcs received converts to 2 Karton
        $karton = $this->converter->convertQuantity(48, 'Pcs', 'Karton');
        $this->assertEquals(2.0, $karton);
    }

    public function test_weight_and_length_metric_conversions(): void
    {
        // 1 Ton = 1000 Kg
        $kg = $this->converter->convertQuantity(2.5, 'Ton', 'Kg');
        $this->assertEquals(2500.0, $kg);

        // 500 Kg to Ton = 0.5 Ton
        $ton = $this->converter->convertQuantity(500, 'Kg', 'Ton');
        $this->assertEquals(0.5, $ton);

        // 1 Roll = 50 Meter
        $meter = $this->converter->convertQuantity(3, 'Roll', 'Meter');
        $this->assertEquals(150.0, $meter);
    }

    public function test_incompatible_units_throw_invalid_argument_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("tidak memiliki konversi terdaftar");

        $this->converter->convert(10, 'Box', 'Meter');
    }

    public function test_get_convertible_units_returns_related_units_list(): void
    {
        $unitsForBox = $this->converter->getConvertibleUnits('Box');

        $this->assertContains('BOX', $unitsForBox);
        $this->assertContains('RIM', $unitsForBox);
        $this->assertNotContains('METER', $unitsForBox);
    }

    public function test_case_insensitive_matching(): void
    {
        // 'box' vs 'RIM'
        $converted = $this->converter->convertQuantity(4, 'box', 'RIM');
        $this->assertEquals(20.0, $converted);
    }
}
