<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Mail\NewMessage;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $client = $request->user()->clientProfile;

        if (! $client) {
            abort(404);
        }

        $messages = Message::where('client_id', $client->id)
            ->with('sender:id,name,last_name,avatar')
            ->orderBy('created_at')
            ->get();

        // Mark unread messages from nutritionist as read
        Message::where('client_id', $client->id)
            ->where('sender_id', '!=', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $nutritionist = $client->nutritionist;

        return Inertia::render('Client/Messages/Index', [
            'messages'      => $messages,
            'nutritionist'  => $nutritionist ? [
                'id'   => $nutritionist->id,
                'name' => $nutritionist->full_name,
            ] : null,
        ]);
    }

    public function store(Request $request)
    {
        $client = $request->user()->clientProfile;

        if (! $client) {
            abort(404);
        }

        $validated = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $message = Message::create([
            'client_id' => $client->id,
            'sender_id' => $request->user()->id,
            'body'      => $validated['body'],
        ]);

        $message->load('sender');

        // Notify nutritionist via email
        $nutritionistEmail = $client->nutritionist?->email;
        if ($nutritionistEmail) {
            Mail::to($nutritionistEmail)->queue(new NewMessage($message));
        }

        return back();
    }
}
