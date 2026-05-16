<?php

namespace App\Models;

use App\Enums\RegistrationPaymentStatus;
use App\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Registration extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'athlete_id',
        'event_category_id',
        'status',
        'payment_status',
        'notes',
        'registered_at',
        'approved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, mixed>
     */
    protected $casts = [
        'status'         => RegistrationStatus::class,
        'payment_status' => RegistrationPaymentStatus::class,
        'registered_at'  => 'datetime',
        'approved_at'    => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The user who submitted this registration.
     *
     * FK: registrations.user_id → users.id
     *
     * @return BelongsTo<User, Registration>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The athlete being registered.
     *
     * FK: registrations.athlete_id → athletes.id
     *
     * Eager load hint: with('athlete.perguruan')
     *
     * @return BelongsTo<Athlete, Registration>
     */
    public function athlete(): BelongsTo
    {
        return $this->belongsTo(Athlete::class, 'athlete_id');
    }

    /**
     * The competition category the athlete is registered into.
     *
     * FK: registrations.event_category_id → event_categories.id
     *
     * Eager load hint: with('eventCategory.event', 'eventCategory.discipline')
     *
     * @return BelongsTo<EventCategory, Registration>
     */
    public function eventCategory(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class, 'event_category_id');
    }

    /**
     * The invoice line item linked to this registration.
     *
     * FK: invoice_items.registration_id → registrations.id
     *
     * Eager load hint: with('invoiceItem.invoice')
     *
     * @return HasOne<InvoiceItem>
     */
    public function invoiceItem(): HasOne
    {
        return $this->hasOne(InvoiceItem::class, 'registration_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Scope to approved registrations.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Registration>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Registration>
     */
    public function scopeApproved($query)
    {
        return $query->where('status', RegistrationStatus::Approved->value);
    }

    /**
     * Scope to pending registrations (awaiting review).
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Registration>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Registration>
     */
    public function scopePending($query)
    {
        return $query->where('status', RegistrationStatus::Pending->value);
    }

    /**
     * Scope to registrations with unpaid status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Registration>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Registration>
     */
    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', RegistrationPaymentStatus::Unpaid->value);
    }

    /**
     * Scope to registrations with paid status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Registration>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Registration>
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', RegistrationPaymentStatus::Paid->value);
    }
}
