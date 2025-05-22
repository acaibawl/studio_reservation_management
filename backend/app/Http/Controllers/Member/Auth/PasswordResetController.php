<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member\Auth;

use App\Exceptions\Member\Auth\PasswordResetTokenVerifyFailedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Member\Auth\PasswordReset\ResetPost;
use App\Http\Requests\Member\Auth\PasswordReset\SendPost;
use App\Services\Member\Auth\PasswordReset\PasswordResetService;
use App\Services\Member\Auth\PasswordReset\SendPasswordResetEmailService;
use DB;
use Illuminate\Http\JsonResponse;
use Throwable;

class PasswordResetController extends Controller
{
    public function __construct(
        private readonly SendPasswordResetEmailService $sendPasswordResetEmailService,
        private readonly PasswordResetService $passwordResetService,
    ) {}

    public function sendEmail(SendPost $request): JsonResponse
    {
        $this->sendPasswordResetEmailService->send($request->validated()['email']);

        return response()->json([
            'message' => '指定のメールアドレスにパスワードリセットメールを送信しました。',
        ]);
    }

    /**
     * @throws Throwable
     * @throws PasswordResetTokenVerifyFailedException
     */
    public function reset(ResetPost $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $validated = $request->validated();
            $this->passwordResetService->reset(
                $validated['email_verified_token'],
                $validated['email'],
                $validated['password'],
            );
            DB::commit();
        } catch (PasswordResetTokenVerifyFailedException $e) {
            // ログ出力不要
            DB::rollBack();
            throw $e;
        } catch (Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage(), $e->getTrace());
            throw $e;
        }

        return response()->json([
            'message' => 'パスワードを変更しました。',
        ]);
    }
}
