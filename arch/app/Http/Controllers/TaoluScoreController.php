<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Contest;
use App\Models\TaoluScore;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class TaoluScoreController extends Controller
{
    /**
     * Daftar contest yang belum/sudah diinput nilainya
     */
    public function index(): View
    {
        $contests = Contest::with([
                'eventCategory.discipline',
                'eventCategory.ageCategory',
                'athlete',
                'score',
            ])
            ->orderBy('start_time')
            ->orderBy('order_number')
            ->paginate(25);

        return view('operator.scores.index', compact('contests'));
    }

    /**
     * Form input nilai juri
     */
    public function edit(Contest $contest): View
    {
        $score = $contest->score ?? new TaoluScore(['contest_id' => $contest->id]);

        return view('operator.scores.edit', compact('contest', 'score'));
    }

    /**
     * Simpan nilai
     */
    public function update(Request $request, Contest $contest): RedirectResponse
    {
        $request->validate([
            'judge_1'   => ['required', 'numeric', 'min:0', 'max:10'],
            'judge_2'   => ['required', 'numeric', 'min:0', 'max:10'],
            'judge_3'   => ['required', 'numeric', 'min:0', 'max:10'],
            'judge_4'   => ['required', 'numeric', 'min:0', 'max:10'],
            'judge_5'   => ['required', 'numeric', 'min:0', 'max:10'],
            'deduction' => ['nullable', 'numeric', 'min:0'],
        ]);

        $score = TaoluScore::firstOrNew(['contest_id' => $contest->id]);
        $score->fill([
            'judge_1'   => $request->judge_1,
            'judge_2'   => $request->judge_2,
            'judge_3'   => $request->judge_3,
            'judge_4'   => $request->judge_4,
            'judge_5'   => $request->judge_5,
            'deduction' => $request->deduction ?? 0,
        ]);

        $score->saveWithFinalScore();

        return redirect()
            ->route('operator.scores.index')
            ->with('success', "Nilai {$contest->athlete->name} berhasil disimpan. Final: {$score->final_score}");
    }
}