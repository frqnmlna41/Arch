<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Athlete extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'coach_id',
        'perguruan_id',
        'disciplines_id',
        'age_category_id',
        'name',
        'birth_date',
        'gender',
        'club',
        'phone',
        'photo',
        'id_card_number',
        'weight',
        'height',
        'address',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'birth_date' => 'date',
        'weight'     => 'decimal:2',
        'height'     => 'decimal:2',
        'is_active'  => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Calculate the athlete's current age in years.
     */
    public function getAgeAttribute(): int
    {
        return $this->birth_date->age;
    }

    /**
     * Human-readable gender.
     */
    public function getGenderLabelAttribute(): string
    {
        return $this->gender === 'male' ? 'Putra' : 'Putri';
    }

    /**
     * Get the full photo URL.
     */
    public function getPhotoUrlAttribute(): string
    {
        return $this->photo
            ? asset('storage/' . $this->photo)
            : asset('images/default-athlete.png');
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The user account that owns this athlete profile.
     *
     * FK: athletes.user_id → users.id (nullable)
     *
     * @return BelongsTo<User, Athlete>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The coach (user) responsible for this athlete.
     *
     * FK: athletes.coach_id → users.id (nullable)
     *
     * @return BelongsTo<User, Athlete>
     */
    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    /**
     * The perguruan this athlete is affiliated with.
     *
     * FK: athletes.perguruan_id → perguruans.id (nullable)
     *
     * Eager load hint: with('perguruan')
     *
     * @return BelongsTo<Perguruan, Athlete>
     */
    public function perguruan(): BelongsTo
    {
        return $this->belongsTo(Perguruan::class, 'perguruan_id');
    }

    /**
     * The primary discipline this athlete competes in.
     *
     * FK: athletes.disciplines_id → disciplines.id (nullable)
     * Note: column name in migration is "disciplines_id" (plural).
     *
     * @return BelongsTo<Discipline, Athlete>
     */
    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class, 'disciplines_id');
    }

    /**
     * The age category this athlete belongs to.
     *
     * FK: athletes.age_category_id → age_categories.id (nullable)
     *
     * @return BelongsTo<AgeCategory, Athlete>
     */
    public function ageCategory(): BelongsTo
    {
        return $this->belongsTo(AgeCategory::class, 'age_category_id');
    }

    /**
     * All event registrations for this athlete.
     *
     * FK: registrations.athlete_id → athletes.id
     *
     * Eager load hint: with('registrations.eventCategory.event')
     *
     * @return HasMany<Registration>
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'athlete_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Scope to only active athletes.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Athlete>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Athlete>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by gender.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Athlete>  $query
     * @param  string  $gender  male|female
     * @return \Illuminate\Database\Eloquent\Builder<Athlete>
     */
    public function scopeGender($query, string $gender)
    {
        return $query->where('gender', $gender);
    }
}
