<?php

namespace App\Http\Requests\Athlete;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * UpdateAthleteRequest
 *
 * Artisan command:
 *   php artisan make:request Athlete/UpdateAthleteRequest
 *
 * Authorization:
 *   admin → update atlet siapapun
 *   coach → hanya atlet yang coach_id == user->id
 *   athlete/judge → TIDAK boleh update
 */
class UpdateAthleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user    = $this->user();
        $athlete = $this->route('athlete');

        if (! $user || ! $athlete) return false;

        // Admin bisa update siapapun
        if ($user->hasRole('admin')) return true;

        // Coach hanya bisa update atlet miliknya
        if ($user->hasRole('coach')) {
            $athleteModel = is_object($athlete) ? $athlete : \App\Models\Athlete::find($athlete);
            return $athleteModel && $athleteModel->coach_id === $user->id;
        }

        return false;
    }

    public function rules(): array
    {
        $athleteId = $this->route('athlete')?->id ?? $this->route('athlete');

        return [
            'name'       => ['sometimes', 'string', 'max:150'],
            'birth_date' => [
                'sometimes',
                'date',
                'before:today',
                'before_or_equal:' . now()->subYears(5)->toDateString(),
            ],
            'gender'     => ['sometimes', Rule::in(['male', 'female'])],
            'club'       => ['nullable', 'string', 'max:150'],
            'phone'      => [
                'nullable',
                'string',
                'max:20',
                'regex:/^(\+62|62|0)[0-9]{8,13}$/',
            ],
            'photo'      => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'id_card_number' => ['nullable', 'string', 'max:50'],
            'weight'     => ['nullable', 'numeric', 'min:10', 'max:300', 'decimal:0,2'],
            'height'     => ['nullable', 'numeric', 'min:50', 'max:250', 'decimal:0,2'],
            'address'    => ['nullable', 'string', 'max:500'],
            'is_active'  => ['boolean'],

            // Coach tidak bisa mengubah coach_id (hanya admin)
            'coach_id'   => [
                'nullable',
                'integer',
                'exists:users,id',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $user = $this->user();
                    if ($user->hasRole('coach') && $value && (int) $value !== $user->id) {
                        $fail('Coach tidak dapat mengalihkan atlet ke coach lain.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'birth_date.before_or_equal' => 'Atlet harus berusia minimal 5 tahun.',
            'gender.in'                  => 'Jenis kelamin harus: male atau female.',
            'phone.regex'                => 'Format nomor telepon tidak valid.',
            'photo.mimes'                => 'Format foto harus: JPG, JPEG, PNG, atau WEBP.',
            'photo.max'                  => 'Ukuran foto maksimal 2MB.',
            'weight.min'                 => 'Berat badan minimal 10 kg.',
            'height.min'                 => 'Tinggi badan minimal 50 cm.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'       => 'nama atlet',
            'birth_date' => 'tanggal lahir',
            'gender'     => 'jenis kelamin',
            'club'       => 'nama klub',
            'phone'      => 'nomor telepon',
            'weight'     => 'berat badan',
            'height'     => 'tinggi badan',
            'coach_id'   => 'coach',
        ];
    }
}
