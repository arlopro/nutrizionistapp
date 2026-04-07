<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? array_merge($user->toArray(), [
                    'roles'     => $user->roles->pluck('name'),
                    'avatarUrl' => $user->avatar ? Storage::disk('public')->url($user->avatar) : null,
                ]) : null,
            ],
            'impersonating' => Session::has('impersonating_original_id'),
            'flash' => [
                'success' => Session::get('success'),
                'error'   => Session::get('error'),
            ],
        ];
    }
}
