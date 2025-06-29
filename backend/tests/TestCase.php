<?php

declare(strict_types=1);

namespace Tests;

use App\Models\Member;
use App\Models\Owner;
use Illuminate\Foundation\Console\CliDumper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    // 予約の作成・更新処理でトランザクションではなくテーブルロックを使う為、DatabaseTransactionsではなくRefreshDatabaseを使う
    use RefreshDatabase;

    /**
     * DB のテーブルに入っているデータを出力します
     */
    protected function dumpDB(): void
    {
        if (class_exists(CliDumper::class)) {
            CliDumper::resolveDumpSourceUsing(fn () => null); // ファイル名や行数の出力を消す
        }

        // Laravel Ver.11 未満は、Schema::getAllTables() として下さい
        foreach (Schema::getTables() as $table) {
            if (isset($table->name)) {
                $name = $table->name;
            } else {
                $table = (array) $table;
                $name = reset($table);
            }

            if (in_array($name, ['migrations'], true)) {
                continue;
            }

            $collection = \DB::table($name)->get();

            if ($collection->isEmpty()) {
                continue;
            }

            $data = $collection->map(function ($item) {
                unset($item->created_at, $item->updated_at);

                return $item;
            })->toArray();

            dump(sprintf('■■■■■■■■■■■■■■■■■■■ %s %s件 ■■■■■■■■■■■■■■■■■■■', $name, $collection->count()));
            dump($data);
        }
    }

    /**
     * Dump the database query.
     */
    protected function dumpQuery(): void
    {
        \DB::enableQueryLog();

        $this->beforeApplicationDestroyed(function () {
            dump(\DB::getQueryLog());
        });
    }

    /**
     * 共通のオーナーログイン処理
     */
    protected function loginAsOwner(?Owner $owner = null): Owner
    {
        $owner ??= Owner::factory()->create();
        $this->actingAs($owner, 'api_owner');

        return $owner;
    }

    /**
     * 共通の会員ログイン処理
     */
    protected function loginAsMember(?Member $member = null): Member
    {
        $member ??= Member::factory()->create();
        $this->actingAs($member, 'api_member');

        return $member;
    }
}
