<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member\Auth;

use App\Exceptions\Member\Auth\MemberAlreadyRegisteredException;
use App\Exceptions\Member\Auth\PassCodeVerifyFailedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Member\Auth\Email\UpdatePatch;
use App\Models\Member;
use App\Services\Member\Auth\EmailUpdateService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class EmailUpdateController extends Controller
{
    public function __construct(
        private readonly EmailUpdateService $emailUpdateService,
    ) {}

    public function update(UpdatePatch $request): JsonResponse
    {
        /** @var Member $member */
        $member = auth()->user();
        try {
            $validated = $request->validated();
            $this->emailUpdateService->update(
                $member,
                $validated['email'],
                $validated['code']
            );
        } catch (PassCodeVerifyFailedException $e) {
            return response()->json([
                'message' => 'コードの検証に失敗しました。',
            ], Response::HTTP_BAD_REQUEST);
        } catch (MemberAlreadyRegisteredException $e) {
            // 攻撃の可能性があるので、infoレベルのログだけ残して成功時と同じレスポンスを返す
            // 通常パスコードの発行自体がされないのでありえない状態
            \Log::info($e->getMessage());
        }

        return response()->json([
            'message' => 'メールアドレスを変更しました。',
        ]);
    }
}
