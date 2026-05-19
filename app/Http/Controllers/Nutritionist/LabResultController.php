<?php

namespace App\Http\Controllers\Nutritionist;

use App\Http\Controllers\Controller;
use App\Models\LabResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class LabResultController extends Controller
{
    private const MARKER_FIELDS = [
        'glucose', 'hba1c', 'total_cholesterol', 'hdl_cholesterol',
        'ldl_cholesterol', 'triglycerides', 'creatinine', 'tsh',
        'crp', 'zonulin', 'calprotectin',
    ];

    public function index(Request $request)
    {
        $this->authorize('viewAny', LabResult::class);

        $labResults = LabResult::whereHas('client', function ($q) use ($request) {
                $q->where('nutritionist_id', $request->user()->id);
            })
            ->with('client.user')
            ->when($request->client_id, fn ($q, $id) => $q->where('client_id', $id))
            ->orderByDesc('date')
            ->paginate(20)
            ->withQueryString();

        $clients = $request->user()->clients()
            ->with('user:id,name')
            ->get(['client_profiles.id', 'user_id']);

        return Inertia::render('Nutritionist/LabResults/Index', [
            'labResults' => $labResults,
            'filters' => $request->only(['client_id']),
            'clients' => $clients,
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', LabResult::class);

        $clients = $request->user()->clients()
            ->with('user:id,name')
            ->get(['client_profiles.id', 'user_id']);

        return Inertia::render('Nutritionist/LabResults/Create', [
            'clients' => $clients,
            'selectedClientId' => $request->integer('client_id') ?: null,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', LabResult::class);

        $validated = $request->validate($this->rules($request));

        // Verify the client belongs to this nutritionist
        $request->user()->clients()->findOrFail($validated['client_id']);

        if ($request->hasFile('file')) {
            $validated['file_path'] = $request->file('file')->store("lab-results/{$validated['client_id']}", 'public');
        }

        LabResult::create($validated);

        return redirect()->route('nutritionist.lab-results.index', ['client_id' => $validated['client_id']])
            ->with('success', 'Esame salvato.');
    }

    public function show(Request $request, LabResult $labResult)
    {
        $labResult->load('client.user');
        $this->authorize('view', $labResult);

        // History for trend charts
        $history = LabResult::where('client_id', $labResult->client_id)
            ->orderBy('date', 'asc')
            ->get(['id', 'date', ...self::MARKER_FIELDS]);

        return Inertia::render('Nutritionist/LabResults/Show', [
            'labResult' => $labResult,
            'history' => $history,
        ]);
    }

    public function edit(Request $request, LabResult $labResult)
    {
        $labResult->load('client.user');
        $this->authorize('update', $labResult);

        $clients = $request->user()->clients()
            ->with('user:id,name')
            ->get(['client_profiles.id', 'user_id']);

        return Inertia::render('Nutritionist/LabResults/Edit', [
            'labResult' => $labResult,
            'clients' => $clients,
        ]);
    }

    public function update(Request $request, LabResult $labResult)
    {
        $labResult->load('client');
        $this->authorize('update', $labResult);

        $validated = $request->validate($this->rules($request));

        $labResult->update($validated);

        return redirect()->route('nutritionist.lab-results.show', $labResult)
            ->with('success', 'Esame aggiornato.');
    }

    public function destroy(LabResult $labResult)
    {
        $labResult->load('client');
        $this->authorize('delete', $labResult);

        $clientId = $labResult->client_id;
        $labResult->delete();

        return redirect()->route('nutritionist.lab-results.index', ['client_id' => $clientId])
            ->with('success', 'Esame eliminato.');
    }

    private function rules(Request $request): array
    {
        return [
            'client_id' => 'required|integer|exists:client_profiles,id',
            'date' => 'required|date',
            'lab_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
            'file' => 'nullable|file|mimes:pdf|max:10240',
            'glucose' => 'nullable|numeric|min:0|max:9999',
            'hba1c' => 'nullable|numeric|min:0|max:99',
            'total_cholesterol' => 'nullable|numeric|min:0|max:9999',
            'hdl_cholesterol' => 'nullable|numeric|min:0|max:9999',
            'ldl_cholesterol' => 'nullable|numeric|min:0|max:9999',
            'triglycerides' => 'nullable|numeric|min:0|max:9999',
            'creatinine' => 'nullable|numeric|min:0|max:999',
            'tsh' => 'nullable|numeric|min:0|max:999',
            'crp' => 'nullable|numeric|min:0|max:999',
            'zonulin' => 'nullable|numeric|min:0|max:9999',
            'calprotectin' => 'nullable|numeric|min:0|max:99999',
        ];
    }
}
