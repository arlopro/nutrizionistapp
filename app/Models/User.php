<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'last_name', 'email', 'password', 'phone', 'avatar', 'invitation_sent_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, Billable;

    protected function casts(): array
    {
        return [
            'email_verified_at'   => 'datetime',
            'invitation_sent_at'  => 'datetime',
            'password'            => 'hashed',
        ];
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
