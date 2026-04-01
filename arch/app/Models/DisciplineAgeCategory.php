<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * App\Models\DisciplineAgeCategory
 *
 * Pivot model untuk tabel discipline_age_categories.
 * Merepresentasikan relasi many-to-many antara Discipline dan AgeCategory.
 *
 * Digunakan saat relasi membutuhkan data tambahan dari pivot
 * atau ketika pivot perlu di-query secara langsung.
 *
 * @property int $discipline_id
 * @property int $age_category_id
 *
 * @property-read Discipline  $discipline
 * @property-read AgeCategory $ageCategory
 *
 * Contoh penggunaan:
 * ─────────────────
 * // Via relasi BelongsToMany (lazy load pivot):
 * $discipline->ageCategories()->withPivot([])->get();
 *
 * // Atau query langsung:
 * DisciplineAgeCategory::where('discipline_id', $id)->get();
 *
 * // Attach kategori umur ke discipline:
 * $discipline->ageCategories()->attach($ageCategoryId);
 *
 * // Detach:
 * $discipline->ageCategories()->detach($ageCategoryId);
 *
 * // Sync (replace all):
 * $discipline->ageCategories()->sync([1, 2, 3]);
 */
class DisciplineAgeCategory extends Pivot
{
    /**
     * Nama tabel pivot di database.
     */
    protected $table = 'discipline_age_categories';

    /**
     * Pivot ini tidak menggunakan auto-increment id.
     * Primary key adalah composite (discipline_id + age_category_id).
     */
    public $incrementing = false;

    /**
     * Pivot sederhana tidak memerlukan timestamps.
     * Ubah ke true jika tabel pivot punya created_at/updated_at.
     */
    public $timestamps = false;

    // ── Fillable ───────────────────────────────────────────────────
    protected $fillable = [
        'discipline_id',
        'age_category_id',
    ];

    // ── Casts ─────────────────────────────────────────────────────
    protected $casts = [
        'discipline_id'   => 'integer',
        'age_category_id' => 'integer',
    ];

    // ══════════════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ══════════════════════════════════════════════════════════════

    /**
     * Discipline yang dimiliki pivot ini.
     */
    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }

    /**
     * AgeCategory yang dimiliki pivot ini.
     */
    public function ageCategory(): BelongsTo
    {
        return $this->belongsTo(AgeCategory::class);
    }
}
