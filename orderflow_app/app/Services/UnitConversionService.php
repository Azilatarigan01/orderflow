<?php

namespace App\Services;

use App\Models\MeasurementUnit;
use App\Models\UnitConversion;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class UnitConversionService
{
    /**
     * Convert quantity from one measurement unit to another.
     * Supports identical units, direct conversions, and inverse conversions.
     */
    public static function convert(float $quantity, string $fromUnit, string $toUnit): array
    {
        $fromCode = self::normalizeCode($fromUnit);
        $toCode = self::normalizeCode($toUnit);

        // 1. Same unit: identity conversion (1:1)
        if ($fromCode === $toCode) {
            return [
                'converted_quantity' => $quantity,
                'raw_quantity' => $quantity,
                'from_unit' => $fromCode,
                'to_unit' => $toCode,
                'multiplier' => 1.0,
                'is_converted' => false,
                'formula_label' => "{$quantity} {$fromCode} = {$quantity} {$toCode}",
            ];
        }

        self::ensureDefaultsSeeded();

        // 2. Direct conversion: from_unit -> to_unit exists (e.g. 1 BOX = 5 RIM)
        $direct = UnitConversion::where('from_unit_code', $fromCode)
            ->where('to_unit_code', $toCode)
            ->first();

        if ($direct) {
            $multiplier = (float) $direct->multiplier;
            $converted = $quantity * $multiplier;

            return [
                'converted_quantity' => round($converted, 4),
                'raw_quantity' => $quantity,
                'from_unit' => $fromCode,
                'to_unit' => $toCode,
                'multiplier' => $multiplier,
                'is_converted' => true,
                'formula_label' => "{$quantity} {$fromCode} = " . round($converted, 2) . " {$toCode} (Rasio: 1 {$fromCode} = {$multiplier} {$toCode})",
            ];
        }

        // 3. Inverse conversion: to_unit -> from_unit exists (e.g. 10 RIM to BOX, where 1 BOX = 5 RIM)
        $inverse = UnitConversion::where('from_unit_code', $toCode)
            ->where('to_unit_code', $fromCode)
            ->first();

        if ($inverse && $inverse->multiplier > 0) {
            $baseMultiplier = (float) $inverse->multiplier;
            $effectiveMultiplier = 1 / $baseMultiplier;
            $converted = $quantity / $baseMultiplier;

            return [
                'converted_quantity' => round($converted, 4),
                'raw_quantity' => $quantity,
                'from_unit' => $fromCode,
                'to_unit' => $toCode,
                'multiplier' => round($effectiveMultiplier, 4),
                'is_converted' => true,
                'formula_label' => "{$quantity} {$fromCode} = " . round($converted, 2) . " {$toCode} (Rasio: 1 {$toCode} = {$baseMultiplier} {$fromCode})",
            ];
        }

        throw new InvalidArgumentException(
            "Satuan '{$fromUnit}' tidak memiliki konversi terdaftar dengan satuan '{$toUnit}'. " .
            "Pastikan konversi satuan telah didaftarkan dalam master data konversi korporat."
        );
    }

    /**
     * Helper to get just the converted float value directly.
     */
    public static function convertQuantity(float $quantity, string $fromUnit, string $toUnit): float
    {
        $result = self::convert($quantity, $fromUnit, $toUnit);
        return $result['converted_quantity'];
    }

    /**
     * Get a list of unit codes that can convert to/from the given base unit.
     */
    public static function getConvertibleUnits(string $baseUnit): array
    {
        $options = self::getConversionOptions($baseUnit);
        return array_keys($options);
    }

    /**
     * Get all conversion options for a given target unit (including target itself).
     */
    public static function getConversionOptions(string $baseUnit): array
    {
        $baseCode = self::normalizeCode($baseUnit);
        self::ensureDefaultsSeeded();

        $options = [
            $baseCode => [
                'code' => $baseCode,
                'multiplier_to_base' => 1.0,
                'description' => "1 {$baseCode} (Satuan Dasar Pesanan)",
                'is_base' => true,
            ],
        ];

        // Items where base is the parent (e.g. 1 BOX = 5 RIM -> 1 RIM = 0.2 BOX)
        $children = UnitConversion::where('from_unit_code', $baseCode)->get();
        foreach ($children as $child) {
            $mult = (float) $child->multiplier;
            if ($mult > 0) {
                $options[$child->to_unit_code] = [
                    'code' => $child->to_unit_code,
                    'multiplier_to_base' => 1 / $mult,
                    'description' => "1 {$baseCode} = {$mult} {$child->to_unit_code}",
                    'is_base' => false,
                ];
            }
        }

        // Items where base is the child (e.g. 1 KARTON = 24 PCS, base is PCS -> 1 KARTON = 24 PCS)
        $parents = UnitConversion::where('to_unit_code', $baseCode)->get();
        foreach ($parents as $parent) {
            $mult = (float) $parent->multiplier;
            $options[$parent->from_unit_code] = [
                'code' => $parent->from_unit_code,
                'multiplier_to_base' => $mult,
                'description' => "1 {$parent->from_unit_code} = {$mult} {$baseCode}",
                'is_base' => false,
            ];
        }

        return $options;
    }

    /**
     * Normalize unit code string.
     */
    public static function normalizeCode(?string $unit): string
    {
        return strtoupper(trim((string) $unit));
    }

    /**
     * Auto-seed standard corporate measurement units and conversions if table is empty.
     */
    public static function ensureDefaultsSeeded(): void
    {
        if (MeasurementUnit::count() > 0) {
            return;
        }

        $units = [
            ['code' => 'UNIT', 'name' => 'Unit (Barang Mandiri)', 'category' => 'count'],
            ['code' => 'PCS', 'name' => 'Pieces / Buah', 'category' => 'count'],
            ['code' => 'SET', 'name' => 'Set / Perangkat', 'category' => 'count'],
            ['code' => 'BOX', 'name' => 'Box / Kotak', 'category' => 'packaging'],
            ['code' => 'RIM', 'name' => 'Rim (500 Lembar)', 'category' => 'packaging'],
            ['code' => 'DUS', 'name' => 'Dus / Kardus Besar', 'category' => 'packaging'],
            ['code' => 'KARTON', 'name' => 'Karton', 'category' => 'packaging'],
            ['code' => 'PACK', 'name' => 'Pack / Bungkus', 'category' => 'packaging'],
            ['code' => 'METER', 'name' => 'Meter (Panjang)', 'category' => 'length'],
            ['code' => 'ROLL', 'name' => 'Roll / Gulung', 'category' => 'length'],
            ['code' => 'KG', 'name' => 'Kilogram', 'category' => 'weight'],
            ['code' => 'GRAM', 'name' => 'Gram', 'category' => 'weight'],
            ['code' => 'TON', 'name' => 'Ton Metrik', 'category' => 'weight'],
            ['code' => 'LITER', 'name' => 'Liter', 'category' => 'volume'],
            ['code' => 'DRUM', 'name' => 'Drum Minyak/Cairan', 'category' => 'volume'],
            ['code' => 'LAYANAN', 'name' => 'Layanan / Jasa', 'category' => 'service'],
            ['code' => 'PAKET', 'name' => 'Paket Pengadaan', 'category' => 'service'],
            ['code' => 'BULAN', 'name' => 'Bulan Langganan', 'category' => 'service'],
        ];

        foreach ($units as $u) {
            MeasurementUnit::firstOrCreate(['code' => $u['code']], $u);
        }

        $conversions = [
            // Kertas: 1 Box = 5 Rim
            ['from_unit_code' => 'BOX', 'to_unit_code' => 'RIM', 'multiplier' => 5.0000, 'notes' => '1 Box kertas standar berisi 5 Rim (2.500 lembar)'],
            // Kemasan umum: 1 Dus = 12 Pack
            ['from_unit_code' => 'DUS', 'to_unit_code' => 'PACK', 'multiplier' => 12.0000, 'notes' => '1 Dus berisi 12 Pack'],
            // Kemasan ritel: 1 Karton = 24 Pcs
            ['from_unit_code' => 'KARTON', 'to_unit_code' => 'PCS', 'multiplier' => 24.0000, 'notes' => '1 Karton berisi 24 Pcs'],
            // Kabel/Material: 1 Roll = 50 Meter
            ['from_unit_code' => 'ROLL', 'to_unit_code' => 'METER', 'multiplier' => 50.0000, 'notes' => '1 Roll kabel jaringan UTP/kain berisi 50 Meter'],
            // Berat: 1 Ton = 1000 Kg, 1 Kg = 1000 Gram
            ['from_unit_code' => 'TON', 'to_unit_code' => 'KG', 'multiplier' => 1000.0000, 'notes' => '1 Ton = 1.000 Kilogram'],
            ['from_unit_code' => 'KG', 'to_unit_code' => 'GRAM', 'multiplier' => 1000.0000, 'notes' => '1 Kilogram = 1.000 Gram'],
            // Cairan: 1 Drum = 200 Liter
            ['from_unit_code' => 'DRUM', 'to_unit_code' => 'LITER', 'multiplier' => 200.0000, 'notes' => '1 Drum industri standar = 200 Liter'],
        ];

        foreach ($conversions as $c) {
            UnitConversion::firstOrCreate(
                ['from_unit_code' => $c['from_unit_code'], 'to_unit_code' => $c['to_unit_code']],
                $c
            );
        }
    }
}
