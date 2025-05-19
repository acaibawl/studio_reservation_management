<?php

declare(strict_types=1);

namespace App\ViewModels\Reservation;

use App\Models\Reservation;

readonly class ReservationShow
{
    public function __construct(
        public Reservation $reservation,
        public int $maxUsageHour
    ) {}
}
