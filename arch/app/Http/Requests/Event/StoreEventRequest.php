<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * StoreEventRequest
 *
 * Artisan command:
 *   php artisan make:request Event/StoreEventRequest
 */
class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'name'               => ['required', 'string', 'max:200'],
            'location'           => ['required', 'string', 'max:255'],
            'start_date'         => ['required', 'date', 'after_or_equal:today'],
            'end_date'           => [
                'required',
                'date',
                'after_or_equal:start_date',
            ],
            'registration_start' => [
                'nullable',
                'date',
                'before_or_equal:start_date',
            ],
            'registration_end'   => [
                'nullable',
                'date',
                'after_or_equal:registration_start',
                'before_or_equal:start_date',
            ],
            'status'             => [
                'sometimes',
                Rule::in(['draft', 'published']),
            ],
            'description'        => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'               => 'Nama event wajib diisi.',
            'name.max'                    => 'Nama event maksimal 200 karakter.',
            'location.required'           => 'Lokasi event wajib diisi.',
            'start_date.required'         => 'Tanggal mulai event wajib diisi.',
            'start_date.date'             => 'Format tanggal mulai tidak valid.',
            'start_date.after_or_equal'   => 'Tanggal mulai tidak boleh di masa lalu.',
            'end_date.required'           => 'Tanggal selesai event wajib diisi.',
            'end_date.date'               => 'Format tanggal selesai tidak valid.',
            'end_date.after_or_equal'     => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
            'registration_start.before_or_equal' => 'Tanggal buka pendaftaran harus sebelum atau sama dengan tanggal mulai event.',
            'registration_end.after_or_equal'    => 'Tanggal tutup pendaftaran harus setelah atau sama dengan tanggal buka.',
            'registration_end.before_or_equal'   => 'Tanggal tutup pendaftaran harus sebelum atau sama dengan tanggal mulai event.',
            'status.in'                   => 'Status event hanya boleh: draft atau published.',
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
            'description'        => 'deskripsi',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $startDate = $this->input('start_date');
                $endDate   = $this->input('end_date');
                $regEnd    = $this->input('registration_end');

                // Validasi tambahan: durasi event maksimal 30 hari
                if ($startDate && $endDate) {
                    $duration = \Carbon\Carbon::parse($startDate)->diffInDays($endDate);
                    if ($duration > 30) {
                        $validator->errors()->add(
                            'end_date',
                            "Durasi event terlalu panjang ({$duration} hari). Maksimal 30 hari."
                        );
                    }
                }
            },
        ];
    }
}
