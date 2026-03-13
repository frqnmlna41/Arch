<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AgeCategory;
use App\Models\Sport;

/**
 * AgeCategorySeeder
 *
 * Membuat kategori umur untuk setiap sport.
 * Kategori umur berbeda antara Wushu dan Wing Chun.
 *
 * Artisan command:
 *   php artisan make:seeder AgeCategorySeeder
 *
 * ─────────────────────────────────────
 * WUSHU:
 *   D  → < 8  tahun
 *   C  → 9–11 tahun
 *   B  → 12–14 tahun
 *   A  → 15–18 tahun
 *
 * WING CHUN:
 *   A  → < 8   tahun
 *   B  → 9–11  tahun
 *   C1 → 12–14 tahun
 *   C2 → 15–17 tahun
 *   D1 → 18–27 tahun
 *   D2 → 28–39 tahun
 *   E  → 40–59 tahun
 *   F  → 60+   tahun
 * ─────────────────────────────────────
 *
 * CATATAN SKEMA:
 * Tabel age_categories memiliki kolom sport_id (FK ke sports)
 * agar kategori umur bisa berbeda per olahraga.
 * Jika skema Anda menggunakan tabel age_categories tanpa sport_id
 * (shared), sesuaikan dengan menghapus 'sport_id' dari data.
 */
class AgeCategorySeeder extends Seeder
{
    /**
     * Definisi age categories per sport.
     * min_age = 0 artinya tidak ada batas bawah (mulai dari bayi).
     * max_age = 999 artinya tidak ada batas atas (60+).
     */
    private array $categories = [
        'Wushu' => [
            [
                'name'        => 'D',
                'label'       => 'Kategori D (Di bawah 8 tahun)',
                'min_age'     => 0,
                'max_age'     => 8,
                'description' => 'Wushu – Kategori D: Peserta berusia di bawah 8 tahun.',
                'is_active'   => true,
            ],
            [
                'name'        => 'C',
                'label'       => 'Kategori C (9–11 tahun)',
                'min_age'     => 9,
                'max_age'     => 11,
                'description' => 'Wushu – Kategori C: Peserta berusia 9 hingga 11 tahun.',
                'is_active'   => true,
            ],
            [
                'name'        => 'B',
                'label'       => 'Kategori B (12–14 tahun)',
                'min_age'     => 12,
                'max_age'     => 14,
                'description' => 'Wushu – Kategori B: Peserta berusia 12 hingga 14 tahun.',
                'is_active'   => true,
            ],
            [
                'name'        => 'A',
                'label'       => 'Kategori A (15–18 tahun)',
                'min_age'     => 15,
                'max_age'     => 18,
                'description' => 'Wushu – Kategori A: Peserta berusia 15 hingga 18 tahun.',
                'is_active'   => true,
            ],
        ],

        'Wing Chun' => [
            [
                'name'        => 'A',
                'label'       => 'Kategori A (Di bawah 8 tahun)',
                'min_age'     => 0,
                'max_age'     => 8,
                'description' => 'Wing Chun – Kategori A: Peserta berusia di bawah 8 tahun.',
                'is_active'   => true,
            ],
            [
                'name'        => 'B',
                'label'       => 'Kategori B (9–11 tahun)',
                'min_age'     => 9,
                'max_age'     => 11,
                'description' => 'Wing Chun – Kategori B: Peserta berusia 9 hingga 11 tahun.',
                'is_active'   => true,
            ],
            [
                'name'        => 'C1',
                'label'       => 'Kategori C1 (12–14 tahun)',
                'min_age'     => 12,
                'max_age'     => 14,
                'description' => 'Wing Chun – Kategori C1: Peserta berusia 12 hingga 14 tahun.',
                'is_active'   => true,
            ],
            [
                'name'        => 'C2',
                'label'       => 'Kategori C2 (15–17 tahun)',
                'min_age'     => 15,
                'max_age'     => 17,
                'description' => 'Wing Chun – Kategori C2: Peserta berusia 15 hingga 17 tahun.',
                'is_active'   => true,
            ],
            [
                'name'        => 'D1',
                'label'       => 'Kategori D1 (18–27 tahun)',
                'min_age'     => 18,
                'max_age'     => 27,
                'description' => 'Wing Chun – Kategori D1: Peserta berusia 18 hingga 27 tahun.',
                'is_active'   => true,
            ],
            [
                'name'        => 'D2',
                'label'       => 'Kategori D2 (28–39 tahun)',
                'min_age'     => 28,
                'max_age'     => 39,
                'description' => 'Wing Chun – Kategori D2: Peserta berusia 28 hingga 39 tahun.',
                'is_active'   => true,
            ],
            [
                'name'        => 'E',
                'label'       => 'Kategori E (40–59 tahun)',
                'min_age'     => 40,
                'max_age'     => 59,
                'description' => 'Wing Chun – Kategori E: Peserta berusia 40 hingga 59 tahun.',
                'is_active'   => true,
            ],
            [
                'name'        => 'F',
                'label'       => 'Kategori F (60 tahun ke atas)',
                'min_age'     => 60,
                'max_age'     => 999,
                'description' => 'Wing Chun – Kategori F: Peserta berusia 60 tahun ke atas.',
                'is_active'   => true,
            ],
        ],
    ];

    public function run(): void
    {
        foreach ($this->categories as $sportName => $ageCats) {
            $sport = Sport::where('name', $sportName)->firstOrFail();

            foreach ($ageCats as $cat) {
                AgeCategory::firstOrCreate(
                    [
                        'sport_id' => $sport->id,
                        'name'     => $cat['name'],
                    ],
                    array_merge($cat, ['sport_id' => $sport->id])
                );

                $this->command->info(
                    "  ✅ AgeCategory [{$sportName}] [{$cat['name']}] → {$cat['min_age']}–{$cat['max_age']} tahun"
                );
            }
        }
    }
}
