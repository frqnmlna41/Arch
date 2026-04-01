<?php

namespace App\Http\Requests\EventParticipant;

use App\Rules\AthleteAgeMatchesCategory;
use App\Rules\DisciplineAllowedForAgeCategory;
use App\Rules\UniqueParticipant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * RegisterParticipantRequest
 *
 * Artisan command:
 *   php artisan make:request EventParticipant/RegisterParticipantRequest
 *
 * Authorization:
 *   admin → bisa daftarkan atlet siapapun
 *   coach → hanya bisa daftarkan atlet miliknya (coach_id == user->id)
 *   athlete/judge → TIDAK boleh
 *
 * Custom rules yang digunakan:
 *   AthleteAgeMatchesCategory   → usia atlet sesuai kategori umur
 *   DisciplineAllowedForAgeCategory → discipline tersedia untuk kategori
 *   UniqueParticipant           → cegah pendaftaran duplikat
 */
class RegisterParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) return false;

        // Admin boleh
        if ($user->hasRole('admin')) return true;

        // Coach boleh: tapi validasi atlet miliknya dilakukan di rules()
        if ($user->hasRole('coach')) return true;

        return false;
    }

    public function rules(): array
    {
        $athleteId      = (int) $this->input('athlete_id');
        $ageCategoryId  = (int) $this->input('age_category_id');
        $eventId        = (int) ($this->route('event')?->id ?? $this->input('event_id'));

        return [
            'athlete_id' => [
                'required',
                'integer',
                'exists:athletes,id',
                // Coach hanya bisa daftarkan atlet miliknya
                function (string $attribute, mixed $value, \Closure $fail) {
                    $user = $this->user();
                    if ($user->hasRole('coach')) {
                        $isOwned = \App\Models\Athlete::where('id', $value)
                            ->where('coach_id', $user->id)
                            ->exists();

                        if (! $isOwned) {
                            $fail('Anda hanya dapat mendaftarkan atlet yang berada di bawah bimbingan Anda.');
                        }
                    }
                },
            ],

            'discipline_id' => [
                'required',
                'integer',
                'exists:disciplines,id',
                // Discipline harus tersedia untuk age_category yang dipilih
                new DisciplineAllowedForAgeCategory($ageCategoryId ?: null),
            ],

            'age_category_id' => [
                'required',
                'integer',
                'exists:age_categories,id',
                // Usia atlet harus sesuai dengan rentang age_category
                $athleteId ? new AthleteAgeMatchesCategory($athleteId) : 'integer',
                // Cegah pendaftaran duplikat
                $athleteId && $eventId
                    ? new UniqueParticipant(
                        eventId:      $eventId,
                        athleteId:    $athleteId,
                        disciplineId: (int) $this->input('discipline_id'),
                    )
                    : 'integer',
            ],

            'weight_at_registration' => [
                'nullable',
                'numeric',
                'min:10',
                'max:300',
                'decimal:0,2',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'athlete_id.required'       => 'Atlet wajib dipilih.',
            'athlete_id.exists'         => 'Atlet yang dipilih tidak ditemukan.',
            'discipline_id.required'    => 'Discipline wajib dipilih.',
            'discipline_id.exists'      => 'Discipline yang dipilih tidak valid.',
            'age_category_id.required'  => 'Kategori umur wajib dipilih.',
            'age_category_id.exists'    => 'Kategori umur yang dipilih tidak valid.',
            'weight_at_registration.min' => 'Berat badan minimal 10 kg.',
            'weight_at_registration.max' => 'Berat badan maksimal 300 kg.',
        ];
    }

    public function attributes(): array
    {
        return [
            'athlete_id'             => 'atlet',
            'discipline_id'          => 'discipline',
            'age_category_id'        => 'kategori umur',
            'weight_at_registration' => 'berat badan saat pendaftaran',
        ];
    }

    /**
     * Validasi tambahan setelah rules() dijalankan.
     * Memvalidasi event masih dalam periode pendaftaran.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $event = $this->route('event');
                $eventModel = is_object($event) ? $event : \App\Models\Event::find($event);

                if (! $eventModel) return;

                // Cek apakah event masih buka pendaftaran
                if (! $eventModel->isRegistrationOpen()) {
                    $validator->errors()->add(
                        'event_id',
                        "Event [{$eventModel->name}] tidak dalam periode pendaftaran. " .
                        "Periode pendaftaran: {$eventModel->registration_start} s/d {$eventModel->registration_end}."
                    );
                }

                // Cek apakah event masih berstatus published
                if (! in_array($eventModel->status, ['published', 'draft'])) {
                    $validator->errors()->add(
                        'event_id',
                        "Pendaftaran tidak dapat dilakukan karena event berstatus [{$eventModel->status}]."
                    );
                }

                // Validasi: discipline harus milik sport yang sama dengan age_category
                $disciplineId  = $this->input('discipline_id');
                $ageCategoryId = $this->input('age_category_id');

                if ($disciplineId && $ageCategoryId && ! $validator->errors()->hasAny(['discipline_id', 'age_category_id'])) {
                    $discipline  = \App\Models\Discipline::find($disciplineId);
                    $ageCategory = \App\Models\AgeCategory::find($ageCategoryId);

                    if ($discipline && $ageCategory && $discipline->sport_id !== $ageCategory->sport_id) {
                        $validator->errors()->add(
                            'discipline_id',
                            "Discipline [{$discipline->name}] dan kategori umur [{$ageCategory->name}] " .
                            "berasal dari cabang olahraga yang berbeda."
                        );
                    }
                }
            },
        ];
    }
}
