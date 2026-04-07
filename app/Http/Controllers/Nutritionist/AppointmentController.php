<?php

namespace App\Http\Controllers\Nutritionist;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->month
            ? \Carbon\Carbon::parse($request->month . '-01')
            : now()->startOfMonth();

        $appointments = Appointment::where('nutritionist_id', $request->user()->id)
            ->with('client.user')
            ->when($request->client_id, fn ($q, $id) => $q->where('client_id', $id))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->whereBetween('starts_at', [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()])
            ->orderBy('starts_at')
            ->paginate(100)
            ->withQueryString();

        $clients = $request->user()->clients()
            ->with('user:id,name')
            ->get(['client_profiles.id', 'user_id']);

        return Inertia::render('Nutritionist/Appointments/Index', [
            'appointments' => $appointments,
            'filters' => $request->only(['client_id', 'status', 'month']),
            'clients' => $clients,
            'types'            => collect(AppointmentType::cases())->map(fn ($t) => ['value' => $t->value, 'label' => $t->label()]),
            'statuses'         => collect(AppointmentStatus::cases())->filter(fn ($s) => $s !== AppointmentStatus::Scheduled)->values()->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()]),
            'sessionDurations' => $request->user()->nutritionistProfile?->session_durations ?? [],
            'locations'        => $request->user()->nutritionistProfile?->locations ?? [],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'        => 'nullable|exists:client_profiles,id',
            'new_client_name'  => 'nullable|required_without:client_id|string|max:255',
            'new_client_email' => 'nullable|required_without:client_id|email|unique:users,email',
            'new_client_phone' => 'nullable|string|max:20',
            'description'      => 'nullable|string',
            'starts_at'        => 'required|date',
            'ends_at'          => 'required|date|after:starts_at',
            'location'         => 'nullable|string|max:255',
            'type'             => 'required|string',
            'notes'            => 'nullable|string',
        ]);

        $clientName = null;
        $clientId   = $validated['client_id'] ?? null;

        // Inline client creation
        if (!$clientId && !empty($validated['new_client_name']) && !empty($validated['new_client_email'])) {
            $newUser = \App\Models\User::create([
                'name'     => $validated['new_client_name'],
                'email'    => $validated['new_client_email'],
                'phone'    => $validated['new_client_phone'] ?? null,
                'password' => Hash::make(Str::random(32)),
            ]);
            $newUser->assignRole('client');
            $newProfile = \App\Models\ClientProfile::create([
                'user_id'         => $newUser->id,
                'nutritionist_id' => $request->user()->id,
            ]);
            $clientId   = $newProfile->id;
            $clientName = $newUser->name;
        } elseif ($clientId) {
            $client = \App\Models\ClientProfile::with('user')->findOrFail($clientId);
            abort_unless($client->nutritionist_id === $request->user()->id, 403);
            $clientName = $client->user->name;
        }

        $typeLabel = AppointmentType::tryFrom($validated['type'])?->label() ?? $validated['type'];
        $title     = $clientName ? "{$typeLabel} — {$clientName}" : $typeLabel;

        Appointment::create([
            'client_id'       => $clientId,
            'description'     => $validated['description'] ?? null,
            'starts_at'       => $validated['starts_at'],
            'ends_at'         => $validated['ends_at'],
            'location'        => $validated['location'] ?? null,
            'type'            => $validated['type'],
            'status'          => AppointmentStatus::Confirmed->value,
            'notes'           => $validated['notes'] ?? null,
            'title'           => $title,
            'nutritionist_id' => $request->user()->id,
        ]);

        return back()->with('success', 'Appuntamento creato.');
    }

    public function update(Request $request, Appointment $appointment)
    {
        abort_unless($appointment->nutritionist_id === $request->user()->id, 403);

        $validated = $request->validate([
            'client_id'   => 'nullable|exists:client_profiles,id',
            'description' => 'nullable|string',
            'starts_at'   => 'required|date',
            'ends_at'     => 'required|date|after:starts_at',
            'location'    => 'nullable|string|max:255',
            'type'        => 'required|string',
            'status'      => 'required|string',
            'notes'       => 'nullable|string',
        ]);

        if ($validated['client_id']) {
            $client = \App\Models\ClientProfile::with('user')->findOrFail($validated['client_id']);
            abort_unless($client->nutritionist_id === $request->user()->id, 403);
            $clientName = $client->user->name;
        } else {
            $clientName = null;
        }

        $typeLabel = AppointmentType::tryFrom($validated['type'])?->label() ?? $validated['type'];
        $title = $clientName ? "{$typeLabel} — {$clientName}" : $typeLabel;

        $appointment->update([...$validated, 'title' => $title]);

        return back()->with('success', 'Appuntamento aggiornato.');
    }

    public function destroy(Appointment $appointment)
    {
        abort_unless($appointment->nutritionist_id === auth()->id(), 403);
        $appointment->delete();

        return back()->with('success', 'Appuntamento eliminato.');
    }
}
