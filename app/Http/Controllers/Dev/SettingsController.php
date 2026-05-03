<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Services\PaymentCredentials;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class SettingsController extends Controller
{
    private const STRIPE_FIELDS = [
        'key'              => ['label' => 'Publishable Key',    'secret' => false],
        'secret'           => ['label' => 'Secret Key',         'secret' => true],
        'webhook_secret'   => ['label' => 'Webhook Secret',     'secret' => true],
        'price_starter'    => ['label' => 'Price ID Starter',   'secret' => false],
        'price_pro'        => ['label' => 'Price ID Pro',       'secret' => false],
        'price_business'   => ['label' => 'Price ID Business',  'secret' => false],
    ];

    private const PAYPAL_FIELDS = [
        'client_id'    => ['label' => 'Client ID',         'secret' => false],
        'secret'       => ['label' => 'Client Secret',     'secret' => true],
        'webhook_id'   => ['label' => 'Webhook ID',        'secret' => false],
        'plan_starter' => ['label' => 'Plan ID Starter',   'secret' => false],
        'plan_pro'     => ['label' => 'Plan ID Pro',       'secret' => false],
        'plan_business'=> ['label' => 'Plan ID Business',  'secret' => false],
        'mode'         => ['label' => 'Modalità',          'secret' => false],
    ];

    public function payments()
    {
        return Inertia::render('Dev/Settings/Payments', [
            'stripe'      => $this->buildFieldData('stripe', self::STRIPE_FIELDS),
            'paypal'      => $this->buildFieldData('paypal', self::PAYPAL_FIELDS),
            'webhookUrls' => [
                'stripe' => url('/stripe/webhook'),
                'paypal' => url('/paypal/webhook'),
            ],
        ]);
    }

    public function update(Request $request, string $provider)
    {
        abort_unless(in_array($provider, ['stripe', 'paypal']), 404);

        $fields = $provider === 'stripe' ? self::STRIPE_FIELDS : self::PAYPAL_FIELDS;

        foreach ($fields as $key => $meta) {
            if ($request->has($key)) {
                $value = $request->input($key);
                if ($value !== null && $value !== '') {
                    AppSetting::set($provider, $key, $value, $meta['secret']);
                }
            }
        }

        PaymentCredentials::resetCache();

        $testResult = $this->runTest($provider);

        return back()->with(
            $testResult['ok'] ? 'success' : 'error',
            $testResult['ok']
                ? ucfirst($provider) . ' salvato e verificato correttamente.'
                : 'Credenziali salvate ma il test ha fallito: ' . $testResult['error']
        );
    }

    public function test(string $provider)
    {
        abort_unless(in_array($provider, ['stripe', 'paypal']), 404);

        PaymentCredentials::resetCache();
        $result = $this->runTest($provider);

        return back()->with(
            $result['ok'] ? 'success' : 'error',
            $result['ok']
                ? ucfirst($provider) . ' verificato con successo.'
                : 'Test fallito: ' . $result['error']
        );
    }

    private function runTest(string $provider): array
    {
        try {
            if ($provider === 'stripe') {
                $values = AppSetting::groupValues('stripe');
                if (empty($values['secret'])) {
                    return ['ok' => false, 'error' => 'Secret key mancante.'];
                }

                $stripe = new \Stripe\StripeClient($values['secret']);
                $stripe->balance->retrieve();

                AppSetting::markGroupVerified('stripe');
                return ['ok' => true];
            }

            if ($provider === 'paypal') {
                $values = AppSetting::groupValues('paypal');
                if (empty($values['client_id']) || empty($values['secret'])) {
                    return ['ok' => false, 'error' => 'Client ID o Secret mancanti.'];
                }

                $mode = $values['mode'] ?? 'sandbox';
                $baseUrl = $mode === 'live'
                    ? 'https://api-m.paypal.com'
                    : 'https://api-m.sandbox.paypal.com';

                $response = \Illuminate\Support\Facades\Http::withBasicAuth($values['client_id'], $values['secret'])
                    ->asForm()
                    ->post("{$baseUrl}/v1/oauth2/token", ['grant_type' => 'client_credentials']);

                if (!$response->ok() || empty($response->json('access_token'))) {
                    $error = $response->json('error_description') ?? 'Risposta non valida da PayPal.';
                    AppSetting::markGroupFailed('paypal', $error);
                    return ['ok' => false, 'error' => $error];
                }

                AppSetting::markGroupVerified('paypal');
                return ['ok' => true];
            }
        } catch (\Throwable $e) {
            Log::warning("Payment test failed for {$provider}: " . $e->getMessage());
            AppSetting::markGroupFailed($provider, $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }

        return ['ok' => false, 'error' => 'Provider sconosciuto.'];
    }

    private function buildFieldData(string $group, array $fields): array
    {
        $rows = AppSetting::where('group', $group)->get()->keyBy('key');
        $verifiedAt = $rows->first()?->verified_at?->toIso8601String();
        $lastError  = $rows->first()?->last_verified_error;

        $data = [
            'verified_at'        => $verifiedAt,
            'last_verified_error' => $lastError,
            'fields'             => [],
        ];

        foreach ($fields as $key => $meta) {
            $row   = $rows->get($key);
            $value = $row?->value ?? '';

            $data['fields'][$key] = [
                'label'     => $meta['label'],
                'is_secret' => $meta['secret'],
                'filled'    => !empty($value),
                'masked'    => $meta['secret'] && !empty($value)
                    ? '••••••••' . substr($value, -4)
                    : ($value ?: ''),
            ];
        }

        return $data;
    }
}
