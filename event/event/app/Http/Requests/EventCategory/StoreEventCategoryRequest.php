<?php

namespace App\Http\Requests\EventCategory;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'event_id'         => ['required', 'integer', 'exists:events,id'],
            'sport_id'         => ['required', 'integer', 'exists:sports,id'],
            'discipline_id'    => ['required', 'integer', 'exists:disciplines,id'],
            'age_category_id'  => ['required', 'integer', 'exists:age_categories,id'],
            'gender'           => ['required', 'in:male,female,mixed'],
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
            'sport_id.exists'        => 'Sport yang dipilih tidak ditemukan.',
            'discipline_id.exists'   => 'Disiplin yang dipilih tidak ditemukan.',
            'age_category_id.exists' => 'Kategori usia yang dipilih tidak ditemukan.',
            'gender.in'              => 'Gender harus salah satu dari: male, female, mixed.',
        ];
    }
}
