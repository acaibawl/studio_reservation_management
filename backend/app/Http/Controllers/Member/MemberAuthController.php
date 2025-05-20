<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Mail\Member\Auth\SignUpEmailVerifiedCodeMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Redis\Connections\PhpRedisConnection;
use Illuminate\Support\Facades\Redis;
use Mail;
use Random\RandomException;

class MemberAuthController extends Controller
{
    /**
     * 600秒=10分 で失効
     */
    private const int TTL_IN_SEC =600;

    /**
     * @throws RandomException
     */
    public function sendSignUpEmailVerifiedCode(): JsonResponse
    {
        $randomNumber = (string) random_int(0, 999999);
        $emailVerifiedCode = str_pad($randomNumber, 6, '0', STR_PAD_LEFT);

        $email = 'dummy@example.com';
        /** @var PhpRedisConnection $connection */
        $connection = Redis::connection();
        $connection->set("sign_up_email_verified_token_{$email}", $emailVerifiedCode, self::TTL_IN_SEC);

        Mail::send(new SignUpEmailVerifiedCodeMail($email, $emailVerifiedCode));

        return response()->json([
            'message' => 'メールアドレス認証コードを送信しました。',
        ]);
    }
}
