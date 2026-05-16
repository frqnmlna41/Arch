<?php

namespace App\Http\Requests\Discipline;

use Illuminate\Foundation\Http\FormRequest;

class StoreDisciplineRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'sport_id'    => ['required', 'integer', 'exists:sports,id'],
            'name'        => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'sport_id.required' => 'Sport wajib dipilih.',
            'sport_id.exists'   => 'Sport yang dipilih tidak ditemukan.',
            'name.required'     => 'Nama disiplin wajib diisi.',
        ];
    }
}
