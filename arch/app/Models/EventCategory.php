<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventCategory extends Model
{
    use HasFactory;

    protected $table = 'event_categories';

    protected $fillable = [
        'event_id',
        'sport_id',
        'discipline_id',
        'age_category_id',
        'gender',
        'arena_id',
        'weight_class',
        'max_participants',
        'format',
        'notes',
    ];

    protected $casts = [
        'max_participants' => 'integer',
    ];

    const FORMAT_SCORING = 'scoring';
    const GENDER_MALE = 'male';
    const GENDER_FEMALE = 'female';

    // Relationships
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }

    public function ageCategory(): BelongsTo
    {
        return $this->belongsTo(AgeCategory::class);
    }

    public function eventParticipants(): HasMany
    {
        return $this->hasMany(EventParticipant::class);
    }
    public function arena(): BelongsTo
    {
    return $this->belongsTo(Arena::class);
    }

    // FIXED: contests instead of matches for solo
    public function contests(): HasMany
    {
        return $this->hasMany(Contest::class, 'event_category_id');
        // return $this->hasMany(Contest::class, 'discipline_id', 'discipline_id');
    }

    public function winners(): HasMany
    {
        return $this->hasMany(Winner::class);
    }

    // Scopes
    public function scopeScoring($query)
    {
        return $query->where('format', self::FORMAT_SCORING);
    }

    public function getLabel(): string
    {
        $parts = [$this->discipline?->name ?? '', $this->ageCategory?->name ?? '', ucfirst($this->gender)];
        return implode(' - ', array_filter($parts));
    }
    public function sessions(): HasMany
{
    return $this->hasMany(CompetitionSession::class);
}
}

