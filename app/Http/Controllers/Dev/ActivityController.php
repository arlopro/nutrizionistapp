<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with(['causer', 'subject'])
            ->orderByDesc('created_at');

        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id)
                  ->where('causer_type', User::class);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', 'like', '%' . $request->subject_type . '%');
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $activities = $query->paginate(50)->withQueryString();

        $nutritionists = User::role('nutritionist')
            ->orderBy('name')
            ->get(['id', 'name', 'last_name', 'email']);

        return Inertia::render('Dev/Activity', [
            'activities'    => $activities,
            'nutritionists' => $nutritionists,
            'filters'       => $request->only(['causer_id', 'event', 'subject_type', 'from_date', 'to_date']),
        ]);
    }
}
