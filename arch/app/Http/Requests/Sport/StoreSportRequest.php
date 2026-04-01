<?php

namespace App\Http\Requests\Sport;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * StoreSportRequest
 *
 * Artisan command:
 *   php artisan make:request Sport/StoreSportRequest
 *
 * Akses: hanya admin
 */
class StoreSportRequest extends FormRequest
{
    // ──────────────────────────────────────────────────────────────
    // AUTHORIZE
    // ──────────────────────────────────────────────────────────────

    public function authorize(): bool
    {
        // Hanya admin yang boleh membuat sport baru
        return $this->user()?->hasRole('admin') ?? false;
    }

    // ──────────────────────────────────────────────────────────────
    // RULES
    // ──────────────────────────────────────────────────────────────

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:100', 'unique:sports,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active'   => ['boolean'],
        ];
    }

    // ──────────────────────────────────────────────────────────────
    // MESSAGES
    // ──────────────────────────────────────────────────────────────

    public function messages(): array
    {
        return [
            'name.required' => 'Nama olahraga wajib diisi.',
            'name.unique'   => 'Nama olahraga [:input] sudah terdaftar. Gunakan nama yang berbeda.',
            'name.max'      => 'Nama olahraga maksimal 100 karakter.',
        ];
    }

    // ──────────────────────────────────────────────────────────────
    // ATTRIBUTES (label field untuk error message)
    // ──────────────────────────────────────────────────────────────

    public function attributes(): array
    {
        return [
            'name'        => 'nama olahraga',
            'description' => 'deskripsi',
            'is_active'   => 'status aktif',
        ];
    }
}
