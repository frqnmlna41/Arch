<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection;

/**
 * App\Models\Arena
 *
 * @property int                             $id
 * @property string                          $name
 * @property string                          $location
 * @property int|null                        $capacity
 * @property string|null                     $notes
 * @property bool                            $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read Collection<int, Match> $matches
 */
class Arena extends Model
{
    use HasFactory;

    // ── Fillable ───────────────────────────────────────────────────
    protected $fillable = [
        'name',
        'location',
        'capacity',
        'notes',
        'is_active',
    ];

    // ── Casts ─────────────────────────────────────────────────────
    protected $casts = [
        'capacity'  => 'integer',
        'is_active' => 'boolean',
    ];

    // ══════════════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ══════════════════════════════════════════════════════════════

    /**
     * Semua pertandingan yang dijadwalkan di arena ini.
     */
    public function matches(): HasMany
    {
        return $this->hasMany(Contest::class);
    }

    // ══════════════════════════════════════════════════════════════
    // SCOPES
    // ══════════════════════════════════════════════════════════════

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
