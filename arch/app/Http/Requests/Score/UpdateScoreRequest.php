<?php

namespace App\Http\Requests\Score;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * UpdateScoreRequest
 *
 * Artisan command:
 *   php artisan make:request Score/UpdateScoreRequest
 *
 * Authorization:
 *   judge → hanya bisa update nilai yang DIA sendiri input (score.judge_id == user->id)
 *   admin → bisa update nilai siapapun
 *   coach/athlete → TIDAK boleh
 */
class UpdateScoreRequest extends FormRequest
{
    // ──────────────────────────────────────────────────────────────
    // AUTHORIZE
    // ──────────────────────────────────────────────────────────────

    public function authorize(): bool
    {
        $user  = $this->user();
        $score = $this->route('score');

        if (! $user || ! $score) return false;

        // Admin bisa update semua
        if ($user->hasRole('admin')) return true;

        // Judge hanya bisa update nilai miliknya
        if ($user->hasRole('judge')) {
            $scoreModel = is_object($score) ? $score : \App\Models\Score::find($score);
            return $scoreModel && $scoreModel->judge_id === $user->id;
        }

        return false;
    }

    // ──────────────────────────────────────────────────────────────
    // RULES
    // ──────────────────────────────────────────────────────────────

    public function rules(): array
    {
        return [
            'score'        => [
                'sometimes',
                'numeric',
                'min:0',
                'max:10',
                'decimal:0,2',
            ],
            'score_type'   => [
                'sometimes',
                'string',
                'max:50',
                'in:technique,difficulty,deduction,total,execution,content,round_1,round_2,round_3',
            ],
            'round_number' => [
                'nullable',
                'integer',
                'min:1',
                'max:10',
            ],
            'notes'        => ['nullable', 'string', 'max:500'],
        ];
    }

    // ──────────────────────────────────────────────────────────────
    // MESSAGES
    // ──────────────────────────────────────────────────────────────

    public function messages(): array
    {
        return [
            'score.numeric'       => 'Nilai harus berupa angka.',
            'score.min'           => 'Nilai minimal adalah 0.',
            'score.max'           => 'Nilai maksimal adalah 10.',
            'score.decimal'       => 'Nilai maksimal 2 angka desimal (contoh: 9.75).',
            'score_type.in'       => 'Tipe nilai tidak valid.',
            'round_number.min'    => 'Nomor ronde minimal 1.',
            'round_number.max'    => 'Nomor ronde maksimal 10.',
        ];
    }

    // ──────────────────────────────────────────────────────────────
    // ATTRIBUTES
    // ──────────────────────────────────────────────────────────────

    public function attributes(): array
    {
        return [
            'score'        => 'nilai',
            'score_type'   => 'tipe nilai',
            'round_number' => 'nomor ronde',
            'notes'        => 'catatan',
        ];
    }

    // ──────────────────────────────────────────────────────────────
    // AFTER – cek match masih ongoing saat update nilai
    // ──────────────────────────────────────────────────────────────

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $score = $this->route('score');
                if (! is_object($score)) return;

                // Pastikan match terkait masih ongoing
                $match = $score->match;
                if ($match && ! in_array($match->status, ['ongoing'])) {
                    // Admin tetap bisa update meski match sudah completed
                    if (! $this->user()?->hasRole('admin')) {
                        $validator->errors()->add(
                            'score',
                            "Nilai tidak dapat diubah karena pertandingan sudah berstatus [{$match->status}]."
                        );
                    }
                }
            },
        ];
    }
}
