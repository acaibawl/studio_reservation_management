<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Owner;

use App\Models\BusinessTime;
use App\Models\RegularHoliday;
use Carbon\WeekDay;
use Illuminate\Support\Arr;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BusinessDayControllerTest extends TestCase
{
    private const array VALID_PUT_ATTRIBUTE = [
        'regular_holidays' => [
            WeekDay::Sunday,
            WeekDay::Friday,
        ],
        'business_time' => [
            'open_time' => '09:00',
            'close_time' => '18:00',
        ],
    ];

    /**
     * indexの正常系テスト
     */
    #[Test]
    public function test_index_success(): void
    {
        RegularHoliday::factory()->createMany([
            ['code' => WeekDay::Saturday],
            ['code' => WeekDay::Sunday],
        ]);
        $businessTime = BusinessTime::factory()->create();
        $this->loginAsOwner();

        $response = $this->getJson('/owner/business-day');

        $response->assertOk();
        $response->assertJson(fn (AssertableJson $json) => $json->hasAll([
            'regular_holidays',
            'business_time',
        ])->where('regular_holidays.0.code', WeekDay::Sunday)
            ->where('regular_holidays.1.code', WeekDay::Saturday)
            ->where('business_time.open_time', $businessTime->open_time->toTimeString())
            ->where('business_time.close_time', $businessTime->close_time->toTimeString())
        );
    }

    /**
     * indexはログインしていないとアクセスできない
     */
    #[Test]
    public function test_index_failed_by_not_logged_in(): void
    {
        $response = $this->getJson('/owner/business-day');
        $response->assertUnauthorized();
    }

    /**
     * update成功のテスト
     */
    #[Test]
    public function test_update_success(): void
    {
        RegularHoliday::factory()->createMany([
            ['code' => WeekDay::Saturday],
            ['code' => WeekDay::Sunday],
        ]);
        BusinessTime::factory()->create();
        $this->loginAsOwner();

        $response = $this->putJson('/owner/business-day', self::VALID_PUT_ATTRIBUTE);

        $response->assertOk();
        $this->assertDatabaseHas('regular_holidays', [
            'code' => WeekDay::Friday,
        ]);
        $this->assertDatabaseHas('regular_holidays', [
            'code' => WeekDay::Sunday,
        ]);
        $this->assertDatabaseMissing('regular_holidays', [
            'code' => WeekDay::Saturday,
        ]);
        $this->assertDatabaseHas('business_times', [
            'open_time' => '09:00',
            'close_time' => '18:00',
        ]);
    }

    #[Test]
    #[DataProvider('dataProviderUpdateInvalidParameter')]
    public function test_update_failed_by_validation_error(array $requestBody, array $expectedError): void
    {
        RegularHoliday::factory()->createMany([
            ['code' => WeekDay::Saturday],
            ['code' => WeekDay::Sunday],
        ]);
        BusinessTime::factory()->create();
        $this->loginAsOwner();

        $response = $this->putJson('/owner/business-day', $requestBody);
        $response->assertUnprocessable();
        $response->assertInvalid($expectedError);
    }

    /**
     * @return array[]
     */
    public static function dataProviderUpdateInvalidParameter(): array
    {
        return [
            '定休日は必須' => [
                'requestBody' => Arr::except(self::VALID_PUT_ATTRIBUTE, ['regular_holidays']),
                'expectedError' => [
                    'regular_holidays' => '定休日は必須項目です。',
                ],
            ],
            '定休日に無効な値' => [
                'requestBody' => [
                    ...self::VALID_PUT_ATTRIBUTE,
                    'regular_holidays' => [7],
                ],
                'expectedError' => [
                    'regular_holidays.0' => '選択した 定休日は 無効です。',
                ],
            ],
            '営業時間は必須' => [
                'requestBody' => Arr::except(self::VALID_PUT_ATTRIBUTE, ['business_time']),
                'expectedError' => [
                    'business_time' => '営業時間は必須項目です。',
                ],
            ],
            '営業開始時間は必須' => [
                'requestBody' => Arr::except(self::VALID_PUT_ATTRIBUTE, ['business_time.open_time']),
                'expectedError' => [
                    'business_time.open_time' => '営業開始時間は必須項目です。',
                ],
            ],
            '営業開始時間が時間になってない' => [
                'requestBody' => [
                    ...self::VALID_PUT_ATTRIBUTE,
                    'business_time' => [
                        'open_time' => 'あああああ',
                    ],
                ],
                'expectedError' => [
                    'business_time.open_time' => "営業開始時間の形式が'H:i'と一致しません。",
                ],
            ],
            '営業開始時間が無効な時間' => [
                'requestBody' => [
                    ...self::VALID_PUT_ATTRIBUTE,
                    'business_time' => [
                        'open_time' => '99:99',
                    ],
                ],
                'expectedError' => [
                    'business_time.open_time' => "営業開始時間の形式が'H:i'と一致しません。",
                ],
            ],
            '営業終了時間は必須' => [
                'requestBody' => Arr::except(self::VALID_PUT_ATTRIBUTE, ['business_time.close_time']),
                'expectedError' => [
                    'business_time.close_time' => '営業終了時間は必須項目です。',
                ],
            ],
            '営業終了時間が時間になってない' => [
                'requestBody' => [
                    ...self::VALID_PUT_ATTRIBUTE,
                    'business_time' => [
                        'close_time' => 'あああああ',
                    ],
                ],
                'expectedError' => [
                    'business_time.close_time' => "営業終了時間の形式が'H:i'と一致しません。",
                ],
            ],
            '営業終了時間が無効な時間' => [
                'requestBody' => [
                    ...self::VALID_PUT_ATTRIBUTE,
                    'business_time' => [
                        'close_time' => '99:99',
                    ],
                ],
                'expectedError' => [
                    'business_time.close_time' => "営業終了時間の形式が'H:i'と一致しません。",
                ],
            ],
        ];
    }
}
