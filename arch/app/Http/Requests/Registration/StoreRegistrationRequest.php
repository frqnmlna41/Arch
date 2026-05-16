<?php

namespace App\Http\Requests\Registration;

use App\Models\Registration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Hanya coach yang boleh submit
        return Auth::user()->hasRole('coach');
    }

// StoreRegistrationRequest
public function rules(): array
{
    $coachId = Auth::id();

    return [
        'athlete_id' => [
            'required',
            'integer',
            Rule::exists('athletes', 'id')->where('coach_id', $coachId),
            Rule::unique('registrations')
                ->where('athlete_id', $this->athlete_id)
                ->where('discipline_id', $this->discipline_id)
                ->where('age_category_id', $this->age_category_id),

                ],
        'discipline_id' => [
            'required',
            'integer',
            Rule::exists('disciplines', 'id'),
        ],
        'age_category_id' => [
            'required',
            'integer',
            Rule::exists('age_categories', 'id'),
        ],
        'club' => [        // ✅ tambahkan ini
            'nullable',
            'string',
            'max:150',

            ],
    ];
}

    public function messages(): array
    {
        return [
            'athlete_id.exists'      => 'Athlete tidak ditemukan atau bukan milik Anda.',
            'athlete_id.unique'      => 'Athlete ini sudah terdaftar di nomor tanding dan kategori yang sama.',
            'discipline_id.required' => 'Nomor tanding wajib dipilih.',
            'discipline_id.exists'   => 'Nomor tanding tidak valid.',
            'age_category_id.required' => 'Kategori usia wajib dipilih.',
            'age_category_id.exists'   => 'Kategori usia tidak valid.',
        ];
    }
}
