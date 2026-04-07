<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $clientProfile = $user->clientProfile;

        $activePlan = $clientProfile?->activePlan();

        $lastCheckIn = $clientProfile?->checkIns()
            ->with('measurements')
            ->latest('date')
            ->first();

        $weightHistory = $clientProfile?->checkIns()
            ->whereNotNull('weight_kg')
            ->orderBy('date')
            ->limit(12)
            ->get(['date', 'weight_kg']) ?? collect();

        $nextAppointment = $clientProfile
            ? Appointment::where('client_id', $clientProfile->id)
                ->upcoming()
                ->first()
            : null;

        return Inertia::render('Client/Dashboard', [
            'activePlan' => $activePlan,
            'lastCheckIn' => $lastCheckIn,
            'weightHistory' => $weightHistory,
            'nextAppointment' => $nextAppointment?->load('nutritionist'),
            'bmi' => $clientProfile?->bmi,
            'bmiCategory' => $clientProfile?->bmi_category,
        ]);
    }
}
