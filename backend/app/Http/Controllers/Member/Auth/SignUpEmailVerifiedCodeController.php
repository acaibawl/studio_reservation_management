<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member\Auth;

use App\Exceptions\Member\Auth\MemberAlreadyRegisteredException;
use App\Exceptions\Member\Auth\PassCodeVerifyFailedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Member\Auth\SignUpEmailVerifiedCode\SendPost;
use App\Http\Requests\Member\Auth\SignUpEmailVerifiedCode\VerifyPost;
use App\Services\Member\Auth\Email\SendSignUpEmailVerifiedCodeService;
use App\Services\Member\Auth\Email\VerifySignUpEmailVerifiedCodeService;
use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SignUpEmailVerifiedCodeController extends Controller
{
    public function __construct(
        private readonly SendSignUpEmailVerifiedCodeService $sendSignUpEmailVerifiedCodeService,
        private readonly VerifySignUpEmailVerifiedCodeService $verifySignUpEmailVerifiedCodeService,
    ) {}

    /**
     * @throws Exception
     */
    public function send(SendPost $request): JsonResponse
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

    public function verify(VerifyPost $request): JsonResponse
    {
        try {
            $this->verifySignUpEmailVerifiedCodeService->verify(
                $request->validated()['email'],
                $request->validated()['code']
            );
        } catch (PassCodeVerifyFailedException $e) {
            return response()->json([
                'message' => 'コードの検証に失敗しました。',
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message' => 'メールアドレス検証しました',
        ]);
    }
}
