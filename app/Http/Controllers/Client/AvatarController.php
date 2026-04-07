<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AvatarController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        return Inertia::render('Client/Settings', [
            'avatarUrl' => $user->avatar ? Storage::disk('public')->url($user->avatar) : null,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user = $request->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->update([
            'avatar' => $request->file('avatar')->store('avatars', 'public'),
        ]);

        return back()->with('success', 'Foto profilo aggiornata.');
    }

    public function destroy(Request $request)
    {
        $user = $request->user();
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->update(['avatar' => null]);
        }
        return back()->with('success', 'Foto profilo rimossa.');
    }
}
