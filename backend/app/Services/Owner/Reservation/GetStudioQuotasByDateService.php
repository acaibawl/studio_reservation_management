<?php

declare(strict_types=1);

namespace App\Services\Owner\Reservation;

use App\Models\BusinessTime;
use App\Models\RegularHoliday;
use App\Models\Studio;
use App\Models\TemporaryClosingDay;
use App\ViewModels\Reservation\StudioReservationQuotas;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

readonly class GetStudioQuotasByDateService
{
    public function __construct(
        private ReservationQuotaFactory $reservationQuotaFactory,
    ) {}

    /**
     * @return Collection<int, StudioReservationQuotas>
     */
    public function get(CarbonImmutable $date): Collection
    {
        $studios = Studio::with('reservations')->get();
        $businessTime = BusinessTime::firstOrFail();
        $regularHolidays = RegularHoliday::get();
        $temporaryClosingDays = TemporaryClosingDay::get();

        return $studios->map(
            function (Studio $studio) use ($date, $businessTime, $regularHolidays, $temporaryClosingDays) {
                $reservationQuotas = collect(range(0, 23))->map(
                    function (int $hour) use ($date, $studio, $businessTime, $regularHolidays, $temporaryClosingDays) {
                        return $this->reservationQuotaFactory->generate(
                            $date,
                            $hour,
                            $studio,
                            $businessTime,
                            $regularHolidays,
                            $temporaryClosingDays
                        );
                    }
                );

                return new StudioReservationQuotas($studio, $reservationQuotas);
            }
        );
    }
}
