<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'plan.feature' => \App\Http\Middleware\EnsurePlanFeature::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
            'paypal/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response, \Throwable $e, \Illuminate\Http\Request $request) {
            $status = $response->getStatusCode();
            if (in_array($status, [403, 404, 419, 429, 500, 503], true) && !$request->expectsJson()) {
                $user = $request->user();
                $role = null;
                if ($user) {
                    if ($user->hasRole('dev')) $role = 'dev';
                    elseif ($user->hasRole('nutritionist')) $role = 'nutritionist';
                    elseif ($user->hasRole('client')) $role = 'client';
                }
                return \Inertia\Inertia::render('Errors/Error', [
                    'status'   => $status,
                    'userRole' => $role,
                ])->toResponse($request)->setStatusCode($status);
            }
            return $response;
        });

        $exceptions->report(function (\Throwable $e) {
            $isProduction = app()->environment('production');
            $isMailEnabled = (bool) env('LOG_ERRORS_VIA_MAIL', false);

            if (($isProduction || $isMailEnabled) && app()->bound('mailer')) {
                \Illuminate\Support\Facades\RateLimiter::attempt(
                    'error-mail:' . md5(get_class($e) . $e->getMessage()),
                    5,
                    function () use ($e) {
                        try {
                            \Illuminate\Support\Facades\Mail::to('info@davidearlotti.it')
                                ->send(new \App\Mail\ServerErrorReport($e, request()));
                        } catch (\Throwable) {
                            // never let error reporting throw
                        }
                    }
                );
            }
        });
    })->create();
