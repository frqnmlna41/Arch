<?php

namespace App\Http\Requests\AgeCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * UpdateAgeCategoryRequest
 *
 * Artisan command:
 *   php artisan make:request AgeCategory/UpdateAgeCategoryRequest
 */
class UpdateAgeCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        $ageCategory   = $this->route('age_category');
        $ageCategoryId = is_object($ageCategory) ? $ageCategory->id : $ageCategory;
        $sportId       = $this->input('sport_id')
                         ?? (is_object($ageCategory) ? $ageCategory->sport_id : null);

        return [
            'sport_id'    => ['sometimes', 'integer', 'exists:sports,id'],
            'name'        => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('age_categories', 'name')
                    ->where('sport_id', $sportId)
                    ->ignore($ageCategoryId),
            ],
            'label'       => ['nullable', 'string', 'max:150'],
            'min_age'     => ['sometimes', 'integer', 'min:0', 'max:120'],
            'max_age'     => ['sometimes', 'integer', 'min:1', 'max:999'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active'   => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique'  => 'Kode kategori [:input] sudah digunakan dalam cabang olahraga ini.',
            'min_age.min'  => 'Usia minimum tidak boleh negatif.',
            'max_age.min'  => 'Usia maksimum minimal 1 tahun.',
        ];
    }

    public function attributes(): array
    {
        return [
            'sport_id' => 'cabang olahraga',
            'name'     => 'kode kategori',
            'min_age'  => 'usia minimum',
            'max_age'  => 'usia maksimum',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                // Resolve nilai min/max (gunakan nilai request atau fallback ke model)
                $ageCategory = $this->route('age_category');
                $minAge = (int) $this->input('min_age', is_object($ageCategory) ? $ageCategory->min_age : 0);
                $maxAge = (int) $this->input('max_age', is_object($ageCategory) ? $ageCategory->max_age : 999);

                if ($maxAge < 999 && $minAge >= $maxAge) {
                    $validator->errors()->add(
                        'max_age',
                        "Usia maksimum ({$maxAge}) harus lebih besar dari usia minimum ({$minAge})."
                    );
                }
            },
        ];
    }
}
