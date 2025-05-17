<?php

declare(strict_types=1);

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Services\Owner\Reservation\GetStudioQuotasByDateService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;

class ReservationController extends Controller
{
    public function __construct(
        private readonly GetStudioQuotasByDateService $getStudioQuotasByDateService,
    )
    {}

    public function getQuotasByDate(CarbonImmutable $date): JsonResponse
    {
        return response()->json([
            'date' => $date->toDateString(),
            'studios' => $this->getStudioQuotasByDateService->get($date),
        ]);
    }
}
