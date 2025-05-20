<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Exceptions\Member\Auth\MemberAlreadyRegisteredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Member\Auth\SendSignUpEmailVerifiedCode\SendPost;
use App\Services\Member\Auth\Email\SendSignUpEmailVerifiedCodeService;
use Exception;
use Illuminate\Http\JsonResponse;

class MemberAuthController extends Controller
{
    public function __construct(
        private readonly SendSignUpEmailVerifiedCodeService $sendSignUpEmailVerifiedCodeService,
    ) {}

    /**
     * @throws Exception
     */
    public function sendSignUpEmailVerifiedCode(SendPost $request): JsonResponse
    {
        try {
            $this->sendSignUpEmailVerifiedCodeService->send($request->validated()['email']);
        } catch (MemberAlreadyRegisteredException $e) {
            // 攻撃の可能性があるので、infoレベルのログだけ残して成功時と同じレスポンスを返す
            \Log::info($e->getMessage());
        } catch (Exception $e) {
            \Log::error($e->getMessage(), $e->getTrace());
            throw $e;
        }

        return response()->json([
            'message' => 'メールアドレス認証コードを送信しました。',
        ]);
    }
}
