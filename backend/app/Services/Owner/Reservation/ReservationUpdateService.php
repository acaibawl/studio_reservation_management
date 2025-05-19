<?php

declare(strict_types=1);

namespace App\Services\Owner\Reservation;

use App\Exceptions\Reservation\AvailableHourExceededException;
use App\Models\Reservation;
use App\Services\GenerateReservationFinishAtService;

readonly class ReservationUpdateService
{
    public function __construct(
        private GenerateReservationFinishAtService $generateReservationFinishAtService,
        private StudioMaxAvailableHourService $studioMaxUsageHourService,
    ) {}

    /**
     * @throws AvailableHourExceededException
     */
    public function update(Reservation $reservation, array $attributes): bool
    {
        $usageHour = $attributes['usage_hour'];
        if ($this->studioMaxUsageHourService->getByReservation($reservation) < $usageHour) {
            throw new AvailableHourExceededException();
        }

        return $reservation->update([
            'finish_at' => $this->generateReservationFinishAtService
                ->generate($reservation->start_at, $attributes['usage_hour']),
            'memo' => $attributes['memo'] ?? null,
        ]);
    }
}
