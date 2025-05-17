<?php

declare(strict_types=1);

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Reservation\DailyQuotasStatusResource;
use App\Services\Owner\Reservation\GetStudioQuotasByDateService;
use App\ViewModels\Reservation\DailyQuotasStatus;
use Carbon\CarbonImmutable;

class ReservationController extends Controller
{
    public function __construct(
        private readonly GetStudioQuotasByDateService $getStudioQuotasByDateService,
    ) {}

    public function getQuotasByDate(CarbonImmutable $date): DailyQuotasStatusResource
    {
        $studioQuotasCollection = $this->getStudioQuotasByDateService->get($date);
        $dailyQuotasStatus = new DailyQuotasStatus($date, $studioQuotasCollection);

        return new DailyQuotasStatusResource($dailyQuotasStatus);
    }
}
