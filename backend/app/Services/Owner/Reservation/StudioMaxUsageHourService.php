<?php

declare(strict_types=1);

namespace App\Services\Owner\Reservation;

use App\Domains\Owner\ReservationQuota\Available;
use App\Models\BusinessTime;
use App\Models\RegularHoliday;
use App\Models\Reservation;
use App\Models\Studio;
use App\Models\TemporaryClosingDay;
use Carbon\CarbonImmutable;

readonly class StudioMaxUsageHourService
{
    private const int MAX_ADDITIONAL_HOURS_TO_CHECK = 5;

    public function __construct(
        private ReservationQuotaFactory $reservationQuotaFactory,
    ) {}

    public function getByReservation(Reservation $reservation): int
    {
        $businessTime = BusinessTime::firstOrFail();
        $regularHolidays = RegularHoliday::get();
        $temporaryClosingDays = TemporaryClosingDay::get();
        $targetTimes = collect();
        $targetTimes->push(new CarbonImmutable($reservation->start_at));
        collect(range(1, self::MAX_ADDITIONAL_HOURS_TO_CHECK))->map(
            fn (int $hour) => $targetTimes->push(new CarbonImmutable($reservation->start_at)->addHours($hour))
        );

        $maxUsageHours = 0;
        foreach ($targetTimes as $targetTime) {
            $reservationQuota = $this->reservationQuotaFactory->generate(
                $targetTime,
                intval($targetTime->format('H')),
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

    public function getByDate(Studio $studio, CarbonImmutable $date, int $hour): int
    {
        $businessTime = BusinessTime::firstOrFail();
        $regularHolidays = RegularHoliday::get();
        $temporaryClosingDays = TemporaryClosingDay::get();
        $targetStartAt = CarbonImmutable::create($date->year, $date->month, $date->day, $hour, $studio->start_at->value);
        $targetTimes = collect();
        $targetTimes->push($targetStartAt);
        collect(range(1, self::MAX_ADDITIONAL_HOURS_TO_CHECK))->map(
            fn (int $hour) => $targetTimes->push($targetStartAt->addHours($hour))
        );

        $maxUsageHours = 0;
        foreach ($targetTimes as $targetTime) {
            $reservationQuota = $this->reservationQuotaFactory->generate(
                $targetTime,
                intval($targetTime->format('H')),
                $studio,
                $businessTime,
                $regularHolidays,
                $temporaryClosingDays,
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
