<?php

declare(strict_types=1);

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\TemporaryClosingDay\StorePost;
use App\Http\Resources\Owner\TemporaryClosingDay\TemporaryClosingDayResource;
use App\Models\TemporaryClosingDay;
use App\Services\Owner\TemporaryClosingDayService;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TemporaryClosingDayController extends Controller
{
    public function __construct(
        private readonly TemporaryClosingDayService $temporaryClosingDayService,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $days = $this->temporaryClosingDayService->getAll();

        return TemporaryClosingDayResource::collection($days);
    }

    /**
     * @throws Throwable
     */
    public function store(StorePost $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $temporaryClosingDay = $this->temporaryClosingDayService->create($request->validated());
            DB::commit();
        } catch (\Exception $e) {
            \Log::error($e->getMessage(), $e->getTrace());
            DB::rollBack();
            throw $e;
        }

        return new TemporaryClosingDayResource($temporaryClosingDay)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @throws Throwable
     */
    public function destroy(TemporaryClosingDay $temporaryClosingDay): JsonResponse
    {
        DB::beginTransaction();
        try {
            $temporaryClosingDay->delete();
            DB::commit();
        } catch (\Exception $e) {
            \Log::error($e->getMessage(), $e->getTrace());
            DB::rollBack();
            throw $e;
        }

        return response()->json(['message' => '臨時休業日を削除しました。']);
    }
}
