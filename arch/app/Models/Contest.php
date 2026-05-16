<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Contest extends Model
{
protected $fillable = [
    // 'event_category_id',
    'athlete_id',
    'registration_id',
    'discipline_id',
    'age_category_id',
    'competition_session_id',
    'appearance_order',
    'status',
];

    public function eventCategory(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class);
    }

    public function athlete(): BelongsTo
    {
        return $this->belongsTo(Athlete::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(CompetitionSession::class, 'competition_session_id');
    }

    public function score(): HasOne
    {
        return $this->hasOne(TaoluScore::class);
    }

    /**
     * Estimasi jam tampil atlet ini
     */
    public function estimatedTime(): ?Carbon
    {
        if (! $this->session || ! $this->appearance_order) return null;
        return $this->session->estimatedTimeForOrder($this->appearance_order);
    }

    public function isScored(): bool
    {
        return $this->score !== null && $this->score->final_score !== null;
    }

    public function arena()
    {
        return $this->belongsTo(Arena::class);
    }
}