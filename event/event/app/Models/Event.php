<?php

namespace App\Models;

use App\Enums\EventStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'location',
        'start_date',
        'end_date',
        'description',
        'status',
        'registration_start',
        'registration_end',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, mixed>
     */
    protected $casts = [
        'start_date'         => 'date',
        'end_date'           => 'date',
        'registration_start' => 'date',
        'registration_end'   => 'date',
        'status'             => EventStatus::class,
    ];

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Check whether registration is currently open.
     */
    public function getIsRegistrationOpenAttribute(): bool
    {
        $now = now()->toDateString();

        return $this->status === EventStatus::Published
            && $this->registration_start?->toDateString() <= $now
            && $this->registration_end?->toDateString() >= $now;
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The admin user who created this event.
     *
     * FK: events.created_by → users.id
     *
     * @return BelongsTo<User, Event>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * All competition categories within this event.
     *
     * FK: event_categories.event_id → events.id
     *
     * Eager load hint: with('eventCategories.discipline', 'eventCategories.ageCategory')
     *
     * @return HasMany<EventCategory>
     */
    public function eventCategories(): HasMany
    {
        return $this->hasMany(EventCategory::class, 'event_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Scope to events that are published or ongoing.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Event>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Event>
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            EventStatus::Published->value,
            EventStatus::Ongoing->value,
        ]);
    }

    /**
     * Scope to events with open registration window.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Event>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Event>
     */
    public function scopeRegistrationOpen($query)
    {
        $today = now()->toDateString();

        return $query
            ->where('status', EventStatus::Published->value)
            ->where('registration_start', '<=', $today)
            ->where('registration_end', '>=', $today);
    }
}
