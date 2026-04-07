<?php

namespace App\Models;

use App\Enums\MeasurementType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckInMeasurement extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'measurement_type' => MeasurementType::class,
            'value_cm' => 'decimal:1',
        ];
    }

    public function checkIn(): BelongsTo
    {
        return $this->belongsTo(CheckIn::class);
    }
}
