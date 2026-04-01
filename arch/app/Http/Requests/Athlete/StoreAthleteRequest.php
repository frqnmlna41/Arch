<?php

namespace App\Http\Requests\Athlete;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * StoreAthleteRequest
 *
 * Artisan command:
 *   php artisan make:request Athlete/StoreAthleteRequest
 *
 * Authorization:
 *   admin → boleh membuat atlet siapapun
 *   coach → hanya boleh membuat atlet (coach_id otomatis = dirinya)
 *   athlete/judge → TIDAK boleh
 */
class StoreAthleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) return false;

        // Admin dan coach boleh membuat atlet
        return $user->hasAnyRole(['admin', 'coach']);
    }

    public function rules(): array
    {
        $user = $this->user();

        return [
            'name'           => ['required', 'string', 'max:150'],
            'birth_date'     => [
                'required',
                'date',
                'before:today',
                // Usia minimal 5 tahun (atlet terkecil)
                'before_or_equal:' . now()->subYears(5)->toDateString(),
            ],
            'gender'         => [
                'required',
                Rule::in(['male', 'female']),
            ],
            'club'           => ['nullable', 'string', 'max:150'],
            'phone'          => [
                'nullable',
                'string',
                'max:20',
                // Format nomor telepon Indonesia
                'regex:/^(\+62|62|0)[0-9]{8,13}$/',
            ],
            'photo'          => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048', // 2MB
            ],
            'id_card_number' => ['nullable', 'string', 'max:50'],
            'weight'         => ['nullable', 'numeric', 'min:10', 'max:300', 'decimal:0,2'],
            'height'         => ['nullable', 'numeric', 'min:50', 'max:250', 'decimal:0,2'],
            'address'        => ['nullable', 'string', 'max:500'],

            // coach_id: admin bisa set bebas, coach hanya bisa set dirinya sendiri
            'coach_id'       => [
                'nullable',
                'integer',
                'exists:users,id',
                // Jika coach, coach_id harus dirinya sendiri
                function (string $attribute, mixed $value, \Closure $fail) use ($user) {
                    if ($user->hasRole('coach') && $value && (int) $value !== $user->id) {
                        $fail('Coach hanya dapat mendaftarkan atlet untuk dirinya sendiri.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                   => 'Nama atlet wajib diisi.',
            'name.max'                        => 'Nama atlet maksimal 150 karakter.',
            'birth_date.required'             => 'Tanggal lahir wajib diisi.',
            'birth_date.before'               => 'Tanggal lahir harus sebelum hari ini.',
            'birth_date.before_or_equal'      => 'Atlet harus berusia minimal 5 tahun.',
            'gender.required'                 => 'Jenis kelamin wajib dipilih.',
            'gender.in'                       => 'Jenis kelamin harus Laki-laki (male) atau Perempuan (female).',
            'phone.regex'                     => 'Format nomor telepon tidak valid. Gunakan format: 08xx, +62xx, atau 62xx.',
            'photo.image'                     => 'File foto harus berupa gambar.',
            'photo.mimes'                     => 'Format foto harus: JPG, JPEG, PNG, atau WEBP.',
            'photo.max'                       => 'Ukuran foto maksimal 2MB.',
            'weight.min'                      => 'Berat badan minimal 10 kg.',
            'weight.max'                      => 'Berat badan maksimal 300 kg.',
            'height.min'                      => 'Tinggi badan minimal 50 cm.',
            'height.max'                      => 'Tinggi badan maksimal 250 cm.',
            'coach_id.exists'                 => 'Coach yang dipilih tidak ditemukan.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'           => 'nama atlet',
            'birth_date'     => 'tanggal lahir',
            'gender'         => 'jenis kelamin',
            'club'           => 'nama klub',
            'phone'          => 'nomor telepon',
            'photo'          => 'foto',
            'id_card_number' => 'nomor identitas',
            'weight'         => 'berat badan',
            'height'         => 'tinggi badan',
            'coach_id'       => 'coach',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                // Validasi: jika umur di bawah 10 tahun, photo wajib ada surat izin orang tua
                // (ini bisa dikembangkan sesuai kebutuhan)
                $birthDate = $this->input('birth_date');
                if ($birthDate) {
                    $age = \Carbon\Carbon::parse($birthDate)->age;
                    if ($age < 8) {
                        // Untuk kategori D/A (usia < 8), tambahkan flag ke request
                        // Validasi dokumen tambahan bisa ditambahkan di sini
                    }
                }
            },
        ];
    }
}
