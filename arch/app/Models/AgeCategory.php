<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection;

/**
 * App\Models\AgeCategory
 *
 * @property int                             $id
 * @property int                             $sport_id
 * @property string                          $name        Kode: D, C, B, A / A, B, C1, C2, D1, D2, E, F
 * @property string|null                     $label       Label panjang: "Kategori D (Di bawah 8 tahun)"
 * @property int                             $min_age
 * @property int                             $max_age
 * @property string|null                     $description
 * @property bool                            $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read Sport                         $sport
 * @property-read Collection<int, Discipline>   $disciplines
 * @property-read Collection<int, EventParticipant> $eventParticipants
 */
class AgeCategory extends Model
{
    use HasFactory;

    protected $table = 'age_categories';

    // ── Fillable ───────────────────────────────────────────────────
    protected $fillable = [
        'sport_id',
        'name',
        'label',
        'min_age',
        'max_age',
        'description',
        'is_active',
    ];

    // ── Casts ─────────────────────────────────────────────────────
    protected $casts = [
        'min_age'   => 'integer',
        'max_age'   => 'integer',
        'is_active' => 'boolean',
    ];

    // ══════════════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ══════════════════════════════════════════════════════════════

    /**
     * Sport yang memiliki kategori umur ini.
     * (Wushu memiliki D/C/B/A; Wing Chun memiliki A/B/C1/C2/D1/D2/E/F)
     */
    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    /**
     * Discipline yang tersedia untuk kategori umur ini.
     * Relasi many-to-many melalui pivot discipline_age_categories.
     */
    public function disciplines(): BelongsToMany
    {
        return $this->belongsToMany(
            Discipline::class,
            'discipline_age_categories',
            'age_category_id',  // FK di pivot → age category ini
            'discipline_id'     // FK di pivot → discipline
        );
    }

    /**
     * Peserta event dalam kategori umur ini.
     */
    public function eventParticipants(): HasMany
    {
        return $this->hasMany(EventParticipant::class);
    }

    // ══════════════════════════════════════════════════════════════
    // SCOPES
    // ══════════════════════════════════════════════════════════════

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Filter kategori umur berdasarkan sport tertentu. */
    public function scopeForSport($query, int $sportId)
    {
        return $query->where('sport_id', $sportId);
    }

    // ══════════════════════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════════════════════

    /**
     * Cek apakah usia tertentu masuk dalam kategori ini.
     */
    public function coversAge(int $age): bool
    {
        return $age >= $this->min_age && $age <= $this->max_age;
    }

    /**
     * Mendapatkan label rentang usia yang human-readable.
     * Contoh: "9–11 tahun" atau "60+ tahun"
     */
    public function getAgeRangeLabelAttribute(): string
    {
        if ($this->min_age === 0) {
            return "Di bawah {$this->max_age} tahun";
        }

        if ($this->max_age >= 999) {
            return "{$this->min_age} tahun ke atas";
        }

        return "{$this->min_age}–{$this->max_age} tahun";
    }
}
