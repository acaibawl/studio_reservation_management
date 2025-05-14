<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\BusinessDay\UpdatePut;
use App\Models\RegularHoliday;
use App\Services\Owner\BusinessTimeService;
use App\Services\Owner\RegularHolidayService;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * 営業時間・定休日のコントローラー
 */
class BusinessDayController extends Controller
{
    /**
     * @param RegularHolidayService $regularHolidayService
     * @param BusinessTimeService $businessTimeService
     */
    public function __construct(
        private readonly RegularHolidayService $regularHolidayService,
        private readonly BusinessTimeService $businessTimeService,
    )
    {
    }

    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $regularHolidays = $this->regularHolidayService->getAll();
        $businessTime = $this->businessTimeService->get();
        return response()->json([
            'regular_holidays' => $regularHolidays->map(fn (RegularHoliday $regularHoliday) => [
                'code' => $regularHoliday->code,
                ]),
            'business_time' => [
                'open_time' => $businessTime->open_time->toTimeString(),
                'close_time' => $businessTime->close_time->toTimeString(),
                ],
            ]);
    }

    /**
     * @param UpdatePut $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function update(UpdatePut $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $this->regularHolidayService->update($request->validated());
            $this->businessTimeService->update($request->validated());
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        return response()->json(['message' => '営業時間・定休日の更新に成功しました。']);
    }
}
