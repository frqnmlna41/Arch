<?php

namespace App\Http\Requests\Perguruan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePerguruanRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('perguruan');

        return [
            'name'      => ['sometimes', 'string', 'max:150', Rule::unique('perguruans', 'name')->ignore($id)],
            'slug'      => ['nullable', 'string', 'max:150', Rule::unique('perguruans', 'slug')->ignore($id)],
            'address'   => ['nullable', 'string'],
            'phone'     => ['nullable', 'string', 'max:20'],
            'email'     => ['nullable', 'email', 'max:100'],
            'logo'      => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
