<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Score\StoreScoreRequest;
use App\Http\Requests\Score\UpdateScoreRequest;
use App\Models\Contest;
use App\Models\Score;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * ScoreController
 *
 * php artisan make:controller ScoreController
 *
 * Akses:
 *   judge → input score, update score miliknya sendiri
 *   admin → view semua, manage semua
 *   coach/athlete → view score (read-only)
 *
 * match_type = performance:
 *   Beberapa judge memberikan nilai, sistem menghitung rata-rata.
 *   Nilai per judge disimpan terpisah per score_type.
 */
class ScoreController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:input score')->only(['store']);
        $this->middleware('permission:update score')->only(['update']);
        $this->middleware('permission:view scores')->only(['index', 'show', 'summary']);
    }

    // ──────────────────────────────────────────────────────────────
    // INDEX – View semua score untuk satu match
    // ──────────────────────────────────────────────────────────────

    public function index(Request $request, Contest $match): JsonResponse
    {
        try {
            $this->authorize('view', $match);

            $scores = $match->scores()
                ->with([
                    'judge:id,name',
                    'athlete:id,name',
                ])
                ->when($request->athlete_id, fn ($q, $v) => $q->where('athlete_id', $v))
                ->when($request->judge_id,   fn ($q, $v) => $q->where('judge_id', $v))
                ->when($request->score_type, fn ($q, $v) => $q->where('score_type', $v))
                ->orderBy('athlete_id')
                ->orderBy('judge_id')
                ->get();

            // Grouping: athlete → judge → scores
            $grouped = $scores->groupBy('athlete_id')->map(function ($athleteScores) {
                $athlete = $athleteScores->first()->athlete;
                return [
                    'athlete'       => $athlete,
                    'scores_detail' => $athleteScores->groupBy('judge_id')->map(function ($judgeScores) {
                        $judge = $judgeScores->first()->judge;
                        return [
                            'judge'   => $judge,
                            'entries' => $judgeScores->map(fn ($s) => [
                                'id'           => $s->id,
                                'score'        => $s->score,
                                'score_type'   => $s->score_type,
                                'round_number' => $s->round_number,
                                'notes'        => $s->notes,
                            ]),
                        ];
                    })->values(),
                    'total_average' => round($athleteScores->avg('score'), 3),
                ];
            })->values();

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'match'   => $match->only('id', 'round', 'status', 'match_date'),
                    'scores'  => $grouped,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // STORE – Judge input nilai
    // ──────────────────────────────────────────────────────────────

    /**
     * Judge menginput nilai untuk satu pertandingan.
     *
     * Request body:
     * {
     *   "scores": [
     *     { "athlete_id": 1, "score": 9.5, "score_type": "technique" },
     *     { "athlete_id": 1, "score": 9.2, "score_type": "difficulty" },
     *     { "athlete_id": 2, "score": 9.0, "score_type": "technique" }
     *   ]
     * }
     */
    public function store(StoreScoreRequest $request, Contest $match): JsonResponse
    {
        try {
            $this->authorize('inputScore', $match);
            $this->authorize('create', Score::class);

            if ($match->status !== 'ongoing') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Scores can only be input for ongoing matches.',
                ], 422);
            }

            /** @var \App\Models\User $judge */
            $judge = $request->user();

            // Validasi: atlet harus peserta match ini
            $validAthleteIds = array_filter([
                $match->athlete1_id,
                $match->athlete2_id,
            ]);

            $saved = DB::transaction(function () use ($request, $match, $judge, $validAthleteIds) {
                $results = [];

                foreach ($request->validated('scores') as $entry) {
                    if (! in_array($entry['athlete_id'], $validAthleteIds)) {
                        continue; // skip atlet yang tidak terlibat
                    }

                    $score = Score::updateOrCreate(
                        [
                            'match_id'     => $match->id,
                            'judge_id'     => $judge->id,
                            'athlete_id'   => $entry['athlete_id'],
                            'score_type'   => $entry['score_type'] ?? 'total',
                            'round_number' => $entry['round_number'] ?? null,
                        ],
                        [
                            'score' => $entry['score'],
                            'notes' => $entry['notes'] ?? null,
                        ]
                    );
                    $results[] = $score;
                }

                return $results;
            });

            return response()->json([
                'status'  => 'success',
                'data'    => $saved,
                'message' => count($saved) . ' score(s) saved successfully.',
            ], 201);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // UPDATE – Judge update nilai miliknya
    // ──────────────────────────────────────────────────────────────

    public function update(UpdateScoreRequest $request, Contest $match, Score $score): JsonResponse
    {
        try {
            // ScorePolicy::update() — judge hanya bisa update nilai miliknya
            $this->authorize('update', $score);

            abort_if($score->match_id !== $match->id, 404, 'Score not found in this match.');

            $score->update($request->validated());

            return response()->json([
                'status'  => 'success',
                'data'    => $score->fresh(['judge:id,name', 'athlete:id,name']),
                'message' => 'Score updated successfully.',
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // SUMMARY – Kalkulasi rata-rata per atlet (untuk WinnerController)
    // ──────────────────────────────────────────────────────────────

    public function summary(Contest $match): JsonResponse
    {
        try {
            $scores = $match->scores()->with('athlete:id,name')->get();

            $summary = $scores->groupBy('athlete_id')->map(function ($athleteScores) {
                $athlete = $athleteScores->first()->athlete;

                return [
                    'athlete_id'     => $athlete->id,
                    'athlete_name'   => $athlete->name,
                    'score_count'    => $athleteScores->count(),
                    'judge_count'    => $athleteScores->pluck('judge_id')->unique()->count(),
                    'average_score'  => round($athleteScores->avg('score'), 3),
                    'highest_score'  => $athleteScores->max('score'),
                    'lowest_score'   => $athleteScores->min('score'),
                    'by_type'        => $athleteScores->groupBy('score_type')->map(fn ($g) => round($g->avg('score'), 3)),
                ];
            })->values();

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'match_id' => $match->id,
                    'round'    => $match->round,
                    'summary'  => $summary,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
