<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'is_secret', 'verified_at', 'last_verified_error'];

    protected $casts = [
        'value'       => 'encrypted',
        'is_secret'   => 'boolean',
        'verified_at' => 'datetime',
    ];

    public static function get(string $group, string $key): ?string
    {
        return static::where('group', $group)->where('key', $key)->value('value');
    }

    public static function set(string $group, string $key, ?string $value, bool $isSecret = false): void
    {
        static::updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => $value, 'is_secret' => $isSecret]
        );
    }

    public static function group(string $group): array
    {
        return static::where('group', $group)
            ->get(['key', 'value', 'is_secret', 'verified_at', 'last_verified_error'])
            ->keyBy('key')
            ->toArray();
    }

    public static function groupValues(string $group): array
    {
        return static::where('group', $group)
            ->get(['key', 'value'])
            ->pluck('value', 'key')
            ->toArray();
    }

    public function markVerified(): void
    {
        $this->updateQuietly([
            'verified_at'          => now(),
            'last_verified_error'  => null,
        ]);
    }

    public function markVerificationFailed(string $error): void
    {
        $this->updateQuietly([
            'verified_at'         => null,
            'last_verified_error' => $error,
        ]);
    }

    public static function markGroupVerified(string $group): void
    {
        static::where('group', $group)->update([
            'verified_at'         => now(),
            'last_verified_error' => null,
        ]);
    }

    public static function markGroupFailed(string $group, string $error): void
    {
        static::where('group', $group)->update([
            'verified_at'         => null,
            'last_verified_error' => $error,
        ]);
    }
}
