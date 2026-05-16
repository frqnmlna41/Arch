<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\Models\Invoice
 *
 * Merepresentasikan tagihan biaya pendaftaran atlet ke event.
 * Dibuat oleh admin, ditujukan ke user.
 *
 * Alur status:
 *   draft → sent → paid
 *   draft → cancelled
 *   sent  → cancelled
 *
 * @property int                             $id
 * @property int                             $user_id          FK ke users (role: user)
 * @property string                          $invoice_number    Format: INV-YYYY-NNNN
 * @property float                           $total_amount
 * @property string                          $status            draft|sent|paid|cancelled
 * @property \Illuminate\Support\Carbon|null $due_date
 * @property \Illuminate\Support\Carbon|null $paid_at
 * @property string|null                     $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read User                       $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, InvoiceItem> $items
 */
class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'invoice_number',
        'total_amount',
        'status',
        'due_date',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'due_date'     => 'date',
        'paid_at'      => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    // Status constants
    const STATUS_DRAFT     = 'draft';
    const STATUS_SENT      = 'sent';
    const STATUS_PAID      = 'paid';
    const STATUS_CANCELLED = 'cancelled';

    // ═══════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════════════════════

    /**
     * User penerima invoice ini.
     * ✅ FIXED: belongsTo User (bukan user::class yang tidak ada)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Baris-baris item dalam invoice ini.
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    // ═══════════════════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════════════════

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SENT);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SENT)
                     ->where('due_date', '<', now());
    }

    public function scopeForuser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    // ═══════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_SENT
            && $this->due_date?->isPast();
    }

    /** Apakah invoice bisa diedit (hanya draft) */
    public function isEditable(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /** Konfirmasi pembayaran */
    public function markAsPaid(?string $paidAt = null): void
    {
        $this->update([
            'status'  => self::STATUS_PAID,
            'paid_at' => $paidAt ?? now(),
        ]);
    }

    /** Hitung ulang total dari semua items */
    public function recalculateTotal(): void
    {
        $this->update([
            'total_amount' => $this->items()->sum('price'),
        ]);
    }

    /** Accessor: label status untuk UI */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT     => 'Draft',
            self::STATUS_SENT      => $this->isOverdue() ? 'Overdue' : 'Terkirim',
            self::STATUS_PAID      => 'Lunas',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default                => ucfirst($this->status),
        };
    }

    /** Accessor: CSS class untuk badge status */
    public function getStatusClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT     => 'ath-status--pending',
            self::STATUS_SENT      => $this->isOverdue() ? 'ath-status--inactive' : 'ath-status--active',
            self::STATUS_PAID      => 'ath-status--active',
            self::STATUS_CANCELLED => 'ath-status--inactive',
            default                => '',
        };
    }

    /**
     * Generate nomor invoice unik.
     * Format: INV-YYYY-NNNN
     */
    // public static function generateNumber(): string
    // {
    //     $year  = now()->year;
    //     $count = static::whereYear('created_at', $year)->count() + 1;

    //     return sprintf('INV-%d-%04d', $year, $count);
    // }
        public static function generateNumber(): string
    {
        $prefix = 'INV-' . now()->format('Ymd');
        $last   = self::where('invoice_number', 'like', $prefix . '%')
                      ->orderByDesc('invoice_number')
                      ->value('invoice_number');

        $sequence = $last
            ? (int) substr($last, -4) + 1
            : 1;

        return $prefix . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
