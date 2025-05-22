<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Member\Auth\PasswordReset\SendPost;
use App\Services\Member\Auth\PasswordReset\SendPasswordResetEmailService;
use Illuminate\Http\JsonResponse;

class PasswordResetController extends Controller
{
    public function __construct(
        private readonly SendPasswordResetEmailService $sendPasswordResetEmailService,
    ) {}

    public function sendEmail(SendPost $request): JsonResponse
    {
        $this->sendPasswordResetEmailService->send($request->validated()['email']);

        return response()->json([
            'message' => '指定のメールアドレスにパスワードリセットメールを送信しました。',
        ]);
    }
}
