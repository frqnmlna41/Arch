<?php

namespace App\Http\Requests\Arena;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * UpdateArenaRequest
 *
 * Artisan command:
 *   php artisan make:request Arena/UpdateArenaRequest
 */
class UpdateArenaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        $arenaId = $this->route('arena')?->id ?? $this->route('arena');

        return [
            'name'      => [
                'sometimes',
                'string',
                'max:100',
                Rule::unique('arenas', 'name')->ignore($arenaId),
            ],
            'location'  => ['nullable', 'string', 'max:255'],
            'capacity'  => ['nullable', 'integer', 'min:1', 'max:100000'],
            'notes'     => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique'  => 'Nama arena [:input] sudah digunakan oleh arena lain.',
            'capacity.min' => 'Kapasitas minimal 1 orang.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'      => 'nama arena',
            'location'  => 'lokasi',
            'capacity'  => 'kapasitas',
            'is_active' => 'status aktif',
        ];
    }
}
