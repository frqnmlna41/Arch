<?php

namespace App\Http\Requests\Registration;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegistrationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'user_id'           => ['nullable', 'integer', 'exists:users,id'],
            'athlete_id'        => ['required', 'integer', 'exists:athletes,id'],
            'event_category_id' => ['required', 'integer', 'exists:event_categories,id'],
            'notes'             => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'athlete_id.required'        => 'Atlet wajib dipilih.',
            'athlete_id.exists'          => 'Atlet yang dipilih tidak ditemukan.',
            'event_category_id.required' => 'Kategori event wajib dipilih.',
            'event_category_id.exists'   => 'Kategori event yang dipilih tidak ditemukan.',
        ];
    }
}
