<?php

namespace App\Enums;

enum EventStatus: string
{
    case Draft     = 'draft';
    case Published = 'published';
    case Ongoing   = 'ongoing';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /**
     * Human-readable label in Bahasa Indonesia.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft     => 'Draft',
            self::Published => 'Dipublikasikan',
            self::Ongoing   => 'Berlangsung',
            self::Completed => 'Selesai',
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
            self::Published => 'blue',
            self::Ongoing   => 'green',
            self::Completed => 'indigo',
            self::Cancelled => 'red',
        };
    }
}
