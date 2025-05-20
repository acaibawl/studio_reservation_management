<?php

namespace Tests\Feature\Http\Controllers\Member;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class MemberAuthControllerTest extends TestCase
{
    public function test_send_register_authentication_code_success()
    {
        $response = $this->postJson('/member-auth/send-register-authentication-code',[
            'email' => '<EMAIL>',
        ]);
        $response->assertOk();
    }
}
