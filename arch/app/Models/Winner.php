<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\Winner
 *
 * Merepresentasikan pemenang akhir dalam sebuah kategori event.
 * Diisi setelah seluruh pertandingan dalam kategori selesai.
 *
 * Rank 1 = Juara 1 (Medali Emas)
 * Rank 2 = Juara 2 (Medali Perak)
 * Rank 3 = Juara 3 (Medali Perunggu — bisa ada 2 untuk double bronze)
 *
 * @property int                             $id
 * @property int                             $event_id
 * @property int                             $discipline_id
 * @property int                             $age_category_id
 * @property int                             $athlete_id
 * @property int                             $rank              1|2|3
 * @property float|null                      $total_score       Total skor akhir (untuk performance)
 * @property string|null                     $medal_type        gold|silver|bronze
 * @property string|null                     $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read Event           $event
 * @property-read Discipline      $discipline
 * @property-read AgeCategory     $ageCategory
 * @property-read Athlete         $athlete
 * @property-read Certificate|null $certificate
 */
class Winner extends Model
{
    use HasFactory;

    protected $table = 'winners';

    // ── Fillable ───────────────────────────────────────────────────
    protected $fillable = [
        // 'event_id',
        // 'discipline_id',
        // 'age_category_id',
        'event_category_id',
        'athlete_id',
        'rank',
        'total_score',
        'medal_type',
        'notes',
    ];

    // ── Casts ─────────────────────────────────────────────────────
    protected $casts = [
        'rank'        => 'integer',
        'total_score' => 'decimal:3',
    ];

    // ── Medal constants ────────────────────────────────────────────
    const MEDAL_GOLD   = 'gold';
    const MEDAL_SILVER = 'silver';
    const MEDAL_BRONZE = 'bronze';

    // ── Rank → Medal mapping ───────────────────────────────────────
    const RANK_MEDAL_MAP = [
        1 => self::MEDAL_GOLD,
        2 => self::MEDAL_SILVER,
        3 => self::MEDAL_BRONZE,
    ];

        // ═══════════════════════════════════════
    // RELATIONSHIPS — yang berubah
    // ═══════════════════════════════════════

    /**
     * ✅ Relasi utama ke EventCategory
     */
    // public function eventCategory(): BelongsTo
    // {
    //     return $this->belongsTo(EventCategory::class);
    // }

    // /**
    //  * ✅ Shortcut accessor — ambil event via eventCategory
    //  */
    // public function getEventAttribute(): ?Event
    // {
    //     return $this->eventCategory?->event;
    // }

    // public function getDisciplineAttribute(): ?Discipline
    // {
    //     return $this->eventCategory?->discipline;
    // }

    // public function getAgeCategoryAttribute(): ?AgeCategory
    // {
    //     return $this->eventCategory?->ageCategory;
    // }

    // // Relasi athlete() dan certificate() tetap sama...

    // // ═══════════════════════════════════════
    // // SCOPES — yang berubah
    // // ═══════════════════════════════════════

    // public function scopeForEvent($query, int $eventId)
    // {
    //     return $query->whereHas('eventCategory', fn($q) =>
    //         $q->where('event_id', $eventId)
    //     );
    // }

    // public function scopeForDiscipline($query, int $disciplineId)
    // {
    //     return $query->whereHas('eventCategory', fn($q) =>
    //         $q->where('discipline_id', $disciplineId)
    //     );
    // }

    // ══════════════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ══════════════════════════════════════════════════════════════

    /**
     * Event tempat kemenangan ini diraih.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Discipline yang dimenangkan.
     */
    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }

    /**
     * Kategori umur pertandingan yang dimenangkan.
     */
    public function ageCategory(): BelongsTo
    {
        return $this->belongsTo(AgeCategory::class);
    }

    /**
     * Atlet yang memenangkan posisi ini.
     */
    public function athlete(): BelongsTo
    {
        return $this->belongsTo(Athlete::class);
    }

    /**
     * Sertifikat yang dihasilkan untuk pemenang ini (one-to-one).
     */
    public function certificate(): HasOne
    {
        return $this->hasOne(Certificate::class);
    }

    // ══════════════════════════════════════════════════════════════
    // SCOPES
    // ══════════════════════════════════════════════════════════════

    public function scopeGold($query)
    {
        return $query->where('rank', 1);
    }

    public function scopeSilver($query)
    {
        return $query->where('rank', 2);
    }

    public function scopeBronze($query)
    {
        return $query->where('rank', 3);
    }

    public function scopeForEvent($query, int $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    public function scopeForDiscipline($query, int $disciplineId)
    {
        return $query->where('discipline_id', $disciplineId);
    }

    // ══════════════════════════════════════════════════════════════
    // ACCESSORS & HELPERS
    // ══════════════════════════════════════════════════════════════

    /** Label medali berdasarkan rank. */
    public function getMedalLabelAttribute(): string
    {
        return match ($this->rank) {
            1 => '🥇 Juara 1 (Emas)',
            2 => '🥈 Juara 2 (Perak)',
            3 => '🥉 Juara 3 (Perunggu)',
            default => "Juara {$this->rank}",
        };
    }

    public function isGold(): bool   { return $this->rank === 1; }
    public function isSilver(): bool { return $this->rank === 2; }
    public function isBronze(): bool { return $this->rank === 3; }

    /** Apakah sertifikat sudah di-generate. */
    public function hasCertificate(): bool
    {
        return $this->certificate()->exists();
    }
}
// ### 3. `Winner.php` — `$fillable` tidak sinkron dengan relasi (Semua relasi null)

// **Masalah:** `$fillable` hanya berisi `event_category_id`, tapi relasi aktif `event()`, `discipline()`, `ageCategory()` menggunakan kolom `event_id`, `discipline_id`, `age_category_id` yang tidak ada di tabel → silently return `null`.

// **Fix:**

// - Relasi `event()`, `discipline()`, `ageCategory()` sebagai `BelongsTo` dihapus.
// - Diganti accessor `getEventAttribute()`, `getDisciplineAttribute()`, `getAgeCategoryAttribute()` yang mengambil data via `$this->eventCategory?->event`, dll. (tidak butuh kolom ekstra).
// - Scope `scopeForEvent()` dan `scopeForDiscipline()` diperbaiki ke `whereHas('eventCategory', ...)`.
// - **Migration disertakan** untuk drop kolom redundan jika masih ada di DB.

// ---