<?php

namespace App\Http\Requests\Athlete;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreAthleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::user()->hasRole('coach');
    }

    public function rules(): array
    {
        $coachId = Auth::id();

        return [
            'name'       => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date', 'before:today'],
            'gender'     => ['required', Rule::in(['male', 'female'])],
            'club'       => ['nullable', 'string', 'max:255'],
            'photo'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

            // Array disciplines wajib ada minimal 1
            'disciplines'                      => ['required', 'array', 'min:1'],
            'disciplines.*.discipline_id'      => [
                'required',
                'integer',
                Rule::exists('disciplines', 'id'),
            ],
            'disciplines.*.age_category_id'    => [
                'required',
                'integer',
                Rule::exists('age_categories', 'id'),
            ],

            // Kombinasi athlete name + discipline + age_category unik per coach
            // (cukup dicek di service/controller jika perlu, tapi minimal ini dulu)
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                          => 'Nama athlete wajib diisi.',
            'birth_date.required'                    => 'Tanggal lahir wajib diisi.',
            'birth_date.before'                      => 'Tanggal lahir harus sebelum hari ini.',
            'gender.required'                        => 'Jenis kelamin wajib dipilih.',
            'photo.image'                            => 'File harus berupa gambar.',
            'photo.max'                              => 'Ukuran foto maksimal 2MB.',
            'disciplines.required'                   => 'Minimal satu disiplin harus dipilih.',
            'disciplines.min'                        => 'Minimal satu disiplin harus dipilih.',
            'disciplines.*.discipline_id.required'   => 'Nomor tanding wajib dipilih.',
            'disciplines.*.discipline_id.exists'     => 'Nomor tanding tidak valid.',
            'disciplines.*.age_category_id.required' => 'Kategori usia wajib dipilih.',
            'disciplines.*.age_category_id.exists'   => 'Kategori usia tidak valid.',
        ];
    }
}
