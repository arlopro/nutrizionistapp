<?php

namespace App\Models;

use App\Enums\PhotoType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CheckInPhoto extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $appends = ['url'];

    protected function casts(): array
    {
        return [
            'photo_type' => PhotoType::class,
            'created_at' => 'datetime',
        ];
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }

    public function checkIn(): BelongsTo
    {
        return $this->belongsTo(CheckIn::class);
    }
}
