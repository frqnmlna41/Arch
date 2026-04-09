<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;
    // use HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $guard_name = 'web';

    // ── Fillable ─────────────────────────────────────────
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'status',         // ✅ NEW
        'perguruan_id',   // ✅ NEW
    ];

    // ── Hidden ───────────────────────────────────────────
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ── Casts ────────────────────────────────────────────
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ═════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ═════════════════════════════════════════════════════

    /**
     * Relasi ke perguruan / club / dojo
     */
    public function perguruan(): BelongsTo
    {
        return $this->belongsTo(Perguruan::class);
    }

    public function athletes(): HasMany
    {
        return $this->hasMany(Athlete::class, 'coach_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(Score::class, 'judge_id');
    }

    public function createdEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'created_by');
    }

    public function registeredParticipants(): HasMany
    {
        return $this->hasMany(EventParticipant::class, 'registered_by');
    }

    public function recordedResults(): HasMany
    {
        return $this->hasMany(ContestResult::class, 'recorded_by');
    }

    public function issuedCertificates(): HasMany
    {
        return $this->hasMany(Certificate::class, 'issued_by');
    }

    // ═════════════════════════════════════════════════════
    // SCOPES (🔥 penting untuk CMS & filtering)
    // ═════════════════════════════════════════════════════

    /**
     * Scope: user pending (belum disetujui admin)
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: user aktif
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: user nonaktif
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', 'inactive');
    }

    // ═════════════════════════════════════════════════════
    // HELPER METHODS
    // ═════════════════════════════════════════════════════

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isCoach(): bool
    {
        return $this->hasRole('coach');
    }

    public function isJudge(): bool
    {
        return $this->hasRole('judge');
    }

    public function isAthlete(): bool
    {
        return $this->hasRole('athlete');
    }

    /**
     * Shortcut status
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
    public function getStatusClassAttribute()
    {
    return match ($this->status) {
        'pending' => 'bg-yellow-100 text-yellow-800',
        'active' => 'bg-green-100 text-green-800',
        'rejected' => 'bg-red-100 text-red-800',
        default => 'bg-gray-100 text-gray-800',
    };
    }
}
