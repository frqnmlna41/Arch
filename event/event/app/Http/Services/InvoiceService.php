<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\RegistrationPaymentStatus;
use App\Enums\RegistrationStatus;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Registration;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvoiceService
{
    /**
     * Default registration fee per category (can be moved to config/settings table).
     */
    private const DEFAULT_REGISTRATION_FEE = 150_000;

    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Invoice::query()
            ->with(['user', 'items.registration.athlete'])
            ->when($filters['status'] ?? null, fn($q, $v) => $q->where('status', $v))
            ->when($filters['user_id'] ?? null, fn($q, $v) => $q->where('user_id', $v))
            ->latest()
            ->paginate($perPage);
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getById(int $id): Invoice
    {
        return Invoice::with([
            'user',
            'items.registration.athlete.perguruan',
            'items.registration.eventCategory.event',
            'items.registration.eventCategory.discipline',
        ])->findOrFail($id);
    }

    /**
     * Manually create an invoice (without auto-linking registrations).
     */
    public function create(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            $data['invoice_number'] = $this->generateInvoiceNumber();
            $data['status']         = $data['status'] ?? InvoiceStatus::Unpaid->value;

            return Invoice::create($data);
        });
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update(int $id, array $data): Invoice
    {
        return DB::transaction(function () use ($id, $data) {
            $invoice = Invoice::findOrFail($id);
            $invoice->update($data);
            return $invoice->fresh(['user', 'items']);
        });
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function delete(int $id): void
    {
        DB::transaction(fn() => Invoice::findOrFail($id)->delete());
    }

    /**
     * Generate a single invoice from multiple approved registrations.
     *
     * Business rules enforced:
     *  1. All registrations must belong to the same user.
     *  2. All registrations must have status = approved.
     *  3. None of the registrations should already have an invoice item.
     *
     * After creation, each registration's payment_status is set to 'pending'.
     *
     * @param  array<int>  $registrationIds
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function generateFromRegistrations(
        array $registrationIds,
        ?string $dueDate = null,
        ?string $notes = null,
    ): Invoice {
        return DB::transaction(function () use ($registrationIds, $dueDate, $notes) {
            $registrations = Registration::with([
                'athlete',
                'eventCategory.event',
                'eventCategory.discipline',
                'invoiceItem',
            ])->findMany($registrationIds);

            // --- Guard: all IDs must exist ---
            if ($registrations->count() !== count($registrationIds)) {
                throw ValidationException::withMessages([
                    'registration_ids' => ['Beberapa ID registrasi tidak ditemukan.'],
                ]);
            }

            // --- Guard: all must be approved ---
            $notApproved = $registrations->filter(
                fn($r) => $r->status !== RegistrationStatus::Approved
            );
            if ($notApproved->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'registration_ids' => [
                        'Semua registrasi harus berstatus "approved" sebelum dapat di-invoice. ' .
                        'ID tidak valid: ' . $notApproved->pluck('id')->implode(', '),
                    ],
                ]);
            }

            // --- Guard: none must already have an invoice item ---
            $alreadyInvoiced = $registrations->filter(fn($r) => $r->invoiceItem !== null);
            if ($alreadyInvoiced->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'registration_ids' => [
                        'Beberapa registrasi sudah memiliki invoice. ' .
                        'ID sudah ter-invoice: ' . $alreadyInvoiced->pluck('id')->implode(', '),
                    ],
                ]);
            }

            // --- Guard: all must belong to the same user ---
            $uniqueUsers = $registrations->pluck('user_id')->unique();
            if ($uniqueUsers->count() > 1) {
                throw ValidationException::withMessages([
                    'registration_ids' => ['Semua registrasi harus dimiliki oleh user yang sama.'],
                ]);
            }

            $userId = $uniqueUsers->first();
            $total  = 0;

            // --- Create Invoice ---
            $invoice = Invoice::create([
                'user_id'        => $userId,
                'invoice_number' => $this->generateInvoiceNumber(),
                'status'         => InvoiceStatus::Unpaid,
                'total_amount'   => 0, // will be updated below
                'due_date'       => $dueDate,
                'notes'          => $notes,
            ]);

            // --- Create InvoiceItems ---
            foreach ($registrations as $registration) {
                $fee         = self::DEFAULT_REGISTRATION_FEE;
                $description = sprintf(
                    'Biaya Pendaftaran: %s – %s (%s)',
                    $registration->athlete->name,
                    $registration->eventCategory->event->name ?? '-',
                    $registration->eventCategory->discipline->name ?? '-',
                );

                InvoiceItem::create([
                    'invoice_id'      => $invoice->id,
                    'registration_id' => $registration->id,
                    'description'     => $description,
                    'amount'          => $fee,
                    'quantity'        => 1,
                    'subtotal'        => $fee,
                ]);

                $total += $fee;

                // Mark registration payment as pending
                $registration->update([
                    'payment_status' => RegistrationPaymentStatus::Pending,
                ]);
            }

            // --- Update total ---
            $invoice->update(['total_amount' => $total]);

            return $invoice->fresh(['items.registration.athlete', 'user']);
        });
    }

    /**
     * Generate a unique invoice number: INV-YYYYMMDD-XXXXX
     */
    private function generateInvoiceNumber(): string
    {
        do {
            $number = 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));
        } while (Invoice::where('invoice_number', $number)->exists());

        return $number;
    }
}
