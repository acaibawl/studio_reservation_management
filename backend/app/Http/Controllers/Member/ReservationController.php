<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Exceptions\Reservation\AvailableHourExceededException;
use App\Exceptions\UserDisplayableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Member\Reservation\StorePost;
use App\Http\Resources\Reservation\DailyQuotasStatusResource;
use App\Http\Resources\Reservation\MaxAvailableHourResource;
use App\Models\Member;
use App\Models\Studio;
use App\Services\Member\Reservation\ReservationAvailabilityService;
use App\Services\Owner\Reservation\ReservationCreateService;
use App\Services\Owner\Reservation\StudioMaxAvailableHourService;
use App\ViewModels\Reservation\DailyQuotasStatus;
use App\ViewModels\Reservation\MaxAvailableHourViewModel;
use Carbon\CarbonImmutable;
use DB;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ReservationController extends Controller
{
    public function __construct(
        private readonly ReservationAvailabilityService $reservationAvailabilityService,
        private readonly StudioMaxAvailableHourService $studioMaxAvailableHourService,
        private readonly ReservationCreateService $reservationCreateService,
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

    /**
     * @throws AvailableHourExceededException
     * @throws Throwable
     * @throws UserDisplayableException
     */
    public function store(StorePost $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            /** @var Member $member */
            $member = auth()->user();
            $this->reservationCreateService->create($member, $request->validated());
            DB::commit();
        } catch (UserDisplayableException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            \Log::error($e->getMessage(), $e->getTrace());
            DB::rollBack();
            throw $e;
        }

        return response()->json([
            'message' => '予約を登録しました。',
        ], Response::HTTP_CREATED);
    }
}
