<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaoluScore extends Model
{
    protected $fillable = [
        'contest_id',
        'judge_1', 'judge_2', 'judge_3', 'judge_4', 'judge_5',
        'deduction',
        'final_score',
        'inputted_by',
        'inputted_at',
    ];

    protected $casts = [
        'inputted_at' => 'datetime',
    ];

    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

    public function inputter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inputted_by');
    }

    /**
     * Hitung final score Taolu:
     * Buang nilai tertinggi & terendah dari 5 juri,
     * jumlahkan 3 sisanya, kurangi deduction.
     */
    public function calculateFinalScore(): float
    {
        $scores = collect([
            $this->judge_1,
            $this->judge_2,
            $this->judge_3,
            $this->judge_4,
            $this->judge_5,
        ])->filter()->sort()->values();

        // Minimal 3 nilai juri untuk dihitung
        if ($scores->count() < 3) {
            return 0;
        }

        // Buang tertinggi & terendah
        $trimmed = $scores->slice(1, $scores->count() - 2);

        return round($trimmed->sum() - $this->deduction, 3);
    }

    /**
     * Simpan dan update final_score otomatis
     */
    public function saveWithFinalScore(): void
    {
        $this->final_score  = $this->calculateFinalScore();
        $this->inputted_at  = now();
        $this->inputted_by  = auth()->id();
        $this->save();

        // Update status contest jadi done
        $this->contest->update(['status' => Contest::STATUS_DONE]);
    }
}