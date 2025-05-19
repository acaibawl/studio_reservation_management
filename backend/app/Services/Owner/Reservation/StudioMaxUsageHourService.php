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
use Illuminate\Support\Collection;

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
        $startTime = new CarbonImmutable($reservation->start_at);

        return $this->calculateMaxUsageHours(
            $startTime,
            $reservation->studio,
            $businessTime,
            $regularHolidays,
            $temporaryClosingDays,
            $reservation->id
        );
    }

    public function getByDate(Studio $studio, CarbonImmutable $date, int $hour): int
    {
        $businessTime = BusinessTime::firstOrFail();
        $regularHolidays = RegularHoliday::get();
        $temporaryClosingDays = TemporaryClosingDay::get();
        $startTime = CarbonImmutable::create($date->year, $date->month, $date->day, $hour, $studio->start_at->value);

        return $this->calculateMaxUsageHours(
            $startTime,
            $studio,
            $businessTime,
            $regularHolidays,
            $temporaryClosingDays
        );
    }

    /**
     * @param Collection<int, RegularHoliday> $regularHolidays
     * @param Collection<int, TemporaryClosingDay> $temporaryClosingDays
     */
    private function calculateMaxUsageHours(
        CarbonImmutable $startTime,
        Studio $studio,
        BusinessTime $businessTime,
        Collection $regularHolidays,
        Collection $temporaryClosingDays,
        ?int $reservationId = null
    ): int {
        $targetTimes = $this->generateTargetTimes($startTime);

        $maxUsageHour = 0;
        foreach ($targetTimes as $targetTime) {
            $reservationQuota = $this->reservationQuotaFactory->generate(
                $targetTime,
                intval($targetTime->format('H')),
                $studio,
                $businessTime,
                $regularHolidays,
                $temporaryClosingDays,
                $reservationId,
            );
            if ($reservationQuota instanceof Available) {
                $maxUsageHour++;
            } else {
                break;
            }
        }

        return $maxUsageHour;
    }

    /**
     * @return Collection<int, CarbonImmutable>
     */
    private function generateTargetTimes(CarbonImmutable $startTime): Collection
    {
        $targetTimes = collect();
        $targetTimes->push($startTime);
        collect(range(1, self::MAX_ADDITIONAL_HOURS_TO_CHECK))->map(
            fn (int $hour) => $targetTimes->push($startTime->addHours($hour))
        );

        return $targetTimes;
    }
}
