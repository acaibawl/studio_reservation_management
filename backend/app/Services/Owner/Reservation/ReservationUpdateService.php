<?php

declare(strict_types=1);

namespace App\Services\Owner\Reservation;

use App\Exceptions\Reservation\AvailableHourExceededException;
use App\Models\Reservation;
use App\Models\Studio;
use App\Services\GenerateReservationFinishAtService;
use DB;

readonly class ReservationUpdateService
{
    public function __construct(
        private GenerateReservationFinishAtService $generateReservationFinishAtService,
        private StudioMaxAvailableHourService $studioMaxUsageHourService,
    ) {}

    /**
     * @throws AvailableHourExceededException
     */
    public function update(Studio $studio, Reservation $reservation, array $attributes): bool
    {
        $usageHour = $attributes['usage_hour'];
        // テーブルをテーブルロックする
        DB::unprepared('LOCK TABLES reservations WRITE, business_times READ, regular_holidays READ, temporary_closing_days READ, studios READ');
        if ($this->studioMaxUsageHourService->getByReservation($studio, $reservation) < $usageHour) {
            throw new AvailableHourExceededException();
        }

        return $reservation->update([
            'finish_at' => $this->generateReservationFinishAtService
                ->generate($reservation->start_at, $attributes['usage_hour']),
            'memo' => $attributes['memo'] ?? null,
        ]);
    }
}
