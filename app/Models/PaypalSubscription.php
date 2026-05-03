<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaypalSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'paypal_subscription_id',
        'plan_key',
        'status',
        'current_period_end',
    ];

    protected $casts = [
        'current_period_end' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'ACTIVE';
    }
}
