<?php

namespace App\Http\Requests\Match;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * UpdateMatchRequest
 *
 * Artisan command:
 *   php artisan make:request Match/UpdateMatchRequest
 */
class UpdateMatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        $match   = $this->route('match');
        $matchId = is_object($match) ? $match->id : $match;

        return [
            'arena_id'     => ['nullable', 'integer', 'exists:arenas,id'],
            'match_date'   => ['sometimes', 'date'],
            'match_time'   => ['sometimes', 'date_format:H:i'],
            'match_number' => ['nullable', 'integer', 'min:1'],
            'status'       => [
                'sometimes',
                Rule::in(['scheduled', 'ongoing', 'completed', 'postponed', 'cancelled', 'walkover']),
            ],
            'notes'        => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'arena_id.exists'        => 'Arena tidak ditemukan.',
            'match_date.date'        => 'Format tanggal pertandingan tidak valid.',
            'match_time.date_format' => 'Format waktu pertandingan harus HH:MM (contoh: 09:30).',
            'status.in'              => 'Status tidak valid. Pilihan: scheduled, ongoing, completed, postponed, cancelled, walkover.',
        ];
    }

    public function attributes(): array
    {
        return [
            'arena_id'   => 'arena',
            'match_date' => 'tanggal pertandingan',
            'match_time' => 'waktu pertandingan',
            'status'     => 'status pertandingan',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $match = $this->route('match');
                if (! is_object($match)) return;

                // Tidak bisa update match yang sudah completed
                if ($match->status === 'completed') {
                    $validator->errors()->add(
                        'status',
                        'Pertandingan yang sudah selesai tidak dapat diubah.'
                    );
                }

                // Cek konflik arena jika arena atau jadwal berubah
                $arenaId   = $this->input('arena_id', $match->arena_id);
                $matchDate = $this->input('match_date', $match->match_date?->toDateString());
                $matchTime = $this->input('match_time', $match->match_time);

                if ($arenaId && $matchDate && $matchTime) {
                    $conflict = \App\Models\Match::where('arena_id', $arenaId)
                        ->whereDate('match_date', $matchDate)
                        ->where('match_time', $matchTime)
                        ->where('id', '!=', $match->id)
                        ->whereNotIn('status', ['cancelled', 'postponed'])
                        ->exists();

                    if ($conflict) {
                        $validator->errors()->add(
                            'arena_id',
                            'Arena ini sudah dijadwalkan untuk pertandingan lain pada waktu yang sama.'
                        );
                    }
                }
            },
        ];
    }
}
