<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\Match
 *
 * Merepresentasikan satu pertandingan dalam event.
 * Bisa berupa duel (athlete1 vs athlete2) atau solo performance (Taolu/Forms).
 *
 * @property int                             $id
 * @property int                             $event_id
 * @property int                             $discipline_id
 * @property int                             $age_category_id
 * @property int|null                        $arena_id
 * @property int                             $athlete1_id
 * @property int|null                        $athlete2_id       null untuk solo performance
 * @property string                          $round             pool|quarter_final|semi_final|final|bronze
 * @property int|null                        $match_number
 * @property string                          $status            scheduled|ongoing|completed|postponed|cancelled|walkover
 * @property \Illuminate\Support\Carbon|null $match_date
 * @property string|null                     $match_time
 * @property string|null                     $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read Event           $event
 * @property-read Discipline      $discipline
 * @property-read AgeCategory     $ageCategory
 * @property-read Arena|null      $arena
 * @property-read Athlete         $athlete1
 * @property-read Athlete|null    $athlete2
 * @property-read MatchResult|null $result
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Score> $scores
 */
class Match extends Model
{
    use HasFactory;

    protected $table = 'matches';

    // ── Fillable ───────────────────────────────────────────────────
    protected $fillable = [
        'event_id',
        'discipline_id',
        'age_category_id',
        'arena_id',
        'athlete1_id',
        'athlete2_id',
        'round',
        'match_number',
        'status',
        'match_date',
        'match_time',
        'notes',
    ];

    // ── Casts ─────────────────────────────────────────────────────
    protected $casts = [
        'match_date'   => 'date',
        'match_number' => 'integer',
    ];

    // ── Round constants ────────────────────────────────────────────
    const ROUND_POOL         = 'pool';
    const ROUND_QUARTER      = 'quarter_final';
    const ROUND_SEMI         = 'semi_final';
    const ROUND_FINAL        = 'final';
    const ROUND_BRONZE       = 'bronze';

    // ── Status constants ───────────────────────────────────────────
    const STATUS_SCHEDULED  = 'scheduled';
    const STATUS_ONGOING    = 'ongoing';
    const STATUS_COMPLETED  = 'completed';
    const STATUS_POSTPONED  = 'postponed';
    const STATUS_CANCELLED  = 'cancelled';
    const STATUS_WALKOVER   = 'walkover';

    // ══════════════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ══════════════════════════════════════════════════════════════

    /**
     * Event induk pertandingan ini.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Discipline yang dipertandingkan.
     */
    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }

    /**
     * Kategori umur pertandingan ini.
     */
    public function ageCategory(): BelongsTo
    {
        return $this->belongsTo(AgeCategory::class);
    }

    /**
     * Arena/lapangan tempat pertandingan berlangsung.
     */
    public function arena(): BelongsTo
    {
        return $this->belongsTo(Arena::class);
    }

    /**
     * Atlet pertama (selalu ada).
     * Untuk solo performance: satu-satunya atlet.
     * Untuk duel: atlet di posisi "merah" / kiri.
     */
    public function athlete1(): BelongsTo
    {
        return $this->belongsTo(Athlete::class, 'athlete1_id');
    }

    /**
     * Atlet kedua (nullable).
     * null untuk solo performance (Taolu/Forms).
     * Untuk duel: atlet di posisi "biru" / kanan.
     */
    public function athlete2(): BelongsTo
    {
        return $this->belongsTo(Athlete::class, 'athlete2_id');
    }

    /**
     * Semua nilai/score yang diberikan juri dalam pertandingan ini.
     */
    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }

    /**
     * Hasil akhir pertandingan (one-to-one).
     * Diisi setelah pertandingan selesai.
     */
    public function result(): HasOne
    {
        return $this->hasOne(MatchResult::class);
    }

    // ══════════════════════════════════════════════════════════════
    // SCOPES
    // ══════════════════════════════════════════════════════════════

    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeOngoing($query)
    {
        return $query->where('status', self::STATUS_ONGOING);
    }

    public function scopeForEvent($query, int $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    public function scopeForAthlete($query, int $athleteId)
    {
        return $query->where('athlete1_id', $athleteId)
                     ->orWhere('athlete2_id', $athleteId);
    }

    public function scopeByRound($query, string $round)
    {
        return $query->where('round', $round);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('match_date', today());
    }

    // ══════════════════════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════════════════════

    /** Cek apakah pertandingan ini solo (tanpa lawan). */
    public function isSolo(): bool
    {
        return is_null($this->athlete2_id);
    }

    /** Cek apakah pertandingan sudah selesai. */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /** Cek apakah pertandingan ini merupakan babak final. */
    public function isFinal(): bool
    {
        return $this->round === self::ROUND_FINAL;
    }

    /**
     * Mendapatkan pemenang pertandingan ini (dari result).
     * Return null jika belum ada hasil atau draw.
     */
    public function getWinner(): ?Athlete
    {
        return $this->result?->winner;
    }
}
