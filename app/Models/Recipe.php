<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recipe extends Model
{
    protected $guarded = ['id'];

    public function nutritionist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nutritionist_id');
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class)->orderBy('sort_order');
    }

    public function getTotalCaloriesAttribute(): float
    {
        return $this->ingredients->sum(function ($ingredient) {
            return ($ingredient->food->calories_per_100g * $ingredient->quantity_grams) / 100;
        });
    }

    public function getTotalProteinAttribute(): float
    {
        return $this->ingredients->sum(function ($ingredient) {
            return ($ingredient->food->protein_per_100g * $ingredient->quantity_grams) / 100;
        });
    }

    public function getTotalCarbsAttribute(): float
    {
        return $this->ingredients->sum(function ($ingredient) {
            return ($ingredient->food->carbs_per_100g * $ingredient->quantity_grams) / 100;
        });
    }

    public function getTotalFatAttribute(): float
    {
        return $this->ingredients->sum(function ($ingredient) {
            return ($ingredient->food->fat_per_100g * $ingredient->quantity_grams) / 100;
        });
    }
}
