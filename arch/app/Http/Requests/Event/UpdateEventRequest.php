<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * UpdateEventRequest
 *
 * Artisan command:
 *   php artisan make:request Event/UpdateEventRequest
 */
class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        $event = $this->route('event');

        // Jika event sudah completed, tidak boleh diupdate
        if (is_object($event) && $event->status === 'completed') {
            return ['_block' => ['required']]; // akan selalu gagal
        }

        return [
            'name'               => ['sometimes', 'string', 'max:200'],
            'location'           => ['sometimes', 'string', 'max:255'],
            'start_date'         => ['sometimes', 'date'],
            'end_date'           => ['sometimes', 'date', 'after_or_equal:start_date'],
            'registration_start' => ['nullable', 'date'],
            'registration_end'   => ['nullable', 'date', 'after_or_equal:registration_start'],
            'status'             => [
                'sometimes',
                Rule::in(['draft', 'published', 'ongoing', 'completed', 'cancelled']),
            ],
            'description'        => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            '_block.required'          => 'Event yang sudah selesai tidak dapat diubah.',
            'end_date.after_or_equal'  => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
            'registration_end.after_or_equal' => 'Tanggal tutup pendaftaran harus setelah tanggal buka.',
            'status.in'                => 'Status event tidak valid. Pilihan: draft, published, ongoing, completed, cancelled.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'               => 'nama event',
            'location'           => 'lokasi',
            'start_date'         => 'tanggal mulai',
            'end_date'           => 'tanggal selesai',
            'registration_start' => 'tanggal buka pendaftaran',
            'registration_end'   => 'tanggal tutup pendaftaran',
            'status'             => 'status',
        ];
    }
}
