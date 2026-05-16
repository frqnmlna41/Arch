<?php

namespace App\Enums;

enum RegistrationPaymentStatus: string
{
    case Unpaid  = 'unpaid';
    case Pending = 'pending';
    case Paid    = 'paid';
    case Refunded = 'refunded';

    /**
     * Human-readable label in Bahasa Indonesia.
     */
    public function label(): string
    {
        return match ($this) {
            self::Unpaid   => 'Belum Bayar',
            self::Pending  => 'Menunggu Konfirmasi',
            self::Paid     => 'Lunas',
            self::Refunded => 'Dikembalikan',
        };
    }

    /**
     * Tailwind/CSS color hint for badge rendering.
     */
    public function color(): string
    {
        return match ($this) {
            self::Unpaid   => 'red',
            self::Pending  => 'yellow',
            self::Paid     => 'green',
            self::Refunded => 'purple',
        };
    }
}
