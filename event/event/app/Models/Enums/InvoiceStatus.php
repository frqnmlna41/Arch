<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Draft    = 'draft';
    case Unpaid   = 'unpaid';
    case Paid     = 'paid';
    case Overdue  = 'overdue';
    case Cancelled = 'cancelled';

    /**
     * Human-readable label in Bahasa Indonesia.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft     => 'Draft',
            self::Unpaid    => 'Belum Dibayar',
            self::Paid      => 'Lunas',
            self::Overdue   => 'Jatuh Tempo',
            self::Cancelled => 'Dibatalkan',
        };
    }

    /**
     * Tailwind/CSS color hint for badge rendering.
     */
    public function color(): string
    {
        return match ($this) {
            self::Draft     => 'gray',
            self::Unpaid    => 'yellow',
            self::Paid      => 'green',
            self::Overdue   => 'red',
            self::Cancelled => 'gray',
        };
    }
}
