<?php

namespace App\Http\Requests\AgeCategory;

use Illuminate\Foundation\Http\FormRequest;

class StoreAgeCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'sport_id'    => ['required', 'integer', 'exists:sports,id'],
            'name'        => ['required', 'string', 'max:100'],
            'label'       => ['required', 'string', 'max:100'],
            'min_age'     => ['required', 'integer', 'min:0'],
            'max_age'     => ['required', 'integer', 'gte:min_age'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'sport_id.exists' => 'Sport yang dipilih tidak ditemukan.',
            'max_age.gte'     => 'Usia maksimum harus lebih besar atau sama dengan usia minimum.',
        ];
    }
}
