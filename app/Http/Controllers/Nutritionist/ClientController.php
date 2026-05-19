<?php

namespace App\Http\Controllers\Nutritionist;

use App\Enums\ActivityLevel;
use App\Enums\ClientGoal;
use App\Enums\Gender;
use App\Http\Controllers\Controller;
use App\Models\ClientProfile;
use App\Models\User;
use App\Services\SubscriptionService;
use App\Notifications\ClientInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $nutritionist = $request->user();

        $totalCount    = $nutritionist->clients()->count();
        $activeCount   = $nutritionist->clients()->where('status', 'active')->count();
        $inactiveCount = $nutritionist->clients()->where('status', 'inactive')->count();
        $archivedCount = $nutritionist->clients()->where('status', 'archived')->count();

        $withoutCheckinCount = $nutritionist->clients()
            ->where('status', 'active')
            ->whereDoesntHave('checkIns', fn ($q) => $q->where('date', '>=', now()->subDays(14)))
            ->count();

        $appointmentsTodayCount = \App\Models\Appointment::whereHas('client', fn ($q) =>
            $q->where('nutritionist_id', $nutritionist->id)
        )->whereBetween('starts_at', [now()->startOfDay(), now()->endOfDay()])->count();

        $goalCounts = $nutritionist->clients()
            ->where('status', 'active')
            ->whereNotNull('goal')
            ->selectRaw('goal, count(*) as count')
            ->groupBy('goal')
            ->pluck('count', 'goal')
            ->toArray();

        $clients = $nutritionist->clients()
            ->addSelect([
                'client_profiles.*',
                \Illuminate\Support\Facades\DB::raw(
                    "(SELECT title FROM nutritional_plans WHERE client_id = client_profiles.id AND status = 'active' AND is_template = 0 ORDER BY created_at DESC LIMIT 1) as active_plan_name"
                ),
            ])
            ->with([
                'user',
                'checkIns'     => fn ($q) => $q->whereNotNull('weight_kg')->orderBy('date', 'desc'),
                'appointments' => fn ($q) => $q->where('starts_at', '>=', now())->orderBy('starts_at'),
            ])
            ->when($request->search, fn ($q, $search) =>
                $q->whereHas('user', fn ($q) =>
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                )
            )
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->goal,   fn ($q, $goal)   => $q->where('goal', $goal))
            ->when($request->sort === 'name', fn ($q) =>
                $q->orderByRaw('(SELECT name FROM users WHERE id = client_profiles.user_id)')
            )
            ->when($request->sort === 'last_checkin', fn ($q) =>
                $q->orderByRaw('(SELECT MAX(date) FROM check_ins WHERE client_id = client_profiles.id) DESC')
            )
            ->when(!in_array($request->sort, ['name', 'last_checkin']), fn ($q) =>
                $q->latest()
            )
            ->paginate(20)
            ->withQueryString();

        $clients->through(function ($client) {
            $checkIns      = $client->checkIns;
            $lastCheckIn   = $checkIns->first();
            $weightHistory = $checkIns->take(6)->pluck('weight_kg')->reverse()->values()->toArray();

            $currentWeight = $lastCheckIn?->weight_kg ?? $client->initial_weight_kg;
            $initialWeight = $client->initial_weight_kg;
            $weightDelta   = ($currentWeight !== null && $initialWeight !== null)
                ? round((float) $currentWeight - (float) $initialWeight, 1)
                : null;

            $nextAppointment = $client->appointments->first();

            return [
                'id'                  => $client->id,
                'user'                => [
                    'name'      => $client->user->name,
                    'last_name' => $client->user->last_name,
                    'email'     => $client->user->email,
                    'phone'     => $client->user->phone,
                ],
                'status'              => $client->status instanceof \BackedEnum ? $client->status->value : $client->status,
                'goal'                => $client->goal instanceof \BackedEnum ? $client->goal->value : $client->goal,
                'current_weight'      => $currentWeight ? (float) $currentWeight : null,
                'weight_delta'        => $weightDelta,
                'weight_history'      => array_map('floatval', $weightHistory),
                'last_checkin_date'   => $lastCheckIn?->date,
                'next_appointment_at' => $nextAppointment?->starts_at,
                'active_plan_name'    => $client->active_plan_name,
            ];
        });

        return Inertia::render('Nutritionist/Clients/Index', [
            'clients'    => $clients,
            'filters'    => $request->only(['search', 'status', 'goal', 'sort']),
            'stats'      => [
                'total'              => $totalCount,
                'active'             => $activeCount,
                'inactive'           => $inactiveCount,
                'archived'           => $archivedCount,
                'without_checkin'    => $withoutCheckinCount,
                'appointments_today' => $appointmentsTodayCount,
            ],
            'goalCounts' => $goalCounts,
        ]);
    }

    public function create()
    {
        return Inertia::render('Nutritionist/Clients/Create', [
            'genders' => collect(Gender::cases())->map(fn ($g) => ['value' => $g->value, 'label' => $g->label()]),
            'activityLevels' => collect(ActivityLevel::cases())->map(fn ($a) => ['value' => $a->value, 'label' => $a->label()]),
            'goals' => collect(ClientGoal::cases())->map(fn ($g) => ['value' => $g->value, 'label' => $g->label()]),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => [
                'nullable', 'email', 'unique:users,email',
                $request->boolean('send_invitation') ? 'required' : 'nullable',
            ],
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string',
            'height_cm' => 'nullable|numeric|min:50|max:250',
            'initial_weight_kg' => 'nullable|numeric|min:20|max:300',
            'activity_level' => 'nullable|string',
            'goal' => 'nullable|string',
            'allergies' => 'nullable|array',
            'intolerances' => 'nullable|array',
            'pathologies' => 'nullable|string',
            'dietary_preferences' => 'nullable|string',
            'notes' => 'nullable|string',
            'fiscal_code' => 'nullable|string|max:16',
            'billing_name' => 'nullable|string|max:255',
            'billing_address' => 'nullable|string|max:255',
            'billing_city' => 'nullable|string|max:100',
            'billing_zip' => 'nullable|string|max:10',
            'billing_province' => 'nullable|string|max:5',
            'vat_number' => 'nullable|string|max:20',
            'send_invitation' => 'boolean',
        ]);

        // Verifica limite piano abbonamento
        if (!SubscriptionService::canAddClient($request->user())) {
            $limit = SubscriptionService::clientLimit($request->user());
            return back()->withErrors([
                'email' => "Hai raggiunto il limite di {$limit} clienti attivi del tuo piano. Esegui l'upgrade per aggiungerne altri.",
            ]);
        }

        $user = User::create([
            'name' => $validated['name'],
            'last_name' => $validated['last_name'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make(Str::random(32)),
        ]);

        $user->assignRole('client');

        $profileFields = ['date_of_birth', 'gender', 'height_cm', 'initial_weight_kg', 'activity_level', 'goal', 'allergies', 'intolerances', 'pathologies', 'dietary_preferences', 'notes', 'fiscal_code', 'billing_name', 'billing_address', 'billing_city', 'billing_zip', 'billing_province', 'vat_number'];

        ClientProfile::create(
            array_merge(
                ['user_id' => $user->id, 'nutritionist_id' => $request->user()->id],
                collect($validated)->only($profileFields)->map(fn ($v) => $v ?? null)->toArray()
            )
        );

        if (!empty($validated['send_invitation'])) {
            $this->sendClientInvitation($user, $request->user());
            $message = 'Cliente creato. Email di benvenuto inviata al cliente.';
        } else {
            $message = 'Cliente creato. Puoi inviare l\'invito di accesso dalla scheda cliente.';
        }

        return redirect()->route('nutritionist.clients.show', $user->clientProfile)
            ->with('success', $message);
    }

    public function export(Request $request)
    {
        $clients = $request->user()->clients()->with('user')->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="clienti-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($clients) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM for Excel
            fputcsv($handle, ['Nome', 'Cognome', 'Email', 'Telefono', 'Obiettivo', 'Stato', 'Peso iniziale (kg)', 'Altezza (cm)', 'Data nascita (YYYY-MM-DD)'], ';');
            foreach ($clients as $client) {
                fputcsv($handle, [
                    $client->user->name,
                    $client->user->last_name,
                    $client->user->email,
                    $client->user->phone,
                    $client->goal instanceof \BackedEnum ? $client->goal->value : $client->goal,
                    $client->status instanceof \BackedEnum ? $client->status->value : $client->status,
                    $client->initial_weight_kg,
                    $client->height_cm,
                    $client->date_of_birth,
                ], ';');
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importSample()
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="clienti-esempio.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, ['Nome', 'Cognome', 'Email', 'Telefono', 'Obiettivo', 'Stato', 'Peso iniziale (kg)', 'Altezza (cm)', 'Data nascita (YYYY-MM-DD)'], ';');
            fputcsv($handle, ['Mario', 'Rossi', 'mario.rossi@example.com', '+39 333 1234567', 'weight_loss', 'active', '80.5', '175', '1990-05-15'], ';');
            fputcsv($handle, ['Giulia', 'Bianchi', 'giulia.bianchi@example.com', '', 'maintenance', 'active', '65', '168', '1985-11-30'], ';');
            fputcsv($handle, ['Luca', 'Verdi', '', '+39 320 9876543', 'muscle_gain', 'active', '75', '182', ''], ';');
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        if (!SubscriptionService::canAddClient($request->user())) {
            return back()->withErrors(['file' => 'Limite clienti raggiunto. Esegui l\'upgrade per importarne altri.']);
        }

        $path = $request->file('file')->getRealPath();
        $rows = array_filter(array_map('str_getcsv', file($path), array_fill(0, count(file($path)), ';')));

        array_shift($rows); // rimuove intestazione

        $created = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $row = array_map('trim', $row);
            if (count($row) < 2 || empty($row[0])) continue;

            $email = !empty($row[2]) ? $row[2] : null;

            if ($email && \App\Models\User::where('email', $email)->exists()) {
                $skipped++;
                continue;
            }

            $user = \App\Models\User::create([
                'name'      => $row[0],
                'last_name' => $row[1] ?? null,
                'email'     => $email,
                'phone'     => $row[3] ?? null,
                'password'  => Hash::make(Str::random(32)),
            ]);

            $user->assignRole('client');

            $validGoals   = ['weight_loss', 'weight_gain', 'maintenance', 'muscle_gain', 'health'];
            $validStatuses = ['active', 'inactive', 'archived'];

            ClientProfile::create([
                'user_id'           => $user->id,
                'nutritionist_id'   => $request->user()->id,
                'goal'              => in_array($row[4] ?? '', $validGoals)   ? $row[4] : null,
                'status'            => in_array($row[5] ?? '', $validStatuses) ? $row[5] : 'active',
                'initial_weight_kg' => is_numeric($row[6] ?? '') ? (float) str_replace(',', '.', $row[6]) : null,
                'height_cm'         => is_numeric($row[7] ?? '') ? (float) str_replace(',', '.', $row[7]) : null,
                'date_of_birth'     => !empty($row[8]) ? $row[8] : null,
            ]);

            $created++;
        }

        $msg = "Importati {$created} clienti.";
        if ($skipped > 0) $msg .= " Saltati {$skipped} (email già esistente).";

        return back()->with('success', $msg);
    }

    public function sendInvitation(ClientProfile $client)
    {
        $this->authorizeClient($client);

        $client->load('user');
        $this->sendClientInvitation($client->user, auth()->user());

        return back()->with('success', 'Email di benvenuto inviata a ' . $client->user->email . '.');
    }

    private function sendClientInvitation(\App\Models\User $client, \App\Models\User $nutritionist): void
    {
        $token = Password::broker()->createToken($client);
        $client->notify(new ClientInvitation($token, $nutritionist));
        $client->update(['invitation_sent_at' => now()]);
    }

    public function show(Request $request, ClientProfile $client)
    {
        $this->authorizeClient($client);

        $client->load([
            'user',
            'nutritionalPlans' => fn ($q) => $q->where('is_template', false)->orderByDesc('created_at'),
            'nutritionalPlans.meals',
            'checkIns' => fn ($q) => $q->orderBy('date', 'desc'),
            'checkIns.measurements',
            'checkIns.photos',
            'appointments' => fn ($q) => $q->orderBy('starts_at', 'desc'),
            'anamnesisSubmissions' => fn ($q) => $q->with('template:id,name')->orderByDesc('sent_at'),
            'labResults' => fn ($q) => $q->orderByDesc('date'),
        ]);

        $activePlan = $client->activePlan();
        if ($activePlan) {
            $activePlan->load('meals');
        }

        $recentMessages = \App\Models\Message::where('client_id', $client->id)
            ->with('sender:id,name,last_name,avatar')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $anamnesisTemplates = \App\Models\AnamnesisTemplate::where('nutritionist_id', auth()->id())
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Nutritionist/Clients/Show', [
            'client' => $client,
            'activePlan' => $activePlan,
            'recentMessages' => $recentMessages,
            'anamnesisTemplates' => $anamnesisTemplates,
            'currentTab' => $request->query('tab', 'panoramica'),
            'currentSub' => $request->query('sub', 'galleria'),
            'appointmentLocations' => $request->user()->nutritionistProfile?->locations ?? [],
        ]);
    }

    public function edit(ClientProfile $client)
    {
        $this->authorizeClient($client);

        $client->load('user');

        return Inertia::render('Nutritionist/Clients/Edit', [
            'client' => $client,
            'genders' => collect(Gender::cases())->map(fn ($g) => ['value' => $g->value, 'label' => $g->label()]),
            'activityLevels' => collect(ActivityLevel::cases())->map(fn ($a) => ['value' => $a->value, 'label' => $a->label()]),
            'goals' => collect(ClientGoal::cases())->map(fn ($g) => ['value' => $g->value, 'label' => $g->label()]),
        ]);
    }

    public function update(Request $request, ClientProfile $client)
    {
        $this->authorizeClient($client);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $client->user_id,
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string',
            'height_cm' => 'nullable|numeric|min:50|max:250',
            'initial_weight_kg' => 'nullable|numeric|min:20|max:300',
            'activity_level' => 'nullable|string',
            'goal' => 'nullable|string',
            'allergies' => 'nullable|array',
            'intolerances' => 'nullable|array',
            'pathologies' => 'nullable|string',
            'dietary_preferences' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'nullable|string',
            'fiscal_code' => 'nullable|string|max:16',
            'billing_name' => 'nullable|string|max:255',
            'billing_address' => 'nullable|string|max:255',
            'billing_city' => 'nullable|string|max:100',
            'billing_zip' => 'nullable|string|max:10',
            'billing_province' => 'nullable|string|max:5',
            'vat_number' => 'nullable|string|max:20',
        ]);

        $client->user->update([
            'name' => $validated['name'],
            'last_name' => $validated['last_name'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
        ]);

        $client->update(collect($validated)->except(['name', 'last_name', 'email', 'phone'])->toArray());

        return redirect()->route('nutritionist.clients.show', $client)
            ->with('success', 'Cliente aggiornato.');
    }

    public function addNote(Request $request, ClientProfile $client)
    {
        $this->authorize('update', $client);

        $validated = $request->validate([
            'text' => 'required|string|max:2000',
        ]);

        $notes = $client->nutritionist_notes ?? [];
        $notes[] = ['date' => now()->toDateString(), 'text' => $validated['text']];
        $client->update(['nutritionist_notes' => $notes]);

        return back()->with('success', 'Nota aggiunta.');
    }

    public function deleteNote(Request $request, ClientProfile $client, int $index)
    {
        $this->authorize('update', $client);

        $notes = $client->nutritionist_notes ?? [];
        array_splice($notes, $index, 1);
        $client->update(['nutritionist_notes' => array_values($notes)]);

        return back()->with('success', 'Nota eliminata.');
    }

    private function authorizeClient(ClientProfile $client): void
    {
        $this->authorize('view', $client);
    }
}
