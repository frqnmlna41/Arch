<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Athlete;
use App\Models\User;
use App\Models\Perguruan;

/**
 * AthleteSeeder
 *
 * Membuat data atlet yang dimiliki oleh coach.
 * Coach mendaftarkan atlet miliknya ke dalam sistem.
 *
 * DEPENDENCIES: UserSeeder, PerguruanSeeder
 */
class AthleteSeeder extends Seeder
{
    public function run(): void
    {
        $coaches = User::role('coach')->get();
        $perguruans = Perguruan::all();

        if ($coaches->isEmpty()) {
            $this->command->error('❌ Tidak ada coach ditemukan. Jalankan UserSeeder terlebih dahulu.');
            return;
        }

        $coachList = $coaches->values();
        $faker = \Faker\Factory::create('id_ID');

        // Buat 100 atlet laki-laki secara random
        for ($i = 0; $i < 100; $i++) {
            $coach = $coachList[$i % $coachList->count()];
            $perguruan = $perguruans->isNotEmpty() ? $perguruans->random() : null;
            Athlete::create([
                'name'           => $faker->firstNameMale . ' ' . $faker->lastName,
                'coach_id'       => $coach->id,
                'perguruan_id'   => $perguruan?->id,
                'birth_date'     => $faker->dateTimeBetween('-20 years', '-10 years')->format('Y-m-d'),
                'gender'         => 'male',
                'club'           => 'Klub Wushu ' . $faker->city,
                'weight'         => $faker->randomFloat(1, 40, 75),
                'height'         => $faker->randomFloat(1, 140, 180),
                'id_card_number' => $faker->numerify('################'),
                'phone'          => $faker->numerify('08##########'),
                'address'        => $faker->address(),
                'is_active'      => true,
            ]);
        }

        // Buat 100 atlet perempuan secara random
        for ($i = 0; $i < 100; $i++) {
            $coach = $coachList[$i % $coachList->count()];
            $perguruan = $perguruans->isNotEmpty() ? $perguruans->random() : null;
            Athlete::create([
                'name'           => $faker->firstNameFemale . ' ' . $faker->lastName,
                'coach_id'       => $coach->id,
                'perguruan_id'   => $perguruan?->id,
                'birth_date'     => $faker->dateTimeBetween('-20 years', '-10 years')->format('Y-m-d'),
                'gender'         => 'female',
                'club'           => 'Klub Wushu ' . $faker->city,
                'weight'         => $faker->randomFloat(1, 38, 70),
                'height'         => $faker->randomFloat(1, 135, 175),
                'id_card_number' => $faker->numerify('################'),
                'phone'          => $faker->numerify('08##########'),
                'address'        => $faker->address(),
                'is_active'      => true,
            ]);
        }

        $this->command->info('✅ AthleteSeeder: ' . Athlete::count() . ' atlet dibuat.');
    }
}
