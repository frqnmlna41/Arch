<?php

namespace App\Http\Requests\Invoice;

use App\Enums\InvoiceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'user_id'      => ['required', 'integer', 'exists:users,id'],
            'status'       => ['sometimes', Rule::enum(InvoiceStatus::class)],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'due_date'     => ['nullable', 'date'],
            'notes'        => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required'      => 'User wajib dipilih.',
            'user_id.exists'        => 'User yang dipilih tidak ditemukan.',
            'total_amount.required' => 'Total jumlah invoice wajib diisi.',
            'total_amount.min'      => 'Total jumlah invoice tidak boleh negatif.',
        ];
    }
}
