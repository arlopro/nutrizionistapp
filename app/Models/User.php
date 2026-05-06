<?php

namespace App\Models;

use Database\Factories\UserFactory;
use App\Models\NutritionalPlan;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'last_name', 'email', 'password', 'phone', 'avatar', 'invitation_sent_at', 'last_login_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, Billable, LogsActivity;

    protected function casts(): array
    {
        return [
            'email_verified_at'   => 'datetime',
            'invitation_sent_at'  => 'datetime',
            'last_login_at'       => 'datetime',
            'password'            => 'hashed',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'last_name', 'email'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function plans(): HasMany
    {
        return $this->hasMany(NutritionalPlan::class, 'nutritionist_id');
    }

    public function nutritionistProfile(): HasOne
    {
        return $this->hasOne(NutritionistProfile::class);
    }

    public function clientProfile(): HasOne
    {
        return $this->hasOne(ClientProfile::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(ClientProfile::class, 'nutritionist_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->name . ' ' . $this->last_name);
    }

    public function isNutritionist(): bool
    {
        return $this->hasRole('nutritionist');
    }

    public function isClient(): bool
    {
        return $this->hasRole('client');
    }
}
