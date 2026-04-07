<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ImpersonateController extends Controller
{
    public function impersonate(Request $request, User $user)
    {
        // Salva l'ID originale in sessione (solo se non stiamo già impersonificando)
        if (!Session::has('impersonating_original_id')) {
            Session::put('impersonating_original_id', $request->user()->id);
        }

        Auth::loginUsingId($user->id);

        // Redirect in base al ruolo dell'utente impersonificato
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
            Auth::loginUsingId($originalId);
        }

        return redirect()->route('dev.dashboard');
    }
}
