<?php

declare(strict_types=1);

namespace Feature\App\Http\Controllers;

use Tests\TestCase;

class HealthCheckControllerTest extends TestCase
{
    /**
     * healthチェックのルートテスト
     */
    public function test_index(): void
    {
        $response = $this->get('/aaa');

        $response->assertStatus(200);
    }
}
