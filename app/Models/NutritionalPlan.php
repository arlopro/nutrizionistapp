<?php

namespace App\Models;

use App\Enums\PlanStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NutritionalPlan extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => PlanStatus::class,
            'protein_grams' => 'decimal:1',
            'carbs_grams' => 'decimal:1',
            'fat_grams' => 'decimal:1',
            'is_template' => 'boolean',
        ];
    }

    public function scopeTemplates($query)
    {
        return $query->where('is_template', true);
    }

    public function scopePlans($query)
    {
        return $query->where('is_template', false);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(ClientProfile::class, 'client_id');
    }

    public function nutritionist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nutritionist_id');
    }

    public function meals(): HasMany
    {
        return $this->hasMany(PlanMeal::class, 'nutritional_plan_id')->orderBy('day_of_week')->orderBy('sort_order');
    }

    public function supplements(): HasMany
    {
        return $this->hasMany(PlanSupplement::class, 'nutritional_plan_id')->orderBy('sort_order');
    }
}
