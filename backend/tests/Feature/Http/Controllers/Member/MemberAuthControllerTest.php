<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Member;

use Illuminate\Redis\Connections\PhpRedisConnection;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class MemberAuthControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // テスト開始前にRedisをクリアする
        /** @var PhpRedisConnection $redisConnection */
        $redisConnection = Redis::connection();
        $redisConnection->flushdb();
    }

    public function test_send_register_authentication_code_success(): void
    {
        $response = $this->postJson('/member-auth/send-register-authentication-code', [
            'email' => '<EMAIL>',
        ]);
        $response->assertOk();
    }
}
