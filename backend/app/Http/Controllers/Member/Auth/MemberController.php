<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member\Auth;

use App\Exceptions\Member\Auth\MemberAlreadyRegisteredException;
use App\Exceptions\Member\Auth\PassCodeVerifyFailedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Member\Auth\StorePost;
use App\Services\Member\Auth\MemberRegisterService;
use DB;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class MemberController extends Controller
{
    public function __construct(
        private readonly MemberRegisterService $memberRegisterService,
    ) {}

    /**
     * @throws Throwable
     */
    public function store(StorePost $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $this->memberRegisterService->register($request->validated());
            DB::commit();
        } catch (MemberAlreadyRegisteredException $e) {
            DB::rollBack();
            // 攻撃の可能性があるので、infoレベルのログだけ残して成功時と同じレスポンスを返す
            \Log::info($e->getMessage());
        } catch (PassCodeVerifyFailedException $e) {
            DB::rollBack();
            // ログ出力不要

            return response()->json([
                'message' => 'コードの検証に失敗しました。',
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e->getMessage(), $e->getTrace());
            throw $e;
        }

        return response()->json([
            'message' => '会員登録をしました。',
        ], Response::HTTP_CREATED);
    }
}
