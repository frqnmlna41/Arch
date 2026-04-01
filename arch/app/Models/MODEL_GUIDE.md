# 🏆 Tournament CMS – Model & Relationship Guide
## Laravel 10 / 11 | Eloquent ORM | Spatie Laravel Permission

---

## 📦 DAFTAR MODEL

| Model | Tabel | Keterangan |
|-------|-------|------------|
| `User` | `users` | Dengan `HasRoles` (Spatie) |
| `Sport` | `sports` | Wushu, Wing Chun |
| `Discipline` | `disciplines` | Chang Quan, Siu Nim Tau, dst. |
| `AgeCategory` | `age_categories` | D/C/B/A, A/B/C1/C2/D1/D2/E/F |
| `DisciplineAgeCategory` | `discipline_age_categories` | Pivot model |
| `Arena` | `arenas` | Lapangan pertandingan |
| `Event` | `events` | Kejuaraan |
| `Athlete` | `athletes` | Data atlet |
| `EventParticipant` | `event_participants` | Pendaftaran atlet ke event |
| `Match` | `matches` | Pertandingan |
| `Score` | `scores` | Nilai dari juri |
| `MatchResult` | `match_results` | Hasil akhir pertandingan |
| `Winner` | `winners` | Pemenang per kategori |
| `Certificate` | `certificates` | Sertifikat pemenang |

---

## 🗺️ PETA RELASI LENGKAP

```
User
 ├── hasMany → Athlete          (coach_id)
 ├── hasMany → Score            (judge_id)
 ├── hasMany → Event            (created_by)
 ├── hasMany → EventParticipant (registered_by)
 ├── hasMany → MatchResult      (recorded_by)
 └── hasMany → Certificate      (issued_by)

Sport
 ├── hasMany → Discipline
 └── hasMany → AgeCategory

Discipline
 ├── belongsTo  → Sport
 ├── belongsToMany → AgeCategory  [pivot: discipline_age_categories]
 ├── hasMany    → Match
 └── hasMany    → EventParticipant

AgeCategory
 ├── belongsTo  → Sport
 ├── belongsToMany → Discipline   [pivot: discipline_age_categories]
 └── hasMany    → EventParticipant

Arena
 └── hasMany → Match

Event
 ├── belongsTo  → User           (created_by)
 ├── hasMany    → EventParticipant
 ├── hasMany    → Match
 └── hasMany    → Winner

Athlete
 ├── belongsTo  → User           (user_id, opsional)
 ├── belongsTo  → User           (coach_id)
 ├── hasMany    → EventParticipant
 ├── hasMany    → Score
 ├── hasMany    → Winner
 ├── hasManyThrough → Certificate (via Winner)
 ├── hasMany    → Match           (athlete1_id)
 └── hasMany    → Match           (athlete2_id)

EventParticipant
 ├── belongsTo → Event
 ├── belongsTo → Athlete
 ├── belongsTo → Discipline
 ├── belongsTo → AgeCategory
 ├── belongsTo → User (registered_by)
 └── belongsTo → User (verified_by)

Match
 ├── belongsTo  → Event
 ├── belongsTo  → Discipline
 ├── belongsTo  → AgeCategory
 ├── belongsTo  → Arena
 ├── belongsTo  → Athlete (athlete1_id)
 ├── belongsTo  → Athlete (athlete2_id, nullable)
 ├── hasMany    → Score
 └── hasOne     → MatchResult

Score
 ├── belongsTo → Match
 ├── belongsTo → User    (judge_id)
 └── belongsTo → Athlete

MatchResult
 ├── belongsTo → Match
 ├── belongsTo → Athlete (winner_id, nullable)
 └── belongsTo → User    (recorded_by)

Winner
 ├── belongsTo → Event
 ├── belongsTo → Discipline
 ├── belongsTo → AgeCategory
 ├── belongsTo → Athlete
 └── hasOne    → Certificate

Certificate
 ├── belongsTo → Winner
 └── belongsTo → User (issued_by)
```

---

## 💡 CONTOH PENGGUNAAN RELASI

### RBAC – Cek Role & Permission
```php
// Cek role
$user->hasRole('admin');
$user->hasRole('coach');
$user->isAdmin();   // helper method
$user->isCoach();

// Cek permission
$user->can('input score');
$user->can('generate certificate');
$user->hasPermissionTo('manage events');

// Middleware di route
Route::middleware(['auth', 'role:admin'])->group(...);
Route::middleware(['auth', 'permission:input score'])->group(...);
```

### Sport & Discipline
```php
// Semua discipline Wushu
$wushu = Sport::where('name', 'Wushu')->with('disciplines')->first();
$wushu->disciplines;

// Discipline senjata saja
$wushu->disciplines()->weapon()->get();

// Discipline beserta kategori umur yang diizinkan
Discipline::with('ageCategories')->where('sport_id', 1)->get();

// Attach kategori umur ke discipline (via pivot)
$discipline->ageCategories()->attach($ageCategoryId);
$discipline->ageCategories()->sync([1, 2, 3]);
$discipline->ageCategories()->detach($ageCategoryId);
```

### Athlete & Coach
```php
// Semua atlet milik coach ini
$coach->athletes;
$coach->athletes()->active()->get();

// Coach dari atlet
$athlete->coach;

// Atlet dengan semua pendaftarannya
$athlete->load('eventParticipants.event', 'eventParticipants.discipline');

// Semua pertandingan seorang atlet (gabungan athlete1 & athlete2)
$athlete->getAllMatches();

// Usia atlet
$athlete->age;           // computed accessor
$athlete->birth_date;    // Carbon date
```

### Event & Participants
```php
// Event yang sedang buka pendaftaran
Event::openRegistration()->get();

// Semua peserta terverifikasi di sebuah event
$event->participants()->verified()->with('athlete', 'discipline')->get();

// Verifikasi peserta
$participant->verify($adminUser);
$participant->reject('Dokumen tidak lengkap');
```

### Match & Schedule (Jadwal)
```php
// Jadwal pertandingan hari ini
Match::today()->with('athlete1', 'athlete2', 'arena', 'discipline')->get();

// Pertandingan seorang atlet
Match::forAthlete($athleteId)->with('event', 'discipline')->get();
// atau
$athlete->matchesAsAthlete1()->get();
$athlete->matchesAsAthlete2()->get();
$athlete->getAllMatches(); // gabungan keduanya

// Pertandingan final
Match::forEvent($eventId)->byRound('final')->get();

// Load result sekaligus
$match->load('result.winner', 'scores.judge');
$match->getWinner(); // Athlete|null
$match->isSolo();    // true untuk Taolu/Forms
```

### Score – Input Nilai (Judge)
```php
// Judge input nilai
Score::create([
    'match_id'   => $match->id,
    'judge_id'   => $judge->id,
    'athlete_id' => $athlete->id,
    'score'      => 9.5,
    'score_type' => Score::TYPE_TECHNIQUE,
]);

// Semua nilai untuk pertandingan tertentu
$match->scores()->forAthlete($athleteId)->get();

// Nilai dari judge tertentu
$match->scores()->byJudge($judgeId)->get();
```

### Match Result
```php
// Simpan hasil pertandingan
MatchResult::create([
    'match_id'       => $match->id,
    'recorded_by'    => $admin->id,
    'winner_id'      => $athlete1->id,
    'athlete1_score' => 9.75,
    'athlete2_score' => 9.20,
    'win_method'     => MatchResult::METHOD_SCORING,
]);

// Akses hasil
$match->result->winner;
$match->result->isDraw();
$match->result->getScoreForAthlete($athleteId);
```

### Winner & Certificate
```php
// Daftar pemenang suatu event
Winner::forEvent($eventId)
    ->with('athlete', 'discipline', 'ageCategory')
    ->orderBy('rank')
    ->get();

// Generate sertifikat
Certificate::create([
    'winner_id'          => $winner->id,
    'issued_by'          => $admin->id,
    'certificate_number' => Certificate::generateNumber(),
    'issued_at'          => now(),
]);

// Akses shortcut
$certificate->athlete;  // via winner
$certificate->event;    // via winner

// Semua sertifikat atlet
$athlete->certificates;  // via hasManyThrough

// Cek medali
$winner->isGold();
$winner->medalLabel;  // "🥇 Juara 1 (Emas)"
$winner->hasCertificate();
```

### Eager Loading (N+1 Prevention)
```php
// Load lengkap untuk halaman jadwal
Match::with([
    'event',
    'discipline.sport',
    'ageCategory',
    'arena',
    'athlete1',
    'athlete2',
    'result.winner',
])->forEvent($eventId)->get();

// Load lengkap untuk halaman profil atlet
Athlete::with([
    'coach',
    'eventParticipants.event',
    'eventParticipants.discipline',
    'winners.certificate',
    'winners.event',
])->findOrFail($athleteId);
```

---

## ⚙️ ARTISAN COMMANDS

```bash
# Buat semua model sekaligus
php artisan make:model Sport
php artisan make:model Discipline
php artisan make:model AgeCategory
php artisan make:model DisciplineAgeCategory
php artisan make:model Arena
php artisan make:model Event
php artisan make:model Athlete
php artisan make:model EventParticipant
php artisan make:model Match
php artisan make:model Score
php artisan make:model MatchResult
php artisan make:model Winner
php artisan make:model Certificate

# Atau buat sekaligus dengan factory
php artisan make:model Sport -f
php artisan make:model Athlete -f
```

---

## 🔧 CATATAN TEKNIS

### Winner Model — Kolom Tambahan vs Spec
Spec asli meminta `event_category_id` (FK ke `event_categories`),
namun implementasi ini menggunakan kombinasi:
- `event_id`
- `discipline_id`
- `age_category_id`

Ini lebih fleksibel dan selaras dengan struktur seeder yang sudah dibuat.
Jika Anda menggunakan tabel `event_categories`, ganti dengan:
```php
public function eventCategory(): BelongsTo {
    return $this->belongsTo(EventCategory::class);
}
```

### Match — Reserved Word
`Match` adalah reserved keyword di PHP < 8.0.
Di Laravel 10/11 (PHP 8.1+) ini aman digunakan sebagai nama class.
Pastikan `protected $table = 'matches'` ada di model.

### Pivot Model DisciplineAgeCategory
Gunakan `using()` di relasi BelongsToMany untuk mengarahkan ke pivot model:
```php
// Di Discipline model:
public function ageCategories(): BelongsToMany
{
    return $this->belongsToMany(AgeCategory::class, 'discipline_age_categories')
                ->using(DisciplineAgeCategory::class);
}
```
