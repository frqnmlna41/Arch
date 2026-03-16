<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection;

/**
 * App\Models\Sport
 *
 * @property int                             $id
 * @property string                          $name
 * @property string|null                     $description
 * @property bool                            $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read Collection<int, Discipline>   $disciplines
 * @property-read Collection<int, AgeCategory>  $ageCategories
 */
class Sport extends Model
{
    use HasFactory;

    // ── Fillable ───────────────────────────────────────────────────
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    // ── Casts ─────────────────────────────────────────────────────
    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ══════════════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ══════════════════════════════════════════════════════════════

    /**
     * Semua discipline yang dimiliki sport ini.
     * Contoh: Wushu → Chang Quan, Nan Quan, Jian Shu, dst.
     */
    public function disciplines(): HasMany
    {
        return $this->hasMany(Discipline::class);
    }

    /**
     * Semua kategori umur yang dimiliki sport ini.
     * Wushu: D,C,B,A | Wing Chun: A,B,C1,C2,D1,D2,E,F
     */
    public function ageCategories(): HasMany
    {
        return $this->hasMany(AgeCategory::class);
    }

    // ══════════════════════════════════════════════════════════════
    // SCOPES
    // ══════════════════════════════════════════════════════════════

    /**
     * Hanya sport yang aktif.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
