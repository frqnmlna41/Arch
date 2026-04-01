<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Collection;

/**
 * App\Models\Event
 *
 * @property int                             $id
 * @property int                             $created_by
 * @property string                          $name
 * @property string                          $location
 * @property \Illuminate\Support\Carbon      $start_date
 * @property \Illuminate\Support\Carbon      $end_date
 * @property \Illuminate\Support\Carbon|null $registration_start
 * @property \Illuminate\Support\Carbon|null $registration_end
 * @property string                          $status       draft|published|ongoing|completed|cancelled
 * @property string|null                     $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @property-read User                              $creator
 * @property-read Collection<int, EventParticipant> $participants
 * @property-read Collection<int, Match>            $matches
 */
class Event extends Model
{
    use HasFactory, SoftDeletes;

    // ── Fillable ───────────────────────────────────────────────────
    protected $fillable = [
        'created_by',
        'name',
        'location',
        'start_date',
        'end_date',
        'registration_start',
        'registration_end',
        'status',
        'description',
    ];

    // ── Casts ─────────────────────────────────────────────────────
    protected $casts = [
        'start_date'         => 'date',
        'end_date'           => 'date',
        'registration_start' => 'date',
        'registration_end'   => 'date',
    ];

    // ── Status constants ───────────────────────────────────────────
    const STATUS_DRAFT     = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_ONGOING   = 'ongoing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // ══════════════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ══════════════════════════════════════════════════════════════

    /**
     * Admin/user yang membuat event ini.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Semua peserta yang terdaftar dalam event ini.
     */
    public function participants(): HasMany
    {
        return $this->hasMany(EventParticipant::class);
    }

    /**
     * Semua pertandingan dalam event ini.
     */
    public function matches(): HasMany
    {
        return $this->hasMany(Contest::class);
    }

    // ══════════════════════════════════════════════════════════════
    // SCOPES
    // ══════════════════════════════════════════════════════════════

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeOngoing($query)
    {
        return $query->where('status', self::STATUS_ONGOING);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    /** Event yang masih dalam periode registrasi. */
    public function scopeOpenRegistration($query)
    {
        $now = now()->toDateString();
        return $query->where('registration_start', '<=', $now)
                     ->where('registration_end', '>=', $now)
                     ->where('status', self::STATUS_PUBLISHED);
    }

    // ══════════════════════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════════════════════

    public function isRegistrationOpen(): bool
    {
        $now = now()->toDateString();
        return $this->status === self::STATUS_PUBLISHED
            && $this->registration_start <= $now
            && $this->registration_end   >= $now;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }
}
