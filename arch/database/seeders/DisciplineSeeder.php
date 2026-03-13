<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Discipline;
use App\Models\Sport;

/**
 * DisciplineSeeder
 *
 * Membuat semua discipline untuk Wushu dan Wing Chun.
 *
 * Artisan command:
 *   php artisan make:seeder DisciplineSeeder
 *
 * Kolom penting di tabel disciplines:
 *   - sport_id   : FK ke sports
 *   - name       : nama discipline
 *   - type       : 'empty_hand' | 'weapon'
 *   - match_type : 'performance' | 'sparring'
 *   - description
 *   - is_active
 *
 * ─────────────────────────────────────
 * WUSHU TAOLU (semua match_type = performance):
 *
 * EMPTY HAND:
 *   Chang Quan, Nan Quan, Chu Ji Nan Quan,
 *   Taiji Quan, Wu Bu Quan (3 Sessions), Wu Bu Quan (5 Sessions)
 *
 * WEAPON:
 *   Jian Shu, Dao Shu, Nan Dao, Chu Ji Nan Dao,
 *   Taiji Jian, Qiang Shu, Gun Shu, Nan Gun, Chu Ji Nan Gun
 *
 * ─────────────────────────────────────
 * WING CHUN KUNGFU (semua match_type = performance):
 *
 * EMPTY HAND:
 *   Taiji Quan, Wing Chun - Siu Nim Tau, Wing Chun - Cham Kiu,
 *   Wing Chun - Biu Jee, Xingyi Quan, Bagua Zhang, Baji Quan,
 *   Wuzu Quan - Sam Cien, Wuzu Quan - Ngo Ho Cien,
 *   Wuzu Quan - Ji Sip Kuen, Jurus Perguruan
 *
 * WEAPON:
 *   Wing Chun - Bart Jarm Dao, Wing Chun - Lok Dim Boon Gwan,
 *   Er Jie Gun, Shuang Er Jie Gun
 * ─────────────────────────────────────
 */
class DisciplineSeeder extends Seeder
{
    private array $disciplines = [

        // ══════════════════════════════════════════════════
        // WUSHU
        // ══════════════════════════════════════════════════
        'Wushu' => [

            // ── Empty Hand ────────────────────────────────
            [
                'name'       => 'Chang Quan',
                'type'       => 'empty_hand',
                'match_type' => 'performance',
                'description'=> 'Jurus panjang Wushu yang menampilkan gerakan dinamis, '
                              . 'melompat, dan berputar dengan kecepatan tinggi.',
            ],
            [
                'name'       => 'Nan Quan',
                'type'       => 'empty_hand',
                'match_type' => 'performance',
                'description'=> 'Jurus selatan Wushu yang mengandalkan kekuatan tubuh bagian '
                              . 'atas, dengan pukulan dan posisi kuda-kuda yang kuat.',
            ],
            [
                'name'       => 'Chu Ji Nan Quan',
                'type'       => 'empty_hand',
                'match_type' => 'performance',
                'description'=> 'Jurus Nan Quan tingkat pemula untuk kategori usia dini.',
            ],
            [
                'name'       => 'Taiji Quan',
                'type'       => 'empty_hand',
                'match_type' => 'performance',
                'description'=> 'Jurus Taiji yang menekankan gerakan lembut, mengalir, '
                              . 'dan keseimbangan energi internal (qi).',
            ],
            [
                'name'       => 'Wu Bu Quan (3 Sessions)',
                'type'       => 'empty_hand',
                'match_type' => 'performance',
                'description'=> 'Jurus lima langkah dasar Wushu dalam 3 sesi (level awal).',
            ],
            [
                'name'       => 'Wu Bu Quan (5 Sessions)',
                'type'       => 'empty_hand',
                'match_type' => 'performance',
                'description'=> 'Jurus lima langkah dasar Wushu dalam 5 sesi (level lanjut).',
            ],

            // ── Weapon ────────────────────────────────────
            [
                'name'       => 'Jian Shu',
                'type'       => 'weapon',
                'match_type' => 'performance',
                'description'=> 'Seni pedang bermata dua (Jian) yang membutuhkan '
                              . 'kecepatan, ketepatan, dan kelenturan tubuh.',
            ],
            [
                'name'       => 'Dao Shu',
                'type'       => 'weapon',
                'match_type' => 'performance',
                'description'=> 'Seni pedang satu sisi (Dao/Golok) dengan gerakan '
                              . 'menyapu yang kuat dan penuh tenaga.',
            ],
            [
                'name'       => 'Nan Dao',
                'type'       => 'weapon',
                'match_type' => 'performance',
                'description'=> 'Dao gaya selatan (Nan) dengan teknik dan posisi kuda-kuda '
                              . 'yang lebih berat dan bertenaga.',
            ],
            [
                'name'       => 'Chu Ji Nan Dao',
                'type'       => 'weapon',
                'match_type' => 'performance',
                'description'=> 'Nan Dao tingkat pemula untuk kategori usia muda.',
            ],
            [
                'name'       => 'Taiji Jian',
                'type'       => 'weapon',
                'match_type' => 'performance',
                'description'=> 'Pedang Taiji dengan gerakan lembut dan mengalir mengikuti '
                              . 'prinsip Taiji Quan.',
            ],
            [
                'name'       => 'Qiang Shu',
                'type'       => 'weapon',
                'match_type' => 'performance',
                'description'=> 'Seni tombak panjang (Qiang) yang menampilkan gerakan '
                              . 'menusuk, menyapu, dan berputar dengan kecepatan tinggi.',
            ],
            [
                'name'       => 'Gun Shu',
                'type'       => 'weapon',
                'match_type' => 'performance',
                'description'=> 'Seni tongkat panjang (Gun) dengan gerakan dinamis, '
                              . 'memutar, dan memukul yang bertenaga.',
            ],
            [
                'name'       => 'Nan Gun',
                'type'       => 'weapon',
                'match_type' => 'performance',
                'description'=> 'Tongkat gaya selatan (Nan Gun) dengan posisi dan teknik '
                              . 'khas aliran selatan Wushu.',
            ],
            [
                'name'       => 'Chu Ji Nan Gun',
                'type'       => 'weapon',
                'match_type' => 'performance',
                'description'=> 'Nan Gun tingkat pemula untuk kategori usia muda.',
            ],
        ],

        // ══════════════════════════════════════════════════
        // WING CHUN
        // ══════════════════════════════════════════════════
        'Wing Chun' => [

            // ── Empty Hand ────────────────────────────────
            [
                'name'       => 'Taiji Quan',
                'type'       => 'empty_hand',
                'match_type' => 'performance',
                'description'=> 'Jurus Taiji Quan yang dipertandingkan dalam konteks Wing Chun, '
                              . 'menekankan aliran energi dan keseimbangan.',
            ],
            [
                'name'       => 'Wing Chun - Siu Nim Tau',
                'type'       => 'empty_hand',
                'match_type' => 'performance',
                'description'=> 'Form pertama Wing Chun, "Ide Kecil". Fondasi semua teknik '
                              . 'Wing Chun yang menekankan posisi pusat dan struktur tangan.',
            ],
            [
                'name'       => 'Wing Chun - Cham Kiu',
                'type'       => 'empty_hand',
                'match_type' => 'performance',
                'description'=> 'Form kedua Wing Chun, "Menjembatani Tangan". '
                              . 'Memperkenalkan perputaran pinggul, tendangan, dan pergeseran kaki.',
            ],
            [
                'name'       => 'Wing Chun - Biu Jee',
                'type'       => 'empty_hand',
                'match_type' => 'performance',
                'description'=> 'Form ketiga Wing Chun, "Jari yang Melesat". '
                              . 'Teknik lanjutan untuk pemulihan posisi dan serangan ekstra.',
            ],
            [
                'name'       => 'Xingyi Quan',
                'type'       => 'empty_hand',
                'match_type' => 'performance',
                'description'=> 'Seni bela diri internal Tiongkok yang menekankan '
                              . 'gerakan lurus dan kekuatan yang meledak.',
            ],
            [
                'name'       => 'Bagua Zhang',
                'type'       => 'empty_hand',
                'match_type' => 'performance',
                'description'=> 'Seni bela diri internal dengan gerakan melingkar '
                              . 'dan perubahan arah yang cepat.',
            ],
            [
                'name'       => 'Baji Quan',
                'type'       => 'empty_hand',
                'match_type' => 'performance',
                'description'=> 'Gaya bela diri yang terkenal dengan pukulan siku '
                              . 'dan bahu yang eksplosif serta penetrasi jarak pendek.',
            ],
            [
                'name'       => 'Wuzu Quan - Sam Cien',
                'type'       => 'empty_hand',
                'match_type' => 'performance',
                'description'=> 'Jurus tiga langkah Wuzu Quan (Lima Leluhur). '
                              . 'Fondasi latihan dalam sistem Wuzu Quan.',
            ],
            [
                'name'       => 'Wuzu Quan - Ngo Ho Cien',
                'type'       => 'empty_hand',
                'match_type' => 'performance',
                'description'=> 'Jurus Lima Binatang dalam sistem Wuzu Quan, '
                              . 'menggabungkan karakteristik lima hewan.',
            ],
            [
                'name'       => 'Wuzu Quan - Ji Sip Kuen',
                'type'       => 'empty_hand',
                'match_type' => 'performance',
                'description'=> 'Jurus advanced Wuzu Quan yang menggabungkan '
                              . 'teknik-teknik komprehensif dari sistem Wuzu.',
            ],
            [
                'name'       => 'Jurus Perguruan',
                'type'       => 'empty_hand',
                'match_type' => 'performance',
                'description'=> 'Jurus khas dari perguruan/aliran masing-masing peserta. '
                              . 'Menampilkan keunikan dan identitas perguruan.',
            ],

            // ── Weapon ────────────────────────────────────
            [
                'name'       => 'Wing Chun - Bart Jarm Dao',
                'type'       => 'weapon',
                'match_type' => 'performance',
                'description'=> 'Jurus "Delapan Pisau Pemenggal" Wing Chun menggunakan '
                              . 'sepasang pedang pendek (butterfly swords).',
            ],
            [
                'name'       => 'Wing Chun - Lok Dim Boon Gwan',
                'type'       => 'weapon',
                'match_type' => 'performance',
                'description'=> 'Jurus "Tongkat Enam Setengah Titik" Wing Chun. '
                              . 'Satu-satunya form senjata tongkat panjang dalam Wing Chun.',
            ],
            [
                'name'       => 'Er Jie Gun',
                'type'       => 'weapon',
                'match_type' => 'performance',
                'description'=> 'Seni nunchaku (dua ruas tongkat) yang menampilkan '
                              . 'kecepatan rotasi dan kontrol senjata.',
            ],
            [
                'name'       => 'Shuang Er Jie Gun',
                'type'       => 'weapon',
                'match_type' => 'performance',
                'description'=> 'Seni double nunchaku (dua pasang nunchaku) yang menuntut '
                              . 'koordinasi tangan tinggi dan kecepatan ganda.',
            ],
        ],
    ];

    public function run(): void
    {
        foreach ($this->disciplines as $sportName => $disciplines) {
            $sport = Sport::where('name', $sportName)->firstOrFail();

            foreach ($disciplines as $disc) {
                Discipline::firstOrCreate(
                    [
                        'sport_id' => $sport->id,
                        'name'     => $disc['name'],
                    ],
                    array_merge($disc, [
                        'sport_id'  => $sport->id,
                        'is_active' => true,
                    ])
                );

                $typeLabel = $disc['type'] === 'weapon' ? '🗡️ weapon' : '✊ empty_hand';
                $this->command->info(
                    "  ✅ [{$sportName}] {$disc['name']} ({$typeLabel}, {$disc['match_type']})"
                );
            }
        }
    }
}
