<?php

namespace App\Models;

use App\Enums\MealType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanMeal extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'meal_type' => MealType::class,
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(NutritionalPlan::class, 'nutritional_plan_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PlanMealItem::class)->orderBy('sort_order');
    }
}
