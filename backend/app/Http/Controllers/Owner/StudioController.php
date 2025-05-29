<?php

declare(strict_types=1);

namespace App\Http\Controllers\Owner;

use App\Exceptions\UserDisplayableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\Studio\StorePost;
use App\Http\Requests\Owner\Studio\UpdatePut;
use App\Http\Resources\Owner\StudioResource;
use App\Models\Studio;
use App\Services\Owner\StudioService;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class StudioController extends Controller
{
    public function __construct(
        private readonly StudioService $studioService,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $studios = $this->studioService->getAll();

        return StudioResource::collection($studios);
    }

    public function store(StorePost $request): JsonResponse
    {
        $this->studioService->insert($request->validated());

        return response()->json([
            'message' => 'スタジオを登録しました。',
        ], Response::HTTP_CREATED);
    }

    public function show(Studio $studio): StudioResource
    {
        return new StudioResource($studio);
    }

    /**
     * @throws Throwable
     */
    public function update(Studio $studio, UpdatePut $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $this->studioService->update($studio, $request->validated());
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
            'message' => 'スタジオを更新しました。',
        ]);
    }

    /**
     * @throws Throwable
     */
    public function destroy(Studio $studio): JsonResponse
    {
        DB::beginTransaction();
        try {
            $this->studioService->delete($studio);
            DB::commit();
        } catch (UserDisplayableException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            \Log::error($e->getMessage(), $e->getTrace());
            DB::rollBack();
            throw $e;
        }

        return response()->json(['message' => 'スタジオを削除しました。']);
    }
}
