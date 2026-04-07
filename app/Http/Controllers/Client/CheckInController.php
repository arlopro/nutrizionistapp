<?php

namespace App\Http\Controllers\Client;

use App\Enums\MeasurementType;
use App\Enums\PhotoType;
use App\Http\Controllers\Controller;
use App\Models\CheckIn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CheckInController extends Controller
{
    public function index(Request $request)
    {
        $clientProfile = $request->user()->clientProfile;
        abort_unless($clientProfile, 404);

        $checkIns = $clientProfile->checkIns()
            ->with('measurements')
            ->orderByDesc('date')
            ->paginate(20);

        return Inertia::render('Client/CheckIns/Index', [
            'checkIns' => $checkIns,
        ]);
    }

    public function create()
    {
        return Inertia::render('Client/CheckIns/Create', [
            'measurementTypes' => collect(MeasurementType::cases())->map(fn ($m) => ['value' => $m->value, 'label' => $m->label()]),
            'photoTypes' => collect(PhotoType::cases())->map(fn ($p) => ['value' => $p->value, 'label' => $p->label()]),
        ]);
    }

    public function store(Request $request)
    {
        $clientProfile = $request->user()->clientProfile;
        abort_unless($clientProfile, 404);

        $validated = $request->validate([
            'date' => 'required|date',
            'weight_kg' => 'nullable|numeric|min:20|max:300',
            'notes' => 'nullable|string',
            'mood' => 'nullable|integer|min:1|max:5',
            'energy_level' => 'nullable|integer|min:1|max:5',
            'sleep_quality' => 'nullable|integer|min:1|max:5',
            'water_liters' => 'nullable|numeric|min:0|max:10',
            'measurements' => 'nullable|array',
            'measurements.*.type' => 'required|string',
            'measurements.*.value' => 'required|numeric|min:0',
            'photos' => 'nullable|array',
            'photos.*.file' => 'required|image|max:5120',
            'photos.*.type' => 'required|string',
        ]);

        $checkIn = $clientProfile->checkIns()->create([
            'date' => $validated['date'],
            'weight_kg' => $validated['weight_kg'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'mood' => $validated['mood'] ?? null,
            'energy_level' => $validated['energy_level'] ?? null,
            'sleep_quality' => $validated['sleep_quality'] ?? null,
            'water_liters' => $validated['water_liters'] ?? null,
        ]);

        // Save measurements
        if (!empty($validated['measurements'])) {
            foreach ($validated['measurements'] as $measurement) {
                $checkIn->measurements()->create([
                    'measurement_type' => $measurement['type'],
                    'value_cm' => $measurement['value'],
                ]);
            }
        }

        // Save photos
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $index => $photoData) {
                $file = $photoData['file'];
                $type = $request->input("photos.{$index}.type", 'other');
                $path = $file->store("check-ins/{$clientProfile->id}", 'public');
                $checkIn->photos()->create([
                    'photo_type' => $type,
                    'file_path' => $path,
                ]);
            }
        }

        return redirect()->route('client.check-ins.index')
            ->with('success', 'Check-in salvato.');
    }

    public function show(Request $request, CheckIn $checkIn)
    {
        $this->authorize('view', $checkIn);

        $checkIn->load(['measurements', 'photos']);

        return Inertia::render('Client/CheckIns/Show', [
            'checkIn' => $checkIn,
            'measurementTypes' => collect(MeasurementType::cases())->map(fn ($m) => ['value' => $m->value, 'label' => $m->label()]),
        ]);
    }
}
