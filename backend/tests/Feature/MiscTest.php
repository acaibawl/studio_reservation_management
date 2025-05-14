<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * その他テスト
 */
class MiscTest extends TestCase
{
    /**
     * ルート名の重複チェックテスト
     */
    public function test_duplicate_route_name_checking(): void
    {
        // ルート名を重複させてしまった際にエラーとして検出
        Artisan::call('route:cache');
        Artisan::call('route:clear');

        /** @phpstan-ignore method.alreadyNarrowedType */
        $this->assertTrue(true);
    }
}
