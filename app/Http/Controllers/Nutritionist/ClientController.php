<?php

namespace App\Http\Controllers\Nutritionist;

use App\Enums\ActivityLevel;
use App\Enums\ClientGoal;
use App\Enums\Gender;
use App\Http\Controllers\Controller;
use App\Models\ClientProfile;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $clients = $request->user()->clients()
            ->with('user')
            ->when($request->search, function ($q, $search) {
                $q->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Nutritionist/Clients/Index', [
            'clients' => $clients,
            'filters' => $request->only(['search', 'status']),
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
            'email' => 'required|email|unique:users,email',
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
            'email' => $validated['email'],
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
            Password::sendResetLink(['email' => $user->email]);
            $user->update(['invitation_sent_at' => now()]);
            $message = 'Cliente creato. Email di accesso inviata al cliente.';
        } else {
            $message = 'Cliente creato. Puoi inviare l\'invito di accesso dalla scheda cliente.';
        }

        return redirect()->route('nutritionist.clients.show', $user->clientProfile)
            ->with('success', $message);
    }

    public function sendInvitation(ClientProfile $client)
    {
        $this->authorizeClient($client);

        $client->load('user');
        Password::sendResetLink(['email' => $client->user->email]);
        $client->user->update(['invitation_sent_at' => now()]);

        return back()->with('success', 'Email di accesso inviata a ' . $client->user->email . '.');
    }

    public function show(ClientProfile $client)
    {
        $this->authorizeClient($client);

        $client->load([
            'user',
            'nutritionalPlans' => fn ($q) => $q->latest(),
            'checkIns' => fn ($q) => $q->orderBy('date', 'asc'),
            'checkIns.measurements',
            'appointments' => fn ($q) => $q->upcoming()->limit(5),
        ]);

        return Inertia::render('Nutritionist/Clients/Show', [
            'client' => $client,
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
            'email' => 'required|email|unique:users,email,' . $client->user_id,
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
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ]);

        $client->update(collect($validated)->except(['name', 'last_name', 'email', 'phone'])->toArray());

        return redirect()->route('nutritionist.clients.show', $client)
            ->with('success', 'Cliente aggiornato.');
    }

    private function authorizeClient(ClientProfile $client): void
    {
        abort_unless($client->nutritionist_id === auth()->id(), 403);
    }
}
