<?php

declare(strict_types=1);

namespace App\Domains\ReservationQuota;

use App\Enums\Reservation\ReservationQuota\Status;

readonly class NotAvailable implements ReservationQuotaInterface
{
    public function __construct(
        private int $hour,
    ) {}

    public function getHour(): int
    {
        return $this->hour;
    }

    public function toArray(): array
    {
        return [
            'hour' => $this->hour,
            'status' => Status::NotAvailable,
        ];
    }
}
