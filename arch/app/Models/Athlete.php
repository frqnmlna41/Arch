<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

/**
 * App\Models\Athlete
 *
 * @property int                             $id
 * @property int|null                        $user_id      Akun user milik atlet (opsional)
 * @property int|null                        $coach_id     FK ke users (role: coach)
 * @property string                          $name
 * @property \Illuminate\Support\Carbon      $birth_date
 * @property string                          $gender       male|female
 * @property string|null                     $club
 * @property string|null                     $phone
 * @property string|null                     $photo
 * @property string|null                     $id_card_number
 * @property float|null                      $weight       Berat badan (kg)
 * @property float|null                      $height       Tinggi badan (cm)
 * @property string|null                     $address
 * @property bool                            $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @property-read int                              $age            Usia saat ini (computed)
 * @property-read User|null                        $user
 * @property-read User|null                        $coach
 * @property-read Collection<int, EventParticipant> $eventParticipants
 * @property-read Collection<int, Score>            $scores
 * @property-read Collection<int, Winner>           $winners
 * @property-read Collection<int, Match>            $matchesAsAthlete1
 * @property-read Collection<int, Match>            $matchesAsAthlete2
 */
class Athlete extends Model
{
    use HasFactory, SoftDeletes;

    // ── Fillable ───────────────────────────────────────────────────
    protected $fillable = [
        'user_id',
        'coach_id',
        'name',
        'birth_date',
        'gender',
        'club',
        'phone',
        'photo',
        'id_card_number',
        'weight',
        'height',
        'address',
        'is_active',
    ];

    // ── Casts ─────────────────────────────────────────────────────
    protected $casts = [
        'birth_date' => 'date',
        'weight'     => 'decimal:2',
        'height'     => 'decimal:2',
        'is_active'  => 'boolean',
    ];

    // ── Gender constants ───────────────────────────────────────────
    const GENDER_MALE   = 'male';
    const GENDER_FEMALE = 'female';

    // ══════════════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ══════════════════════════════════════════════════════════════

    /**
     * Akun user yang dimiliki atlet ini (jika ada).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Coach (user dengan role coach) yang mengelola atlet ini.
     */
    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    /**
     * Semua pendaftaran event milik atlet ini.
     */
    public function eventParticipants(): HasMany
    {
        return $this->hasMany(EventParticipant::class);
    }

    /**
     * Semua nilai/score yang diterima atlet ini dari para juri.
     */
    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }

    /**
     * Semua kemenangan atlet ini di berbagai event.
     */
    public function winners(): HasMany
    {
        return $this->hasMany(Winner::class);
    }

    /**
     * Sertifikat yang dimiliki atlet ini (melalui winners).
     */
    public function certificates(): HasManyThrough
    {
        return $this->hasManyThrough(Certificate::class, Winner::class);
    }

    /**
     * Pertandingan di mana atlet ini berada di posisi athlete1.
     */
    public function matchesAsAthlete1(): HasMany
    {
        return $this->hasMany(CompetitionMatch::class, 'athlete1_id');
    }

    /**
     * Pertandingan di mana atlet ini berada di posisi athlete2.
     */
    public function matchesAsAthlete2(): HasMany
    {
        return $this->hasMany(CompetitionMatch::class, 'athlete2_id');
    }

    /**
     * Hasil pertandingan di mana atlet ini menjadi pemenang.
     */
    public function wonMatches(): HasMany
    {
        return $this->hasMany(MatchResult::class, 'winner_id');
    }

    // ══════════════════════════════════════════════════════════════
    // ACCESSORS
    // ══════════════════════════════════════════════════════════════

    /**
     * Usia atlet saat ini berdasarkan birth_date.
     *
     * @return int
     */
    public function getAgeAttribute(): int
    {
        return Carbon::parse($this->birth_date)->age;
    }

    /**
     * Nama lengkap dengan klub.
     */
    public function getFullLabelAttribute(): string
    {
        return "{$this->name}" . ($this->club ? " ({$this->club})" : '');
    }

    // ══════════════════════════════════════════════════════════════
    // SCOPES
    // ══════════════════════════════════════════════════════════════

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeMale($query)
    {
        return $query->where('gender', self::GENDER_MALE);
    }

    public function scopeFemale($query)
    {
        return $query->where('gender', self::GENDER_FEMALE);
    }

    public function scopeByCoach($query, int $coachId)
    {
        return $query->where('coach_id', $coachId);
    }

    // ══════════════════════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════════════════════

    /**
     * Gabungkan semua pertandingan atlet (sebagai athlete1 dan athlete2).
     * Mengembalikan collection yang sudah di-merge.
     */
    public function getAllMatches(): Collection
    {
        return $this->matchesAsAthlete1
            ->merge($this->matchesAsAthlete2)
            ->sortBy('match_date');
    }
}
