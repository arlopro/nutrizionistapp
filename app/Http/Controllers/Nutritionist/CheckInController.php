<?php

namespace App\Http\Controllers\Nutritionist;

use App\Enums\MeasurementType;
use App\Enums\PhotoType;
use App\Http\Controllers\Controller;
use App\Models\CheckIn;
use App\Models\ClientProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CheckInController extends Controller
{
    public function index(Request $request)
    {
        $nutritionistId = $request->user()->id;

        $base = CheckIn::whereHas('client', fn ($q) => $q->where('nutritionist_id', $nutritionistId));

        $stats = [
            'to_review'            => (clone $base)->whereNull('reviewed_at')->count(),
            'reviewed_this_week'   => (clone $base)->whereNotNull('reviewed_at')
                ->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            'total_this_month'     => (clone $base)
                ->whereMonth('date', now()->month)->whereYear('date', now()->year)->count(),
            'low_mood'             => (clone $base)->whereNull('reviewed_at')
                ->whereNotNull('mood')->where('mood', '<=', 2)->count(),
        ];

        $checkIns = (clone $base)
            ->with(['client.user', 'measurements'])
            ->when($request->client_id, fn ($q, $id) => $q->where('client_id', $id))
            ->when($request->status === 'to_review', fn ($q) => $q->whereNull('reviewed_at'))
            ->when($request->status === 'reviewed', fn ($q) => $q->whereNotNull('reviewed_at'))
            ->when($request->search, fn ($q, $s) => $q->whereHas('client.user', fn ($q2) => $q2->where('name', 'like', "%{$s}%")))
            ->orderByDesc('date')
            ->paginate(20)
            ->withQueryString();

        $clients = $request->user()->clients()
            ->with('user:id,name')
            ->get(['client_profiles.id', 'user_id']);

        return Inertia::render('Nutritionist/CheckIns/Index', [
            'checkIns' => $checkIns,
            'filters'  => $request->only(['client_id', 'status', 'search']),
            'clients'  => $clients,
            'stats'    => $stats,
        ]);
    }

    public function reviewAll(Request $request)
    {
        CheckIn::whereHas('client', fn ($q) => $q->where('nutritionist_id', $request->user()->id))
            ->whereNull('reviewed_at')
            ->update(['reviewed_at' => now()]);

        return back()->with('success', 'Tutti i monitoraggi segnati come revisionati.');
    }

    public function show(Request $request, CheckIn $checkIn)
    {
        $checkIn->load(['client.user', 'measurements', 'photos']);
        $this->authorize('view', $checkIn);

        $allCheckInIds = CheckIn::where('client_id', $checkIn->client_id)
            ->orderByDesc('date')
            ->pluck('id')
            ->values();

        $currentPosition = $allCheckInIds->search($checkIn->id);
        $prevId = $currentPosition < $allCheckInIds->count() - 1 ? $allCheckInIds[$currentPosition + 1] : null;
        $nextId = $currentPosition > 0 ? $allCheckInIds[$currentPosition - 1] : null;

        $weightHistory = CheckIn::where('client_id', $checkIn->client_id)
            ->whereNotNull('weight_kg')
            ->orderBy('date', 'asc')
            ->get(['id', 'date', 'weight_kg']);

        $recentCheckIns = CheckIn::where('client_id', $checkIn->client_id)
            ->orderByDesc('date')
            ->limit(6)
            ->get(['id', 'date', 'weight_kg']);

        $prevWeightCheckIn = null;
        $idx = $weightHistory->search(fn ($w) => $w->id === $checkIn->id);
        if ($idx !== false && $idx > 0) {
            $prevWeightCheckIn = $weightHistory[$idx - 1];
        }

        $bodyCompHistory = CheckIn::where('client_id', $checkIn->client_id)
            ->where(function ($q) {
                $q->whereNotNull('body_fat_percentage')
                  ->orWhereNotNull('lean_mass_kg')
                  ->orWhereNotNull('body_water_percentage');
            })
            ->orderBy('date', 'asc')
            ->get(['id', 'date', 'body_fat_percentage', 'lean_mass_kg', 'body_water_percentage']);

        return Inertia::render('Nutritionist/CheckIns/Show', [
            'checkIn'          => $checkIn,
            'prevId'           => $prevId,
            'nextId'           => $nextId,
            'weightHistory'    => $weightHistory,
            'recentCheckIns'   => $recentCheckIns,
            'prevWeightCheckIn' => $prevWeightCheckIn,
            'bodyCompHistory'  => $bodyCompHistory,
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

    public function storeForClient(Request $request, ClientProfile $client)
    {
        $this->authorize('update', $client);

        $validated = $request->validate([
            'date' => 'required|date',
            'weight_kg' => 'nullable|numeric|min:20|max:300',
            'body_fat_percentage' => 'nullable|numeric|min:1|max:70',
            'lean_mass_kg' => 'nullable|numeric|min:10|max:200',
            'body_water_percentage' => 'nullable|numeric|min:20|max:80',
            'notes' => 'nullable|string',
            'mood' => 'nullable|integer|min:1|max:5',
            'measurements' => 'nullable|array',
            'measurements.*.type' => 'required|string',
            'measurements.*.value' => 'required|numeric|min:0',
            'photo_files' => 'nullable|array',
            'photo_files.*' => 'image|max:5120',
            'photo_types' => 'nullable|array',
            'photo_types.*' => 'nullable|string',
        ]);

        $checkIn = $client->checkIns()->create([
            'date' => $validated['date'],
            'weight_kg' => $validated['weight_kg'] ?? null,
            'body_fat_percentage' => $validated['body_fat_percentage'] ?? null,
            'lean_mass_kg' => $validated['lean_mass_kg'] ?? null,
            'body_water_percentage' => $validated['body_water_percentage'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'mood' => $validated['mood'] ?? null,
        ]);

        if (!empty($validated['measurements'])) {
            foreach ($validated['measurements'] as $m) {
                $checkIn->measurements()->create([
                    'measurement_type' => $m['type'],
                    'value_cm' => $m['value'],
                ]);
            }
        }

        if ($request->hasFile('photo_files')) {
            $types = $request->input('photo_types', []);
            foreach ($request->file('photo_files') as $i => $file) {
                $type = $types[$i] ?? 'other';
                $path = $file->store("check-ins/{$client->id}", 'public');
                $checkIn->photos()->create([
                    'photo_type' => $type,
                    'file_path'  => $path,
                ]);
            }
        }

        return back()->with('success', 'Misurazione salvata.');
    }

    public function addNotes(Request $request, CheckIn $checkIn)
    {
        $checkIn->load('client');
        $this->authorize('update', $checkIn);

        $validated = $request->validate([
            'nutritionist_notes' => 'nullable|string',
        ]);

        $checkIn->update($validated);

        return back()->with('success', 'Note salvate.');
    }

    public function review(Request $request, CheckIn $checkIn)
    {
        $checkIn->load('client');
        $this->authorize('update', $checkIn);

        $validated = $request->validate([
            'nutritionist_notes' => 'nullable|string',
        ]);

        $checkIn->update(array_merge($validated, ['reviewed_at' => now()]));

        return back()->with('success', 'Monitoraggio segnato come revisionato.');
    }
}
