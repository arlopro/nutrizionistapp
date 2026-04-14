<?php

namespace App\Http\Controllers\Nutritionist;

use App\Http\Controllers\Controller;
use App\Mail\NewMessage;
use App\Models\ClientProfile;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $nutritionistId = $request->user()->id;

        $threads = ClientProfile::where('nutritionist_id', $nutritionistId)
            ->with('user')
            ->withCount(['messages as unread_count' => function ($q) use ($nutritionistId) {
                $q->where('sender_id', '!=', $nutritionistId)->whereNull('read_at');
            }])
            ->withMax('messages', 'created_at')
            ->having('messages_max_created_at', '!=', null)
            ->orderByDesc('messages_max_created_at')
            ->get()
            ->map(fn ($c) => [
                'id'           => $c->id,
                'name'         => $c->user->full_name,
                'avatar'       => $c->user->avatarUrl ?? null,
                'unread_count' => $c->unread_count,
                'last_message' => $c->messages()->latest()->first()?->only('body', 'created_at', 'sender_id'),
            ]);

        // Also include clients with no messages for starting conversations
        $allClients = ClientProfile::where('nutritionist_id', $nutritionistId)
            ->with('user')
            ->get()
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->user->full_name]);

        return Inertia::render('Nutritionist/Messages/Index', [
            'threads' => $threads,
            'clients' => $allClients,
        ]);
    }

    public function show(Request $request, ClientProfile $client)
    {
        $this->authorize('view', $client);

        $messages = Message::where('client_id', $client->id)
            ->with('sender:id,name,last_name,avatar')
            ->orderBy('created_at')
            ->get();

        // Mark unread messages from client as read
        Message::where('client_id', $client->id)
            ->where('sender_id', '!=', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return Inertia::render('Nutritionist/Messages/Show', [
            'client'   => $client->load('user'),
            'messages' => $messages,
        ]);
    }

    public function store(Request $request, ClientProfile $client)
    {
        $this->authorize('view', $client);

        $validated = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $message = Message::create([
            'client_id' => $client->id,
            'sender_id' => $request->user()->id,
            'body'      => $validated['body'],
        ]);

        $message->load('sender');

        // Notify client via email
        $clientEmail = $client->user?->email;
        if ($clientEmail) {
            Mail::to($clientEmail)->queue(new NewMessage($message));
        }

        return back();
    }
}
