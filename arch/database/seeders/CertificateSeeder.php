<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Winner;
use App\Models\User;
use App\Models\Certificate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

/**
 * CertificateSeeder
 * Creates certificates for each winner
 * Dependencies: WinnerSeeder
 */
class CertificateSeeder extends Seeder
{
    public function run(): void
    {
        $admins = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->get();
        $issuer = $admins->first() ?? User::find(1);

        $winners = Winner::with('athlete')->get();

        DB::transaction(function () use ($winners, $issuer) {
            foreach ($winners as $winner) {
                Certificate::create([
                    'winner_id' => $winner->id,
                    'event_id' => $winner->event_id,
                    'certificate_number' => 'CERT-' . Carbon::now()->format('Y') . '-' . str_pad($winner->id, 4, '0', STR_PAD_LEFT),
                    'issued_date' => now(),
                    'issued_by' => $issuer->id,
                    'status' => 'printed',
                ]);
            }
        });

        $total = Certificate::count();
        $this->command->info("  📜 Total Certificates: {$total}");
    }
}
?>

