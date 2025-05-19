<?php

declare(strict_types=1);

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Reservation\DailyQuotasStatusResource;
use App\Http\Resources\Reservation\ReservationShowResource;
use App\Models\Reservation;
use App\Services\Owner\Reservation\GetStudioQuotasByDateService;
use App\Services\Owner\Reservation\ReservationUpdateService;
use App\Services\Owner\Reservation\StudioMaxUsageHourService;
use App\ViewModels\Reservation\DailyQuotasStatus;
use App\ViewModels\Reservation\ReservationShow;
use Carbon\CarbonImmutable;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ReservationController extends Controller
{
    public function __construct(
        private readonly GetStudioQuotasByDateService $getStudioQuotasByDateService,
        private readonly StudioMaxUsageHourService $studioUsageLimitService,
        private readonly ReservationUpdateService $reservationUpdateService,
    ) {}

    public function getQuotasByDate(CarbonImmutable $date): DailyQuotasStatusResource
    {
        $studioQuotasCollection = $this->getStudioQuotasByDateService->get($date);
        $dailyQuotasStatus = new DailyQuotasStatus($date, $studioQuotasCollection);

        return new DailyQuotasStatusResource($dailyQuotasStatus);
    }

    public function show(Reservation $reservation): ReservationShowResource
    {
        $showViewModel = new ReservationShow(
            $reservation,
            $this->studioUsageLimitService->getByReservation($reservation)
        );

        return new ReservationShowResource($showViewModel);
    }

    public function update(Reservation $reservation, Request $request): JsonResponse
    {
        $this->reservationUpdateService->update($reservation, $request->toArray());

        return response()->json([
            'message' => '予約を更新しました。',
        ]);
    }

    /**
     * @throws Throwable
     */
    public function destroy(Reservation $reservation): JsonResponse
    {
        DB::beginTransaction();
        try {
            $reservation->delete();
            DB::commit();
        } catch (\Exception $e) {
            \Log::error($e->getMessage(), $e->getTrace());
            DB::rollBack();
            throw $e;
        }

        return response()->json([
            'message' => '予約を削除しました。',
        ]);
    }
}
