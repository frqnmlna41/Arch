<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Discipline extends Model
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
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Get the full label: "Sport – Discipline".
     */
    public function getLabelAttribute(): string
    {
        return $this->sport?->name . ' – ' . $this->name;
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The sport this discipline belongs to.
     *
     * FK: disciplines.sport_id → sports.id
     *
     * Eager load hint: with('sport')
     *
     * @return BelongsTo<Sport, Discipline>
     */
    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class, 'sport_id');
    }

    /**
     * Event categories that use this discipline.
     *
     * FK: event_categories.discipline_id → disciplines.id
     *
     * Eager load hint: with('eventCategories.event')
     *
     * @return HasMany<EventCategory>
     */
    public function eventCategories(): HasMany
    {
        return $this->hasMany(EventCategory::class, 'discipline_id');
    }

    /**
     * Athletes registered under this discipline.
     *
     * FK: athletes.disciplines_id → disciplines.id
     *
     * @return HasMany<Athlete>
     */
    public function athletes(): HasMany
    {
        return $this->hasMany(Athlete::class, 'disciplines_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Scope to only active disciplines.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Discipline>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Discipline>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
