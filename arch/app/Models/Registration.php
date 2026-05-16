<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Registration extends Model
{
    protected $fillable = [
        'user_id',  // coach_id → user_id (consistent with migrations)
        'athlete_id',
        'discipline_id',
        'age_category_id',
        'event_category_id',
        'status',
        'registered_at',
        
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'appearance_order' => 'integer',
    ];

    // Relationships
    public function user(): BelongsTo  // coach
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function athlete(): BelongsTo
    {
        return $this->belongsTo(Athlete::class);
    }

    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }

    public function age_category(): BelongsTo
    {
        return $this->belongsTo(AgeCategory::class);
    }

    public function invoiceItem(): HasOne
    {
        return $this->hasOne(InvoiceItem::class);
    }

    // NEW: Solo contests from this registration
    public function contests(): HasMany
    {
        return $this->hasMany(Contest::class);
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Helpers
    public function approve(): void
    {
        $this->update(['status' => 'approved']);
    }

    public function reject(): void
    {
        $this->update(['status' => 'rejected']);
    }
}

