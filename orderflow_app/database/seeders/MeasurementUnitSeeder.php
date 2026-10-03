<?php

namespace Database\Seeders;

use App\Services\UnitConversionService;
use Illuminate\Database\Seeder;

class MeasurementUnitSeeder extends Seeder
{
    /**
     * Seed standard corporate measurement units and conversions.
     */
    public function run(): void
    {
        UnitConversionService::ensureDefaultsSeeded();
    }
}
