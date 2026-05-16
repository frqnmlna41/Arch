<?php

namespace App\Http\Requests\Athlete;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAthleteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'user_id'         => ['nullable', 'integer', 'exists:users,id'],
            'coach_id'        => ['nullable', 'integer', 'exists:users,id'],
            'perguruan_id'    => ['nullable', 'integer', 'exists:perguruans,id'],
            'disciplines_id'  => ['nullable', 'integer', 'exists:disciplines,id'],
            'age_category_id' => ['nullable', 'integer', 'exists:age_categories,id'],
            'name'            => ['sometimes', 'string', 'max:150'],
            'birth_date'      => ['sometimes', 'date', 'before:today'],
            'gender'          => ['sometimes', 'in:male,female'],
            'club'            => ['nullable', 'string', 'max:150'],
            'phone'           => ['nullable', 'string', 'max:20'],
            'photo'           => ['nullable', 'string', 'max:255'],
            'id_card_number'  => ['nullable', 'string', 'max:50'],
            'weight'          => ['nullable', 'numeric', 'min:0'],
            'height'          => ['nullable', 'numeric', 'min:0'],
            'address'         => ['nullable', 'string'],
            'is_active'       => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'perguruan_id.exists'    => 'Perguruan yang dipilih tidak ditemukan.',
            'disciplines_id.exists'  => 'Disiplin yang dipilih tidak ditemukan.',
            'age_category_id.exists' => 'Kategori usia yang dipilih tidak ditemukan.',
            'birth_date.before'      => 'Tanggal lahir tidak boleh hari ini atau masa depan.',
            'gender.in'              => 'Gender harus salah satu dari: male atau female.',
        ];
    }
}
