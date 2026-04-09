<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgeCategory;
use App\Models\Arena;
use App\Models\Athlete;
use App\Models\Certificate;
use App\Models\Discipline;
use App\Models\Event;
use App\Models\Score;
use App\Models\Sport;
use App\Models\User;
use App\Models\Winner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Admin dashboard with comprehensive stats for all features.
     */
    public function index(Request $request): View
    {
        // Counts for all major entities
        $stats = [
            'totalPerguruan' => User::role('perguruan')->active()->count(),
            'pendingPerguruan' => User::role('perguruan')->pending()->count(),
            'pendingCoaches' => User::role('coach')->pending()->count(),
            'totalAthletes' => Athlete::active()->count(),
            'totalEvents' => Event::published()->count(),
            'totalSports' => Sport::active()->count(),
            'totalDisciplines' => Discipline::count(),
            'totalArenas' => Arena::count(), // Add active scope if exists
            'totalCertificates' => Certificate::count(),
            'totalWinners' => Winner::count(),
            'totalScores' => Score::count(),
            // Add more: e.g., totalMatches => Match::count(), if model exists
        ];

        // Actionable lists
        $pendingPerguruans = User::role('perguruan')
            ->pending()
            ->latest()
            ->limit(5)
            ->get(['id', 'name', 'email']);

        $recentEvents = Event::published()
            ->latest()
            ->limit(5)
            ->get(['id', 'name', 'start_date', 'end_date']);

        $recentUsers = User::active()
            ->with('roles')
            ->latest()
            ->limit(10)
            ->get(['id', 'name', 'email']);

        // Chart data example: Events per month (last 12 months) - Postgres compatible
        $chartData = [];
        if (method_exists(Event::class, 'published')) {
            $chartData = Event::published()
                ->selectRaw("to_char(start_date, 'YYYY-MM') as period, COUNT(*) as count")
                ->whereRaw("start_date >= (CURRENT_DATE - INTERVAL '12 months')")
                ->groupBy('period')
                ->orderBy('period')
                ->pluck('count', 'period')
                ->toArray();
        }

        return view('dashboard.admin', compact(
            'stats',
            'pendingPerguruans',
            'recentEvents',
            'recentUsers',
            'chartData'
        ));
    }
}

