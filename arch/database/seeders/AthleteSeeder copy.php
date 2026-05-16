<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Athlete;
use App\Models\User;
use App\Models\Discipline;
use App\Models\AgeCategory;
use Illuminate\Support\Facades\Log;

class AthleteSeeder extends Seeder
{
    public function run(): void
    {
        $coach = User::role('coach')->first();
        $athleteUsers = User::role('athlete')->pluck('id')->toArray();

        $disciplines = Discipline::pluck('id')->toArray();
        $ageCategories = AgeCategory::pluck('id')->toArray();

        if (!$coach) {
            $this->command->error('❌ Coach tidak ditemukan');
            return;
        }

        if (empty($disciplines) || empty($ageCategories)) {
            $this->command->error('❌ Discipline / AgeCategory kosong');
            return;
        }

        $this->command->info("🚀 Generating 50 athletes...");

        for ($i = 1; $i <= 50; $i++) {

            try {
                $gender = fake()->randomElement(['male', 'female']);

                Athlete::create([
                    'user_id' => !empty($athleteUsers) && rand(0, 1)
                        ? fake()->randomElement($athleteUsers)
                        : null,

                    'coach_id' => $coach->id,
                    'disciplines_id' => fake()->randomElement($disciplines),
                    'age_category_id' => fake()->randomElement($ageCategories),

                    'name' => fake()->name(),
                    'birth_date' => fake()->dateTimeBetween('-25 years', '-8 years')->format('Y-m-d'),

                    'gender' => $gender,

                    'club' => fake()->randomElement([
                        'Garuda Wushu Club',
                        'Naga Mas Academy',
                        'Dragon Warrior School',
                        'Shaolin Indonesia',
                        'Tiger Martial Arts'
                    ]),

                    'phone' => fake()->phoneNumber(),
                    'photo' => null,
                    'id_card_number' => fake()->numerify('################'),

                    'weight' => fake()->randomFloat(2, 25, 90),
                    'height' => fake()->randomFloat(2, 120, 190),

                    'address' => fake()->address(),
                    'is_active' => fake()->boolean(90),
                ]);

                $this->command->info("✔ Athlete $i created");

            } catch (\Throwable $e) {
                $this->command->error("❌ Error at $i: " . $e->getMessage());

                Log::error('Athlete Seeder Error', [
                    'index' => $i,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->command->info("✅ 50 Athletes seeding completed");
    }
}
