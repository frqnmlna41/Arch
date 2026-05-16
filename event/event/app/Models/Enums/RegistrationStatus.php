<?php

namespace App\Enums;

enum RegistrationStatus: string
{
    case Pending  = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    /**
     * Human-readable label in Bahasa Indonesia.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending   => 'Menunggu',
            self::Approved  => 'Disetujui',
            self::Rejected  => 'Ditolak',
            self::Cancelled => 'Dibatalkan',
        };
    }

    /**
     * Tailwind/CSS color hint for badge rendering.
     */
    public function color(): string
    {
        return match ($this) {
            self::Pending   => 'yellow',
            self::Approved  => 'green',
            self::Rejected  => 'red',
            self::Cancelled => 'gray',
        };
    }
}
