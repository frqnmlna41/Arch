<?php

namespace App\Services;

use App\Models\Coach;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    /**
     * Buat invoice untuk satu coach berdasarkan semua registrasi pending-nya.
     * Dipanggil ketika coach submit pendaftaran atau admin approve.
     */
    public function generateForCoach(Coach $coach): Invoice
    {
        return DB::transaction(function () use ($coach) {

            // Ambil registrasi yang sudah approved dan belum punya invoice item
            $registrations = Registration::where('coach_id', $coach->id)
                ->where('status', 'approved')
                ->whereDoesntHave('invoiceItem')
                ->with('discipline')
                ->get();

            if ($registrations->isEmpty()) {
                throw new \RuntimeException('Tidak ada registrasi yang bisa diinvoice.');
            }

            $invoice = Invoice::create([
                'coach_id'       => $coach->id,
                'invoice_number' => Invoice::generateNumber(),
                'total_amount'   => 0,
                'status'         => 'unpaid',
                'due_date'       => now()->addDays(7),
            ]);

            foreach ($registrations as $registration) {
                InvoiceItem::create([
                    'invoice_id'      => $invoice->id,
                    'registration_id' => $registration->id,
                    'athlete_id'      => $registration->athlete_id,
                    'discipline_id'   => $registration->discipline_id,
                    // Snapshot harga saat ini
                    'price'           => $registration->discipline->price,
                ]);
            }

            // Update total
            $invoice->recalculateTotal();

            return $invoice->fresh('items');
        });
    }
}
