<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class CompetitionSession extends Model
{
protected $fillable = [
    'event_category_id',
    'gender',
    'arena_id',             // ← ganti dari lapangan
    'start_time',
    'duration_per_athlete',
    'notes',
    'status',
    
];
    protected $casts = [
        'start_time' => 'datetime',
    ];
    public function arena(): BelongsTo
    {
        return $this->belongsTo(Arena::class);
    }
    public function eventCategory(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class);
    }

    public function contests(): HasMany
    {
        return $this->hasMany(
            Contest::class,
            'competition_session_id'
        )->orderBy('appearance_order');
    }

    /**
     * Hitung estimasi waktu selesai sesi
     */
    public function estimatedEndTime(): Carbon
    {
        $totalMinutes = $this->contests->count() * $this->duration_per_athlete;

        return $this->start_time
            ->copy()
            ->addMinutes($totalMinutes);
    }

    /**
     * Hitung estimasi waktu tampil per atlet
     */
    public function estimatedTimeForOrder(int $order): Carbon
    {
        $offset = ($order - 1) * $this->duration_per_athlete;

        return $this->start_time
            ->copy()
            ->addMinutes($offset);
    }

    /**
     * Total durasi sesi
     */
    public function totalDurationMinutes(): int
    {
        return $this->contests->count()
            * $this->duration_per_athlete;
    }
}