<?php

namespace App\Http\Requests\Score;

use App\Rules\MatchIsOngoing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * StoreScoreRequest
 *
 * Artisan command:
 *   php artisan make:request Score/StoreScoreRequest
 *
 * Authorization:
 *   judge → input score (hanya untuk match ongoing)
 *   admin → bisa input/override score
 *   coach/athlete → TIDAK boleh
 *
 * Mendukung bulk input score (array):
 * {
 *   "scores": [
 *     { "athlete_id": 1, "score": 9.5, "score_type": "technique" },
 *     { "athlete_id": 1, "score": 9.0, "score_type": "difficulty" },
 *     { "athlete_id": 2, "score": 8.8, "score_type": "technique" }
 *   ]
 * }
 */
class StoreScoreRequest extends FormRequest
{
    // ──────────────────────────────────────────────────────────────
    // AUTHORIZE
    // ──────────────────────────────────────────────────────────────

    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) return false;

        // Admin dan judge boleh input score
        return $user->hasAnyRole(['admin', 'judge']);
    }

    // ──────────────────────────────────────────────────────────────
    // RULES
    // ──────────────────────────────────────────────────────────────

    public function rules(): array
    {
        $matchId = $this->route('match')?->id ?? $this->route('match');

        return [
            // Validasi match: harus ada dan harus ongoing
            'match_id' => [
                'sometimes',
                'integer',
                'exists:matches,id',
                new MatchIsOngoing(),
            ],

            // Array scores (bulk input)
            'scores'                 => ['required', 'array', 'min:1', 'max:20'],
            'scores.*.athlete_id'   => [
                'required',
                'integer',
                'exists:athletes,id',
                // Atlet harus peserta dari match ini
                function (string $attribute, mixed $value, \Closure $fail) use ($matchId) {
                    if (! $matchId) return;

                    $match = \App\Models\Match::find($matchId);
                    if (! $match) return;

                    $validAthletes = array_filter([
                        $match->athlete1_id,
                        $match->athlete2_id,
                    ]);

                    if (! in_array((int) $value, $validAthletes)) {
                        $athleteName = \App\Models\Athlete::find($value)?->name ?? "ID:{$value}";
                        $fail("Atlet [{$athleteName}] bukan peserta dari pertandingan ini.");
                    }
                },
            ],
            'scores.*.score'        => [
                'required',
                'numeric',
                'min:0',
                'max:10',
                'decimal:0,2', // maksimal 2 desimal
            ],
            'scores.*.score_type'   => [
                'nullable',
                'string',
                'max:50',
                'in:technique,difficulty,deduction,total,execution,content,round_1,round_2,round_3',
            ],
            'scores.*.round_number' => [
                'nullable',
                'integer',
                'min:1',
                'max:10',
            ],
            'scores.*.notes'        => ['nullable', 'string', 'max:500'],
        ];
    }

    // ──────────────────────────────────────────────────────────────
    // MESSAGES
    // ──────────────────────────────────────────────────────────────

    public function messages(): array
    {
        return [
            'match_id.exists'              => 'Pertandingan tidak ditemukan.',
            'scores.required'              => 'Data nilai wajib diisi.',
            'scores.array'                 => 'Data nilai harus berupa daftar.',
            'scores.min'                   => 'Minimal harus ada 1 data nilai.',
            'scores.max'                   => 'Maksimal 20 data nilai dalam satu request.',
            'scores.*.athlete_id.required' => 'Atlet wajib dipilih untuk setiap nilai.',
            'scores.*.athlete_id.exists'   => 'Salah satu atlet tidak ditemukan.',
            'scores.*.score.required'      => 'Nilai wajib diisi.',
            'scores.*.score.numeric'       => 'Nilai harus berupa angka.',
            'scores.*.score.min'           => 'Nilai minimal adalah 0.',
            'scores.*.score.max'           => 'Nilai maksimal adalah 10.',
            'scores.*.score.decimal'       => 'Nilai maksimal memiliki 2 angka desimal (contoh: 9.75).',
            'scores.*.score_type.in'       => 'Tipe nilai tidak valid. Pilihan: technique, difficulty, deduction, total, execution, content.',
            'scores.*.round_number.min'    => 'Nomor ronde minimal 1.',
            'scores.*.round_number.max'    => 'Nomor ronde maksimal 10.',
        ];
    }

    // ──────────────────────────────────────────────────────────────
    // ATTRIBUTES
    // ──────────────────────────────────────────────────────────────

    public function attributes(): array
    {
        return [
            'match_id'               => 'pertandingan',
            'scores'                 => 'daftar nilai',
            'scores.*.athlete_id'   => 'atlet',
            'scores.*.score'        => 'nilai',
            'scores.*.score_type'   => 'tipe nilai',
            'scores.*.round_number' => 'nomor ronde',
            'scores.*.notes'        => 'catatan',
        ];
    }

    // ──────────────────────────────────────────────────────────────
    // AFTER – custom logic setelah rules()
    // ──────────────────────────────────────────────────────────────

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $user  = $this->user();
                $match = $this->route('match');

                if (! $match || ! is_object($match)) return;

                // Judge tidak boleh input nilai ganda untuk tipe yang sama
                // dalam satu request (cegah submit form dua kali)
                $scores       = $this->input('scores', []);
                $judgeId      = $user->id;
                $seenEntries  = [];

                foreach ($scores as $index => $entry) {
                    $athleteId  = $entry['athlete_id']  ?? null;
                    $scoreType  = $entry['score_type']  ?? 'total';
                    $roundNum   = $entry['round_number'] ?? null;

                    $key = "{$athleteId}_{$scoreType}_{$roundNum}";

                    if (in_array($key, $seenEntries)) {
                        $validator->errors()->add(
                            "scores.{$index}.score_type",
                            "Duplikat nilai: tipe [{$scoreType}] untuk atlet yang sama sudah ada dalam request ini."
                        );
                    } else {
                        $seenEntries[] = $key;
                    }

                    // Cek apakah judge sudah pernah input nilai ini (jika bukan update)
                    if ($athleteId && ! $validator->errors()->has("scores.{$index}.athlete_id")) {
                        $alreadyScored = \App\Models\Score::where([
                            'match_id'     => $match->id,
                            'judge_id'     => $judgeId,
                            'athlete_id'   => $athleteId,
                            'score_type'   => $scoreType,
                            'round_number' => $roundNum,
                        ])->exists();

                        if ($alreadyScored) {
                            $validator->errors()->add(
                                "scores.{$index}.score",
                                "Anda sudah menginput nilai [{$scoreType}] untuk atlet ini. " .
                                "Gunakan endpoint update jika ingin mengubah nilai."
                            );
                        }
                    }
                }

                // Validasi: judge hanya bisa input saat match ongoing
                if ($match->status !== 'ongoing') {
                    $validator->errors()->add(
                        'match_id',
                        'Nilai hanya bisa diinput saat pertandingan sedang berlangsung (status: ongoing).'
                    );
                }
            },
        ];
    }
}
