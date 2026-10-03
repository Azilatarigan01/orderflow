<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitConversion extends Model
{
    protected $fillable = [
        'from_unit_code',
        'to_unit_code',
        'multiplier',
        'notes',
    ];

    protected $casts = [
        'multiplier' => 'float',
    ];

    public function fromUnit()
    {
        return $this->belongsTo(MeasurementUnit::class, 'from_unit_code', 'code');
    }

    public function toUnit()
    {
        return $this->belongsTo(MeasurementUnit::class, 'to_unit_code', 'code');
    }
}
