<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Invoice;
use App\Models\Registration;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;

/**
 * InvoiceItemSeeder
 * Creates line items for each registration
 * Dependencies: InvoiceSeeder, RegistrationSeeder
 */
class InvoiceItemSeeder extends Seeder
{
    public function run(): void
    {
        $invoices = Invoice::with('coach')->get();
        $registrations = Registration::with('athlete')->get();

        DB::transaction(function () use ($invoices, $registrations) {
            foreach ($invoices as $invoice) {
                // Items for this coach's registrations
                $coachItems = $registrations->where('coach_id', $invoice->coach_id);

                foreach ($coachItems as $reg) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'athlete_id' => $reg->athlete_id,
                        'event_category_id' => $reg->eventParticipant->event_category_id ?? 1, // fallback
                        'discipline_id' => $reg->discipline_id,
                        'description' => 'Pendaftaran ' . $reg->athlete->name,
                        'quantity' => 1,
                        'unit_price' => 50000,
                        'total_price' => 50000,
                    ]);
                }
            }
        });

        $total = InvoiceItem::count();
        $this->command->info("  ✅ Total Invoice Items: {$total}");
    }
}
?>

