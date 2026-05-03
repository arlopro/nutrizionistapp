<?php

namespace App\Http\Controllers\Dev;

use App\Events\ImpersonationStarted;
use App\Events\ImpersonationStopped;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ImpersonateController extends Controller
{
    public function impersonate(Request $request, User $user)
    {
        $admin = $request->user();

        abort_if($user->hasRole('dev'), 403, 'Non puoi impersonare un altro account dev.');
        abort_if($user->id === $admin->id, 403, 'Non puoi impersonare te stesso.');

        if (!Session::has('impersonating_original_id')) {
            Session::put('impersonating_original_id', $admin->id);
        }

        ImpersonationStarted::dispatch($admin, $user);

        Auth::loginUsingId($user->id);

        if ($user->hasRole('nutritionist')) {
            return redirect()->route('nutritionist.dashboard');
        }
        if ($user->hasRole('client')) {
            return redirect()->route('client.dashboard');
        }

        return redirect()->route('dashboard');
    }

    public function stop()
    {
        $originalId = Session::pull('impersonating_original_id');

        if ($originalId) {
            $impersonated = Auth::user();
            $admin = User::find($originalId);

            if (!$admin || !$admin->hasRole('dev')) {
                Auth::logout();
                return redirect()->route('login');
            }

            ImpersonationStopped::dispatch($admin, $impersonated);
            Auth::loginUsingId($originalId);
        }

        return redirect()->route('dev.dashboard');
    }
}
