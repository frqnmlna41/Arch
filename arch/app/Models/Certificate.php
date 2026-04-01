<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Certificate
 *
 * Merepresentasikan sertifikat yang diterbitkan untuk seorang pemenang.
 * Relasi one-to-one dengan Winner (satu winner satu sertifikat).
 *
 * @property int                             $id
 * @property int                             $winner_id
 * @property int|null                        $issued_by          FK ke users (admin)
 * @property string                          $certificate_number Nomor unik: CERT-2026-00001
 * @property string|null                     $file_path          Path ke file PDF
 * @property \Illuminate\Support\Carbon|null $issued_at
 * @property bool                            $is_printed
 * @property \Illuminate\Support\Carbon|null $printed_at
 * @property string|null                     $template_version
 * @property string|null                     $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read Winner   $winner
 * @property-read User|null $issuedBy
 *
 * @property-read Athlete  $athlete   Via winner
 * @property-read Event    $event     Via winner
 */
class Certificate extends Model
{
    use HasFactory;

    protected $table = 'certificates';

    // ── Fillable ───────────────────────────────────────────────────
    protected $fillable = [
        'winner_id',
        'issued_by',
        'certificate_number',
        'file_path',
        'issued_at',
        'is_printed',
        'printed_at',
        'template_version',
        'notes',
    ];

    // ── Casts ─────────────────────────────────────────────────────
    protected $casts = [
        'issued_at'  => 'datetime',
        'printed_at' => 'datetime',
        'is_printed' => 'boolean',
    ];

    // ══════════════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ══════════════════════════════════════════════════════════════

    /**
     * Pemenang yang mendapatkan sertifikat ini.
     */
    public function winner(): BelongsTo
    {
        return $this->belongsTo(Winner::class);
    }

    /**
     * Admin yang menerbitkan sertifikat ini.
     */
    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    // ══════════════════════════════════════════════════════════════
    // ACCESSORS (shortcut via winner)
    // ══════════════════════════════════════════════════════════════

    /**
     * Mendapatkan atlet pemilik sertifikat ini (shortcut via winner).
     */
    public function getAthleteAttribute(): ?Athlete
    {
        return $this->winner?->athlete;
    }

    /**
     * Mendapatkan event tempat sertifikat ini diraih (shortcut via winner).
     */
    public function getEventAttribute(): ?Event
    {
        return $this->winner?->event;
    }

    // ══════════════════════════════════════════════════════════════
    // SCOPES
    // ══════════════════════════════════════════════════════════════

    public function scopeIssued($query)
    {
        return $query->whereNotNull('issued_at');
    }

    public function scopePrinted($query)
    {
        return $query->where('is_printed', true);
    }

    public function scopeNotPrinted($query)
    {
        return $query->where('is_printed', false);
    }

    // ══════════════════════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════════════════════

    public function isIssued(): bool
    {
        return ! is_null($this->issued_at);
    }

    public function isPrinted(): bool
    {
        return $this->is_printed;
    }

    public function hasFile(): bool
    {
        return ! is_null($this->file_path);
    }

    /**
     * Generate nomor sertifikat otomatis.
     * Format: CERT-YYYY-NNNNN
     */
    public static function generateNumber(): string
    {
        $year = now()->year;
        $last = static::whereYear('created_at', $year)->count();
        return sprintf('CERT-%d-%05d', $year, $last + 1);
    }
}
