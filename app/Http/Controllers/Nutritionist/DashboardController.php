<?php

namespace App\Http\Controllers\Nutritionist;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\CheckIn;
use App\Models\NutritionalPlan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user    = $request->user();
        $profile = $user->nutritionistProfile;

        $clientCount       = $user->clients()->count();
        $activeClientCount = $user->clients()->active()->count();
        $planCount         = NutritionalPlan::where('nutritionist_id', $user->id)->count();

        $todayAppointments = Appointment::where('nutritionist_id', $user->id)
            ->whereDate('starts_at', today())
            ->with('client.user')
            ->orderBy('starts_at')
            ->get();

        $upcomingAppointments = Appointment::where('nutritionist_id', $user->id)
            ->where('starts_at', '>', now())
            ->whereDate('starts_at', '>', today())
            ->with('client.user')
            ->orderBy('starts_at')
            ->limit(5)
            ->get();

        $recentCheckIns = CheckIn::whereIn('client_id', $user->clients()->pluck('id'))
            ->with('client.user')
            ->latest('date')
            ->limit(5)
            ->get();

        // Onboarding: mostra finché non completato
        $onboarding = null;
        if ($profile && !$profile->onboarding_completed_at) {
            $onboarding = [
                'steps' => [
                    [
                        'key'   => 'logo',
                        'label' => 'Carica il tuo logo',
                        'desc'  => 'Verrà mostrato nei PDF e nelle comunicazioni ai clienti',
                        'done'  => !empty($profile->logo),
                        'action' => 'settings',
                    ],
                    [
                        'key'   => 'business_name',
                        'label' => 'Nome studio o professionista',
                        'desc'  => 'Come vuoi presentarti ai clienti',
                        'done'  => !empty($profile->business_name),
                        'action' => 'settings',
                    ],
                    [
                        'key'   => 'phone',
                        'label' => 'Numero di telefono',
                        'desc'  => 'Usato per i promemoria appuntamenti',
                        'done'  => !empty($user->phone),
                        'action' => 'inline',
                    ],
                    [
                        'key'   => 'client_tone',
                        'label' => 'Come ti rivolgi ai clienti',
                        'desc'  => 'Formale, informale o amichevole — usato nelle email e comunicazioni',
                        'done'  => !empty($profile->client_tone),
                        'action' => 'inline',
                    ],
                    [
                        'key'   => 'session_durations',
                        'label' => 'Durata per tipo di appuntamento',
                        'desc'  => 'Prima visita, controllo, check-in… ognuno con la sua durata — pre-compilerà gli appuntamenti in automatico',
                        'done'  => !empty($profile->session_durations),
                        'action' => 'inline',
                    ],
                    [
                        'key'   => 'first_client',
                        'label' => 'Aggiungi il tuo primo cliente',
                        'desc'  => 'Inizia a costruire la tua rubrica pazienti',
                        'done'  => $clientCount > 0,
                        'action' => 'clients',
                    ],
                ],
                'current' => [
                    'client_tone'      => $profile->client_tone,
                    'session_durations' => $profile->session_durations ?? [],
                    'phone'            => $user->phone,
                ],
            ];
        }

        return Inertia::render('Nutritionist/Dashboard', [
            'stats' => [
                'clientCount'           => $clientCount,
                'activeClientCount'     => $activeClientCount,
                'todayAppointmentCount' => $todayAppointments->count(),
                'planCount'             => $planCount,
            ],
            'todayAppointments'    => $todayAppointments,
            'upcomingAppointments' => $upcomingAppointments,
            'recentCheckIns'       => $recentCheckIns,
            'onboarding'           => $onboarding,
        ]);
    }
}
