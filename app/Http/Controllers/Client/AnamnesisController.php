<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\AnamnesisSubmission;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnamnesisController extends Controller
{
    public function index(Request $request)
    {
        $clientProfile = $request->user()->clientProfile;
        abort_unless($clientProfile, 404);

        $submissions = AnamnesisSubmission::where('client_id', $clientProfile->id)
            ->with('template:id,name,description')
            ->orderByDesc('sent_at')
            ->get();

        return Inertia::render('Client/Anamnesis/Index', [
            'submissions' => $submissions,
        ]);
    }

    public function show(Request $request, AnamnesisSubmission $submission)
    {
        $clientProfile = $request->user()->clientProfile;
        abort_unless($clientProfile && $submission->client_id === $clientProfile->id, 403);

        $submission->load('template');

        return Inertia::render('Client/Anamnesis/Fill', [
            'submission' => $submission,
        ]);
    }

    public function submit(Request $request, AnamnesisSubmission $submission)
    {
        $clientProfile = $request->user()->clientProfile;
        abort_unless($clientProfile && $submission->client_id === $clientProfile->id, 403);
        abort_unless($submission->status === 'pending', 422, 'Questionario già compilato.');

        $submission->load('template');
        $questions = $submission->template->questions;

        $rules = [];
        foreach ($questions as $q) {
            $key = "answers.{$q['id']}";
            if ($q['required'] ?? false) {
                $rules[$key] = 'required';
            } else {
                $rules[$key] = 'nullable';
            }
        }
        $rules['answers'] = 'required|array';

        $validated = $request->validate($rules);

        $submission->update([
            'answers' => $validated['answers'],
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return redirect()->route('client.anamnesis.index')
            ->with('success', 'Questionario compilato con successo!');
    }
}
