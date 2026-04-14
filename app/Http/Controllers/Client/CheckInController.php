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
            'body_fat_percentage' => 'nullable|numeric|min:1|max:70',
            'lean_mass_kg' => 'nullable|numeric|min:10|max:200',
            'body_water_percentage' => 'nullable|numeric|min:20|max:80',
            'skinfold_triceps' => 'nullable|numeric|min:1|max:80',
            'skinfold_biceps' => 'nullable|numeric|min:1|max:80',
            'skinfold_subscapular' => 'nullable|numeric|min:1|max:80',
            'skinfold_suprailiac' => 'nullable|numeric|min:1|max:80',
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
            'body_fat_percentage' => $validated['body_fat_percentage'] ?? null,
            'lean_mass_kg' => $validated['lean_mass_kg'] ?? null,
            'body_water_percentage' => $validated['body_water_percentage'] ?? null,
            'skinfold_triceps' => $validated['skinfold_triceps'] ?? null,
            'skinfold_biceps' => $validated['skinfold_biceps'] ?? null,
            'skinfold_subscapular' => $validated['skinfold_subscapular'] ?? null,
            'skinfold_suprailiac' => $validated['skinfold_suprailiac'] ?? null,
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

    public function updatePatientNotes(Request $request, CheckIn $checkIn)
    {
        $this->authorize('update', $checkIn);

        $validated = $request->validate([
            'patient_notes' => 'nullable|string',
        ]);

        $checkIn->update($validated);

        return back()->with('success', 'Note aggiornate.');
    }
}
