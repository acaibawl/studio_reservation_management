<?php

declare(strict_types=1);

namespace App\ViewModels\Reservation;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

readonly class DailyQuotasStatus
{
    /**
     * @param Collection<int, StudioReservationQuotas> $studioReservationQuotas
     */
    public function __construct(
        public CarbonImmutable $date,
        public Collection $studioReservationQuotas,
    ) {}
}
