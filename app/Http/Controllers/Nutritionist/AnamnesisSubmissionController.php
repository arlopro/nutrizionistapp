<?php

namespace App\Http\Controllers\Nutritionist;

use App\Http\Controllers\Controller;
use App\Models\AnamnesisSubmission;
use App\Models\AnamnesisTemplate;
use App\Models\ClientProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnamnesisSubmissionController extends Controller
{
    public function send(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:anamnesis_templates,id',
            'client_id' => 'required|exists:client_profiles,id',
        ]);

        $template = AnamnesisTemplate::where('id', $validated['template_id'])
            ->where('nutritionist_id', $request->user()->id)
            ->firstOrFail();

        $client = ClientProfile::where('id', $validated['client_id'])
            ->where('nutritionist_id', $request->user()->id)
            ->firstOrFail();

        $submission = AnamnesisSubmission::create([
            'anamnesis_template_id' => $template->id,
            'client_id' => $client->id,
            'sent_by' => $request->user()->id,
            'status' => 'pending',
            'sent_at' => now(),
        ]);

        return back()->with('success', "Questionario \"{$template->name}\" inviato al paziente.");
    }

    public function show(Request $request, AnamnesisSubmission $submission)
    {
        $submission->load(['template', 'client.user']);

        abort_unless($submission->template->nutritionist_id === $request->user()->id, 403);

        return Inertia::render('Nutritionist/Anamnesis/Submission', [
            'submission' => $submission,
        ]);
    }
}
