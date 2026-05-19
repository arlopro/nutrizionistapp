<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class LabResult extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = ['id'];

    protected $appends = ['file_url'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'glucose' => 'decimal:2',
            'hba1c' => 'decimal:2',
            'total_cholesterol' => 'decimal:2',
            'hdl_cholesterol' => 'decimal:2',
            'ldl_cholesterol' => 'decimal:2',
            'triglycerides' => 'decimal:2',
            'creatinine' => 'decimal:2',
            'tsh' => 'decimal:2',
            'crp' => 'decimal:2',
            'zonulin' => 'decimal:2',
            'calprotectin' => 'decimal:2',
        ];
    }

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? Storage::disk('public')->url($this->file_path) : null;
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(ClientProfile::class, 'client_id');
    }
}
