<?php

namespace App\Http\Requests\Sport;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * UpdateSportRequest
 *
 * Artisan command:
 *   php artisan make:request Sport/UpdateSportRequest
 */
class UpdateSportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        // Ambil ID sport dari route parameter untuk ignore unique
        $sportId = $this->route('sport')?->id ?? $this->route('sport');

        return [
            'name' => [
                'sometimes',
                'string',
                'max:100',
                // Unique, kecuali sport ini sendiri
                Rule::unique('sports', 'name')->ignore($sportId),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active'   => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Nama olahraga [:input] sudah digunakan oleh olahraga lain.',
            'name.max'    => 'Nama olahraga maksimal 100 karakter.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'        => 'nama olahraga',
            'description' => 'deskripsi',
            'is_active'   => 'status aktif',
        ];
    }
}
