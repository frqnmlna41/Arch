<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Discipline;
use App\Models\AgeCategory;
use App\Models\Sport;

/**
 * DisciplineAgeCategorySeeder
 *
 * Mengisi tabel pivot discipline_age_categories.
 * Menentukan kategori umur mana yang diizinkan untuk setiap discipline.
 *
 * Artisan command:
 *   php artisan make:seeder DisciplineAgeCategorySeeder
 *
 * ─────────────────────────────────────────────────────────────────
 * MAPPING WUSHU (kategori: D, C, B, A)
 * ─────────────────────────────────────────────────────────────────
 *
 * EMPTY HAND:
 *   Chang Quan         → D C B A
 *   Nan Quan           → D C B A
 *   Chu Ji Nan Quan    → C B        (hanya 2 kategori, tingkat pemula)
 *   Taiji Quan         →   C B A
 *   Wu Bu Quan (3)     → D C        (usia sangat muda)
 *   Wu Bu Quan (5)     →   C B A
 *
 * WEAPON:
 *   Jian Shu           →   C B A   (tidak untuk D, terlalu muda)
 *   Dao Shu            →   C B A
 *   Nan Dao            →   C B A
 *   Chu Ji Nan Dao     →   C B      (tingkat pemula)
 *   Taiji Jian         →   C B A
 *   Qiang Shu          →   C B A
 *   Gun Shu            →   C B A
 *   Nan Gun            →   C B A
 *   Chu Ji Nan Gun     →   C B      (tingkat pemula)
 *
 * ─────────────────────────────────────────────────────────────────
 * MAPPING WING CHUN (kategori: A, B, C1, C2, D1, D2, E, F)
 * ─────────────────────────────────────────────────────────────────
 *
 * EMPTY HAND:
 *   Taiji Quan         → A B C1 C2 D1 D2 E F   (semua kategori)
 *   Siu Nim Tau        → A B C1 C2 D1 D2         (s/d D2)
 *   Cham Kiu           →   B C1 C2 D1            (B hingga D1)
 *   Biu Jee            →   B C1 C2 D1            (B hingga D1)
 *   Xingyi Quan        →     C1 C2 D1 D2 E F
 *   Bagua Zhang        →     C1 C2 D1 D2 E F
 *   Baji Quan          →     C1 C2 D1 D2 E F
 *   Wuzu - Sam Cien    → A B C1 C2 D1 D2 E F   (semua)
 *   Wuzu - Ngo Ho Cien →     C1 C2 D1 D2 E F
 *   Wuzu - Ji Sip Kuen →         D1 D2 E F      (dewasa ke atas)
 *   Jurus Perguruan    → A B C1 C2 D1 D2 E F   (semua)
 *
 * WEAPON:
 *   Bart Jarm Dao      →       C2 D1 D2 E F    (15 tahun ke atas)
 *   Lok Dim Boon Gwan  →     C1 C2 D1 D2 E F  (12 tahun ke atas)
 *   Er Jie Gun         →     C1 C2 D1 D2 E F
 *   Shuang Er Jie Gun  →         D1 D2 E F      (dewasa)
 * ─────────────────────────────────────────────────────────────────
 */
class DisciplineAgeCategorySeeder extends Seeder
{
    public function run(): void
    {
        // Bersihkan pivot sebelum re-seed untuk menghindari duplikat
        DB::table('discipline_age_categories')->truncate();
        $this->command->info('  🗑️  Tabel pivot discipline_age_categories dikosongkan.');

        // ── WUSHU ─────────────────────────────────────────────────
        $this->command->info('  📌 Seeding Wushu discipline-age mappings...');
        $this->seedWushu();

        // ── WING CHUN ─────────────────────────────────────────────
        $this->command->info('  📌 Seeding Wing Chun discipline-age mappings...');
        $this->seedWingChun();

        $total = DB::table('discipline_age_categories')->count();
        $this->command->info("  ✅ Total pivot records: {$total}");
    }

    // ══════════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ══════════════════════════════════════════════════════════════

    /**
     * Seed mapping discipline → age category untuk WUSHU.
     */
    private function seedWushu(): void
    {
        $sport = Sport::where('name', 'Wushu')->firstOrFail();

        /**
         * Mapping: discipline_name → [age_category_names]
         * Nama kategori sesuai dengan yang di-seed di AgeCategorySeeder (Wushu): D, C, B, A
         */
        $mapping = [
            // EMPTY HAND
            'Chang Quan'            => ['D', 'C', 'B', 'A'],
            'Nan Quan'              => ['D', 'C', 'B', 'A'],
            'Chu Ji Nan Quan'       => ['C', 'B'],
            'Taiji Quan'            => ['C', 'B', 'A'],
            'Wu Bu Quan (3 Sessions)' => ['D', 'C'],
            'Wu Bu Quan (5 Sessions)' => ['C', 'B', 'A'],

            // WEAPON
            'Jian Shu'              => ['C', 'B', 'A'],
            'Dao Shu'               => ['C', 'B', 'A'],
            'Nan Dao'               => ['C', 'B', 'A'],
            'Chu Ji Nan Dao'        => ['C', 'B'],
            'Taiji Jian'            => ['C', 'B', 'A'],
            'Qiang Shu'             => ['C', 'B', 'A'],
            'Gun Shu'               => ['C', 'B', 'A'],
            'Nan Gun'               => ['C', 'B', 'A'],
            'Chu Ji Nan Gun'        => ['C', 'B'],
        ];

        $this->attachPivot($sport, $mapping);
    }

    /**
     * Seed mapping discipline → age category untuk WING CHUN.
     */
    private function seedWingChun(): void
    {
        $sport = Sport::where('name', 'Wing Chun')->firstOrFail();

        /**
         * Nama kategori sesuai AgeCategorySeeder (Wing Chun):
         * A, B, C1, C2, D1, D2, E, F
         */
        $allCats    = ['A', 'B', 'C1', 'C2', 'D1', 'D2', 'E', 'F'];
        $adultPlus  = ['D1', 'D2', 'E', 'F'];
        $teen       = ['C1', 'C2', 'D1', 'D2', 'E', 'F'];
        $youngAdult = ['B', 'C1', 'C2', 'D1'];

        $mapping = [
            // EMPTY HAND
            'Taiji Quan'                  => $allCats,
            'Wing Chun - Siu Nim Tau'     => ['A', 'B', 'C1', 'C2', 'D1', 'D2'],
            'Wing Chun - Cham Kiu'        => $youngAdult,
            'Wing Chun - Biu Jee'         => $youngAdult,
            'Xingyi Quan'                 => $teen,
            'Bagua Zhang'                 => $teen,
            'Baji Quan'                   => $teen,
            'Wuzu Quan - Sam Cien'        => $allCats,
            'Wuzu Quan - Ngo Ho Cien'     => $teen,
            'Wuzu Quan - Ji Sip Kuen'     => $adultPlus,
            'Jurus Perguruan'             => $allCats,

            // WEAPON
            'Wing Chun - Bart Jarm Dao'   => ['C2', 'D1', 'D2', 'E', 'F'],
            'Wing Chun - Lok Dim Boon Gwan' => ['C1', 'C2', 'D1', 'D2', 'E', 'F'],
            'Er Jie Gun'                  => ['C1', 'C2', 'D1', 'D2', 'E', 'F'],
            'Shuang Er Jie Gun'           => $adultPlus,
        ];

        $this->attachPivot($sport, $mapping);
    }

    /**
     * Helper: insert ke tabel pivot discipline_age_categories
     * berdasarkan mapping [discipline_name => [age_category_names]].
     *
     * @param  Sport  $sport
     * @param  array  $mapping
     */
    private function attachPivot(Sport $sport, array $mapping): void
    {
        foreach ($mapping as $disciplineName => $ageCategoryNames) {
            $discipline = Discipline::where('sport_id', $sport->id)
                ->where('name', $disciplineName)
                ->first();

            if (! $discipline) {
                $this->command->warn(
                    "  ⚠️  Discipline tidak ditemukan: [{$sport->name}] {$disciplineName}"
                );
                continue;
            }

            $rows     = [];
            $attached = [];

            foreach ($ageCategoryNames as $ageName) {
                $ageCategory = AgeCategory::where('sport_id', $sport->id)
                    ->where('name', $ageName)
                    ->first();

                if (! $ageCategory) {
                    $this->command->warn(
                        "  ⚠️  AgeCategory tidak ditemukan: [{$sport->name}] {$ageName}"
                    );
                    continue;
                }

                $rows[] = [
                    'discipline_id'   => $discipline->id,
                    'age_category_id' => $ageCategory->id,
                ];
                $attached[] = $ageName;
            }

            if (! empty($rows)) {
                // insertOrIgnore mencegah duplikat jika seeder dijalankan ulang
                DB::table('discipline_age_categories')->insertOrIgnore($rows);

                $this->command->line(
                    "    → [{$sport->name}] {$disciplineName}: ["
                    . implode(', ', $attached) . ']'
                );
            }
        }
    }
}
