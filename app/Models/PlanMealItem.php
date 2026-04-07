<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanMealItem extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'quantity_grams' => 'decimal:1',
        ];
    }

    public function meal(): BelongsTo
    {
        return $this->belongsTo(PlanMeal::class, 'plan_meal_id');
    }

    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class);
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function alternatives(): HasMany
    {
        return $this->hasMany(PlanMealItem::class, 'alternative_of');
    }

    public function alternativeOf(): BelongsTo
    {
        return $this->belongsTo(PlanMealItem::class, 'alternative_of');
    }
}
