<?php

declare(strict_types=1);

namespace App\Services\Owner\Reservation;

use App\Domains\Owner\ReservationQuota\Available;
use App\Models\BusinessTime;
use App\Models\RegularHoliday;
use App\Models\Reservation;
use App\Models\TemporaryClosingDay;
use Carbon\CarbonImmutable;

readonly class StudioUsageLimitService
{
    public function __construct(
        private ReservationQuotaFactory $reservationQuotaFactory,
    ) {}

    public function getMaxUsageHoursByReservation(Reservation $reservation): int
    {
        $businessTime = BusinessTime::firstOrFail();
        $regularHolidays = RegularHoliday::get();
        $temporaryClosingDays = TemporaryClosingDay::get();
        $targetTimes = collect();
        $targetTimes->push(new CarbonImmutable($reservation->start_at));
        collect(range(1, 5))->map(
            fn (int $hour) => $targetTimes->push(new CarbonImmutable($reservation->start_at)->addHours($hour))
        );

        $maxUsageHours = 0;
        foreach ($targetTimes as $targetTime) {
            $reservationQuota = $this->reservationQuotaFactory->generate(
                $targetTime,
                $targetTime->format('H'),
                $reservation->studio,
                $businessTime,
                $regularHolidays,
                $temporaryClosingDays,
                $reservation->id,
            );
            if ($reservationQuota instanceof Available) {
                $maxUsageHours++;
            } else {
                break;
            }
        }

        return $maxUsageHours;
    }
}
