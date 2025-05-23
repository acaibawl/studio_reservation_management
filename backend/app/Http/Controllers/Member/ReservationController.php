<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Resources\Reservation\DailyQuotasStatusResource;
use App\Http\Resources\Reservation\MaxAvailableHourResource;
use App\Models\Studio;
use App\Services\Member\Reservation\ReservationAvailabilityService;
use App\Services\Owner\Reservation\StudioMaxAvailableHourService;
use App\ViewModels\Reservation\DailyQuotasStatus;
use App\ViewModels\Reservation\MaxAvailableHourViewModel;
use Carbon\CarbonImmutable;

class ReservationController extends Controller
{
    public function __construct(
        private readonly ReservationAvailabilityService $reservationAvailabilityService,
        private readonly StudioMaxAvailableHourService $studioMaxAvailableHourService,
    ) {}

    public function getAvailabilityByDate(CarbonImmutable $date): DailyQuotasStatusResource
    {
        $studioQuotasCollection = $this->reservationAvailabilityService->getByDate($date);
        $dailyQuotasStatus = new DailyQuotasStatus($date, $studioQuotasCollection);

        return new DailyQuotasStatusResource($dailyQuotasStatus);
    }

    public function getMaxAvailableHour(Studio $studio, CarbonImmutable $date, int $hour): MaxAvailableHourResource
    {
        return new MaxAvailableHourResource(
            new MaxAvailableHourViewModel(
                $studio,
                $date,
                $hour,
                $this->studioMaxAvailableHourService->getByDate($studio, $date, $hour)
            )
        );
    }
}
