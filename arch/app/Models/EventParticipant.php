<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EventParticipant extends Model
{
    use HasFactory;

    protected $table = 'event_participants';

    protected $fillable = [
        'event_category_id',      // ← aktifkan
        'athlete_id',
        'registration_id',
        'registered_by',
        'verified_by',
        'registration_number',
        'status',
        'weight_at_registration',
        'verified_at',
        'rejection_reason',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    const STATUS_PENDING      = 'pending';
    const STATUS_VERIFIED     = 'verified';
    const STATUS_REJECTED     = 'rejected';
    const STATUS_WITHDRAWN    = 'withdrawn';
    const STATUS_DISQUALIFIED = 'disqualified';

    // ═══════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════
    public function event():BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Kategori event (discipline + age_category + gender dalam satu event)
     */
    public function eventCategory(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class);
    }

    /**
     * Ambil event via eventCategory (tidak perlu kolom event_id langsung)
     */
    public function getEventAttribute()
    {
        return $this->eventCategory?->event;
    }

    /**
     * Ambil discipline via eventCategory
     */
    public function getDisciplineAttribute()
    {
        return $this->eventCategory?->discipline;
    }
    // Ambil jumlah pertandingan base disiplin
    public function getParticipantCountAttribute()
    {
        return $this->eventParticipants?->count();
    }

    /**
     * Ambil age category via eventCategory
     */
    public function getAgeCategoryAttribute()
    {
        return $this->eventCategory?->ageCategory;
    }

    public function athlete(): BelongsTo
    {
        return $this->belongsTo(Athlete::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Slot tanding peserta ini (satu participant = satu contest di Taolu)
     */
    public function contest(): HasOne
    {
        return $this->hasOne(Contest::class);
    }

    // ═══════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════

    public function scopeVerified($query)
    {
        return $query->where('status', self::STATUS_VERIFIED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    // ═══════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════

    public function isVerified(): bool
    {
        return $this->status === self::STATUS_VERIFIED;
    }

    public function verify(User $admin): self
    {
        $this->update([
            'status'      => self::STATUS_VERIFIED,
            'verified_by' => $admin->id,
            'verified_at' => now(),
        ]);
        return $this;
    }

    public function reject(string $reason): self
    {
        $this->update([
            'status'           => self::STATUS_REJECTED,
            'rejection_reason' => $reason,
        ]);
        return $this;
    }

    /**
     * Generate nomor pendaftaran otomatis
     * Format: WU-2024-0001
     */
    public static function generateNumber(): string
    {
        $prefix = 'WU-' . now()->year . '-';
        $last   = self::where('registration_number', 'like', $prefix . '%')
                      ->orderByDesc('registration_number')
                      ->value('registration_number');

        $sequence = $last ? (int) substr($last, -4) + 1 : 1;

        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}