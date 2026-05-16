<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EventParticipant;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * EventParticipantSeeder
 *
 * Mensimulasikan ALUR ADMIN:
 *   Admin melihat daftar registrasi pending → Verifikasi peserta → Status berubah ke 'verified'
 *   Beberapa registrasi sengaja dibiarkan 'pending' untuk simulasi data antrian
 *
 * DEPENDENCIES: RegistrationSeeder
 */
class EventParticipantSeeder extends Seeder
{
    public function run(): void
    {
        $admin         = User::role('admin')->first();
        $registrations = Registration::with(['athlete', 'eventCategory'])->get();

        if ($registrations->isEmpty()) {
            $this->command->error('❌ Tidak ada registrasi. Jalankan RegistrationSeeder terlebih dahulu.');
            return;
        }

        if (! $admin) {
            $this->command->error('❌ Tidak ada admin. Jalankan UserSeeder terlebih dahulu.');
            return;
        }

        $created  = 0;
        $skipped  = 0;

        DB::transaction(function () use ($registrations, $admin, &$created, &$skipped) {

            $regNumber = EventParticipant::max('id') ?? 0; // untuk generate nomor urut

            foreach ($registrations as $registration) {

                // Cek duplikat berdasarkan unique constraint: (event_category_id, athlete_id)
                $alreadyExists = EventParticipant::where('event_category_id', $registration->event_category_id)
                    ->where('athlete_id', $registration->athlete_id)
                    ->exists();

                if ($alreadyExists) {
                    $skipped++;
                    continue;
                }

                // Simulasi: 80% registrasi langsung diverifikasi admin, 20% masih pending
                $isVerified = rand(1, 100) <= 80;

                $regNumber++;
                $regNum = 'WU-' . now()->year . '-' . str_pad($regNumber, 4, '0', STR_PAD_LEFT);

                EventParticipant::create([
                    'event_category_id'      => $registration->event_category_id,
                    'registration_id'        => $registration->id,
                    'athlete_id'             => $registration->athlete_id,
                    'registered_by'          => $registration->user_id,
                    'verified_by'            => $isVerified ? $admin->id : null,
                    'registration_number'    => $regNum,
                    'status'                 => $isVerified ? 'verified' : 'pending',
                    'weight_at_registration' => $registration->athlete->weight ?? rand(40, 70),
                    'verified_at'            => $isVerified ? now()->subDays(rand(1, 5)) : null,
                ]);

                // Update status registrasi sesuai
                $registration->update([
                    'status' => $isVerified ? 'approved' : 'pending',
                ]);

                $created++;
            }
        });

        $total    = EventParticipant::count();
        $verified = EventParticipant::where('status', 'verified')->count();
        $pending  = EventParticipant::where('status', 'pending')->count();

        $this->command->info("  ✅ Event Participants: {$created} dibuat, {$skipped} dilewati.");
        $this->command->info("     Verified: {$verified} | Pending: {$pending} | Total: {$total}");
    }
}
