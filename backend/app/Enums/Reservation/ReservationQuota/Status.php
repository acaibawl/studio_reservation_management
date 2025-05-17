<?php

declare(strict_types=1);

namespace App\Enums\Reservation\ReservationQuota;

enum Status: int
{
    case Available = 0;
    case NotAvailable = 1;
    case Reserved = 2;

    public function label(): string
    {
        return match ($this) {
            Status::Available => 'available',
            Status::NotAvailable => 'not available',
            Status::Reserved => 'reserved',
        };
    }
}
