<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection;

/**
 * App\Models\Discipline
 *
 * @property int                             $id
 * @property int                             $sport_id
 * @property string                          $name
 * @property string                          $type          empty_hand|weapon
 * @property string                          $match_type    performance|sparring
 * @property string|null                     $description
 * @property bool                            $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read Sport                          $sport
 * @property-read Collection<int, AgeCategory>   $ageCategories
 * @property-read Collection<int, Match>         $matches
 * @property-read Collection<int, EventParticipant> $eventParticipants
 */
class Discipline extends Model
{
    use HasFactory;

    // ── Fillable ───────────────────────────────────────────────────
    protected $fillable = [
        'sport_id',
        'name',
        'type',
        'match_type',
        'description',
        'is_active',
    ];

    // ── Casts ─────────────────────────────────────────────────────
    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Enum constants ─────────────────────────────────────────────
    const TYPE_EMPTY_HAND = 'empty_hand';
    const TYPE_WEAPON     = 'weapon';

    const MATCH_TYPE_PERFORMANCE = 'performance';
    const MATCH_TYPE_SPARRING    = 'sparring';

    // ══════════════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ══════════════════════════════════════════════════════════════

    /**
     * Sport induk dari discipline ini.
     * Contoh: Chang Quan → Wushu
     */
    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    /**
     * Kategori umur yang diizinkan untuk discipline ini.
     * Relasi many-to-many melalui pivot discipline_age_categories.
     *
     * Contoh: Chang Quan → [D, C, B, A]
     * Contoh: Wing Chun Cham Kiu → [B, C1, C2, D1]
     */
    public function ageCategories(): BelongsToMany
    {
        return $this->belongsToMany(
            AgeCategory::class,
            'discipline_age_categories', // nama tabel pivot
            'discipline_id',             // FK di pivot → discipline ini
            'age_category_id'            // FK di pivot → age category
        );
    }

    /**
     * Semua pertandingan dalam discipline ini.
     */
    public function matches(): HasMany
    {
        // return $this->hasMany(Match::class);
        return $this->hasMany(CompetitionMatch::class);
    }

    /**
     * Semua peserta event yang mendaftar di discipline ini.
     */
    public function eventParticipants(): HasMany
    {
        return $this->hasMany(EventParticipant::class);
    }

    // ══════════════════════════════════════════════════════════════
    // SCOPES
    // ══════════════════════════════════════════════════════════════

    /** Hanya discipline aktif. */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Filter berdasarkan tipe senjata. */
    public function scopeWeapon($query)
    {
        return $query->where('type', self::TYPE_WEAPON);
    }

    /** Filter berdasarkan tangan kosong. */
    public function scopeEmptyHand($query)
    {
        return $query->where('type', self::TYPE_EMPTY_HAND);
    }

    /** Filter berdasarkan tipe performance (Taolu/Forms). */
    public function scopePerformance($query)
    {
        return $query->where('match_type', self::MATCH_TYPE_PERFORMANCE);
    }

    /** Filter berdasarkan tipe sparring (Sanda/Combat). */
    public function scopeSparring($query)
    {
        return $query->where('match_type', self::MATCH_TYPE_SPARRING);
    }

    // ══════════════════════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════════════════════

    /** Cek apakah discipline menggunakan senjata. */
    public function isWeapon(): bool
    {
        return $this->type === self::TYPE_WEAPON;
    }

    /** Cek apakah discipline adalah performance (bukan sparring). */
    public function isPerformance(): bool
    {
        return $this->match_type === self::MATCH_TYPE_PERFORMANCE;
    }
}
