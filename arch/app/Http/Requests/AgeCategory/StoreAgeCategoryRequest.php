<?php

namespace App\Http\Requests\AgeCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * StoreAgeCategoryRequest
 *
 * Artisan command:
 *   php artisan make:request AgeCategory/StoreAgeCategoryRequest
 */
class StoreAgeCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'sport_id'    => [
                'required',
                'integer',
                'exists:sports,id',
            ],
            'name'        => [
                'required',
                'string',
                'max:20',
                // Nama kategori unik per sport
                Rule::unique('age_categories', 'name')
                    ->where('sport_id', $this->input('sport_id')),
            ],
            'label'       => ['nullable', 'string', 'max:150'],
            'min_age'     => [
                'required',
                'integer',
                'min:0',
                'max:120',
            ],
            'max_age'     => [
                'required',
                'integer',
                'min:1',
                'max:999',
                // max_age harus lebih besar dari min_age
                'gt:min_age',
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active'   => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'sport_id.required' => 'Cabang olahraga wajib dipilih.',
            'sport_id.exists'   => 'Cabang olahraga tidak valid.',
            'name.required'     => 'Kode kategori umur wajib diisi (contoh: A, B, C1).',
            'name.unique'       => 'Kode kategori [:input] sudah ada dalam cabang olahraga ini.',
            'name.max'          => 'Kode kategori maksimal 20 karakter.',
            'min_age.required'  => 'Usia minimum wajib diisi.',
            'min_age.integer'   => 'Usia minimum harus berupa angka bulat.',
            'min_age.min'       => 'Usia minimum tidak boleh negatif.',
            'min_age.max'       => 'Usia minimum tidak boleh lebih dari 120 tahun.',
            'max_age.required'  => 'Usia maksimum wajib diisi.',
            'max_age.integer'   => 'Usia maksimum harus berupa angka bulat.',
            'max_age.gt'        => 'Usia maksimum harus lebih besar dari usia minimum.',
        ];
    }

    public function attributes(): array
    {
        return [
            'sport_id' => 'cabang olahraga',
            'name'     => 'kode kategori',
            'label'    => 'label kategori',
            'min_age'  => 'usia minimum',
            'max_age'  => 'usia maksimum',
        ];
    }

    /**
     * Custom after-validation untuk logika tambahan.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $minAge = (int) $this->input('min_age', 0);
                $maxAge = (int) $this->input('max_age', 0);

                // Kategori 60+ menggunakan max_age = 999, valid
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
