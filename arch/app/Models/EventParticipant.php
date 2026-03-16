<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\EventParticipant
 *
 * Merepresentasikan pendaftaran atlet ke dalam sebuah event
 * untuk discipline dan age category tertentu.
 *
 * @property int                             $id
 * @property int                             $event_id
 * @property int                             $athlete_id
 * @property int                             $discipline_id
 * @property int                             $age_category_id
 * @property int                             $registered_by       FK ke users (coach/admin)
 * @property int|null                        $verified_by         FK ke users (admin)
 * @property string                          $registration_number Nomor pendaftaran unik
 * @property string                          $status              pending|verified|rejected|withdrawn|disqualified
 * @property float|null                      $weight_at_registration
 * @property \Illuminate\Support\Carbon|null $verified_at
 * @property string|null                     $rejection_reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read Event       $event
 * @property-read Athlete     $athlete
 * @property-read Discipline  $discipline
 * @property-read AgeCategory $ageCategory
 * @property-read User        $registeredBy
 * @property-read User|null   $verifiedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Match> $matches
 */
class EventParticipant extends Model
{
    use HasFactory;

    protected $table = 'event_participants';

    // ── Fillable ───────────────────────────────────────────────────
    protected $fillable = [
        'event_id',
        'athlete_id',
        'discipline_id',
        'age_category_id',
        'registered_by',
        'verified_by',
        'registration_number',
        'status',
        'weight_at_registration',
        'verified_at',
        'rejection_reason',
    ];

    // ── Casts ─────────────────────────────────────────────────────
    protected $casts = [
        'weight_at_registration' => 'decimal:2',
        'verified_at'            => 'datetime',
    ];

    // ── Status constants ───────────────────────────────────────────
    const STATUS_PENDING       = 'pending';
    const STATUS_VERIFIED      = 'verified';
    const STATUS_REJECTED      = 'rejected';
    const STATUS_WITHDRAWN     = 'withdrawn';
    const STATUS_DISQUALIFIED  = 'disqualified';

    // ══════════════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ══════════════════════════════════════════════════════════════

    /**
     * Event tempat atlet ini mendaftar.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Atlet yang mendaftar.
     */
    public function athlete(): BelongsTo
    {
        return $this->belongsTo(Athlete::class);
    }

    /**
     * Discipline yang diikuti atlet dalam event ini.
     */
    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }

    /**
     * Kategori umur atlet dalam event ini.
     */
    public function ageCategory(): BelongsTo
    {
        return $this->belongsTo(AgeCategory::class);
    }

    /**
     * User (coach/admin) yang mendaftarkan atlet ini.
     */
    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    /**
     * Admin yang memverifikasi pendaftaran.
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Pertandingan-pertandingan yang melibatkan peserta ini.
     * Relasi via athlete_id (athlete1 atau athlete2 di tabel matches).
     *
     * Catatan: Karena matches menggunakan athlete1_id dan athlete2_id,
     * kita query berdasarkan athlete_id dari peserta ini.
     */
    public function matches(): HasMany
    {
        // Pertandingan di event & discipline yang sama dengan peserta ini
        return $this->hasMany(Match::class, 'event_id', 'event_id')
            ->where('discipline_id', $this->discipline_id);
    }

    // ══════════════════════════════════════════════════════════════
    // SCOPES
    // ══════════════════════════════════════════════════════════════

    public function scopeVerified($query)
    {
        return $query->where('status', self::STATUS_VERIFIED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeForEvent($query, int $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    public function scopeForDiscipline($query, int $disciplineId)
    {
        return $query->where('discipline_id', $disciplineId);
    }

    // ══════════════════════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════════════════════

    public function isVerified(): bool
    {
        return $this->status === self::STATUS_VERIFIED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Verifikasi pendaftaran oleh admin.
     */
    public function verify(User $admin): self
    {
        $this->update([
            'status'      => self::STATUS_VERIFIED,
            'verified_by' => $admin->id,
            'verified_at' => now(),
        ]);

        return $this;
    }

    /**
     * Tolak pendaftaran dengan alasan.
     */
    public function reject(string $reason): self
    {
        $this->update([
            'status'           => self::STATUS_REJECTED,
            'rejection_reason' => $reason,
        ]);

        return $this;
    }
}
