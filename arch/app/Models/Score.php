<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Score
 *
 * Merepresentasikan nilai yang diberikan oleh seorang juri
 * kepada seorang atlet dalam sebuah pertandingan.
 *
 * Setiap baris = satu penilaian dari satu juri untuk satu atlet
 * dalam satu pertandingan (dengan score_type dan round_number opsional).
 *
 * Untuk Wushu Taolu   : judge memberi nilai (misal 9.5)
 * Untuk Wushu Sanda   : poin per ronde per juri
 * Untuk Wing Chun Forms: nilai teknik per aspek
 *
 * @property int                             $id
 * @property int                             $match_id
 * @property int                             $judge_id
 * @property int                             $athlete_id
 * @property float                           $score
 * @property string|null                     $score_type    technique|difficulty|deduction|total|round_1|dst
 * @property int|null                        $round_number  Nomor ronde (untuk Sanda)
 * @property string|null                     $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read Match   $match
 * @property-read User    $judge
 * @property-read Athlete $athlete
 */
class Score extends Model
{
    use HasFactory;

    protected $table = 'scores';

    // ── Fillable ───────────────────────────────────────────────────
    protected $fillable = [
        'match_id',
        'judge_id',
        'athlete_id',
        'score',
        'score_type',
        'round_number',
        'notes',
    ];

    // ── Casts ─────────────────────────────────────────────────────
    protected $casts = [
        'score'        => 'decimal:2',
        'round_number' => 'integer',
    ];

    // ── Score type constants ───────────────────────────────────────
    const TYPE_TECHNIQUE  = 'technique';
    const TYPE_DIFFICULTY = 'difficulty';
    const TYPE_DEDUCTION  = 'deduction';
    const TYPE_TOTAL      = 'total';

    // ══════════════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ══════════════════════════════════════════════════════════════

    /**
     * Pertandingan tempat nilai ini diberikan.
     */
    public function match(): BelongsTo
    {
        return $this->belongsTo(Match::class);
    }

    /**
     * Juri (user dengan role judge) yang memberikan nilai ini.
     */
    public function judge(): BelongsTo
    {
        return $this->belongsTo(User::class, 'judge_id');
    }

    /**
     * Atlet yang menerima nilai ini.
     */
    public function athlete(): BelongsTo
    {
        return $this->belongsTo(Athlete::class);
    }

    // ══════════════════════════════════════════════════════════════
    // SCOPES
    // ══════════════════════════════════════════════════════════════

    public function scopeForMatch($query, int $matchId)
    {
        return $query->where('match_id', $matchId);
    }

    public function scopeForAthlete($query, int $athleteId)
    {
        return $query->where('athlete_id', $athleteId);
    }

    public function scopeByJudge($query, int $judgeId)
    {
        return $query->where('judge_id', $judgeId);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('score_type', $type);
    }

    public function scopeByRound($query, int $roundNumber)
    {
        return $query->where('round_number', $roundNumber);
    }
}
