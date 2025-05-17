<?php

declare(strict_types=1);

namespace App\Domains\Owner\ReservationQuota;

use App\Enums\Reservation\ReservationQuota\Status;

readonly class Reserved implements ReservationQuotaInterface
{
    public function __construct(
        private int $hour,
        private int $reservationId,
    ) {}

    public function toArray(): array
    {
        return [
            'hour' => $this->hour,
            'status' => Status::Reserved->label(),
            'reservation_id' => $this->reservationId,
        ];
    }
}
