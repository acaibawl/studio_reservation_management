<?php

declare(strict_types=1);

namespace App\Services\Member\Reservation;

use App\Domains\ReservationQuota\NotAvailable;
use App\Domains\ReservationQuota\ReservationQuotaInterface;
use App\Domains\ReservationQuota\Reserved;
use App\ViewModels\Reservation\StudioReservationQuotas;
use Illuminate\Support\Collection;

class ConvertReservedToNotAvailableService
{
    /**
     * @param Collection<int, StudioReservationQuotas> $studioQuotas
     * @return Collection<int, StudioReservationQuotas>
     */
    public function convert(Collection $studioQuotas): Collection
    {
        return $studioQuotas->map(function (StudioReservationQuotas $studioQuotas) {
            /** @var Collection<int, ReservationQuotaInterface> $reservationQuotas */
            $reservationQuotas = $studioQuotas->reservationQuotas->map(function (ReservationQuotaInterface $reservationQuota) {
                return $reservationQuota instanceof Reserved ? new NotAvailable($reservationQuota->getHour()) : $reservationQuota;
            });

            return new StudioReservationQuotas($studioQuotas->studio, $reservationQuotas);
        });
    }
}
