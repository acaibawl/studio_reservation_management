<?php

declare(strict_types=1);

namespace App\Http\Controllers\Owner;

use App\Exceptions\Reservation\AvailableHourExceededException;
use App\Exceptions\UserDisplayableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\Reservation\StorePost;
use App\Http\Requests\Owner\Reservation\UpdatePatch;
use App\Http\Resources\Reservation\DailyQuotasStatusResource;
use App\Http\Resources\Reservation\MaxAvailableHourResource;
use App\Http\Resources\Reservation\ReservationShowResource;
use App\Models\Member;
use App\Models\Reservation;
use App\Models\Studio;
use App\Services\Owner\Reservation\GetStudioQuotasByDateService;
use App\Services\Owner\Reservation\ReservationCreateService;
use App\Services\Owner\Reservation\ReservationUpdateService;
use App\Services\Owner\Reservation\StudioMaxAvailableHourService;
use App\ViewModels\Reservation\DailyQuotasStatus;
use App\ViewModels\Reservation\MaxAvailableHourViewModel;
use App\ViewModels\Reservation\ReservationShow;
use Carbon\CarbonImmutable;
use DB;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ReservationController extends Controller
{
    const int OWNER_MEMBER_ID = 9999999;

    public function __construct(
        private readonly GetStudioQuotasByDateService $getStudioQuotasByDateService,
        private readonly StudioMaxAvailableHourService $studioMaxAvailableHourService,
        private readonly ReservationUpdateService $reservationUpdateService,
        private readonly ReservationCreateService $reservationCreateService,
    ) {}

    public function getQuotasByDate(CarbonImmutable $date): DailyQuotasStatusResource
    {
        $studioQuotasCollection = $this->getStudioQuotasByDateService->get($date);
        $dailyQuotasStatus = new DailyQuotasStatus($date, $studioQuotasCollection);

        return new DailyQuotasStatusResource($dailyQuotasStatus);
    }

    public function show(Studio $studio, Reservation $reservation): ReservationShowResource
    {
        $showViewModel = new ReservationShow(
            $reservation,
            $this->studioMaxAvailableHourService->getByReservation($studio, $reservation)
        );

        return new ReservationShowResource($showViewModel);
    }

    /**
     * @throws Throwable
     * @throws UserDisplayableException
     * @throws AvailableHourExceededException
     */
    public function update(Studio $studio, Reservation $reservation, UpdatePatch $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $this->reservationUpdateService->update($studio, $reservation, $request->validated());
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
            'message' => '予約を更新しました。',
        ]);
    }

    /**
     * @throws Throwable
     */
    public function destroy(Studio $studio, Reservation $reservation): JsonResponse
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

    public function getReservationQuota(Studio $studio, CarbonImmutable $date, int $hour): MaxAvailableHourResource
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
    public function store(Studio $studio, StorePost $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $reservation = $this->reservationCreateService->create(
                Member::findOrFail(self::OWNER_MEMBER_ID),
                $studio,
                $request->validated()
            );
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
            'reservation_id' => $reservation->id,
        ], Response::HTTP_CREATED);
    }
}
