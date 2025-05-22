<?php

declare(strict_types=1);

namespace App\Services\Member\Reservation;

use App\Services\Owner\Reservation\GetStudioQuotasByDateService;
use App\ViewModels\Reservation\StudioReservationQuotas;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

readonly class ReservationAvailabilityService
{
    public function __construct(
        private GetStudioQuotasByDateService $getStudioQuotasByDateService,
        private ConvertReservedToNotAvailableService $convertReservedToNotAvailableService,
    ) {}

    /**
     * @return Collection<int, StudioReservationQuotas>
     */
    public function getByDate(CarbonImmutable $date): Collection
    {
        $studioQuotas = $this->getStudioQuotasByDateService->get($date);

        return $this->convertReservedToNotAvailableService->convert($studioQuotas);
    }
}
