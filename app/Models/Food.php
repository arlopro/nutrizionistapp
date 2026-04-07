<?php

namespace App\Models;

use App\Enums\FoodCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Food extends Model
{
    protected $table = 'foods';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'category' => FoodCategory::class,
            'calories_per_100g' => 'decimal:1',
            'protein_per_100g' => 'decimal:1',
            'carbs_per_100g' => 'decimal:1',
            'fat_per_100g' => 'decimal:1',
            'fiber_per_100g' => 'decimal:1',
        ];
    }

    public function nutritionist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nutritionist_id');
    }

    public function scopeGlobal($query)
    {
        return $query->whereNull('nutritionist_id');
    }

    public function scopeForNutritionist($query, int $nutritionistId)
    {
        return $query->where(function ($q) use ($nutritionistId) {
            $q->whereNull('nutritionist_id')
              ->orWhere('nutritionist_id', $nutritionistId);
        });
    }
}
