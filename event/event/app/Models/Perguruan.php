<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Perguruan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'address',
        'phone',
        'email',
        'logo',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Get the full logo URL.
     */
    public function getLogoUrlAttribute(): string
    {
        return $this->logo
            ? asset('storage/' . $this->logo)
            : asset('images/default-logo.png');
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * All athletes that belong to this perguruan.
     *
     * FK: athletes.perguruan_id → perguruans.id
     *
     * Eager load hint: with('athletes')
     *
     * @return HasMany<Athlete>
     */
    public function athletes(): HasMany
    {
        return $this->hasMany(Athlete::class, 'perguruan_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Scope to only active perguruans.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Perguruan>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Perguruan>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
