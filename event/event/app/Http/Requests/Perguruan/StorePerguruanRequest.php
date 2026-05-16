<?php

namespace App\Http\Requests\Perguruan;

use Illuminate\Foundation\Http\FormRequest;

class StorePerguruanRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:150', 'unique:perguruans,name'],
            'slug'      => ['nullable', 'string', 'max:150', 'unique:perguruans,slug'],
            'address'   => ['nullable', 'string'],
            'phone'     => ['nullable', 'string', 'max:20'],
            'email'     => ['nullable', 'email', 'max:100'],
            'logo'      => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Nama perguruan sudah terdaftar.',
            'slug.unique' => 'Slug perguruan sudah digunakan.',
        ];
    }
}
