<?php

declare(strict_types=1);

namespace App\Services\Member\Reservation;

use App\Domains\ReservationQuota\NotAvailableQuota;
use App\Domains\ReservationQuota\ReservationQuotaInterface;
use App\Domains\ReservationQuota\ReservedQuota;
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
        return $studioQuotas->map(function (StudioReservationQuotas $studioQuota) {
            /** @var Collection<int, ReservationQuotaInterface> $reservationQuotas */
            $reservationQuotas = $studioQuota->reservationQuotas->map(function (ReservationQuotaInterface $reservationQuota) {
                return $this->convertReservationQuota($reservationQuota);
            });

            return new StudioReservationQuotas($studioQuota->studio, $reservationQuotas);
        });
    }

    private function convertReservationQuota(ReservationQuotaInterface $reservationQuota): ReservationQuotaInterface
    {
        return $reservationQuota instanceof ReservedQuota
            ? new NotAvailableQuota($reservationQuota->getHour())
            : $reservationQuota;
    }
}
