<?php

namespace App\Http\Controllers\Nutritionist;

use App\Enums\MeasurementType;
use App\Http\Controllers\Controller;
use App\Models\CheckIn;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CheckInController extends Controller
{
    public function index(Request $request)
    {
        $checkIns = CheckIn::whereHas('client', function ($q) use ($request) {
                $q->where('nutritionist_id', $request->user()->id);
            })
            ->with(['client.user', 'measurements'])
            ->when($request->client_id, function ($q, $clientId) {
                $q->where('client_id', $clientId);
            })
            ->orderByDesc('date')
            ->paginate(20)
            ->withQueryString();

        $clients = $request->user()->clients()
            ->with('user:id,name')
            ->get(['client_profiles.id', 'user_id']);

        return Inertia::render('Nutritionist/CheckIns/Index', [
            'checkIns' => $checkIns,
            'filters' => $request->only(['client_id']),
            'clients' => $clients,
        ]);
    }

    public function show(Request $request, CheckIn $checkIn)
    {
        $checkIn->load(['client.user', 'measurements', 'photos']);
        $this->authorize('view', $checkIn);

        $weightHistory = CheckIn::where('client_id', $checkIn->client_id)
            ->whereNotNull('weight_kg')
            ->orderBy('date', 'asc')
            ->get(['id', 'date', 'weight_kg']);

        $bodyCompHistory = CheckIn::where('client_id', $checkIn->client_id)
            ->where(function ($q) {
                $q->whereNotNull('body_fat_percentage')
                  ->orWhereNotNull('lean_mass_kg')
                  ->orWhereNotNull('body_water_percentage');
            })
            ->orderBy('date', 'asc')
            ->get(['id', 'date', 'body_fat_percentage', 'lean_mass_kg', 'body_water_percentage']);

        return Inertia::render('Nutritionist/CheckIns/Show', [
            'checkIn' => $checkIn,
            'weightHistory' => $weightHistory,
            'bodyCompHistory' => $bodyCompHistory,
            'measurementTypes' => collect(MeasurementType::cases())->map(fn ($m) => ['value' => $m->value, 'label' => $m->label()]),
        ]);
    }

    public function photoCompare(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:client_profiles,id',
        ]);

        $client = \App\Models\ClientProfile::where('id', $validated['client_id'])
            ->where('nutritionist_id', $request->user()->id)
            ->with('user:id,name')
            ->firstOrFail();

        $checkInsWithPhotos = CheckIn::where('client_id', $client->id)
            ->whereHas('photos')
            ->with('photos')
            ->orderByDesc('date')
            ->get(['id', 'date', 'weight_kg']);

        return Inertia::render('Nutritionist/CheckIns/PhotoCompare', [
            'client' => $client,
            'checkIns' => $checkInsWithPhotos,
        ]);
    }

    public function addNotes(Request $request, CheckIn $checkIn)
    {
        $checkIn->load('client');
        $this->authorize('update', $checkIn);

        $validated = $request->validate([
            'nutritionist_notes' => 'required|string',
        ]);

        $checkIn->update($validated);

        return back()->with('success', 'Note salvate.');
    }
}
