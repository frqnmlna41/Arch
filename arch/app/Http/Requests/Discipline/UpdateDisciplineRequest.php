<?php

namespace App\Http\Requests\Discipline;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * UpdateDisciplineRequest
 *
 * Artisan command:
 *   php artisan make:request Discipline/UpdateDisciplineRequest
 */
class UpdateDisciplineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        $discipline = $this->route('discipline');
        $disciplineId = is_object($discipline) ? $discipline->id : $discipline;

        // Gunakan sport_id dari input jika ada, fallback ke sport_id disiplin saat ini
        $sportId = $this->input('sport_id')
            ?? (is_object($discipline) ? $discipline->sport_id : null);

        return [
            'sport_id'    => ['sometimes', 'integer', 'exists:sports,id'],
            'name'        => [
                'sometimes',
                'string',
                'max:100',
                Rule::unique('disciplines', 'name')
                    ->where('sport_id', $sportId)
                    ->ignore($disciplineId),
            ],
            'type'        => ['sometimes', 'string', Rule::in(['empty_hand', 'weapon'])],
            'match_type'  => ['sometimes', 'string', Rule::in(['performance', 'sparring'])],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active'   => ['boolean'],

            'age_category_ids'   => ['nullable', 'array'],
            'age_category_ids.*' => [
                'integer',
                $sportId
                    ? Rule::exists('age_categories', 'id')->where('sport_id', $sportId)
                    : 'exists:age_categories,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'sport_id.exists'          => 'Cabang olahraga yang dipilih tidak valid.',
            'name.unique'              => 'Nama discipline [:input] sudah ada dalam cabang olahraga ini.',
            'type.in'                  => 'Tipe harus: empty_hand atau weapon.',
            'match_type.in'            => 'Tipe pertandingan harus: performance atau sparring.',
            'age_category_ids.*.exists'=> 'Salah satu kategori umur tidak valid.',
        ];
    }

    public function attributes(): array
    {
        return [
            'sport_id'         => 'cabang olahraga',
            'name'             => 'nama discipline',
            'type'             => 'tipe',
            'match_type'       => 'tipe pertandingan',
            'age_category_ids' => 'kategori umur',
        ];
    }
}
