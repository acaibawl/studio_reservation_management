<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Resources\Reservation\DailyQuotasStatusResource;
use App\Services\Member\Reservation\ReservationAvailabilityService;
use App\ViewModels\Reservation\DailyQuotasStatus;
use Carbon\CarbonImmutable;

class ReservationController extends Controller
{
    public function __construct(
        private readonly ReservationAvailabilityService $reservationAvailabilityService,
    ) {}

    public function getAvailabilityByDate(CarbonImmutable $date): DailyQuotasStatusResource
    {
        $studioQuotasCollection = $this->reservationAvailabilityService->getByDate($date);
        $dailyQuotasStatus = new DailyQuotasStatus($date, $studioQuotasCollection);

        return new DailyQuotasStatusResource($dailyQuotasStatus);
    }
}
