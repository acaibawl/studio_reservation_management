<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member\Auth;

use App\Exceptions\Member\Auth\MemberAlreadyRegisteredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Member\Auth\ChangeEmailVerifiedCode\SendPost;
use App\Services\Member\Auth\Email\SendChangeEmailVerifiedCodeService;
use Exception;
use Illuminate\Http\JsonResponse;

class ChangeEmailVerifiedCodeController extends Controller
{
    public function __construct(
        private readonly SendChangeEmailVerifiedCodeService $sendChangeEmailVerifiedCodeService,
    ) {}

    /**
     * @throws Exception
     */
    public function send(SendPost $request): JsonResponse
    {
        try {
            $this->sendChangeEmailVerifiedCodeService->send($request->validated()['email']);
        } catch (MemberAlreadyRegisteredException $e) {
            // 攻撃の可能性があるので、infoレベルのログだけ残して成功時と同じレスポンスを返す
            \Log::info($e->getMessage());
        } catch (Exception $e) {
            \Log::error($e->getMessage(), $e->getTrace());
            throw $e;
        }

        return response()->json([
            'message' => 'メールアドレス変更認証コードを送信しました。',
        ]);
    }
}
