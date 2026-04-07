<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['nutritionist_id', 'name', 'description', 'is_default', 'questions'])]
class AnamnesisTemplate extends Model
{
    protected function casts(): array
    {
        return [
            'questions'  => 'array',
            'is_default' => 'boolean',
        ];
    }

    public function getQuestionsAttribute($value): array
    {
        if (!$value) return [];
        $decoded = is_string($value) ? json_decode($value, true) : $value;
        return is_array($decoded) ? $decoded : [];
    }

    public function nutritionist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nutritionist_id');
    }
}
