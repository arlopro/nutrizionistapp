<?php

namespace App\Http\Controllers\Nutritionist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $profile = $user->nutritionistProfile;

        return Inertia::render('Nutritionist/Settings', [
            'profile'   => $profile,
            'logoUrl'   => $profile?->logo ? Storage::disk('public')->url($profile->logo) : null,
            'userPhone' => $user->phone,
            'locations' => $profile?->locations ?? [],
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'phone'          => 'nullable|string|max:50',
            'bio'            => 'nullable|string',
            'specialization' => 'nullable|string|max:255',
            'city'           => 'nullable|string|max:100',
            'address'        => 'nullable|string|max:255',
            'website'        => 'nullable|url|max:255',
            'instagram'      => 'nullable|string|max:100',
            'logo'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'avatar'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user = $request->user();
        $profile = $user->nutritionistProfile;

        $userUpdate = ['name' => $validated['name'], 'phone' => $validated['phone'] ?? null];

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $userUpdate['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update(array_filter($userUpdate, fn($v) => $v !== null));

        $profileData = collect($validated)->except(['name', 'phone', 'logo', 'avatar'])->toArray();

        if ($request->hasFile('logo')) {
            if ($profile?->logo) {
                Storage::disk('public')->delete($profile->logo);
            }
            $profileData['logo'] = $request->file('logo')->store('logos', 'public');
        }

        if ($profile) {
            $profile->update($profileData);
        } else {
            $user->nutritionistProfile()->create($profileData);
        }

        return back()->with('success', 'Impostazioni salvate.');
    }

    public function deleteAvatar(Request $request)
    {
        $user = $request->user();
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->update(['avatar' => null]);
        }
        return back()->with('success', 'Foto profilo rimossa.');
    }

    public function updateLocations(Request $request)
    {
        $validated = $request->validate([
            'locations'   => 'nullable|array',
            'locations.*' => 'string|max:255',
        ]);

        $user    = $request->user();
        $profile = $user->nutritionistProfile;

        $locations = array_values(array_filter($validated['locations'] ?? [], fn ($l) => trim($l) !== ''));

        if ($profile) {
            $profile->update(['locations' => $locations]);
        } else {
            $user->nutritionistProfile()->create(['locations' => $locations]);
        }

        return back()->with('success', 'Luoghi aggiornati.');
    }

    public function deleteLogo(Request $request)
    {
        $profile = $request->user()->nutritionistProfile;

        if ($profile?->logo) {
            Storage::disk('public')->delete($profile->logo);
            $profile->update(['logo' => null]);
        }

        return back()->with('success', 'Logo rimosso.');
    }
}
