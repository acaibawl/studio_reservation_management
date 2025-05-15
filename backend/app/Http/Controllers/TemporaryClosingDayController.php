<?php

namespace App\Http\Controllers;

use App\Http\Requests\Owner\TemporaryClosingDay\StorePost;
use App\Models\TemporaryClosingDay;
use App\Services\Owner\TemporaryClosingDayService;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TemporaryClosingDayController extends Controller
{
    public function __construct(
        private readonly TemporaryClosingDayService $temporaryClosingDayService,
    )
    {
    }

    public function index(): JsonResponse
    {
        $days = $this->temporaryClosingDayService->getAll();
        return response()->json([
            'temporary_closing_days' => $days->map(fn (TemporaryClosingDay $day) => [
                'id' => $day->id,
                'date' => $day->date->toDateString(),
            ])
        ]);
    }

    /**
     * @param StorePost $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(StorePost $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $this->temporaryClosingDayService->create($request->validated());
            DB::commit();
        } catch (\Exception $e) {
            \Log::error($e->getMessage(), $e->getTrace());
            DB::rollBack();
            throw $e;
        }
        return response()->json(['message' => '臨時休業日を登録しました。'], Response::HTTP_CREATED);
    }

    /**
     * @param TemporaryClosingDay $date
     * @return JsonResponse
     * @throws Throwable
     */
    public function destroy(TemporaryClosingDay $date): JsonResponse
    {
        DB::beginTransaction();
        try {
            $date->delete();
            DB::commit();
        } catch (\Exception $e) {
            \Log::error($e->getMessage(), $e->getTrace());
            DB::rollBack();
            throw $e;
        }
        return response()->json(['message' => '臨時休業日を削除しました。']);
    }
}
