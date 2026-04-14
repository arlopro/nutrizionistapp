<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NutritionistProfile extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'session_durations'       => 'array',
            'locations'               => 'array',
            'notification_settings'   => 'array',
            'onboarding_completed_at' => 'datetime',
        ];
    }

    public function getNotificationSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->notification_settings, $key, $default);
    }

    public function defaultNotificationSettings(): array
    {
        return [
            'appointment_reminder'      => true,
            'appointment_reminder_hours' => 24,
            'checkin_reminder'           => true,
            'checkin_reminder_day'       => 'monday',
            'plan_delivered'             => true,
        ];
    }

    public function mergedNotificationSettings(): array
    {
        return array_merge($this->defaultNotificationSettings(), $this->notification_settings ?? []);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
