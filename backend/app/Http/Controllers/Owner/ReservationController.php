<?php

declare(strict_types=1);

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Reservation\DailyQuotasStatusResource;
use App\Http\Resources\Reservation\ShowResource;
use App\Models\Reservation;
use App\Services\Owner\Reservation\GetStudioQuotasByDateService;
use App\Services\Owner\Reservation\StudioUsageLimitService;
use App\ViewModels\Reservation\DailyQuotasStatus;
use App\ViewModels\Reservation\ReservationShow;
use Carbon\CarbonImmutable;

class ReservationController extends Controller
{
    public function __construct(
        private readonly GetStudioQuotasByDateService $getStudioQuotasByDateService,
        private readonly StudioUsageLimitService $studioUsageLimitService,
    ) {}

    public function getQuotasByDate(CarbonImmutable $date): DailyQuotasStatusResource
    {
        $studioQuotasCollection = $this->getStudioQuotasByDateService->get($date);
        $dailyQuotasStatus = new DailyQuotasStatus($date, $studioQuotasCollection);

        return new DailyQuotasStatusResource($dailyQuotasStatus);
    }

    public function show(Reservation $reservation): ShowResource
    {
        $showViewModel = new ReservationShow(
            $reservation,
            $this->studioUsageLimitService->getMaxUsageHoursByReservation($reservation)
        );

        return new ShowResource($showViewModel);
    }
}
