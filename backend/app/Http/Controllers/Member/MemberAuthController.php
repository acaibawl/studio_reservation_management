<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Redis\Connections\PhpRedisConnection;
use Illuminate\Support\Facades\Redis;

class MemberAuthController extends Controller
{
    public function sendRegisterAuthenticationCode(): JsonResponse
    {
        \Mail::raw('test from laravel 本文です。', function($message) {
            $message->to('atesaki@example.com')->subject('testメールタイトルです。');
        });

        /** @var PhpRedisConnection $connection */
        $connection = Redis::connection();
        $connection->set('aaa', '123456');

        return response()->json([
            'message' => '会員登録認証コードを送信しました。',
        ]);
    }
}
