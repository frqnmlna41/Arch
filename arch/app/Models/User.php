<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * App\Models\User
 *
 * @property int                             $id
 * @property string                          $name
 * @property string                          $email
 * @property string|null                     $phone
 * @property string|null                     $avatar
 * @property bool                            $is_active
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Athlete>  $athletes
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Score>    $scores
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Event>    $createdEvents
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role>       $roles
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    // ── Guard para Spatie ──────────────────────────────────────────
    protected $guard_name = 'web';

    // ── Fillable ───────────────────────────────────────────────────
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'is_active',
    ];

    // ── Hidden ────────────────────────────────────────────────────
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ── Casts ─────────────────────────────────────────────────────
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // ══════════════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ══════════════════════════════════════════════════════════════

    /**
     * Atlet-atlet yang dikelola oleh user ini (sebagai coach).
     * Role: coach
     */
    public function athletes(): HasMany
    {
        return $this->hasMany(Athlete::class, 'coach_id');
    }

    /**
     * Nilai-nilai yang diinput oleh user ini (sebagai juri).
     * Role: judge
     */
    public function scores(): HasMany
    {
        return $this->hasMany(Score::class, 'judge_id');
    }

    /**
     * Event-event yang dibuat oleh user ini (sebagai admin).
     */
    public function createdEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'created_by');
    }

    /**
     * Peserta event yang didaftarkan oleh user ini (coach mendaftarkan atlet).
     */
    public function registeredParticipants(): HasMany
    {
        return $this->hasMany(EventParticipant::class, 'registered_by');
    }

    /**
     * Hasil pertandingan yang direkap oleh user ini (admin/operator).
     */
    public function recordedResults(): HasMany
    {
        return $this->hasMany(MatchResult::class, 'recorded_by');
    }

    /**
     * Sertifikat yang diterbitkan oleh user ini (admin).
     */
    public function issuedCertificates(): HasMany
    {
        return $this->hasMany(Certificate::class, 'issued_by');
    }

    // ══════════════════════════════════════════════════════════════
    // HELPER METHODS
    // ══════════════════════════════════════════════════════════════

    /**
     * Cek apakah user adalah admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Cek apakah user adalah coach.
     */
    public function isCoach(): bool
    {
        return $this->hasRole('coach');
    }

    /**
     * Cek apakah user adalah judge.
     */
    public function isJudge(): bool
    {
        return $this->hasRole('judge');
    }

    /**
     * Cek apakah user adalah athlete.
     */
    public function isAthlete(): bool
    {
        return $this->hasRole('athlete');
    }
}
