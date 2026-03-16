<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\MatchResult
 *
 * Menyimpan hasil akhir dari satu pertandingan.
 * Relasi one-to-one dengan Match.
 * Diisi setelah pertandingan selesai oleh admin/operator.
 *
 * @property int                             $id
 * @property int                             $match_id
 * @property int                             $recorded_by         FK ke users (admin)
 * @property int|null                        $winner_id           FK ke athletes (null jika draw/solo)
 * @property float|null                      $athlete1_score      Total skor akhir atlet1
 * @property float|null                      $athlete2_score      Total skor akhir atlet2
 * @property int                             $athlete1_rounds_won
 * @property int                             $athlete2_rounds_won
 * @property string|null                     $win_method          points|knockout|technical_ko|walkover|disqualification|withdrawal|scoring|draw
 * @property string|null                     $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read Match        $match
 * @property-read Athlete|null $winner
 * @property-read User         $recordedBy
 */
class MatchResult extends Model
{
    use HasFactory;

    protected $table = 'match_results';

    // ── Fillable ───────────────────────────────────────────────────
    protected $fillable = [
        'match_id',
        'recorded_by',
        'winner_id',
        'athlete1_score',
        'athlete2_score',
        'athlete1_rounds_won',
        'athlete2_rounds_won',
        'win_method',
        'notes',
    ];

    // ── Casts ─────────────────────────────────────────────────────
    protected $casts = [
        'athlete1_score'      => 'decimal:3',
        'athlete2_score'      => 'decimal:3',
        'athlete1_rounds_won' => 'integer',
        'athlete2_rounds_won' => 'integer',
    ];

    // ── Win method constants ───────────────────────────────────────
    const METHOD_POINTS            = 'points';
    const METHOD_KNOCKOUT          = 'knockout';
    const METHOD_TECHNICAL_KO      = 'technical_ko';
    const METHOD_WALKOVER          = 'walkover';
    const METHOD_DISQUALIFICATION  = 'disqualification';
    const METHOD_WITHDRAWAL        = 'withdrawal';
    const METHOD_SCORING           = 'scoring';  // untuk Taolu/Forms
    const METHOD_DRAW              = 'draw';

    // ══════════════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ══════════════════════════════════════════════════════════════

    /**
     * Pertandingan yang menghasilkan result ini.
     */
    public function match(): BelongsTo
    {
        return $this->belongsTo(Match::class);
    }

    /**
     * Atlet yang memenangkan pertandingan.
     * null jika draw atau pertandingan solo tanpa penilaian menang/kalah.
     */
    public function winner(): BelongsTo
    {
        return $this->belongsTo(Athlete::class, 'winner_id');
    }

    /**
     * Admin/operator yang merekap hasil pertandingan.
     */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    // ══════════════════════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════════════════════

    /** Cek apakah hasil adalah draw. */
    public function isDraw(): bool
    {
        return $this->win_method === self::METHOD_DRAW;
    }

    /** Cek apakah ada pemenang yang jelas. */
    public function hasWinner(): bool
    {
        return ! is_null($this->winner_id);
    }

    /**
     * Mendapatkan skor atlet tertentu dari result ini.
     */
    public function getScoreForAthlete(int $athleteId): ?float
    {
        $match = $this->match;

        if ($match->athlete1_id === $athleteId) {
            return $this->athlete1_score;
        }

        if ($match->athlete2_id === $athleteId) {
            return $this->athlete2_score;
        }

        return null;
    }
}
