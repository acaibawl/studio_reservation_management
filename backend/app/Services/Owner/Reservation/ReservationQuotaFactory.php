<?php

declare(strict_types=1);

namespace App\Services\Owner\Reservation;

use App\Domains\ReservationQuota\Available;
use App\Domains\ReservationQuota\NotAvailable;
use App\Domains\ReservationQuota\ReservationQuotaInterface;
use App\Domains\ReservationQuota\Reserved;
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
        // 日付を跨ぐ営業日か
        $isCrossDateOperation = $this->isCrossDateOperation($businessTime);
        // 適用営業日
        $applicableDate = $this->getApplicableDate($date, $hour, $businessTime, $isCrossDateOperation);

        // 現在日時より前ではないか
        if ($this->isPastTime($date, $hour, $studio->start_at)) {
            return new NotAvailable($hour);
        }

        // 現在日付より60日先までしか予約不可
        if ($this->isOverMaxReservationPeriod($applicableDate)) {
            return new NotAvailable($hour);
        }

        // 定休日の曜日か判定
        if ($this->isRegularHoliday($applicableDate, $regularHolidays)) {
            return new NotAvailable($hour);
        }

        // 臨時休業日か判定
        if ($this->isTemporaryClosingDay($applicableDate, $temporaryClosingDays)) {
            return new NotAvailable($hour);
        }

        // 営業時間外ではないか
        if ($this->isOutOfBusinessHours($hour, $businessTime, $isCrossDateOperation)) {
            return new NotAvailable($hour);
        }

        // 既に他の予約が入っているか
        $dateTime = Carbon::create($date->year, $date->month, $date->day, $hour, $studio->start_at->value);
        $alreadyReservation = $this->findConflictingReservation($dateTime, $studio, $ignoreReservationId);
        if ($alreadyReservation) {
            return new Reserved($hour, $alreadyReservation->id);
        }

        return new Available($hour);
    }

    private function getApplicableDate(
        CarbonImmutable $date,
        int $hour,
        BusinessTime $businessTime,
        bool $isCrossDateOperation
    ): CarbonImmutable {
        if ($isCrossDateOperation && $businessTime->close_time->isAfter(Carbon::createFromTime($hour))) {
            return $date->subDay();
        } else {
            return $date;
        }
    }

    private function isPastTime(CarbonImmutable $date, int $hour, StartAt $studioStartAt): bool
    {
        $targetDateTime = Carbon::create($date->year, $date->month, $date->day, $hour, $studioStartAt->value);

        return $targetDateTime->lessThanOrEqualTo(Carbon::now());
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

    private function isOutOfBusinessHours(int $hour, BusinessTime $businessTime, bool $isCrossDateOperation): bool
    {
        $hourCarbon = Carbon::createFromTime($hour);
        if ($isCrossDateOperation) {
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

    private function isOverMaxReservationPeriod(CarbonImmutable $applicableDate): bool
    {
        return Carbon::now()->diffInDays($applicableDate) > self::MAX_RESERVATION_PERIOD_DAYS;
    }

    private function isCrossDateOperation(BusinessTime $businessTime): bool
    {
        return $businessTime->close_time->lessThanOrEqualTo($businessTime->open_time);
    }
}
