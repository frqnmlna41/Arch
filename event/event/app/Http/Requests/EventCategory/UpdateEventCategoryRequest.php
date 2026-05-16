<?php

namespace App\Http\Requests\EventCategory;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'event_id'         => ['sometimes', 'integer', 'exists:events,id'],
            'sport_id'         => ['sometimes', 'integer', 'exists:sports,id'],
            'discipline_id'    => ['sometimes', 'integer', 'exists:disciplines,id'],
            'age_category_id'  => ['sometimes', 'integer', 'exists:age_categories,id'],
            'gender'           => ['sometimes', 'in:male,female,mixed'],
            'weight_class'     => ['nullable', 'string', 'max:50'],
            'max_participants' => ['nullable', 'integer', 'min:1'],
            'format'           => ['nullable', 'string', 'max:100'],
            'notes'            => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'event_id.exists'        => 'Event yang dipilih tidak ditemukan.',
            'discipline_id.exists'   => 'Disiplin yang dipilih tidak ditemukan.',
            'age_category_id.exists' => 'Kategori usia yang dipilih tidak ditemukan.',
            'gender.in'              => 'Gender harus salah satu dari: male, female, mixed.',
        ];
    }
}
