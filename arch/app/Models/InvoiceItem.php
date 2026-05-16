<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\InvoiceItem
 *
 * Satu baris dalam invoice = satu atlet di satu event_category.
 * Menggantikan relasi ke Registration (yang tidak ada di migration).
 *
 * @property int   $id
 * @property int   $invoice_id
 * @property int   $athlete_id
 * @property int   $event_category_id   ✅ FIXED: ganti registration_id
 * @property int   $discipline_id       Denormalisasi untuk kemudahan query/laporan
 * @property float $price
 *
 * @property-read Invoice       $invoice
 * @property-read Athlete       $athlete
 * @property-read EventCategory $eventCategory
 * @property-read Discipline    $discipline
 */
class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'athlete_id',
        'event_participant_id',  // ✅ FIXED: ganti registration_id → event_category_id
        'discipline_id',      // denormalisasi untuk kemudahan filter/laporan
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    // ═══════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════════════════════

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function athlete(): BelongsTo
    {
        return $this->belongsTo(Athlete::class);
    }

    /**
     * ✅ FIXED: ganti Registration → EventCategory
     */
    public function eventCategory(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class);
    }

    /**
     * Discipline (denormalisasi — tersimpan langsung di kolom discipline_id)
     */
    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }

    // ═══════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════

    /**
     * Shortcut — ambil event via eventCategory
     */
    public function getEventAttribute(): ?Event
    {
        return $this->eventCategory?->event;
    }

    /**
     * Label deskriptif untuk item ini
     * Contoh: "Rizky Aditya — Sanda Remaja Putra 52kg"
     */
    public function getLabelAttribute(): string
    {
        $name     = $this->athlete?->name ?? 'Atlet';
        $catLabel = $this->eventCategory?->getLabel() ?? '-';

        return "{$name} — {$catLabel}";
    }
}
