<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Perguruan extends Model
{
    use HasFactory;

    // ── Fillable ─────────────────────────────────────────
    protected $fillable = [
        'name',
        'slug',
        'address',
        'phone',
        'email',
        'logo',
        'is_active',
    ];

    // ── Casts ────────────────────────────────────────────
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
    protected static function booted()
    {
        static::creating(function ($model) {
            $model->slug = Str::slug($model->name);
        });
    }

    // ═════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ═════════════════════════════════════════════════════

    /**
     * Semua user yang tergabung dalam perguruan ini
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Atlet dalam perguruan ini (opsional jika langsung relasi)
     */
    public function athletes(): HasMany
    {
        return $this->hasMany(Athlete::class);
    }

    // ═════════════════════════════════════════════════════
    // SCOPES
    // ═════════════════════════════════════════════════════

    /**
     * Scope: aktif
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: tidak aktif
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    // ═════════════════════════════════════════════════════
    // HELPER METHODS
    // ═════════════════════════════════════════════════════

    /**
     * Cek apakah perguruan aktif
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

}

