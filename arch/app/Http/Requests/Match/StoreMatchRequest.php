<?php

namespace App\Http\Requests\Match;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * StoreMatchRequest
 *
 * Artisan command:
 *   php artisan make:request Match/StoreMatchRequest
 */
class StoreMatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'event_id' => [
                'required',
                'integer',
                'exists:events,id',
            ],
            'discipline_id' => [
                'required',
                'integer',
                'exists:disciplines,id',
            ],
            'age_category_id' => [
                'required',
                'integer',
                'exists:age_categories,id',
            ],
            'arena_id' => [
                'required',
                'integer',
                'exists:arenas,id',
            ],
            'athlete1_id' => [
                'required',
                'integer',
                'exists:athletes,id',
                'different:athlete2_id',
            ],
            'athlete2_id' => [
                'nullable',
                'integer',
                'exists:athletes,id',
                'different:athlete1_id',
            ],
            'round' => [
                'required',
                'string',
                'max:50',
                Rule::in(['pool', 'quarter_final', 'semi_final', 'final', 'bronze', 'round_1', 'round_2', 'round_3']),
            ],
            'match_number' => ['nullable', 'integer', 'min:1'],
            'match_date'   => [
                'required',
                'date',
            ],
            'match_time'   => [
                'required',
                'date_format:H:i',
            ],
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
            'event_id.required'       => 'Event wajib dipilih.',
            'event_id.exists'         => 'Event tidak ditemukan.',
            'discipline_id.required'  => 'Discipline wajib dipilih.',
            'discipline_id.exists'    => 'Discipline tidak ditemukan.',
            'age_category_id.required'=> 'Kategori umur wajib dipilih.',
            'age_category_id.exists'  => 'Kategori umur tidak ditemukan.',
            'arena_id.required'       => 'Arena wajib dipilih.',
            'arena_id.exists'         => 'Arena tidak ditemukan.',
            'athlete1_id.required'    => 'Atlet pertama wajib dipilih.',
            'athlete1_id.exists'      => 'Atlet pertama tidak ditemukan.',
            'athlete1_id.different'   => 'Atlet pertama dan kedua tidak boleh sama.',
            'athlete2_id.different'   => 'Atlet kedua tidak boleh sama dengan atlet pertama.',
            'round.required'          => 'Babak pertandingan wajib diisi.',
            'round.in'                => 'Babak tidak valid. Pilihan: pool, quarter_final, semi_final, final, bronze.',
            'match_date.required'     => 'Tanggal pertandingan wajib diisi.',
            'match_date.date'         => 'Format tanggal pertandingan tidak valid.',
            'match_time.required'     => 'Waktu pertandingan wajib diisi.',
            'match_time.date_format'  => 'Format waktu pertandingan harus HH:MM (contoh: 09:30).',
            'status.in'               => 'Status tidak valid. Pilihan: scheduled, ongoing, completed, postponed, cancelled, walkover.',
        ];
    }

    public function attributes(): array
    {
        return [
            'event_id'        => 'event',
            'discipline_id'   => 'discipline',
            'age_category_id' => 'kategori umur',
            'arena_id'        => 'arena',
            'athlete1_id'     => 'atlet pertama',
            'athlete2_id'     => 'atlet kedua',
            'round'           => 'babak',
            'match_date'      => 'tanggal pertandingan',
            'match_time'      => 'waktu pertandingan',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                // Validasi: kedua atlet harus terdaftar di event + discipline + age_category yang sama
                $eventId        = $this->input('event_id');
                $disciplineId   = $this->input('discipline_id');
                $ageCategoryId  = $this->input('age_category_id');
                $athlete1Id     = $this->input('athlete1_id');
                $athlete2Id     = $this->input('athlete2_id');

                if ($eventId && $disciplineId && $ageCategoryId && $athlete1Id) {
                    $this->validateAthleteRegistered($validator, $athlete1Id, $eventId, $disciplineId, $ageCategoryId, 'athlete1_id', '1');

                    if ($athlete2Id) {
                        $this->validateAthleteRegistered($validator, $athlete2Id, $eventId, $disciplineId, $ageCategoryId, 'athlete2_id', '2');
                    }
                }

                // Validasi: cek konflik arena + waktu yang sama
                $arenaId   = $this->input('arena_id');
                $matchDate = $this->input('match_date');
                $matchTime = $this->input('match_time');

                if ($arenaId && $matchDate && $matchTime) {
                    $conflict = \App\Models\Match::where('arena_id', $arenaId)
                        ->whereDate('match_date', $matchDate)
                        ->where('match_time', $matchTime)
                        ->whereNotIn('status', ['cancelled', 'postponed'])
                        ->exists();

                    if ($conflict) {
                        $validator->errors()->add(
                            'arena_id',
                            "Arena ini sudah dijadwalkan untuk pertandingan lain pada tanggal dan waktu yang sama."
                        );
                    }
                }
            },
        ];
    }

    private function validateAthleteRegistered(
        Validator $validator,
        int       $athleteId,
        int       $eventId,
        int       $disciplineId,
        int       $ageCategoryId,
        string    $field,
        string    $label
    ): void {
        $registered = \App\Models\EventParticipant::where([
            'event_id'        => $eventId,
            'athlete_id'      => $athleteId,
            'discipline_id'   => $disciplineId,
            'age_category_id' => $ageCategoryId,
            'status'          => 'verified',
        ])->exists();

        if (! $registered) {
            $athlete = \App\Models\Athlete::find($athleteId);
            $validator->errors()->add(
                $field,
                "Atlet " . ($athlete?->name ?? "#{$athleteId}") . " belum terdaftar (status: verified) " .
                "di event ini untuk discipline dan kategori umur yang dipilih."
            );
        }
    }
}
