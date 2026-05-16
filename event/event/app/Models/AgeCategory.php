<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgeCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'sport_id',
        'name',
        'label',
        'min_age',
        'max_age',
        'description',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'min_age'   => 'integer',
        'max_age'   => 'integer',
        'is_active' => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Get a formatted age range string, e.g. "10 – 14 tahun".
     */
    public function getAgeRangeAttribute(): string
    {
        return "{$this->min_age} – {$this->max_age} tahun";
    }

    /**
     * Get a full display label: "Remaja A (10–14)".
     */
    public function getDisplayLabelAttribute(): string
    {
        return "{$this->label} ({$this->min_age}–{$this->max_age})";
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The sport this age category belongs to.
     *
     * FK: age_categories.sport_id → sports.id
     *
     * @return BelongsTo<Sport, AgeCategory>
     */
    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class, 'sport_id');
    }

    /**
     * Event categories using this age category.
     *
     * FK: event_categories.age_category_id → age_categories.id
     *
     * Eager load hint: with('eventCategories.event')
     *
     * @return HasMany<EventCategory>
     */
    public function eventCategories(): HasMany
    {
        return $this->hasMany(EventCategory::class, 'age_category_id');
    }

    /**
     * Athletes assigned to this age category.
     *
     * FK: athletes.age_category_id → age_categories.id
     *
     * @return HasMany<Athlete>
     */
    public function athletes(): HasMany
    {
        return $this->hasMany(Athlete::class, 'age_category_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Scope to only active age categories.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<AgeCategory>  $query
     * @return \Illuminate\Database\Eloquent\Builder<AgeCategory>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
