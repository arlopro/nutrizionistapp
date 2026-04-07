<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $clientProfile = $request->user()->clientProfile;
        abort_unless($clientProfile, 404);

        $appointments = Appointment::where('client_id', $clientProfile->id)
            ->with('nutritionist')
            ->orderByDesc('starts_at')
            ->paginate(20);

        return Inertia::render('Client/Appointments', [
            'appointments' => $appointments,
        ]);
    }
}
