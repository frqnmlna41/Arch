<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Athlete extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'perguruan_id',
        'name',
        'birth_date',
        'gender',
        'club',
        'phone',
        'photo',
        'id_card_number',
        'weight',
        'height',
        'address',
        'is_active',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'weight'     => 'decimal:2',
        'height'     => 'decimal:2',
        'is_active'  => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Akun user milik atlet (jika atlet punya akun sendiri).
     */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function eventParticipants(): HasMany
    {
    return $this->hasMany(EventParticipant::class);
    }
    public function eventCategories(): HasMany
    {
    return $this->hasMany(EventCategory::class);
    }
    public function winners(): HasMany
    {
    return $this->hasMany(Winner::class);
    }

    /**
     * Coach yang mendaftarkan atlet ini.
     */
    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    /**
     * Perguruan tempat atlet terdaftar.
     */
    public function perguruan(): BelongsTo
    {
        return $this->belongsTo(Perguruan::class, 'perguruan_id');
    }

    /**
     * Disiplin-disiplin yang diikuti atlet (many-to-many via pivot athlete_discipline).
     * Pivot menyimpan age_category_id.
     *
     * Contoh akses:
     *   $athlete->disciplines                             // collection of Discipline
     *   $athlete->disciplines->first()->pivot->age_category_id
     */
    public function disciplines(): BelongsToMany
    {
        return $this->belongsToMany(Discipline::class, 'athlete_discipline')
            ->withPivot('age_category_id')
            ->withTimestamps();
    }

    /**
     * Age categories yang dipilih atlet (via pivot athlete_discipline).
     *
     * Contoh akses:
     *   $athlete->ageCategories                          // collection of AgeCategory
     *   $athlete->ageCategories->first()->pivot->discipline_id
     */
    public function ageCategories(): BelongsToMany
    {
        return $this->belongsToMany(AgeCategory::class, 'athlete_discipline')
            ->withPivot('discipline_id')
            ->withTimestamps();
    }

    /**
     * Semua registrasi kompetisi milik atlet ini.
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Filter hanya atlet aktif.
     *
     * Contoh: Athlete::active()->get()
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }


    /**
     * Filter atlet milik coach tertentu.
     *
     * Contoh: Athlete::byCoach(Auth::id())->get()
     */
    public function scopeByCoach($query, int $coachId)
    {
        return $query->where('coach_id', $coachId);
    }

    /**
     * Filter atlet milik perguruan tertentu.
     *
     * Contoh: Athlete::byPerguruan($perguruanId)->get()
     */
    public function scopeByPerguruan($query, int $perguruanId)
    {
        return $query->where('perguruan_id', $perguruanId);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /**
     * URL lengkap foto profil. Fallback ke avatar default jika kosong.
     *
     * Contoh: $athlete->photo_url
     */
    public function getPhotoUrlAttribute(): string
    {
        return $this->photo
            ? asset('storage/' . $this->photo)
            : asset('images/default-avatar.png');
    }

    /**
     * Usia atlet dihitung dari birth_date.
     *
     * Contoh: $athlete->age  →  17
     */
    public function getAgeAttribute(): int
    {
        return $this->birth_date->age;
    }

    /**
     * Nama gender yang mudah dibaca.
     *
     * Contoh: $athlete->gender_label  →  'Laki-laki' / 'Perempuan'
     */
    public function getGenderLabelAttribute(): string
    {
        return $this->gender === 'male' ? 'Laki-laki' : 'Perempuan';
    }
}

