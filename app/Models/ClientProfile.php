<?php

namespace App\Models;

use App\Enums\ActivityLevel;
use App\Enums\ClientGoal;
use App\Enums\ClientStatus;
use App\Enums\Gender;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientProfile extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['bmi', 'bmi_category', 'current_weight'];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'gender' => Gender::class,
            'activity_level' => ActivityLevel::class,
            'goal' => ClientGoal::class,
            'status' => ClientStatus::class,
            'allergies' => 'array',
            'intolerances' => 'array',
            'height_cm' => 'decimal:1',
            'initial_weight_kg' => 'decimal:1',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function nutritionist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nutritionist_id');
    }

    public function nutritionalPlans(): HasMany
    {
        return $this->hasMany(NutritionalPlan::class, 'client_id');
    }

    public function checkIns(): HasMany
    {
        return $this->hasMany(CheckIn::class, 'client_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'client_id');
    }

    public function labResults(): HasMany
    {
        return $this->hasMany(LabResult::class, 'client_id');
    }

    /**
     * Un cliente è "attivo" se ha almeno un piano nutrizionale collegato.
     */
    public function scopeActive($query)
    {
        return $query->whereHas('nutritionalPlans');
    }

    public function activePlan()
    {
        return $this->nutritionalPlans()
            ->where('status', 'active')
            ->where('start_date', '<=', now()->toDateString())
            ->where(function ($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now()->toDateString());
            })
            ->orderByDesc('start_date')
            ->first();
    }

    public function mealCompletions(): HasMany
    {
        return $this->hasMany(MealCompletion::class, 'client_id');
    }

    public function getBmiAttribute(): ?float
    {
        $weight = $this->latestWeight();
        if (!$this->height_cm || !$weight) {
            return null;
        }
        $heightM = $this->height_cm / 100;
        return round($weight / ($heightM * $heightM), 1);
    }

    public function latestWeight(): ?float
    {
        $lastCheckInWeight = $this->checkIns()
            ->whereNotNull('weight_kg')
            ->latest('date')
            ->value('weight_kg');

        return $lastCheckInWeight ?? $this->initial_weight_kg;
    }

    public function getCurrentWeightAttribute(): ?float
    {
        return $this->latestWeight();
    }

    public function getBmiCategoryAttribute(): ?string
    {
        $bmi = $this->bmi;
        if ($bmi === null) return null;
        return match (true) {
            $bmi < 18.5 => 'Sottopeso',
            $bmi < 25.0 => 'Normopeso',
            $bmi < 30.0 => 'Sovrappeso',
            default     => 'Obesità',
        };
    }
}
