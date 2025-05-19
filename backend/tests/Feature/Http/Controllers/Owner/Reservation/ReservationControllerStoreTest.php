<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Owner\Reservation;

use App\Enums\Studio\StartAt;
use App\Exceptions\Reservation\AvailableHourExceededException;
use App\Models\BusinessTime;
use App\Models\Studio;
use Database\Seeders\Prod\MemberSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Exceptions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReservationControllerStoreTest extends TestCase
{
    const int OWNER_DUMMY_MEMBER_ID = 9999999;

    /**
     * 予約成功
     */
    #[Test]
    public function test_store_success(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');
        $this->seed(MemberSeeder::class);

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(22, 0, 0),
        ]);
        $studio = Studio::factory()->create([
            'start_at' => StartAt::Thirty,
        ]);
        $this->loginAsOwner();

        $response = $this->postJson('/owner/reservations', [
            'studio_id' => $studio->id,
            'start_at' => '2025-05-18 16:30:00',
            'usage_hour' => 6,
            'memo' => str_repeat('a', 512),
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('reservations', [
            'studio_id' => $studio->id,
            'member_id' => self::OWNER_DUMMY_MEMBER_ID,
            'start_at' => '2025-05-18 16:30:00',
            'finish_at' => '2025-05-18 22:29:59',
            'memo' => str_repeat('a', 512),
        ]);
    }

    /**
     * 予約失敗（4時間までのところを5時間指定）
     */
    #[Test]
    public function test_store_failed_by_available_hour_exceeded_5_to_4(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');
        Exceptions::fake();
        $this->seed(MemberSeeder::class);

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(22, 0, 0),
        ]);
        $studio = Studio::factory()->create([
            'start_at' => StartAt::Thirty,
        ]);
        $this->loginAsOwner();

        $response = $this->postJson('/owner/reservations', [
            'studio_id' => $studio->id,
            'start_at' => '2025-05-18 18:30:00',
            'usage_hour' => 5,
            'memo' => 'メモ本文',
        ]);

        Exceptions::assertReported(AvailableHourExceededException::class);
        $response->assertBadRequest();
        $this->assertDatabaseMissing('reservations', [
            'studio_id' => $studio->id,
            'member_id' => self::OWNER_DUMMY_MEMBER_ID,
            'start_at' => '2025-05-18 18:30:00',
            'memo' => 'メモ本文',
        ]);
    }

    /**
     * 予約失敗（4時間までのところを5時間指定）
     */
    #[Test]
    public function test_store_failed_by_available_hour_exceeded_1_to_0(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');
        Exceptions::fake();
        $this->seed(MemberSeeder::class);

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(22, 0, 0),
        ]);
        $studio = Studio::factory()->create([
            'start_at' => StartAt::Thirty,
        ]);
        $this->loginAsOwner();

        $response = $this->postJson('/owner/reservations', [
            'studio_id' => $studio->id,
            'start_at' => '2025-05-18 22:30:00',
            'usage_hour' => 1,
            'memo' => 'メモ本文',
        ]);

        Exceptions::assertReported(AvailableHourExceededException::class);
        $response->assertBadRequest();
        $this->assertDatabaseMissing('reservations', [
            'studio_id' => $studio->id,
            'member_id' => self::OWNER_DUMMY_MEMBER_ID,
            'start_at' => '2025-05-18 18:30:00',
            'memo' => 'メモ本文',
        ]);
    }

    /**
     * バリデーションエラーによる登録失敗のテスト
     */
    #[Test]
    #[DataProvider('dataProviderStoreInvalidParameter')]
    public function test_store_failed_by_validation_error(callable $provider): void
    {
        ['requestBody' => $requestBody, 'expectedError' => $expectedError] = $provider();
        Carbon::setTestNow('2025-05-17 18:00:00');
        $this->seed(MemberSeeder::class);

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(22, 0, 0),
        ]);
        $this->loginAsOwner();

        $response = $this->postJson('/owner/reservations', $requestBody);

        $response->assertUnprocessable();
        $response->assertInvalid($expectedError);
    }

    /**
     * 各テストケースごとにstudio_idの値をfactoryでcreateしたStudioのidを元に指定するために、関数を返して遅延実行させる
     */
    public static function dataProviderStoreInvalidParameter(): array
    {
        return [
            'スタジオIDが存在しない' => [
                'provider' => function () {
                    return [
                        'requestBody' => [
                            'studio_id' => Studio::factory()->create(['start_at' => StartAt::Thirty])->id + 1,
                            'start_at' => '2025-05-18 18:30:00',
                            'usage_hour' => 4,
                            'memo' => 'メモ本文',
                        ],
                        'expectedError' => [
                            'studio_id' => '選択されたスタジオIDは、有効ではありません。',
                        ],
                    ];
                },
            ],
            'スタジオIDが空' => [
                'provider' => function () {
                    return [
                        'requestBody' => [
                            'studio_id' => '',
                            'start_at' => '2025-05-18 18:30:00',
                            'usage_hour' => 4,
                            'memo' => 'メモ本文',
                        ],
                        'expectedError' => [
                            'studio_id' => 'スタジオIDは必須項目です。',
                        ],
                    ];
                },
            ],
            'スタジオIDが文字列' => [
                'provider' => function () {
                    return [
                        'requestBody' => [
                            'studio_id' => 'あ',
                            'start_at' => '2025-05-18 18:30:00',
                            'usage_hour' => 4,
                            'memo' => 'メモ本文',
                        ],
                        'expectedError' => [
                            'studio_id' => 'スタジオIDには、整数を指定してください。',
                        ],
                    ];
                },
            ],
            '利用開始時間が空' => [
                'provider' => function () {
                    return [
                        'requestBody' => [
                            'studio_id' => Studio::factory()->create(['start_at' => StartAt::Thirty])->id,
                            'start_at' => '',
                            'usage_hour' => 4,
                            'memo' => 'メモ本文',
                        ],
                        'expectedError' => [
                            'start_at' => '利用開始時間は必須項目です。',
                        ],
                    ];
                },
            ],
            '利用開始時間が正しいフォーマットではない' => [
                'provider' => function () {
                    return [
                        'requestBody' => [
                            'studio_id' => Studio::factory()->create(['start_at' => StartAt::Thirty])->id,
                            'start_at' => '1234567890',
                            'usage_hour' => 4,
                            'memo' => 'メモ本文',
                        ],
                        'expectedError' => [
                            'start_at' => "利用開始時間の形式が'Y-m-d H:i:s'と一致しません。",
                        ],
                    ];
                },
            ],
            '利用開始時間が存在しない日付' => [
                'provider' => function () {
                    return [
                        'requestBody' => [
                            'studio_id' => Studio::factory()->create(['start_at' => StartAt::Thirty])->id,
                            'start_at' => '2025-05-32 18:30:00',
                            'usage_hour' => 4,
                            'memo' => 'メモ本文',
                        ],
                        'expectedError' => [
                            'start_at' => "利用開始時間の形式が'Y-m-d H:i:s'と一致しません。",
                        ],
                    ];
                },
            ],
            '利用時間が空' => [
                'provider' => function () {
                    return [
                        'requestBody' => [
                            'studio_id' => Studio::factory()->create(['start_at' => StartAt::Thirty])->id,
                            'start_at' => '2025-05-18 18:30:00',
                            'usage_hour' => '',
                            'memo' => 'メモ本文',
                        ],
                        'expectedError' => [
                            'usage_hour' => '利用時間は必須項目です。',
                        ],
                    ];
                },
            ],
            '利用時間が数値以外' => [
                'provider' => function () {
                    return [
                        'requestBody' => [
                            'studio_id' => Studio::factory()->create(['start_at' => StartAt::Thirty])->id,
                            'start_at' => '2025-05-18 18:30:00',
                            'usage_hour' => 'a',
                            'memo' => 'メモ本文',
                        ],
                        'expectedError' => [
                            'usage_hour' => '利用時間には、整数を指定してください。',
                        ],
                    ];
                },
            ],
            '利用時間が下限未満' => [
                'provider' => function () {
                    return [
                        'requestBody' => [
                            'studio_id' => Studio::factory()->create(['start_at' => StartAt::Thirty])->id,
                            'start_at' => '2025-05-18 18:30:00',
                            'usage_hour' => 0,
                            'memo' => 'メモ本文',
                        ],
                        'expectedError' => [
                            'usage_hour' => '利用時間には、1から、6までの数字を指定してください。',
                        ],
                    ];
                },
            ],
            '利用時間が上限超過' => [
                'provider' => function () {
                    return [
                        'requestBody' => [
                            'studio_id' => Studio::factory()->create(['start_at' => StartAt::Thirty])->id,
                            'start_at' => '2025-05-18 18:30:00',
                            'usage_hour' => 7,
                            'memo' => 'メモ本文',
                        ],
                        'expectedError' => [
                            'usage_hour' => '利用時間には、1から、6までの数字を指定してください。',
                        ],
                    ];
                },
            ],
            'メモが文字数上限超過' => [
                'provider' => function () {
                    return [
                        'requestBody' => [
                            'studio_id' => Studio::factory()->create(['start_at' => StartAt::Thirty])->id,
                            'start_at' => '2025-05-18 18:30:00',
                            'usage_hour' => 4,
                            'memo' => str_repeat('a', 513),
                        ],
                        'expectedError' => [
                            'memo' => 'メモの文字数は、512文字以下である必要があります。',
                        ],
                    ];
                },
            ],
        ];
    }
}
