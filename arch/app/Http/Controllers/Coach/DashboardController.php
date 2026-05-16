<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\Athlete;
use App\Models\EventParticipant;
use App\Models\Score;
use App\Models\Winner;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Perguruan dashboard - shows athletes, events, stats
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        // $coach = $request->user('coach');

        // Stats
        $stats = [
            'totalAthletes' => Athlete::where('perguruan_id', $user->perguruan_id)->active()->count(),
            'activeEvents' => EventParticipant::whereHas('athlete', fn($q) => $q->where('perguruan_id', $user->perguruan_id))->count(),
            'totalWins' => Winner::whereHas('athlete', fn($q) => $q->where('perguruan_id', $user->perguruan_id))->count(),
            // 'totalScores' => Score::whereHas('athlete', fn($q) => $q->where('perguruan_id', $user->perguruan_id))->count(),
        ];

        // Recent athletes
        $athletes = Athlete::where('perguruan_id', $user->perguruan_id)
            ->active()
            ->latest()
            ->limit(10)
            ->get(['id', 'name', 'gender', 'weight', 'birth_date', 'is_active']);

        // Recent event participants
        $participants = EventParticipant::with(['event', 'athlete'])
            ->whereHas('athlete', fn($q) => $q->where('perguruan_id', $user->perguruan_id))
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard.coach', compact('stats', 'athletes', 'participants'));
    }
}
