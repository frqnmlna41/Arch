<?php

namespace App\Http\Requests\Invoice;

use App\Enums\InvoiceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'status'       => ['sometimes', Rule::enum(InvoiceStatus::class)],
            'total_amount' => ['sometimes', 'numeric', 'min:0'],
            'due_date'     => ['nullable', 'date'],
            'paid_at'      => ['nullable', 'date'],
            'notes'        => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.enum'      => 'Status invoice tidak valid.',
            'total_amount.min' => 'Total jumlah invoice tidak boleh negatif.',
        ];
    }
}
