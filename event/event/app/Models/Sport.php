<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sport extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * All disciplines that belong to this sport.
     *
     * FK: disciplines.sport_id → sports.id
     *
     * Eager load hint: with('disciplines')
     *
     * @return HasMany<Discipline>
     */
    public function disciplines(): HasMany
    {
        return $this->hasMany(Discipline::class, 'sport_id');
    }

    /**
     * Age categories defined for this sport.
     *
     * FK: age_categories.sport_id → sports.id
     *
     * @return HasMany<AgeCategory>
     */
    public function ageCategories(): HasMany
    {
        return $this->hasMany(AgeCategory::class, 'sport_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Scope to only active sports.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Sport>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Sport>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
