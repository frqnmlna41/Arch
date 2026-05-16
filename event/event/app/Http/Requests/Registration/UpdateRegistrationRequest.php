<?php

namespace App\Http\Requests\Registration;

use App\Enums\RegistrationPaymentStatus;
use App\Enums\RegistrationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRegistrationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'status'         => ['sometimes', Rule::enum(RegistrationStatus::class)],
            'payment_status' => ['sometimes', Rule::enum(RegistrationPaymentStatus::class)],
            'notes'          => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.enum'         => 'Status registrasi tidak valid.',
            'payment_status.enum' => 'Status pembayaran tidak valid.',
        ];
    }
}
