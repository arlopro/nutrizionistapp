<?php

namespace App\Http\Controllers\Nutritionist;

use App\Http\Controllers\Controller;
use App\Models\AnamnesisTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnamnesisTemplateController extends Controller
{
    public function index(Request $request)
    {
        $templates = AnamnesisTemplate::where('nutritionist_id', $request->user()->id)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'is_default', 'questions', 'created_at']);

        return Inertia::render('Nutritionist/Anamnesis/Index', [
            'templates' => $templates,
        ]);
    }

    public function create()
    {
        return Inertia::render('Nutritionist/Anamnesis/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_default'  => 'boolean',
            'questions'   => 'array',
            'questions.*.id'       => 'required|string',
            'questions.*.type'     => 'required|in:text,textarea,checkbox,radio,number,scale',
            'questions.*.label'    => 'required|string|max:500',
            'questions.*.required' => 'boolean',
            'questions.*.options'  => 'nullable|array',
        ]);

        if ($validated['is_default'] ?? false) {
            AnamnesisTemplate::where('nutritionist_id', $request->user()->id)
                ->update(['is_default' => false]);
        }

        AnamnesisTemplate::create([
            ...$validated,
            'nutritionist_id' => $request->user()->id,
        ]);

        return redirect()->route('nutritionist.anamnesis.index')
            ->with('success', 'Template anamnesi creato.');
    }

    public function edit(AnamnesisTemplate $anamnesi)
    {
        abort_unless($anamnesi->nutritionist_id === auth()->id(), 403);
        return Inertia::render('Nutritionist/Anamnesis/Edit', [
            'template' => $anamnesi,
        ]);
    }

    public function update(Request $request, AnamnesisTemplate $anamnesi)
    {
        abort_unless($anamnesi->nutritionist_id === $request->user()->id, 403);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_default'  => 'boolean',
            'questions'   => 'array',
            'questions.*.id'       => 'required|string',
            'questions.*.type'     => 'required|in:text,textarea,checkbox,radio,number,scale',
            'questions.*.label'    => 'required|string|max:500',
            'questions.*.required' => 'boolean',
            'questions.*.options'  => 'nullable|array',
        ]);

        if ($validated['is_default'] ?? false) {
            AnamnesisTemplate::where('nutritionist_id', $request->user()->id)
                ->where('id', '!=', $anamnesi->id)
                ->update(['is_default' => false]);
        }

        $anamnesi->update($validated);

        return redirect()->route('nutritionist.anamnesis.index')
            ->with('success', 'Template aggiornato.');
    }

    public function destroy(AnamnesisTemplate $anamnesi)
    {
        abort_unless($anamnesi->nutritionist_id === auth()->id(), 403);
        $anamnesi->delete();
        return back()->with('success', 'Template eliminato.');
    }
}
