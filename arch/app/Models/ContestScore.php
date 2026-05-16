<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContestScore extends Model
{
    use HasFactory;

    protected $table = 'contest_scores';

    protected $fillable = [
        'contest_id',
        'judge_id',
        'score',
        'notes',
        'scored_at',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'scored_at' => 'datetime',
    ];

    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

    public function judge(): BelongsTo
    {
        return $this->belongsTo(User::class, 'judge_id');
    }
}

