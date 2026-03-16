<?php

namespace App\Http\Controllers\Judge;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreScoreRequest;
use App\Http\Requests\UpdateScoreRequest;
use App\Models\Match;
use App\Models\Score;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ScoringController (Judge)
 *
 * Controller untuk judge menginput dan memperbarui nilai pertandingan.
 *
 * Akses:
 * - Hanya user dengan role 'judge'
 * - Hanya bisa input nilai ke pertandingan yang sedang 'ongoing'
 * - Hanya bisa update nilai yang dia sendiri input
 *
 * Artisan command:
 *   php artisan make:controller Judge/ScoringController
 */
class ScoringController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:judge');
        $this->middleware('permission:input score')->only(['index', 'store']);
        $this->middleware('permission:update score')->only(['update']);
    }

    // ══════════════════════════════════════════════════════════════
    // INDEX – Form input nilai untuk satu pertandingan
    // ══════════════════════════════════════════════════════════════

    /**
     * Menampilkan form input nilai untuk pertandingan tertentu.
     *
     * authorize('inputScore', $match) → MatchPolicy::inputScore()
     * Cek: match harus 'ongoing' dan user adalah judge.
     */
    public function index(Match $match): View
    {
        // Policy custom ability: inputScore
        $this->authorize('inputScore', $match);

        $match->load([
            'athlete1',
            'athlete2',
            'discipline.sport',
            'ageCategory',
            'arena',
            'scores' => fn ($q) => $q->where('judge_id', auth()->id()),
        ]);

        // Nilai yang sudah diinput judge ini untuk pertandingan ini
        $existingScores = $match->scores
            ->where('judge_id', auth()->id())
            ->keyBy('athlete_id');

        return view('judge.scoring.index', compact('match', 'existingScores'));
    }

    // ══════════════════════════════════════════════════════════════
    // STORE – Simpan nilai baru
    // ══════════════════════════════════════════════════════════════

    /**
     * Menyimpan nilai yang diinput judge.
     *
     * authorize('create', Score::class) → ScorePolicy::create()
     */
    public function store(StoreScoreRequest $request, Match $match): RedirectResponse
    {
        $this->authorize('inputScore', $match);
        $this->authorize('create', Score::class);

        $judge = $request->user();

        // Simpan nilai untuk setiap atlet dalam pertandingan
        foreach ($request->validated('scores') as $athleteId => $scoreData) {
            Score::updateOrCreate(
                [
                    'match_id'    => $match->id,
                    'judge_id'    => $judge->id,
                    'athlete_id'  => $athleteId,
                    'score_type'  => $scoreData['score_type'] ?? 'total',
                    'round_number'=> $scoreData['round_number'] ?? null,
                ],
                [
                    'score' => $scoreData['score'],
                    'notes' => $scoreData['notes'] ?? null,
                ]
            );
        }

        return redirect()
            ->route('judge.matches.show', $match)
            ->with('success', 'Nilai berhasil disimpan.');
    }

    // ══════════════════════════════════════════════════════════════
    // UPDATE – Update nilai yang sudah ada
    // ══════════════════════════════════════════════════════════════

    /**
     * Memperbarui nilai yang sebelumnya sudah diinput.
     *
     * authorize('update', $score) → ScorePolicy::update()
     * Judge hanya bisa update nilai yang DIA sendiri input (judge_id == auth()->id())
     */
    public function update(
        UpdateScoreRequest $request,
        Match $match,
        Score $score
    ): RedirectResponse {
        // Policy check: hanya judge yang menginput nilai ini yang bisa mengubahnya
        $this->authorize('update', $score);

        // Pastikan score memang milik match ini (route model binding tambahan)
        abort_if(
            $score->match_id !== $match->id,
            403,
            'Score tidak terkait dengan pertandingan ini.'
        );

        $score->update($request->validated());

        return redirect()
            ->route('judge.scoring.index', $match)
            ->with('success', 'Nilai berhasil diperbarui.');
    }
}
