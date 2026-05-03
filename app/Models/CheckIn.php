<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CheckIn extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'weight_kg' => 'decimal:1',
            'body_fat_percentage' => 'decimal:1',
            'lean_mass_kg' => 'decimal:1',
            'body_water_percentage' => 'decimal:1',
            'skinfold_triceps' => 'decimal:1',
            'skinfold_biceps' => 'decimal:1',
            'skinfold_subscapular' => 'decimal:1',
            'skinfold_suprailiac' => 'decimal:1',
            'water_liters' => 'decimal:1',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(ClientProfile::class, 'client_id');
    }

    public function measurements(): HasMany
    {
        return $this->hasMany(CheckInMeasurement::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(CheckInPhoto::class);
    }
}
