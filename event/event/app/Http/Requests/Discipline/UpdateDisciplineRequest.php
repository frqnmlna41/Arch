<?php

namespace App\Http\Requests\Discipline;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDisciplineRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'sport_id'    => ['sometimes', 'integer', 'exists:sports,id'],
            'name'        => ['sometimes', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'sport_id.exists' => 'Sport yang dipilih tidak ditemukan.',
        ];
    }
}
