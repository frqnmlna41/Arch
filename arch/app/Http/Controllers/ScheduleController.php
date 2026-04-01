<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Athlete;
use App\Models\Event;
use App\Models\Contest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ScheduleController
 *
 * php artisan make:controller ScheduleController
 *
 * Digunakan semua role untuk melihat jadwal pertandingan.
 * Filter tersedia: event, discipline, age_category, arena, date.
 *
 * Role behavior:
 *   admin   → semua jadwal
 *   coach   → hanya jadwal atlet yang dia kelola
 *   judge   → semua jadwal (perlu lihat semua untuk persiapan)
 *   athlete → hanya jadwal pertandingannya sendiri
 */
class ScheduleController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware(['auth', 'permission:view schedule']);
    // }

    // ──────────────────────────────────────────────────────────────
    // INDEX – Jadwal dengan filter
    // ──────────────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        try {
            /** @var \App\Models\User $user */
            $user = $request->user();

            $query = Contest::query()
                ->with([
                    'event:id,name,location',
                    'discipline:id,name,type,match_type',
                    'ageCategory:id,name',
                    'arena:id,name,location',
                    'athlete1:id,name,club,gender,photo',
                    'athlete2:id,name,club,gender,photo',
                    'result:id,match_id,winner_id,athlete1_score,athlete2_score,win_method',
                ])
                ->whereNotIn('status', ['cancelled']);

            // ── Role-based filtering ───────────────────────────────

            if ($user->hasRole('coach')) {
                // Coach: hanya match yang melibatkan atlet miliknya
                $athleteIds = $user->athletes()->pluck('id');
                $query->where(function ($q) use ($athleteIds) {
                    $q->whereIn('athlete1_id', $athleteIds)
                      ->orWhereIn('athlete2_id', $athleteIds);
                });
            } elseif ($user->hasRole('athlete')) {
                // Athlete: hanya match dirinya sendiri
                $athleteProfile = $user->athletes()->first();
                if ($athleteProfile) {
                    $query->where(function ($q) use ($athleteProfile) {
                        $q->where('athlete1_id', $athleteProfile->id)
                          ->orWhere('athlete2_id', $athleteProfile->id);
                    });
                } else {
                    // Tidak ada profil atlet → return empty
                    return response()->json([
                        'status'  => 'success',
                        'data'    => [],
                        'message' => 'No athlete profile linked to your account.',
                    ]);
                }
            }
            // admin dan judge: tidak ada filter khusus → lihat semua

            // ── Request filters ────────────────────────────────────

            $query
                ->when($request->event_id,       fn ($q, $v) => $q->where('event_id', $v))
                ->when($request->discipline_id,  fn ($q, $v) => $q->where('discipline_id', $v))
                ->when($request->age_category_id,fn ($q, $v) => $q->where('age_category_id', $v))
                ->when($request->arena_id,       fn ($q, $v) => $q->where('arena_id', $v))
                ->when($request->round,          fn ($q, $v) => $q->where('round', $v))
                ->when($request->status,         fn ($q, $v) => $q->where('status', $v))
                ->when($request->date,           fn ($q, $v) => $q->whereDate('match_date', $v))
                ->when($request->from_date,      fn ($q, $v) => $q->whereDate('match_date', '>=', $v))
                ->when($request->to_date,        fn ($q, $v) => $q->whereDate('match_date', '<=', $v));

            // Sort: tanggal & waktu ascending (default jadwal)
            $query->orderBy('match_date')->orderBy('match_time')->orderBy('arena_id');

            $schedule = $query->paginate($request->integer('per_page', 20));

            // Tambahkan metadata ringkas
            $meta = [
                'total_matches'   => $schedule->total(),
                'today_matches'   => Contest::whereDate('match_date', today())
                    ->whereNotIn('status', ['cancelled'])->count(),
                'ongoing_matches' => Contest::where('status', 'ongoing')->count(),
            ];

            return response()->json([
                'status' => 'success',
                'meta'   => $meta,
                'data'   => $schedule,
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // TODAY – Hanya jadwal hari ini
    // ──────────────────────────────────────────────────────────────

    public function today(Request $request): JsonResponse
    {
        try {
            $request->merge(['date' => today()->toDateString()]);
            return $this->index($request);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // SHOW – Detail satu pertandingan
    // ──────────────────────────────────────────────────────────────

    public function show(Contest $match): JsonResponse
    {
        try {
            $this->authorize('view', $match);

            $match->load([
                'event:id,name,location,start_date,end_date',
                'discipline:id,name,type,match_type',
                'ageCategory:id,name,min_age,max_age',
                'arena:id,name,location,capacity',
                'athlete1',
                'athlete2',
                'scores' => fn ($q) => $q->with('judge:id,name')->orderBy('athlete_id'),
                'result.winner:id,name',
            ]);

            $data = $match->toArray();

            // Summary scores per atlet
            $data['score_summary'] = $match->scores
                ->groupBy('athlete_id')
                ->map(fn ($s) => [
                    'athlete_id'    => $s->first()->athlete_id,
                    'average_score' => round($s->avg('score'), 3),
                    'judge_count'   => $s->pluck('judge_id')->unique()->count(),
                ])
                ->values();

            return response()->json(['status' => 'success', 'data' => $data]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // BY EVENT – Jadwal per event, dikelompokkan per hari + arena
    // ──────────────────────────────────────────────────────────────

    public function byEvent(Request $request, Event $event): JsonResponse
    {
        try {
            $this->authorize('view', $event);

            $matches = Contest::where('event_id', $event->id)
                ->with([
                    'discipline:id,name',
                    'ageCategory:id,name',
                    'arena:id,name',
                    'athlete1:id,name,club',
                    'athlete2:id,name,club',
                ])
                ->whereNotIn('status', ['cancelled'])
                ->orderBy('match_date')
                ->orderBy('arena_id')
                ->orderBy('match_time')
                ->get();

            // Grouping per tanggal → per arena
            $grouped = $matches
                ->groupBy(fn ($m) => $m->match_date?->toDateString() ?? 'unscheduled')
                ->map(fn ($dayMatches, $date) => [
                    'date'   => $date,
                    'arenas' => $dayMatches
                        ->groupBy('arena_id')
                        ->map(fn ($arenaMatches) => [
                            'arena'   => $arenaMatches->first()->arena,
                            'matches' => $arenaMatches->values(),
                        ])
                        ->values(),
                ])
                ->values();

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'event'    => $event->only('id', 'name', 'location', 'start_date', 'end_date'),
                    'schedule' => $grouped,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
