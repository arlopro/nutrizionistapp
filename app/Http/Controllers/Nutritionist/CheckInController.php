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
        abort_unless($checkIn->client->nutritionist_id === $request->user()->id, 403);

        $weightHistory = CheckIn::where('client_id', $checkIn->client_id)
            ->whereNotNull('weight_kg')
            ->orderBy('date', 'asc')
            ->get(['id', 'date', 'weight_kg']);

        return Inertia::render('Nutritionist/CheckIns/Show', [
            'checkIn' => $checkIn,
            'weightHistory' => $weightHistory,
            'measurementTypes' => collect(MeasurementType::cases())->map(fn ($m) => ['value' => $m->value, 'label' => $m->label()]),
        ]);
    }

    public function addNotes(Request $request, CheckIn $checkIn)
    {
        $checkIn->load('client');
        abort_unless($checkIn->client->nutritionist_id === $request->user()->id, 403);

        $validated = $request->validate([
            'nutritionist_notes' => 'required|string',
        ]);

        $checkIn->update($validated);

        return back()->with('success', 'Note salvate.');
    }
}
