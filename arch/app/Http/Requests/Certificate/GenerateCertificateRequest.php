<?php

namespace App\Http\Requests\Certificate;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * GenerateCertificateRequest
 *
 * Artisan command:
 *   php artisan make:request Certificate/GenerateCertificateRequest
 *
 * Authorization: hanya admin yang boleh generate sertifikat.
 *
 * Sertifikat hanya bisa digenerate jika:
 *  - winner sudah ada
 *  - event sudah completed
 *  - sertifikat belum pernah digenerate untuk winner ini
 */
class GenerateCertificateRequest extends FormRequest
{
    // ──────────────────────────────────────────────────────────────
    // AUTHORIZE
    // ──────────────────────────────────────────────────────────────

    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    // ──────────────────────────────────────────────────────────────
    // RULES
    // ──────────────────────────────────────────────────────────────

    public function rules(): array
    {
        return [
            'winner_id' => [
                'required',
                'integer',
                'exists:winners,id',
            ],

            // certificate_number opsional (akan di-generate otomatis jika kosong)
            'certificate_number' => [
                'nullable',
                'string',
                'max:100',
                // Unik di tabel certificates
                Rule::unique('certificates', 'certificate_number'),
            ],

            // Opsional: path file jika sudah ada file PDF sebelumnya
            'file_path' => [
                'nullable',
                'string',
                'max:500',
            ],

            // Versi template yang digunakan
            'template_version' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^\d+\.\d+$/', // format: "1.0", "2.1"
            ],

            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    // ──────────────────────────────────────────────────────────────
    // MESSAGES
    // ──────────────────────────────────────────────────────────────

    public function messages(): array
    {
        return [
            'winner_id.required'             => 'Pemenang wajib dipilih.',
            'winner_id.exists'               => 'Data pemenang tidak ditemukan.',
            'certificate_number.unique'      => 'Nomor sertifikat [:input] sudah digunakan. Gunakan nomor yang berbeda.',
            'certificate_number.max'         => 'Nomor sertifikat maksimal 100 karakter.',
            'template_version.regex'         => 'Format versi template tidak valid. Gunakan format: 1.0, 2.1, dst.',
        ];
    }

    // ──────────────────────────────────────────────────────────────
    // ATTRIBUTES
    // ──────────────────────────────────────────────────────────────

    public function attributes(): array
    {
        return [
            'winner_id'          => 'pemenang',
            'certificate_number' => 'nomor sertifikat',
            'file_path'          => 'path file',
            'template_version'   => 'versi template',
            'notes'              => 'catatan',
        ];
    }

    // ──────────────────────────────────────────────────────────────
    // AFTER – validasi bisnis
    // ──────────────────────────────────────────────────────────────

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $winnerId = $this->input('winner_id');
                if (! $winnerId) return;

                $winner = \App\Models\Winner::with('event', 'athlete')->find($winnerId);
                if (! $winner) return;

                // Sertifikat tidak boleh digenerate dua kali untuk winner yang sama
                $alreadyIssued = \App\Models\Certificate::where('winner_id', $winnerId)->exists();
                if ($alreadyIssued) {
                    $athleteName = $winner->athlete?->name ?? "ID:{$winner->athlete_id}";
                    $validator->errors()->add(
                        'winner_id',
                        "Sertifikat untuk [{$athleteName}] sudah pernah diterbitkan. " .
                        "Gunakan endpoint download untuk mengunduh sertifikat yang ada."
                    );
                }

                // Event harus sudah completed untuk generate sertifikat
                $event = $winner->event;
                if ($event && $event->status !== 'completed') {
                    $validator->errors()->add(
                        'winner_id',
                        "Sertifikat hanya bisa digenerate setelah event berstatus 'completed'. " .
                        "Status event [{$event->name}] saat ini: [{$event->status}]."
                    );
                }

                // Auto-generate certificate_number jika tidak diisi
                if (! $this->input('certificate_number') && ! $validator->errors()->has('winner_id')) {
                    $year       = now()->year;
                    $lastCount  = \App\Models\Certificate::whereYear('created_at', $year)->count();
                    $certNumber = sprintf('CERT-%d-%05d', $year, $lastCount + 1);
                    $this->merge(['certificate_number' => $certNumber]);
                }

                // Auto-set template_version jika tidak diisi
                if (! $this->input('template_version')) {
                    $this->merge(['template_version' => '1.0']);
                }
            },
        ];
    }
}
