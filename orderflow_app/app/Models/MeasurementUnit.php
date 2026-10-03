<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeasurementUnit extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'code',
        'name',
        'category',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function conversionsFrom()
    {
        return $this->hasMany(UnitConversion::class, 'from_unit_code', 'code');
    }

    public function conversionsTo()
    {
        return $this->hasMany(UnitConversion::class, 'to_unit_code', 'code');
    }
}
