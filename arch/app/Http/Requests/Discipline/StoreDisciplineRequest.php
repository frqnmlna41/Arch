<?php

namespace App\Http\Requests\Discipline;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * StoreDisciplineRequest
 *
 * Artisan command:
 *   php artisan make:request Discipline/StoreDisciplineRequest
 */
class StoreDisciplineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'sport_id'   => [
                'required',
                'integer',
                'exists:sports,id',
            ],
            'name'       => [
                'required',
                'string',
                'max:100',
                // Nama discipline unik per sport
                Rule::unique('disciplines', 'name')
                    ->where('sport_id', $this->input('sport_id')),
            ],
            'type'       => [
                'required',
                'string',
                Rule::in(['empty_hand', 'weapon']),
            ],
            'match_type' => [
                'required',
                'string',
                Rule::in(['performance', 'sparring']),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active'   => ['boolean'],

            // Kategori umur yang diizinkan (opsional saat create)
            'age_category_ids'   => ['nullable', 'array'],
            'age_category_ids.*' => [
                'integer',
                // Pastikan age_category milik sport yang sama
                Rule::exists('age_categories', 'id')->where('sport_id', $this->input('sport_id')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'sport_id.required'        => 'Cabang olahraga wajib dipilih.',
            'sport_id.exists'          => 'Cabang olahraga yang dipilih tidak valid.',
            'name.required'            => 'Nama discipline wajib diisi.',
            'name.unique'              => 'Nama discipline [:input] sudah ada dalam cabang olahraga ini.',
            'name.max'                 => 'Nama discipline maksimal 100 karakter.',
            'type.required'            => 'Tipe discipline wajib dipilih.',
            'type.in'                  => 'Tipe discipline harus salah satu dari: Tangan Kosong (empty_hand) atau Senjata (weapon).',
            'match_type.required'      => 'Tipe pertandingan wajib dipilih.',
            'match_type.in'            => 'Tipe pertandingan harus salah satu dari: Penampilan (performance) atau Sparring (sparring).',
            'age_category_ids.array'   => 'Kategori umur harus berupa daftar.',
            'age_category_ids.*.exists'=> 'Salah satu kategori umur tidak valid atau bukan milik cabang olahraga ini.',
        ];
    }

    public function attributes(): array
    {
        return [
            'sport_id'         => 'cabang olahraga',
            'name'             => 'nama discipline',
            'type'             => 'tipe',
            'match_type'       => 'tipe pertandingan',
            'description'      => 'deskripsi',
            'age_category_ids' => 'kategori umur',
        ];
    }
}
