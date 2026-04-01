<?php

namespace App\Http\Requests\Winner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * GenerateWinnerRequest
 *
 * Artisan command:
 *   php artisan make:request Winner/GenerateWinnerRequest
 *
 * Digunakan untuk menentukan pemenang dari sebuah
 * event + discipline + age_category tertentu.
 *
 * Authorization: hanya admin
 */
class GenerateWinnerRequest extends FormRequest
{
    // ──────────────────────────────────────────────────────────────
    // AUTHORIZE
    // ──────────────────────────────────────────────────────────────

    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    // ──────────────────────────────────────────────────────────────
    // RULES
    // ──────────────────────────────────────────────────────────────

    public function rules(): array
    {
        return [
            'event_id'        => ['required', 'integer', 'exists:events,id'],
            'discipline_id'   => ['required', 'integer', 'exists:disciplines,id'],
            'age_category_id' => ['required', 'integer', 'exists:age_categories,id'],

            // rank: 1=emas, 2=perak, 3=perunggu
            'rank'            => ['required', 'integer', 'in:1,2,3'],

            'athlete_id'      => ['required', 'integer', 'exists:athletes,id'],
            'total_score'     => ['nullable', 'numeric', 'min:0', 'max:100', 'decimal:0,3'],
            'medal_type'      => ['nullable', 'string', 'in:gold,silver,bronze'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ];
    }

    // ──────────────────────────────────────────────────────────────
    // MESSAGES
    // ──────────────────────────────────────────────────────────────

    public function messages(): array
    {
        return [
            'event_id.required'        => 'Event wajib dipilih.',
            'event_id.exists'          => 'Event tidak ditemukan.',
            'discipline_id.required'   => 'Discipline wajib dipilih.',
            'discipline_id.exists'     => 'Discipline tidak ditemukan.',
            'age_category_id.required' => 'Kategori umur wajib dipilih.',
            'age_category_id.exists'   => 'Kategori umur tidak ditemukan.',
            'rank.required'            => 'Peringkat wajib diisi.',
            'rank.in'                  => 'Peringkat harus: 1 (Emas), 2 (Perak), atau 3 (Perunggu).',
            'athlete_id.required'      => 'Atlet wajib dipilih.',
            'athlete_id.exists'        => 'Atlet tidak ditemukan.',
            'total_score.numeric'      => 'Total nilai harus berupa angka.',
            'total_score.min'          => 'Total nilai minimal 0.',
            'total_score.max'          => 'Total nilai maksimal 100.',
            'medal_type.in'            => 'Tipe medali harus: gold, silver, atau bronze.',
        ];
    }

    // ──────────────────────────────────────────────────────────────
    // ATTRIBUTES
    // ──────────────────────────────────────────────────────────────

    public function attributes(): array
    {
        return [
            'event_id'        => 'event',
            'discipline_id'   => 'discipline',
            'age_category_id' => 'kategori umur',
            'rank'            => 'peringkat',
            'athlete_id'      => 'atlet',
            'total_score'     => 'total nilai',
            'medal_type'      => 'tipe medali',
        ];
    }

    // ──────────────────────────────────────────────────────────────
    // AFTER – validasi bisnis
    // ──────────────────────────────────────────────────────────────

    public function after(): array
    {
        return [
            function (Validator $validator) {

                $eventId        = $this->input('event_id');
                $disciplineId   = $this->input('discipline_id');
                $ageCategoryId  = $this->input('age_category_id');
                $athleteId      = $this->input('athlete_id');
                $rank           = (int) $this->input('rank');

                // Pastikan event sudah completed
                $event = \App\Models\Event::find($eventId);
                if ($event && $event->status !== 'completed') {
                    $validator->errors()->add(
                        'event_id',
                        "Pemenang hanya bisa ditentukan setelah event berstatus 'completed'. " .
                        "Status saat ini: [{$event->status}]."
                    );
                }

                // Pastikan semua match untuk kombinasi ini sudah selesai
                if ($eventId && $disciplineId && $ageCategoryId) {
                    $pendingCount = \App\Models\Match::where([
                        'event_id'        => $eventId,
                        'discipline_id'   => $disciplineId,
                        'age_category_id' => $ageCategoryId,
                    ])
                    ->whereNotIn('status', ['completed', 'walkover', 'cancelled'])
                    ->count();

                    if ($pendingCount > 0) {
                        $validator->errors()->add(
                            'discipline_id',
                            "{$pendingCount} pertandingan belum selesai. " .
                            "Semua pertandingan harus diselesaikan sebelum menentukan pemenang."
                        );
                    }
                }

                // Pastikan atlet terdaftar di event + discipline + age_category ini
                if ($athleteId && $eventId && $disciplineId && $ageCategoryId) {
                    $isParticipant = \App\Models\EventParticipant::where([
                        'event_id'        => $eventId,
                        'athlete_id'      => $athleteId,
                        'discipline_id'   => $disciplineId,
                        'age_category_id' => $ageCategoryId,
                        'status'          => 'verified',
                    ])->exists();

                    if (! $isParticipant) {
                        $athlete = \App\Models\Athlete::find($athleteId);
                        $validator->errors()->add(
                            'athlete_id',
                            "Atlet [" . ($athlete?->name ?? "ID:{$athleteId}") . "] " .
                            "tidak terdaftar sebagai peserta terverifikasi dalam kategori ini."
                        );
                    }
                }

                // Pastikan rank 1 dan 2 belum diisi (tidak ada duplikat rank emas/perak)
                if ($eventId && $disciplineId && $ageCategoryId && in_array($rank, [1, 2])) {
                    $exists = \App\Models\Winner::where([
                        'event_id'        => $eventId,
                        'discipline_id'   => $disciplineId,
                        'age_category_id' => $ageCategoryId,
                        'rank'            => $rank,
                    ])->exists();

                    if ($exists) {
                        $rankLabel = $rank === 1 ? 'Emas (Juara 1)' : 'Perak (Juara 2)';
                        $validator->errors()->add(
                            'rank',
                            "Peringkat {$rankLabel} sudah ditentukan untuk kategori ini. " .
                            "Hapus pemenang yang ada sebelum menggantinya."
                        );
                    }
                }

                // Pastikan atlet belum ada di winners untuk kategori yang sama
                if ($athleteId && $eventId && $disciplineId && $ageCategoryId) {
                    $alreadyWinner = \App\Models\Winner::where([
                        'event_id'        => $eventId,
                        'athlete_id'      => $athleteId,
                        'discipline_id'   => $disciplineId,
                        'age_category_id' => $ageCategoryId,
                    ])->exists();

                    if ($alreadyWinner) {
                        $athlete = \App\Models\Athlete::find($athleteId);
                        $validator->errors()->add(
                            'athlete_id',
                            "Atlet [" . ($athlete?->name ?? "ID:{$athleteId}") . "] " .
                            "sudah terdaftar sebagai pemenang dalam kategori ini."
                        );
                    }
                }

                // Auto-set medal_type berdasarkan rank jika tidak diisi
                if (! $this->input('medal_type') && $rank) {
                    $medalMap = [1 => 'gold', 2 => 'silver', 3 => 'bronze'];
                    $this->merge(['medal_type' => $medalMap[$rank] ?? 'bronze']);
                }
            },
        ];
    }
}
