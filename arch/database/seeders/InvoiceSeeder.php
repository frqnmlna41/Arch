<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

/**
 * InvoiceSeeder
 * Creates invoices for coaches based on registrations
 * Dependencies: RegistrationSeeder
 */
class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $coaches = User::whereHas('roles', fn($q) => $q->where('name', 'coach'))->get();

        DB::transaction(function () use ($coaches) {
            foreach ($coaches as $coach) {
                // 1 invoice per coach for their athletes
$registrations = Registration::where('user_id', $coach->id)->get();

                if ($registrations->isNotEmpty()) {
                    $total = $registrations->count() * 50000; // Rp50k per athlete

                    $invoice = Invoice::create([
'coach_id' => $coach->id, // FK to users.coach_id ?
                        'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . str_pad($coach->id, 3, '0', STR_PAD_LEFT),
                        'total_amount' => $total,
'status' => 'draft',
                        'due_date' => Carbon::now()->addDays(7),
                        'notes' => 'Invoice for Kejuaraan Wushu Nasional 2024',
                    ]);

                    $this->command->info("  ✅ Invoice #{$invoice->id} for Coach {$coach->name}: Rp " . number_format($total));
                }
            }
        });
    }
}
?>

