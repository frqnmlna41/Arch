<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'phone',
        'avatar',
        'is_active',
        'perguruan',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Get user's avatar URL or default placeholder.
     */
    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : asset('images/default-avatar.png');
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * Athletes registered/owned by this user.
     *
     * FK: athletes.user_id → users.id
     *
     * Eager load hint: with('athletes')
     *
     * @return HasMany<Athlete>
     */
    public function athletes(): HasMany
    {
        return $this->hasMany(Athlete::class, 'user_id');
    }

    /**
     * Athletes coached by this user (coach role).
     *
     * FK: athletes.coach_id → users.id
     *
     * @return HasMany<Athlete>
     */
    public function coachedAthletes(): HasMany
    {
        return $this->hasMany(Athlete::class, 'coach_id');
    }

    /**
     * Registrations submitted by this user.
     *
     * FK: registrations.user_id → users.id
     *
     * Eager load hint: with('registrations.athlete', 'registrations.eventCategory')
     *
     * @return HasMany<Registration>
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'user_id');
    }

    /**
     * Invoices belonging to this user.
     *
     * FK: invoices.user_id → users.id
     *
     * Eager load hint: with('invoices.items')
     *
     * @return HasMany<Invoice>
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'user_id');
    }

    /**
     * Events created by this user (admin).
     *
     * FK: events.created_by → users.id
     *
     * @return HasMany<Event>
     */
    public function createdEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'created_by');
    }
}
