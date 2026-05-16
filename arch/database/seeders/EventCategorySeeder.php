<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\Sport;
use App\Models\Discipline;
use App\Models\AgeCategory;
use App\Models\EventCategory;
use Illuminate\Support\Facades\DB;

/**
 * EventCategorySeeder
 * Creates 4 categories for demo Event ID=1
 * Dependencies: EventSeeder, SportSeeder, DisciplineSeeder, AgeCategorySeeder
 */
class EventCategorySeeder extends Seeder
{
    public function run(): void
    {
        $event = Event::firstOrFail(); // ID=1 from EventSeeder
        $wushuSport = Sport::where('name', 'Wushu')->firstOrFail();
        $wingchunSport = Sport::where('name', 'Kung fu')->firstOrFail();

        // Get first discipline & age category for each sport (simplified matching)
        $wushuDiscipline = Discipline::where('sport_id', $wushuSport->id)->firstOrFail();
        $wushuAgeCat = AgeCategory::where('sport_id', $wushuSport->id)->firstOrFail();
        $wingchunDiscipline = Discipline::where('sport_id', $wingchunSport->id)->firstOrFail();
        $wingchunAgeCat = AgeCategory::where('sport_id', $wingchunSport->id)->firstOrFail();

        $categories = [
            [
                'event_id' => $event->id,
                'sport_id' => $wushuSport->id,
                'discipline_id' => $wushuDiscipline->id,
                'age_category_id' => $wushuAgeCat->id,
                'gender' => 'male',
                'weight_class' => 'null',
                'max_participants' => 100,
                'format' => 'scoring',
                'notes' => 'Wushu Taolu Putra',
            ],
            [
                'event_id' => $event->id,
                'sport_id' => $wushuSport->id,
                'discipline_id' => $wushuDiscipline->id,
                'age_category_id' => $wushuAgeCat->id,
                'gender' => 'female',
                // 'weight_class' => 'null',
                'max_participants' => 100,
                'format' => 'scoring',
                'notes' => 'Wushu Taolu Putri',
            ],
            [
                'event_id' => $event->id,
                'sport_id' => $wingchunSport->id,
                'discipline_id' => $wingchunDiscipline->id,
                'age_category_id' => $wingchunAgeCat->id,
                'gender' => 'male',
                'max_participants' => 100,
                'format' => 'scoring',
                'notes' => 'Kung fu Form Putra',
            ],
            [
                'event_id' => $event->id,
                'sport_id' => $wingchunSport->id,
                'discipline_id' => $wingchunDiscipline->id,
                'age_category_id' => $wingchunAgeCat->id,
                'gender' => 'female',
                'max_participants' => 100,
                'format' => 'scoring',
                'notes' => 'Kung fu Form Putri',
            ],
        ];

        DB::transaction(function () use ($categories) {
            foreach ($categories as $data) {
                $category = EventCategory::firstOrCreate(
                    [
                        'event_id' => $data['event_id'],
                        'discipline_id' => $data['discipline_id'],
                        'gender' => $data['gender'],
                    ],
                    $data
                );
                $this->command->info("  ✅ EventCategory #{$category->id}: {$category->notes}");
            }
        });
    }
}
?>

