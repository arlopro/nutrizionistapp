<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NutritionistProfile extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'session_durations'       => 'array',
            'locations'               => 'array',
            'onboarding_completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
