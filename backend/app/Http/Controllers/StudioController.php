<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\UserDisplayableException;
use App\Http\Requests\Owner\Studio\StorePost;
use App\Http\Requests\Owner\Studio\UpdatePut;
use App\Models\Studio;
use App\Services\Owner\StudioService;
use DB;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class StudioController extends Controller
{
    public function __construct(
        private readonly StudioService $studioService,
    ) {}

    public function index(): JsonResponse
    {
        $studios = $this->studioService->getAll();

        return response()->json([
            'studios' => $studios->map(fn (Studio $studio) => [
                'id' => $studio->id,
                'name' => $studio->name,
                'start_at' => $studio->start_at,
            ]),
        ]);
    }

    public function store(StorePost $request): JsonResponse
    {
        $this->studioService->insert($request->validated());

        return response()->json([
            'message' => 'スタジオを登録しました。',
        ], Response::HTTP_CREATED);
    }

    public function show(Studio $studio): JsonResponse
    {
        return response()->json([
            'studio' => [
                'id' => $studio->id,
                'name' => $studio->name,
                'start_at' => $studio->start_at,
            ],
        ]);
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
