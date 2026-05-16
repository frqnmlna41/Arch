<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'event_id',
        'sport_id',
        'discipline_id',
        'age_category_id',
        'gender',
        'weight_class',
        'max_participants',
        'format',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'max_participants' => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * A human-readable label for this category.
     * Example: "Sanda – Remaja A – Putra – 52kg"
     */
    public function getLabelAttribute(): string
    {
        $parts = [
            $this->discipline?->name,
            $this->ageCategory?->label,
            $this->genderLabel,
        ];

        if ($this->weight_class) {
            $parts[] = $this->weight_class;
        }

        return implode(' – ', array_filter($parts));
    }

    /**
     * Human-readable gender label.
     */
    public function getGenderLabelAttribute(): string
    {
        return match ($this->gender) {
            'male'   => 'Putra',
            'female' => 'Putri',
            'mixed'  => 'Campuran',
            default  => ucfirst($this->gender),
        };
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The parent event this category belongs to.
     *
     * FK: event_categories.event_id → events.id
     *
     * @return BelongsTo<Event, EventCategory>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    /**
     * The sport for this category.
     *
     * FK: event_categories.sport_id → sports.id
     *
     * @return BelongsTo<Sport, EventCategory>
     */
    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class, 'sport_id');
    }

    /**
     * The discipline for this category.
     *
     * FK: event_categories.discipline_id → disciplines.id
     *
     * @return BelongsTo<Discipline, EventCategory>
     */
    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class, 'discipline_id');
    }

    /**
     * The age category for this event category.
     *
     * FK: event_categories.age_category_id → age_categories.id
     *
     * @return BelongsTo<AgeCategory, EventCategory>
     */
    public function ageCategory(): BelongsTo
    {
        return $this->belongsTo(AgeCategory::class, 'age_category_id');
    }

    /**
     * All registrations for this event category.
     *
     * FK: registrations.event_category_id → event_categories.id
     *
     * Eager load hint: with('registrations.athlete')
     *
     * @return HasMany<Registration>
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'event_category_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Scope to a specific gender.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<EventCategory>  $query
     * @param  string  $gender  male|female|mixed
     * @return \Illuminate\Database\Eloquent\Builder<EventCategory>
     */
    public function scopeForGender($query, string $gender)
    {
        return $query->where('gender', $gender);
    }

    /**
     * Scope to categories that still have open slots.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<EventCategory>  $query
     * @return \Illuminate\Database\Eloquent\Builder<EventCategory>
     */
    public function scopeWithOpenSlots($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('max_participants')
              ->orWhereColumn('max_participants', '>', function ($sub) {
                  $sub->selectRaw('COUNT(*)')
                      ->from('registrations')
                      ->whereColumn('registrations.event_category_id', 'event_categories.id');
              });
        });
    }
}
