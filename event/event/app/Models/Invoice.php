<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'invoice_number',
        'status',
        'total_amount',
        'paid_at',
        'due_date',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, mixed>
     */
    protected $casts = [
        'status'       => InvoiceStatus::class,
        'total_amount' => 'decimal:2',
        'paid_at'      => 'datetime',
        'due_date'     => 'date',
    ];

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Format total amount as Indonesian Rupiah.
     */
    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }

    /**
     * Check if the invoice is overdue.
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->status !== InvoiceStatus::Paid
            && $this->due_date?->isPast();
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The user this invoice belongs to.
     *
     * FK: invoices.user_id → users.id
     *
     * @return BelongsTo<User, Invoice>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * All line items on this invoice.
     *
     * FK: invoice_items.invoice_id → invoices.id
     *
     * Eager load hint: with('items.registration.athlete', 'items.registration.eventCategory')
     *
     * @return HasMany<InvoiceItem>
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Scope to paid invoices.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Invoice>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Invoice>
     */
    public function scopePaid($query)
    {
        return $query->where('status', InvoiceStatus::Paid->value);
    }

    /**
     * Scope to unpaid (pending) invoices.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Invoice>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Invoice>
     */
    public function scopeUnpaid($query)
    {
        return $query->where('status', InvoiceStatus::Unpaid->value);
    }

    /**
     * Scope to overdue invoices.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Invoice>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Invoice>
     */
    public function scopeOverdue($query)
    {
        return $query
            ->where('status', '!=', InvoiceStatus::Paid->value)
            ->where('due_date', '<', now()->toDateString());
    }
}
