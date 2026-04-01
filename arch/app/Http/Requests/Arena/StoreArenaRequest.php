<?php

namespace App\Http\Requests\Arena;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * StoreArenaRequest
 *
 * Artisan command:
 *   php artisan make:request Arena/StoreArenaRequest
 */
class StoreArenaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:100', 'unique:arenas,name'],
            'location'  => ['nullable', 'string', 'max:255'],
            'capacity'  => ['nullable', 'integer', 'min:1', 'max:100000'],
            'notes'     => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama arena wajib diisi.',
            'name.unique'   => 'Nama arena [:input] sudah terdaftar.',
            'name.max'      => 'Nama arena maksimal 100 karakter.',
            'capacity.min'  => 'Kapasitas minimal 1 orang.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'      => 'nama arena',
            'location'  => 'lokasi',
            'capacity'  => 'kapasitas',
            'notes'     => 'catatan',
            'is_active' => 'status aktif',
        ];
    }
}
