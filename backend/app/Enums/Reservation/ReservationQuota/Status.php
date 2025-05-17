<?php

declare(strict_types=1);

namespace App\Enums\Reservation\ReservationQuota;

enum Status: string
{
    case Available = 'available';
    case NotAvailable = 'not available';
    case Reserved = 'reserved';

    public function label(): string
    {
        return match ($this) {
            Status::Available => '予約可',
            Status::NotAvailable => '予約不可',
            Status::Reserved => '予約済',
        };
    }
}
