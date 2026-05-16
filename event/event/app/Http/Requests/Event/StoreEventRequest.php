<?php

namespace App\Http\Requests\Event;

use App\Enums\EventStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'               => ['required', 'string', 'max:200'],
            'location'           => ['required', 'string', 'max:255'],
            'start_date'         => ['required', 'date'],
            'end_date'           => ['required', 'date', 'after_or_equal:start_date'],
            'registration_start' => ['nullable', 'date'],
            'registration_end'   => ['nullable', 'date', 'after_or_equal:registration_start'],
            'description'        => ['nullable', 'string'],
            'status'             => ['sometimes', Rule::enum(EventStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'end_date.after_or_equal'           => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'registration_end.after_or_equal'   => 'Tanggal tutup pendaftaran tidak boleh sebelum tanggal buka.',
        ];
    }
}
