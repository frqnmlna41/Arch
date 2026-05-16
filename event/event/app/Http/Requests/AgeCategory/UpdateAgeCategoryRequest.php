<?php

namespace App\Http\Requests\AgeCategory;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAgeCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'sport_id'    => ['sometimes', 'integer', 'exists:sports,id'],
            'name'        => ['sometimes', 'string', 'max:100'],
            'label'       => ['sometimes', 'string', 'max:100'],
            'min_age'     => ['sometimes', 'integer', 'min:0'],
            'max_age'     => ['sometimes', 'integer', 'gte:min_age'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['sometimes', 'boolean'],
        ];
    }
}
