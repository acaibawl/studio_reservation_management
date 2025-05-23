<?php

declare(strict_types=1);

namespace App\Services\Owner\Reservation;

use App\Domains\ReservationQuota\AvailableQuota;
use App\Domains\ReservationQuota\NotAvailableQuota;
use App\Domains\ReservationQuota\ReservationQuotaInterface;
use App\Domains\ReservationQuota\ReservedQuota;
use App\Enums\Studio\StartAt;
use App\Models\BusinessTime;
use App\Models\RegularHoliday;
use App\Models\Reservation;
use App\Models\Studio;
use App\Models\TemporaryClosingDay;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ReservationQuotaFactory
{
    private const int MAX_RESERVATION_PERIOD_DAYS = 60;

    /**
     * @param Collection<int, RegularHoliday> $regularHolidays
     * @param Collection<int, TemporaryClosingDay> $temporaryClosingDays
     */
    public function generate(
        CarbonImmutable $date,
        int $hour,
        Studio $studio,
        BusinessTime $businessTime,
        Collection $regularHolidays,
        Collection $temporaryClosingDays,
        ?int $ignoreReservationId = null
    ): ReservationQuotaInterface {
        if ($this->hasNotAvailableIssues($date, $hour, $studio, $regularHolidays, $temporaryClosingDays, $businessTime)) {
            return new NotAvailableQuota($hour);
        }

        // 既に他の予約が入っているか
        $dateTime = Carbon::create($date->year, $date->month, $date->day, $hour, $studio->start_at->value);
        $alreadyReservation = $this->findConflictingReservation($dateTime, $studio, $ignoreReservationId);
        if ($alreadyReservation) {
            return new ReservedQuota($hour, $alreadyReservation->id);
        }

        return new AvailableQuota($hour);
    }

    private function getApplicableDate(
        CarbonImmutable $date,
        int $hour,
        BusinessTime $businessTime,
    ): CarbonImmutable {
        if ($businessTime->is_cross_date_operation && $businessTime->close_time->isAfter(Carbon::createFromTime($hour))) {
            return $date->subDay();
        } else {
            return $date;
        }
    }

    private function hasNotAvailableIssues(
        CarbonImmutable $date,
        int $hour,
        Studio $studio,
        Collection $regularHolidays,
        Collection $temporaryClosingDays,
        BusinessTime $businessTime
    ): bool {
        $applicableDate = $this->getApplicableDate($date, $hour, $businessTime);

        return $this->isPastTime($date, $hour, $studio->start_at)
            || $this->isOverMaxReservationPeriod($applicableDate)
            || $this->isRegularHoliday($applicableDate, $regularHolidays)
            || $this->isTemporaryClosingDay($applicableDate, $temporaryClosingDays)
            || $this->isOutOfBusinessHours($hour, $businessTime);
    }

    private function isPastTime(CarbonImmutable $date, int $hour, StartAt $studioStartAt): bool
    {
        $targetDateTime = Carbon::create($date->year, $date->month, $date->day, $hour, $studioStartAt->value);

        return $targetDateTime->lessThanOrEqualTo(Carbon::now());
    }

    private function isOverMaxReservationPeriod(CarbonImmutable $applicableDate): bool
    {
        return Carbon::now()->diffInDays($applicableDate) > self::MAX_RESERVATION_PERIOD_DAYS;
    }

    /**
     * @param Collection<int, RegularHoliday> $regularHolidays
     */
    private function isRegularHoliday(CarbonImmutable $applicableDate, Collection $regularHolidays): bool
    {
        return $regularHolidays->contains(function (RegularHoliday $regularHoliday) use ($applicableDate) {
            return $regularHoliday->code->value === $applicableDate->dayOfWeek;
        });
    }

    /**
     * @param Collection<int, TemporaryClosingDay> $temporaryClosingDays
     */
    private function isTemporaryClosingDay(
        CarbonImmutable $applicableDate,
        Collection $temporaryClosingDays
    ): bool {
        return $temporaryClosingDays->contains(
            function (TemporaryClosingDay $temporaryClosingDay) use ($applicableDate) {
                return $temporaryClosingDay->date->isSameDay($applicableDate);
            }
        );
    }

    private function isOutOfBusinessHours(int $hour, BusinessTime $businessTime): bool
    {
        $hourCarbon = Carbon::createFromTime($hour);
        if ($businessTime->is_cross_date_operation) {
            // 例：open = 10:00, close = 5:00, hour = 5:00 の場合 true
            return $businessTime->open_time->isAfter($hourCarbon) &&
                $businessTime->close_time->lessThanOrEqualTo($hourCarbon);
        } else {
            // 例：open = 10:00, close = 22:00, hour = 22:00 の場合 true
            return $businessTime->open_time->isAfter($hourCarbon) ||
                $businessTime->close_time->lessThanOrEqualTo($hourCarbon);
        }
    }

    private function findConflictingReservation(Carbon $dateTime, Studio $studio, ?int $ignoreReservationId): ?Reservation
    {
        $query = $studio->reservations()->where('start_at', '<=', $dateTime)->where('finish_at', '>=', $dateTime);
        if ($ignoreReservationId) {
            $query->whereNot('id', '=', $ignoreReservationId);
        }

        return $query->first();
    }
}
