<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Redis\Connections\PhpRedisConnection;
use Illuminate\Support\Facades\Redis;

class MemberAuthController extends Controller
{
    public function sendRegisterAuthenticationCode(): JsonResponse
    {
        /** @var PhpRedisConnection $connection */
        $connection = Redis::connection();
        $connection->set('member_auth_code', '123456');
        return response()->json([
            'message' => '会員登認証コードを送信しました。',
        ]);
    }
}
