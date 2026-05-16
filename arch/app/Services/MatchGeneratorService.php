
// ─────────────────────────────────────────────────────────────
// File: app/Services/MatchGeneratorService.php
// ─────────────────────────────────────────────────────────────
<?php

namespace App\Services;

use App\Models\AgeCategory;
use App\Models\Contest ;
use App\Models\Discipline;
// use App\Models\Match as Contest;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;

class MatchGeneratorService
{
    /**
     * Auto-generate jadwal match untuk satu discipline + age_category.
     * Hanya registrasi berstatus 'approved' yang diikutkan.
     * Urutan tampil: random / bisa diurutkan sesuai kebutuhan.
     */
    public function generate(
        Discipline  $discipline,
        AgeCategory $ageCategory,
        string      $matchDate,
        string      $matchTime,
        string      $venue,
        int         $intervalMinutes = 10
    ): array {
        return DB::transaction(function () use (
            $discipline, $ageCategory, $matchDate, $matchTime, $venue, $intervalMinutes
        ) {
            // Pastikan belum ada match untuk slot ini
            $exists = Contest::where('discipline_id', $discipline->id)
                ->where('age_category_id', $ageCategory->id)
                ->exists();

            if ($exists) {
                throw new \RuntimeException('Match untuk nomor dan kategori ini sudah digenerate.');
            }

            $registrations = Registration::where('discipline_id', $discipline->id)
                ->where('age_category_id', $ageCategory->id)
                ->where('status', 'approved')
                ->inRandomOrder() // urutan tampil random; bisa diganti ->orderBy('...')
                ->get();

            if ($registrations->isEmpty()) {
                throw new \RuntimeException('Tidak ada peserta approved untuk nomor ini.');
            }

            $matches       = [];
            $currentTime   = \Carbon\Carbon::parse("{$matchDate} {$matchTime}");

            foreach ($registrations as $order => $registration) {
                $match = Contest::create([
                    'registration_id' => $registration->id,
                    'discipline_id'   => $discipline->id,
                    'age_category_id' => $ageCategory->id,
                    'athlete_id'      => $registration->athlete_id,
                    'appearance_order' => $order + 1,
                    'match_date'      => $matchDate,
                    'match_time'      => $currentTime->format('H:i:s'),
                    'venue'           => $venue,
                    'status'          => 'scheduled',
                ]);

                $matches[] = $match;
                $currentTime->addMinutes($intervalMinutes);
            }

            return $matches;
        });
    }
}
