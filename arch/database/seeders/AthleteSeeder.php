<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Athlete;
use App\Models\User;

/**
 * AthleteSeeder
 *
 * Membuat contoh data atlet untuk demo sistem.
 *
 * Artisan command:
 *   php artisan make:seeder AthleteSeeder
 *
 * PENTING: UserSeeder harus dijalankan sebelum ini.
 */
class AthleteSeeder extends Seeder
{
    public function run(): void
    {
        $coach       = User::where('email', 'coach@tournament.com')->firstOrFail();
        $athleteUser = User::where('email', 'athlete@tournament.com')->firstOrFail();

        $athletes = [
            // ── Atlet Wushu ─────────────────────────────────────
            [
                'user_id'        => $athleteUser->id,
                'coach_id'       => $coach->id,
                'name'           => 'Budi Santoso',
                'birth_date'     => '2010-03-15',   // 15 tahun → Wushu Kategori A
                'gender'         => 'male',
                'club'           => 'Perguruan Wushu Naga Mas Jakarta',
                'phone'          => '081234567890',
                'id_card_number' => '3174001503100001',
                'weight'         => 52.5,
                'height'         => 162.0,
                'address'        => 'Jl. Kebon Sirih No. 10, Jakarta Pusat',
                'is_active'      => true,
            ],
            [
                'user_id'    => null,
                'coach_id'   => $coach->id,
                'name'       => 'Siti Rahayu',
                'birth_date' => '2012-07-20',   // 13 tahun → Wushu Kategori B
                'gender'     => 'female',
                'club'       => 'Perguruan Wushu Naga Mas Jakarta',
                'phone'      => '081234567891',
                'weight'     => 45.0,
                'height'     => 155.0,
                'address'    => 'Jl. Sabang No. 5, Jakarta Pusat',
                'is_active'  => true,
            ],
            [
                'user_id'    => null,
                'coach_id'   => $coach->id,
                'name'       => 'Ahmad Fauzi',
                'birth_date' => '2016-01-10',   // 9 tahun → Wushu Kategori C
                'gender'     => 'male',
                'club'       => 'Perguruan Wushu Naga Mas Jakarta',
                'phone'      => '081234567892',
                'weight'     => 28.0,
                'height'     => 130.0,
                'address'    => 'Jl. Wahid Hasyim No. 3, Jakarta Pusat',
                'is_active'  => true,
            ],

            // ── Atlet Wing Chun ──────────────────────────────────
            [
                'user_id'    => null,
                'coach_id'   => $coach->id,
                'name'       => 'Kevin Wijaya',
                'birth_date' => '2000-05-22',   // 25 tahun → Wing Chun D1
                'gender'     => 'male',
                'club'       => 'Wing Chun Association Indonesia',
                'phone'      => '081234567893',
                'weight'     => 68.0,
                'height'     => 172.0,
                'address'    => 'Jl. Mangga Besar No. 20, Jakarta Barat',
                'is_active'  => true,
            ],
            [
                'user_id'    => null,
                'coach_id'   => $coach->id,
                'name'       => 'Dewi Anggraeni',
                'birth_date' => '2013-09-05',   // 12 tahun → Wing Chun C1
                'gender'     => 'female',
                'club'       => 'Wing Chun Association Indonesia',
                'phone'      => '081234567894',
                'weight'     => 40.0,
                'height'     => 148.0,
                'address'    => 'Jl. Pluit Selatan No. 8, Jakarta Utara',
                'is_active'  => true,
            ],
            [
                'user_id'    => null,
                'coach_id'   => $coach->id,
                'name'       => 'Rendra Pratama',
                'birth_date' => '1985-11-30',   // 40 tahun → Wing Chun E
                'gender'     => 'male',
                'club'       => 'Perguruan Kung Fu Nusantara',
                'phone'      => '081234567895',
                'weight'     => 75.0,
                'height'     => 170.0,
                'address'    => 'Jl. Cideng Barat No. 15, Jakarta Pusat',
                'is_active'  => true,
            ],
        ];

        foreach ($athletes as $athleteData) {
            $athlete = Athlete::firstOrCreate(
                [
                    'name'       => $athleteData['name'],
                    'birth_date' => $athleteData['birth_date'],
                ],
                $athleteData
            );

            $this->command->info(
                "  ✅ Athlete [{$athlete->name}] – {$athlete->gender} – {$athlete->club}"
            );
        }
    }
}
