<?php

namespace App\Models;

use App\Enums\FoodCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\PlanMealItem;

class Food extends Model
{
    use SoftDeletes;

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
            'sodium_mg' => 'decimal:1',
            'potassium_mg' => 'decimal:1',
            'calcium_mg' => 'decimal:1',
            'iron_mg' => 'decimal:1',
            'vitamin_d_mcg' => 'decimal:1',
            'vitamin_b12_mcg' => 'decimal:1',
            'glycemic_index' => 'integer',
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

    public function scopeVisibleTo($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->whereNull('nutritionist_id')
              ->orWhere('nutritionist_id', $userId);
        })->whereNotIn('id', function ($sub) use ($userId) {
            $sub->select('food_id')
                ->from('food_user_hidden')
                ->where('user_id', $userId);
        });
    }

    public function hiddenByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'food_user_hidden');
    }

    public function isUsedByNutritionist(int $nutritionistId): bool
    {
        return PlanMealItem::where('food_id', $this->id)
            ->whereHas('planMeal.plan', fn ($q) => $q->where('nutritionist_id', $nutritionistId))
            ->exists();
    }
}
