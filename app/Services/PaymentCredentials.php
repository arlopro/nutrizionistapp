<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Log;

class PaymentCredentials
{
    private static ?array $stripeCache = null;
    private static ?array $paypalCache = null;
    private static bool $stripeResolved = false;
    private static bool $paypalResolved = false;

    public static function stripe(): ?array
    {
        if (!static::$stripeResolved) {
            static::$stripeResolved = true;
            $v = AppSetting::groupValues('stripe');

            if (!empty($v['secret']) && !empty($v['key'])) {
                static::$stripeCache = $v;
            }
        }
        return static::$stripeCache;
    }

    public static function paypal(): ?array
    {
        if (!static::$paypalResolved) {
            static::$paypalResolved = true;
            $v = AppSetting::groupValues('paypal');

            if (!empty($v['client_id']) && !empty($v['secret'])) {
                static::$paypalCache = $v;
            }
        }
        return static::$paypalCache;
    }

    public static function stripeConfigured(): bool
    {
        return static::stripe() !== null;
    }

    public static function paypalConfigured(): bool
    {
        return static::paypal() !== null;
    }

    public static function applyToConfig(): void
    {
        try {
            $stripe = static::stripe();
            if ($stripe) {
                config([
                    'cashier.key'                => $stripe['key'] ?? null,
                    'cashier.secret'             => $stripe['secret'] ?? null,
                    'cashier.webhook.secret'     => $stripe['webhook_secret'] ?? null,
                    'cashier.price_starter'      => $stripe['price_starter'] ?? null,
                    'cashier.price_pro'          => $stripe['price_pro'] ?? null,
                    'cashier.price_business'     => $stripe['price_business'] ?? null,
                ]);
            }

            $paypal = static::paypal();
            if ($paypal) {
                $mode = $paypal['mode'] ?? 'sandbox';
                config([
                    'services.paypal.client_id'     => $paypal['client_id'] ?? null,
                    'services.paypal.secret'         => $paypal['secret'] ?? null,
                    'services.paypal.webhook_id'     => $paypal['webhook_id'] ?? null,
                    'services.paypal.plan_starter'   => $paypal['plan_starter'] ?? null,
                    'services.paypal.plan_pro'       => $paypal['plan_pro'] ?? null,
                    'services.paypal.plan_business'  => $paypal['plan_business'] ?? null,
                    'services.paypal.mode'           => $mode,
                    // Override the srmklive/paypal config directly so the SDK picks it up
                    'paypal.mode'                       => $mode,
                    "paypal.{$mode}.client_id"          => $paypal['client_id'] ?? null,
                    "paypal.{$mode}.client_secret"      => $paypal['secret'] ?? null,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('PaymentCredentials::applyToConfig failed: ' . $e->getMessage());
        }
    }

    public static function resetCache(): void
    {
        static::$stripeCache = null;
        static::$paypalCache = null;
        static::$stripeResolved = false;
        static::$paypalResolved = false;
    }
}
