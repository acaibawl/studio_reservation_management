<?php

declare(strict_types=1);

namespace App\Services\Owner\Reservation;

use App\Domains\ReservationQuota\AvailableQuota;
use App\Models\BusinessTime;
use App\Models\RegularHoliday;
use App\Models\Reservation;
use App\Models\Studio;
use App\Models\TemporaryClosingDay;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

readonly class StudioMaxAvailableHourService
{
    private const int MAX_ADDITIONAL_HOURS_TO_CHECK = 5;

    public function __construct(
        private ReservationQuotaFactory $reservationQuotaFactory,
    ) {}

    /**
     * 既に存在する予約に対する最大予約可能時間取得処理
     */
    public function getByReservation(Studio $studio, Reservation $reservation): int
    {
        $businessTime = BusinessTime::firstOrFail();
        $regularHolidays = RegularHoliday::get();
        $temporaryClosingDays = TemporaryClosingDay::get();
        $startTime = new CarbonImmutable($reservation->start_at);

        return $this->calculateMaxAvailableHour(
            $startTime,
            $studio,
            $businessTime,
            $regularHolidays,
            $temporaryClosingDays,
            $reservation->id
        );
    }

    /**
     * 枠に対する新規予約向けの最大予約可能時間取得処理
     */
    public function getByDate(Studio $studio, CarbonImmutable $date, int $hour): int
    {
        $businessTime = BusinessTime::firstOrFail();
        $regularHolidays = RegularHoliday::get();
        $temporaryClosingDays = TemporaryClosingDay::get();
        $startTime = CarbonImmutable::create($date->year, $date->month, $date->day, $hour, $studio->start_at->value);

        return $this->calculateMaxAvailableHour(
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
    private function calculateMaxAvailableHour(
        CarbonImmutable $startTime,
        Studio $studio,
        BusinessTime $businessTime,
        Collection $regularHolidays,
        Collection $temporaryClosingDays,
        ?int $reservationId = null
    ): int {
        $targetTimes = $this->generateTargetTimes($startTime);

        $maxAvailableHour = 0;
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
            if ($reservationQuota instanceof AvailableQuota) {
                $maxAvailableHour++;
            } else {
                break;
            }
        }

        return $maxAvailableHour;
    }

    /**
     * 対象時間帯を一定時間分生成するメソッド
     * 入力された開始時刻から N 時間分の対象時刻を生成して、予約可能性を逐次チェックする目的で使用
     * @return Collection<int, CarbonImmutable>
     */
    private function generateTargetTimes(CarbonImmutable $startTime): Collection
    {
        return collect(range(0, self::MAX_ADDITIONAL_HOURS_TO_CHECK))->map(
            fn (int $h) => $startTime->addHours($h)
        );
    }
}
