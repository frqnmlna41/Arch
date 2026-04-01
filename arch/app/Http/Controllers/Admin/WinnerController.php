<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Athlete;
use App\Models\Discipline;
use App\Models\Event;
use App\Models\Contest;
use App\Models\ContestResult;
use App\Models\Winner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * WinnerController
 *
 * php artisan make:controller Admin/WinnerController
 *
 * Akses:
 *   admin  → calculate & store winner
 *   semua  → view winners (permission: view results)
 */
class WinnerController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    //     $this->middleware('role:admin')->only(['calculate', 'store', 'destroy']);
    //     $this->middleware('permission:view results')->only(['index', 'show']);
    // }

    // ──────────────────────────────────────────────────────────────
    // INDEX – Daftar pemenang
    // ──────────────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        try {
            $winners = Winner::query()
                ->with([
                    'event:id,name',
                    'discipline:id,name',
                    'ageCategory:id,name',
                    'athlete:id,name,club,photo',
                    'certificate:id,certificate_number,issued_at',
                ])
                ->when($request->event_id,       fn ($q, $v) => $q->where('event_id', $v))
                ->when($request->discipline_id,  fn ($q, $v) => $q->where('discipline_id', $v))
                ->when($request->age_category_id,fn ($q, $v) => $q->where('age_category_id', $v))
                ->when($request->rank,           fn ($q, $v) => $q->where('rank', $v))
                ->orderBy('rank')
                ->paginate($request->integer('per_page', 20));

            return response()->json(['status' => 'success', 'data' => $winners]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // SHOW
    // ──────────────────────────────────────────────────────────────

    public function show(Winner $winner): JsonResponse
    {
        try {
            $winner->load([
                'event:id,name,location',
                'discipline:id,name,type',
                'ageCategory:id,name',
                'athlete:id,name,club,photo,gender',
                'certificate',
            ]);

            return response()->json(['status' => 'success', 'data' => $winner]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // CALCULATE – Hitung pemenang otomatis berdasarkan score
    // ──────────────────────────────────────────────────────────────

    /**
     * Menghitung pemenang untuk kombinasi event + discipline + age_category.
     *
     * Logika:
     * - Performance (Taolu/Forms):
     *   Ranking berdasarkan rata-rata total score dari semua judge.
     *   Juara 1 = score tertinggi, dst.
     *
     * - Sparring (Sanda):
     *   Berdasarkan win/loss dari match_results.
     *   Final match menentukan Juara 1 & 2.
     *   Bronze dari semi-final loser.
     */
    public function calculate(Request $request, Event $event): JsonResponse
    {
        try {
            $request->validate([
                'discipline_id'   => ['required', 'exists:disciplines,id'],
                'age_category_id' => ['required', 'exists:age_categories,id'],
            ]);

            $discipline  = Discipline::findOrFail($request->discipline_id);
            $ageCategoryId = $request->age_category_id;

            // Pastikan semua match sudah completed
            $pendingMatches = Contest::where([
                'event_id'        => $event->id,
                'discipline_id'   => $discipline->id,
                'age_category_id' => $ageCategoryId,
            ])->whereNotIn('status', ['completed', 'walkover', 'cancelled'])->count();

            if ($pendingMatches > 0) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "{$pendingMatches} match(es) are not yet completed. All matches must be completed first.",
                ], 422);
            }

            $winners = [];

            DB::transaction(function () use ($event, $discipline, $ageCategoryId, &$winners) {
                // Hapus winner lama untuk kombinasi ini
                Winner::where([
                    'event_id'        => $event->id,
                    'discipline_id'   => $discipline->id,
                    'age_category_id' => $ageCategoryId,
                ])->delete();

                if ($discipline->isPerformance()) {
                    $winners = $this->calculatePerformanceWinners($event, $discipline, $ageCategoryId);
                } else {
                    $winners = $this->calculateSparringWinners($event, $discipline, $ageCategoryId);
                }
            });

            return response()->json([
                'status'  => 'success',
                'data'    => $winners,
                'message' => 'Winners calculated and saved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // DESTROY – Hapus winner (untuk recalculate)
    // ──────────────────────────────────────────────────────────────

    public function destroy(Winner $winner): JsonResponse
    {
        try {
            if ($winner->hasCertificate()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Cannot delete winner: certificate has been issued.',
                ], 422);
            }

            $winner->delete();

            return response()->json(['status' => 'success', 'message' => 'Winner record deleted.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // PRIVATE: Calculate Performance Winners (Taolu/Forms)
    // ──────────────────────────────────────────────────────────────

    private function calculatePerformanceWinners(Event $event, Discipline $discipline, int $ageCategoryId): array
    {
        // Ambil semua match untuk kategori ini
        $matches = Contest::where([
            'event_id'        => $event->id,
            'discipline_id'   => $discipline->id,
            'age_category_id' => $ageCategoryId,
        ])->with('scores')->get();

        // Hitung rata-rata score per atlet dari semua match mereka
        $athleteScores = [];
        foreach ($matches as $match) {
            $athleteId = $match->athlete1_id;
            $avgScore  = $match->scores
                ->where('athlete_id', $athleteId)
                ->avg('score') ?? 0;

            if (! isset($athleteScores[$athleteId]) || $avgScore > $athleteScores[$athleteId]) {
                $athleteScores[$athleteId] = round($avgScore, 3);
            }
        }

        // Sort descending (tertinggi = juara 1)
        arsort($athleteScores);

        $winners    = [];
        $rank       = 1;
        $medalMap   = [1 => 'gold', 2 => 'silver', 3 => 'bronze'];

        foreach ($athleteScores as $athleteId => $totalScore) {
            if ($rank > 3) break; // hanya 3 teratas

            $winner = Winner::create([
                'event_id'        => $event->id,
                'discipline_id'   => $discipline->id,
                'age_category_id' => $ageCategoryId,
                'athlete_id'      => $athleteId,
                'rank'            => $rank,
                'total_score'     => $totalScore,
                'medal_type'      => $medalMap[$rank] ?? 'bronze',
            ]);

            $winners[] = $winner->load('athlete:id,name');
            $rank++;
        }

        return $winners;
    }

    // ──────────────────────────────────────────────────────────────
    // PRIVATE: Calculate Sparring Winners (Sanda/Combat)
    // ──────────────────────────────────────────────────────────────

    private function calculateSparringWinners(Event $event, Discipline $discipline, int $ageCategoryId): array
    {
        $winners = [];

        // Juara 1 & 2 dari final match
        $finalMatch = Contest::where([
            'event_id'        => $event->id,
            'discipline_id'   => $discipline->id,
            'age_category_id' => $ageCategoryId,
            'round'           => 'final',
        ])->with('result')->first();

        if ($finalMatch && $finalMatch->result) {
            $result = $finalMatch->result;

            // Juara 1: pemenang final
            if ($result->winner_id) {
                $winners[] = Winner::create([
                    'event_id'        => $event->id,
                    'discipline_id'   => $discipline->id,
                    'age_category_id' => $ageCategoryId,
                    'athlete_id'      => $result->winner_id,
                    'rank'            => 1,
                    'medal_type'      => 'gold',
                    'total_score'     => $result->athlete1_id === $result->winner_id
                                           ? $result->athlete1_score
                                           : $result->athlete2_score,
                ]);

                // Juara 2: loser final
                $loserId = ($result->winner_id === $finalMatch->athlete1_id)
                    ? $finalMatch->athlete2_id
                    : $finalMatch->athlete1_id;

                if ($loserId) {
                    $winners[] = Winner::create([
                        'event_id'        => $event->id,
                        'discipline_id'   => $discipline->id,
                        'age_category_id' => $ageCategoryId,
                        'athlete_id'      => $loserId,
                        'rank'            => 2,
                        'medal_type'      => 'silver',
                    ]);
                }
            }
        }

        // Juara 3 (Bronze): loser dari semi-final (bisa dua atlet)
        $semiMatches = Contest::where([
            'event_id'        => $event->id,
            'discipline_id'   => $discipline->id,
            'age_category_id' => $ageCategoryId,
            'round'           => 'semi_final',
        ])->with('result')->get();

        foreach ($semiMatches as $semi) {
            if (! $semi->result?->winner_id) continue;

            // Loser semi-final = bronze
            $loserId = ($semi->result->winner_id === $semi->athlete1_id)
                ? $semi->athlete2_id
                : $semi->athlete1_id;

            if ($loserId) {
                // Hindari duplikat (jika sudah ada di rank 1/2)
                $alreadyWinner = collect($winners)->pluck('athlete_id')->contains($loserId);
                if (! $alreadyWinner) {
                    $winners[] = Winner::create([
                        'event_id'        => $event->id,
                        'discipline_id'   => $discipline->id,
                        'age_category_id' => $ageCategoryId,
                        'athlete_id'      => $loserId,
                        'rank'            => 3,
                        'medal_type'      => 'bronze',
                    ]);
                }
            }
        }

        return collect($winners)->map(fn ($w) => $w->load('athlete:id,name'))->all();
    }
}
