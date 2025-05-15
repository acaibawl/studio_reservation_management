<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Enums\Studio\StartAt;
use App\Exceptions\Owner\Studio\ReservedStudioCantDeleteException;
use App\Exceptions\Owner\Studio\ReservedStudioCantUpdateStartAtException;
use App\Models\Member;
use App\Models\Studio;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StudioControllerTest extends TestCase
{
    #[Test]
    public function test_index_success(): void
    {
        /** @var Collection<int, Studio> $studios */
        $studios = Studio::factory()->count(3)->create();
        $this->loginAsOwner();

        $response = $this->getJson('/owner/studios');

        $response->assertOk();
        $response->assertJson(fn (AssertableJson $json) => $json->has('studios')
            ->where('studios.0.id', $studios[0]->id)
            ->where('studios.0.name', $studios[0]->name)
            ->where('studios.1.id', $studios[1]->id)
            ->where('studios.1.name', $studios[1]->name)
            ->where('studios.2.id', $studios[2]->id)
            ->where('studios.2.name', $studios[2]->name)
        );
    }

    /**
     * スタジオ削除の成功テスト
     */
    #[Test]
    public function test_destroy_success(): void
    {
        $studio = Studio::factory()->create();
        $this->loginAsOwner();

        $response = $this->deleteJson("/owner/studios/{$studio->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('studios', [
            'id' => $studio->id,
        ]);
    }

    /**
     * 削除対象のスタジオidが存在しなくて404
     */
    #[Test]
    public function test_destroy_failed_by_not_found(): void
    {
        $this->loginAsOwner();

        $response = $this->deleteJson('/owner/studios/9999');

        $response->assertNotFound();
    }

    #[Test]
    public function test_destroy_failed_by_reserved(): void
    {
        Exceptions::fake();
        $studio = Studio::factory()->create();
        $member = Member::factory()->create();
        $studio->reservations()->create([
            'member_id' => $member->id,
            'start_at' => now(),
            'finish_at' => now()->addHours(6),
            'memo' => 'memoです。',
        ]);
        $this->loginAsOwner();

        $response = $this->deleteJson("/owner/studios/{$studio->id}");

        Exceptions::assertReported(ReservedStudioCantDeleteException::class);
        $response->assertBadRequest();
        $response->assertExactJson([
            'message' => ReservedStudioCantDeleteException::MESSAGE,
        ]);
        $this->assertDatabaseHas('studios', [
            'id' => $studio->id,
        ]);
    }

    public function test_store_success(): void
    {
        $this->loginAsOwner();

        $response = $this->postJson('/owner/studios', [
            'name' => str_repeat('あ', 50),
            'start_at' => StartAt::Thirty,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('studios', [
            'name' => str_repeat('あ', 50),
            'start_at' => StartAt::Thirty,
        ]);
    }

    /**
     * スタジオ登録の失敗テスト
     */
    #[Test]
    #[DataProvider('dataProviderStoreInvalidParameter')]
    public function test_store_failed_by_validation_error(array $requestBody, array $expectedError): void
    {
        Studio::factory()->create([
            'name' => '被り確認用',
        ]);
        $this->loginAsOwner();

        $response = $this->postJson('/owner/studios', $requestBody);

        $response->assertUnprocessable();
        $response->assertInvalid($expectedError);
    }

    public static function dataProviderStoreInvalidParameter(): array
    {
        return [
            '名前は必須' => [
                'requestBody' => [
                    'name' => '',
                    'start_at' => StartAt::Thirty,
                ],
                'expectedError' => [
                    'name' => '名前は必須項目です。',
                ],
            ],
            '名前は文字列' => [
                'requestBody' => [
                    'name' => 12345,
                    'start_at' => StartAt::Thirty,
                ],
                'expectedError' => [
                    'name' => '名前には、文字列を指定してください。',
                ],
            ],
            '名前は50文字以下' => [
                'requestBody' => [
                    'name' => str_repeat('a', 51),
                    'start_at' => StartAt::Thirty,
                ],
                'expectedError' => [
                    'name' => '名前の文字数は、50文字以下である必要があります。',
                ],
            ],
            '名前はユニーク' => [
                'requestBody' => [
                    'name' => '被り確認用',
                    'start_at' => StartAt::Thirty,
                ],
                'expectedError' => [
                    'name' => '指定の名前は既に使用されています。',
                ],
            ],
            '開始時間は必須' => [
                'requestBody' => [
                    'name' => 'Aスタ',
                    'start_at' => '',
                ],
                'expectedError' => [
                    'start_at' => '開始時間は必須項目です。',
                ],
            ],
            '開始時間は数値' => [
                'requestBody' => [
                    'name' => 'Aスタ',
                    'start_at' => 'a',
                ],
                'expectedError' => [
                    'start_at' => '選択した 開始時間は 無効です。',
                ],
            ],
            '開始時間は特定値以外受け付けない' => [
                'requestBody' => [
                    'name' => 'Aスタ',
                    'start_at' => 15,
                ],
                'expectedError' => [
                    'start_at' => '選択した 開始時間は 無効です。',
                ],
            ],
        ];
    }

    /*
     * スタジオ1件取得の成功テスト
     */
    #[Test]
    public function test_show_success(): void
    {
        $studio = Studio::factory()->create();
        $this->loginAsOwner();

        $response = $this->getJson("/owner/studios/{$studio->id}");
        $response->assertOk();
        $response->assertJson(fn (AssertableJson $json) => $json->has('studio')
            ->where('studio.id', $studio->id)
            ->where('studio.name', $studio->name)
            ->where('studio.start_at', $studio->start_at)
        );
    }

    /*
     * スタジオ1件取得の失敗テスト
     */
    #[Test]
    public function test_show_failed_404(): void
    {
        $studio = Studio::factory()->create();
        $invalidId = $studio->id + 1;
        $this->loginAsOwner();

        $response = $this->getJson("/owner/studios/{$invalidId}");
        $response->assertNotFound();
    }

    /**
     * スタジオ更新の成功テスト
     */
    #[Test]
    public function test_update_success(): void
    {
        $studio = Studio::factory()->create();
        $this->loginAsOwner();

        $response = $this->putJson("/owner/studios/{$studio->id}", [
            'name' => '変更後スタジオ名',
            'start_at' => StartAt::Thirty,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('studios', [
            'name' => '変更後スタジオ名',
            'start_at' => StartAt::Thirty,
        ]);
    }

    #[Test]
    public function test_update_name_success_with_reservation(): void
    {
        $studio = Studio::factory()->create([
            'name' => 'Aスタ',
            'start_at' => StartAt::Thirty,
        ]);
        $member = Member::factory()->create();
        $studio->reservations()->create([
            'member_id' => $member->id,
            'start_at' => now(),
            'finish_at' => now()->addHours(6),
            'memo' => 'memoです。',
        ]);
        $this->loginAsOwner();

        $response = $this->putJson("/owner/studios/{$studio->id}", [
            'name' => '変更後スタジオ名',
            'start_at' => StartAt::Thirty,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('studios', [
            'name' => '変更後スタジオ名',
            'start_at' => StartAt::Thirty,
        ]);
    }

    /**
     * スタジオ更新の成功テスト（名前を変更しない場合にuniqueルールに引っかからないことの確認）
     */
    #[Test]
    public function test_update_success_no_name_change(): void
    {
        $studio = Studio::factory()->create([
            'name' => 'Aスタ',
            'start_at' => StartAt::Thirty,
        ]);
        $this->loginAsOwner();

        $response = $this->putJson("/owner/studios/{$studio->id}", [
            'name' => 'Aスタ',
            'start_at' => StartAt::Zero,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('studios', [
            'name' => 'Aスタ',
            'start_at' => StartAt::Zero,
        ]);
    }

    /**
     * リクエストパラメータが原因のスタジオ更新の失敗テスト
     */
    #[Test]
    #[DataProvider('dataProviderUpdateInvalidParameter')]
    public function test_update_failed_by_validation_error(array $requestBody, array $expectedError): void
    {
        Studio::factory()->create([
            'name' => '被り確認用',
        ]);
        $updateTargetStudio = Studio::factory()->create();
        $this->loginAsOwner();

        $response = $this->putJson("/owner/studios/{$updateTargetStudio->id}", $requestBody);

        $response->assertUnprocessable();
        $response->assertInvalid($expectedError);
    }

    public static function dataProviderUpdateInvalidParameter(): array
    {
        return [
            '名前は必須' => [
                'requestBody' => [
                    'name' => '',
                    'start_at' => StartAt::Thirty,
                ],
                'expectedError' => [
                    'name' => '名前は必須項目です。',
                ],
            ],
            '名前は文字列' => [
                'requestBody' => [
                    'name' => 12345,
                    'start_at' => StartAt::Thirty,
                ],
                'expectedError' => [
                    'name' => '名前には、文字列を指定してください。',
                ],
            ],
            '名前は50文字以下' => [
                'requestBody' => [
                    'name' => str_repeat('a', 51),
                    'start_at' => StartAt::Thirty,
                ],
                'expectedError' => [
                    'name' => '名前の文字数は、50文字以下である必要があります。',
                ],
            ],
            '名前はユニーク' => [
                'requestBody' => [
                    'name' => '被り確認用',
                    'start_at' => StartAt::Thirty,
                ],
                'expectedError' => [
                    'name' => '指定の名前は既に使用されています。',
                ],
            ],
            '開始時間は必須' => [
                'requestBody' => [
                    'name' => 'Aスタ',
                    'start_at' => '',
                ],
                'expectedError' => [
                    'start_at' => '開始時間は必須項目です。',
                ],
            ],
            '開始時間は数値' => [
                'requestBody' => [
                    'name' => 'Aスタ',
                    'start_at' => 'a',
                ],
                'expectedError' => [
                    'start_at' => '選択した 開始時間は 無効です。',
                ],
            ],
            '開始時間は特定値以外受け付けない' => [
                'requestBody' => [
                    'name' => 'Aスタ',
                    'start_at' => 15,
                ],
                'expectedError' => [
                    'start_at' => '選択した 開始時間は 無効です。',
                ],
            ],
        ];
    }

    /**
     * 終了時間が未来の予約が入っているスタジオは開始時間を変更できない
     */
    #[Test]
    public function test_update_start_at_failed_by_reserved(): void
    {
        Exceptions::fake();
        $studio = Studio::factory()->create([
            'name' => 'Aスタ',
            'start_at' => StartAt::Thirty,
        ]);
        $member = Member::factory()->create();
        $studio->reservations()->create([
            'member_id' => $member->id,
            'start_at' => now(),
            'finish_at' => now()->addHours(6),
            'memo' => 'memoです。',
        ]);
        $this->loginAsOwner();

        $response = $this->putJson("/owner/studios/{$studio->id}", [
            'name' => 'Aスタ',
            'start_at' => StartAt::Zero,
        ]);

        Exceptions::assertReported(ReservedStudioCantUpdateStartAtException::class);
        $response->assertBadRequest();
        $response->assertExactJson([
            'message' => ReservedStudioCantUpdateStartAtException::MESSAGE,
        ]);
        $this->assertDatabaseHas('studios', [
            'name' => 'Aスタ',
            'start_at' => StartAt::Thirty,
        ]);
    }
}
