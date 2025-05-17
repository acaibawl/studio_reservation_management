<?php

declare(strict_types=1);

namespace App\ViewModels\Reservation;

use App\Domains\Owner\ReservationQuota\ReservationQuotaInterface;
use App\Models\Studio;
use Illuminate\Support\Collection;

readonly class StudioReservationQuotas
{
    /**
     * @param Collection<int, ReservationQuotaInterface> $reservationQuotas
     */
    public function __construct(
        public Studio $studio,
        public Collection $reservationQuotas,
    ) {}
}
