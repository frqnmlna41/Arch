<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Match as Contest;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sport_type',
        'registration_start',
        'registration_end',
        'start_date',
        'end_date',
        'location',
        'venue',
        'capacity',
        'status',
        'created_by',
        'banner_image',
        'registration_fee',
    ];

    protected $casts = [
        'registration_start' => 'datetime',
        'registration_end' => 'datetime',
        'start_date' => 'date',
        'end_date' => 'date',
        'capacity' => 'integer',
        'registration_fee' => 'decimal:2',
    ];

    /**
     * Scopes
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
                     ->where('start_date', '>=', now()->startOfDay());
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('status', 'published')
                     ->where('start_date', '>', now());
    }

    public function scopePast(Builder $query): Builder
    {
        return $query->where('end_date', '<', now());
    }

    /**
     * Relations
     */
    public function categories(): HasMany
    {
        return $this->hasMany(EventCategory::class);
    }

    // public function matches(): HasMany
    // {
    //     return $this->hasManyThrough(Contest::class, EventCategory::class);
    // }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
     public function participants(): HasMany
    {
        return $this->hasMany(EventParticipant::class);
    }

    /**
     * Accessors
     */
    public function getDurationAttribute(): string
    {
        return $this->start_date->diffForHumans($this->end_date);
    }

    public function getStatusBadgeAttribute(): string
    {
        $status = $this->status ?? 'draft';

        return match($status) {
            'draft' => 'bg-gray-100 text-gray-800',
            'published' => 'bg-green-100 text-green-800',
            'ongoing' => 'bg-blue-100 text-blue-800',
            'completed' => 'bg-indigo-100 text-indigo-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Is registration open?
     */
    public function getRegistrationOpenAttribute(): bool
    {
        return now()->between(
            $this->registration_start,
            $this->registration_end
        );
    }

    /**
     * Total registered participants
     */
    public function getTotalParticipantsAttribute(): int
    {
        return $this->categories()
            ->withCount('eventParticipants')
            ->get()
            ->sum('event_participants_count');
    }

    /**
     * Check if event has matches generated
     */
    public function getHasMatchesAttribute(): bool
    {
        $this->contests()->exists();
    }
}
