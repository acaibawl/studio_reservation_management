<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Owner\Reservation;

use App\Enums\Reservation\ReservationQuota\Status;
use App\Enums\Studio\StartAt;
use App\Models\BusinessTime;
use App\Models\RegularHoliday;
use App\Models\Reservation;
use App\Models\Studio;
use App\Models\TemporaryClosingDay;
use Carbon\WeekDay;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * dateメソッドのテストクラス
 */
class ReservationControllerGetQuotasByDateTest extends TestCase
{
    /**
     * 営業時間10-22時（跨がない）
     * 営業時間外のテスト
     */
    #[Test]
    public function test_10_to_22_hour_operation_out_of_business_hours(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(22, 0, 0),
        ]);
        $studio = Studio::factory()->create();
        $this->loginAsOwner();

        $response = $this->getJson('/owner/reservations/get-quotas-by-date/2025-05-18');

        $response->assertOk();
        $response->assertExactJson([
            'date' => '2025-05-18',
            'studios' => [
                [
                    'id' => $studio->id,
                    'name' => $studio->name,
                    'start_at' => $studio->start_at->value,
                    'reservation_quotas' => [
                        [
                            'hour' => 0,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 1,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 2,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 3,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 4,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 5,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 6,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 7,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 8,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 9,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 10,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 11,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 12,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 13,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 14,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 15,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 16,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 17,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 18,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 19,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 20,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 21,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 22,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 23,
                            'status' => Status::NotAvailable,
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * 営業時間10-5時（跨ぐ）
     * 営業時間外のテスト
     */
    #[Test]
    public function test_10_to_5_hour_operation_out_of_business_hours(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(5, 0, 0),
        ]);
        $studio = Studio::factory()->create();
        $this->loginAsOwner();

        $response = $this->getJson('/owner/reservations/get-quotas-by-date/2025-05-19');

        $response->assertOk();
        $response->assertExactJson([
            'date' => '2025-05-19',
            'studios' => [
                [
                    'id' => $studio->id,
                    'name' => $studio->name,
                    'start_at' => $studio->start_at->value,
                    'reservation_quotas' => [
                        [
                            'hour' => 0,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 1,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 2,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 3,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 4,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 5,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 6,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 7,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 8,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 9,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 10,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 11,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 12,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 13,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 14,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 15,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 16,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 17,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 18,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 19,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 20,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 21,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 22,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 23,
                            'status' => Status::Available,
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * 営業時間0-0時（24時間）
     * 営業時間外のテスト
     */
    #[Test]
    public function test_0_to_0_hour_operation_out_of_business_hours(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(0, 0, 0),
            'close_time' => Carbon::createFromTime(0, 0, 0),
        ]);
        $studio = Studio::factory()->create();
        $this->loginAsOwner();

        $response = $this->getJson('/owner/reservations/get-quotas-by-date/2025-05-19');

        $response->assertOk();
        $response->assertExactJson([
            'date' => '2025-05-19',
            'studios' => [
                [
                    'id' => $studio->id,
                    'name' => $studio->name,
                    'start_at' => $studio->start_at->value,
                    'reservation_quotas' => [
                        [
                            'hour' => 0,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 1,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 2,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 3,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 4,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 5,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 6,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 7,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 8,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 9,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 10,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 11,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 12,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 13,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 14,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 15,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 16,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 17,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 18,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 19,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 20,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 21,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 22,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 23,
                            'status' => Status::Available,
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * 営業時間10-10時（24時間）
     * 営業時間外のテスト
     */
    #[Test]
    public function test_10_to_10_hour_operation_out_of_business_hours(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(10, 0, 0),
        ]);
        $studio = Studio::factory()->create();
        $this->loginAsOwner();

        $response = $this->getJson('/owner/reservations/get-quotas-by-date/2025-05-19');

        $response->assertOk();
        $response->assertExactJson([
            'date' => '2025-05-19',
            'studios' => [
                [
                    'id' => $studio->id,
                    'name' => $studio->name,
                    'start_at' => $studio->start_at->value,
                    'reservation_quotas' => [
                        [
                            'hour' => 0,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 1,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 2,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 3,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 4,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 5,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 6,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 7,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 8,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 9,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 10,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 11,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 12,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 13,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 14,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 15,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 16,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 17,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 18,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 19,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 20,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 21,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 22,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 23,
                            'status' => Status::Available,
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * 営業時間10-22時（跨がない）
     * 2ヶ月先は予約不可のテスト
     */
    #[Test]
    public function test_10_to_22_hour_operation_2_months_ahead(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(22, 0, 0),
        ]);
        $studio = Studio::factory()->create();
        $this->loginAsOwner();

        $response = $this->getJson('/owner/reservations/get-quotas-by-date/2025-07-17');

        $response->assertOk();
        $response->assertExactJson([
            'date' => '2025-07-17',
            'studios' => [
                [
                    'id' => $studio->id,
                    'name' => $studio->name,
                    'start_at' => $studio->start_at->value,
                    'reservation_quotas' => [
                        [
                            'hour' => 0,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 1,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 2,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 3,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 4,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 5,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 6,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 7,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 8,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 9,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 10,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 11,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 12,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 13,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 14,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 15,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 16,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 17,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 18,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 19,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 20,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 21,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 22,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 23,
                            'status' => Status::NotAvailable,
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * 営業時間10-5時（跨ぐ）
     * 2ヶ月先は予約不可のテスト
     */
    #[Test]
    public function test_10_to_5_hour_operation_2_months_ahead(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(5, 0, 0),
        ]);
        $studio = Studio::factory()->create();
        $this->loginAsOwner();

        $response = $this->getJson('/owner/reservations/get-quotas-by-date/2025-07-17');

        $response->assertOk();
        $response->assertExactJson([
            'date' => '2025-07-17',
            'studios' => [
                [
                    'id' => $studio->id,
                    'name' => $studio->name,
                    'start_at' => $studio->start_at->value,
                    'reservation_quotas' => [
                        [
                            'hour' => 0,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 1,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 2,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 3,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 4,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 5,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 6,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 7,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 8,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 9,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 10,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 11,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 12,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 13,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 14,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 15,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 16,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 17,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 18,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 19,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 20,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 21,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 22,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 23,
                            'status' => Status::NotAvailable,
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * 営業時間0-0時（24時間）
     * 2ヶ月先は予約不可のテスト
     */
    #[Test]
    public function test_0_to_0_hour_operation_2_months_ahead(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(0, 0, 0),
            'close_time' => Carbon::createFromTime(0, 0, 0),
        ]);
        $studio = Studio::factory()->create();
        $this->loginAsOwner();

        $response = $this->getJson('/owner/reservations/get-quotas-by-date/2025-07-17');

        $response->assertOk();
        $response->assertExactJson([
            'date' => '2025-07-17',
            'studios' => [
                [
                    'id' => $studio->id,
                    'name' => $studio->name,
                    'start_at' => $studio->start_at->value,
                    'reservation_quotas' => [
                        [
                            'hour' => 0,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 1,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 2,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 3,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 4,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 5,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 6,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 7,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 8,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 9,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 10,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 11,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 12,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 13,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 14,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 15,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 16,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 17,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 18,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 19,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 20,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 21,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 22,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 23,
                            'status' => Status::NotAvailable,
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * 営業時間10-10時（24時間）
     * 2ヶ月先は予約不可のテスト
     */
    #[Test]
    public function test_10_to_10_hour_operation_2_months_ahead(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(10, 0, 0),
        ]);
        $studio = Studio::factory()->create();
        $this->loginAsOwner();

        $response = $this->getJson('/owner/reservations/get-quotas-by-date/2025-07-17');

        $response->assertOk();
        $response->assertExactJson([
            'date' => '2025-07-17',
            'studios' => [
                [
                    'id' => $studio->id,
                    'name' => $studio->name,
                    'start_at' => $studio->start_at->value,
                    'reservation_quotas' => [
                        [
                            'hour' => 0,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 1,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 2,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 3,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 4,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 5,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 6,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 7,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 8,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 9,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 10,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 11,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 12,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 13,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 14,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 15,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 16,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 17,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 18,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 19,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 20,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 21,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 22,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 23,
                            'status' => Status::NotAvailable,
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * 営業時間10-22時（跨がない）
     * 現在日時より前のテスト
     */
    #[Test]
    public function test_10_to_22_hour_operation_past_time(): void
    {
        Carbon::setTestNow('2025-05-17 18:30:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(22, 0, 0),
        ]);
        $studio = Studio::factory()->create([
            'start_at' => StartAt::Thirty,
        ]);
        $this->loginAsOwner();

        $response = $this->getJson('/owner/reservations/get-quotas-by-date/2025-05-17');

        $response->assertOk();
        $response->assertExactJson([
            'date' => '2025-05-17',
            'studios' => [
                [
                    'id' => $studio->id,
                    'name' => $studio->name,
                    'start_at' => $studio->start_at->value,
                    'reservation_quotas' => [
                        [
                            'hour' => 0,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 1,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 2,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 3,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 4,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 5,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 6,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 7,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 8,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 9,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 10,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 11,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 12,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 13,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 14,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 15,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 16,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 17,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 18,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 19,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 20,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 21,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 22,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 23,
                            'status' => Status::NotAvailable,
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * 営業時間10-5時（跨ぐ）
     * 現在日時より前のテスト
     */
    #[Test]
    public function test_10_to_5_hour_operation_past_time(): void
    {
        Carbon::setTestNow('2025-05-18 1:30:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(5, 0, 0),
        ]);
        $studio = Studio::factory()->create([
            'start_at' => StartAt::Thirty,
        ]);
        $this->loginAsOwner();

        $response = $this->getJson('/owner/reservations/get-quotas-by-date/2025-05-18');

        $response->assertOk();
        $response->assertExactJson([
            'date' => '2025-05-18',
            'studios' => [
                [
                    'id' => $studio->id,
                    'name' => $studio->name,
                    'start_at' => $studio->start_at->value,
                    'reservation_quotas' => [
                        [
                            'hour' => 0,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 1,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 2,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 3,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 4,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 5,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 6,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 7,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 8,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 9,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 10,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 11,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 12,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 13,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 14,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 15,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 16,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 17,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 18,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 19,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 20,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 21,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 22,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 23,
                            'status' => Status::Available,
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * 営業時間0-0時（24時間）
     * 現在日時より前のテスト
     */
    #[Test]
    public function test_0_to_0_hour_operation_past_time(): void
    {
        Carbon::setTestNow('2025-05-17 00:30:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(0, 0, 0),
            'close_time' => Carbon::createFromTime(0, 0, 0),
        ]);
        $studio = Studio::factory()->create([
            'start_at' => StartAt::Thirty,
        ]);
        $this->loginAsOwner();

        $response = $this->getJson('/owner/reservations/get-quotas-by-date/2025-05-17');

        $response->assertOk();
        $response->assertExactJson([
            'date' => '2025-05-17',
            'studios' => [
                [
                    'id' => $studio->id,
                    'name' => $studio->name,
                    'start_at' => $studio->start_at->value,
                    'reservation_quotas' => [
                        [
                            'hour' => 0,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 1,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 2,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 3,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 4,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 5,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 6,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 7,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 8,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 9,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 10,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 11,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 12,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 13,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 14,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 15,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 16,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 17,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 18,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 19,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 20,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 21,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 22,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 23,
                            'status' => Status::Available,
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * 営業時間10-10時（24時間）
     * 現在日時より前のテスト
     */
    #[Test]
    public function test_10_to_10_hour_operation_past_time(): void
    {
        Carbon::setTestNow('2025-05-17 10:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(10, 0, 0),
        ]);
        $studio = Studio::factory()->create([
            'start_at' => StartAt::Zero,
        ]);
        $this->loginAsOwner();

        $response = $this->getJson('/owner/reservations/get-quotas-by-date/2025-05-17');

        $response->assertOk();
        $response->assertExactJson([
            'date' => '2025-05-17',
            'studios' => [
                [
                    'id' => $studio->id,
                    'name' => $studio->name,
                    'start_at' => $studio->start_at->value,
                    'reservation_quotas' => [
                        [
                            'hour' => 0,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 1,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 2,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 3,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 4,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 5,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 6,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 7,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 8,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 9,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 10,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 11,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 12,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 13,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 14,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 15,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 16,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 17,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 18,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 19,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 20,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 21,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 22,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 23,
                            'status' => Status::Available,
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * 営業時間10-22時（跨がない）
     * 定休日のテスト
     */
    #[Test]
    public function test_10_to_22_hour_operation_regular_holiday(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(22, 0, 0),
        ]);
        $studio = Studio::factory()->create();
        RegularHoliday::factory()->create([
            'code' => WeekDay::Sunday,
        ]);
        $this->loginAsOwner();

        $response = $this->getJson('/owner/reservations/get-quotas-by-date/2025-05-18');

        $response->assertOk();
        $response->assertExactJson([
            'date' => '2025-05-18',
            'studios' => [
                [
                    'id' => $studio->id,
                    'name' => $studio->name,
                    'start_at' => $studio->start_at->value,
                    'reservation_quotas' => [
                        [
                            'hour' => 0,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 1,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 2,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 3,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 4,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 5,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 6,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 7,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 8,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 9,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 10,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 11,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 12,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 13,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 14,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 15,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 16,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 17,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 18,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 19,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 20,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 21,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 22,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 23,
                            'status' => Status::NotAvailable,
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * 営業時間10-5時（跨ぐ）
     * 定休日のテスト
     */
    #[Test]
    public function test_10_to_5_hour_operation_regular_holiday(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(5, 0, 0),
        ]);
        $studio = Studio::factory()->create();
        RegularHoliday::factory()->create([
            'code' => WeekDay::Sunday,
        ]);
        $this->loginAsOwner();

        $response = $this->getJson('/owner/reservations/get-quotas-by-date/2025-05-19');

        $response->assertOk();
        $response->assertExactJson([
            'date' => '2025-05-19',
            'studios' => [
                [
                    'id' => $studio->id,
                    'name' => $studio->name,
                    'start_at' => $studio->start_at->value,
                    'reservation_quotas' => [
                        [
                            'hour' => 0,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 1,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 2,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 3,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 4,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 5,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 6,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 7,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 8,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 9,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 10,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 11,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 12,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 13,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 14,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 15,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 16,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 17,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 18,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 19,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 20,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 21,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 22,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 23,
                            'status' => Status::Available,
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * 営業時間10-10時（24時間）
     * 定休日のテスト
     */
    #[Test]
    public function test_10_to_10_hour_operation_regular_holiday(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(10, 0, 0),
        ]);
        $studio = Studio::factory()->create();
        RegularHoliday::factory()->create([
            'code' => WeekDay::Sunday,
        ]);
        $this->loginAsOwner();

        $response = $this->getJson('/owner/reservations/get-quotas-by-date/2025-05-18');

        $response->assertOk();
        $response->assertExactJson([
            'date' => '2025-05-18',
            'studios' => [
                [
                    'id' => $studio->id,
                    'name' => $studio->name,
                    'start_at' => $studio->start_at->value,
                    'reservation_quotas' => [
                        [
                            'hour' => 0,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 1,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 2,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 3,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 4,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 5,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 6,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 7,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 8,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 9,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 10,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 11,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 12,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 13,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 14,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 15,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 16,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 17,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 18,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 19,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 20,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 21,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 22,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 23,
                            'status' => Status::NotAvailable,
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * 営業時間10-22時（跨がない）
     * 臨時休業日のテスト
     */
    #[Test]
    public function test_10_to_22_hour_operation_temporary_closing_day(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(22, 0, 0),
        ]);
        $studio = Studio::factory()->create();
        TemporaryClosingDay::factory()->create([
            'date' => Carbon::create(2025, 5, 18),
        ]);
        $this->loginAsOwner();

        $response = $this->getJson('/owner/reservations/get-quotas-by-date/2025-05-18');

        $response->assertOk();
        $response->assertExactJson([
            'date' => '2025-05-18',
            'studios' => [
                [
                    'id' => $studio->id,
                    'name' => $studio->name,
                    'start_at' => $studio->start_at->value,
                    'reservation_quotas' => [
                        [
                            'hour' => 0,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 1,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 2,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 3,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 4,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 5,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 6,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 7,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 8,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 9,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 10,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 11,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 12,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 13,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 14,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 15,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 16,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 17,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 18,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 19,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 20,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 21,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 22,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 23,
                            'status' => Status::NotAvailable,
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * 営業時間10-5時（跨ぐ）
     * 臨時休業日のテスト
     */
    #[Test]
    public function test_10_to_5_hour_operation_temporary_closing_day(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(5, 0, 0),
        ]);
        $studio = Studio::factory()->create();
        TemporaryClosingDay::factory()->create([
            'date' => Carbon::create(2025, 5, 18),
        ]);
        $this->loginAsOwner();

        $response = $this->getJson('/owner/reservations/get-quotas-by-date/2025-05-19');

        $response->assertOk();
        $response->assertExactJson([
            'date' => '2025-05-19',
            'studios' => [
                [
                    'id' => $studio->id,
                    'name' => $studio->name,
                    'start_at' => $studio->start_at->value,
                    'reservation_quotas' => [
                        [
                            'hour' => 0,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 1,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 2,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 3,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 4,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 5,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 6,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 7,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 8,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 9,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 10,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 11,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 12,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 13,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 14,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 15,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 16,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 17,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 18,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 19,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 20,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 21,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 22,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 23,
                            'status' => Status::Available,
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * 営業時間0-0時（24時間）
     * 臨時休業日のテスト
     */
    #[Test]
    public function test_0_to_0_hour_operation_temporary_closing_day(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(0, 0, 0),
            'close_time' => Carbon::createFromTime(0, 0, 0),
        ]);
        $studio = Studio::factory()->create();
        TemporaryClosingDay::factory()->create([
            'date' => Carbon::create(2025, 5, 19),
        ]);
        $this->loginAsOwner();

        $response = $this->getJson('/owner/reservations/get-quotas-by-date/2025-05-19');

        $response->assertOk();
        $response->assertExactJson([
            'date' => '2025-05-19',
            'studios' => [
                [
                    'id' => $studio->id,
                    'name' => $studio->name,
                    'start_at' => $studio->start_at->value,
                    'reservation_quotas' => [
                        [
                            'hour' => 0,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 1,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 2,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 3,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 4,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 5,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 6,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 7,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 8,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 9,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 10,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 11,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 12,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 13,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 14,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 15,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 16,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 17,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 18,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 19,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 20,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 21,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 22,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 23,
                            'status' => Status::NotAvailable,
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * 営業時間10-10時（24時間）
     * 臨時休業日のテスト
     */
    #[Test]
    public function test_10_to_10_hour_operation_temporary_closing_day(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(10, 0, 0),
        ]);
        $studio = Studio::factory()->create();
        TemporaryClosingDay::factory()->create([
            'date' => Carbon::create(2025, 5, 18),
        ]);
        $this->loginAsOwner();

        $response = $this->getJson('/owner/reservations/get-quotas-by-date/2025-05-19');

        $response->assertOk();
        $response->assertExactJson([
            'date' => '2025-05-19',
            'studios' => [
                [
                    'id' => $studio->id,
                    'name' => $studio->name,
                    'start_at' => $studio->start_at->value,
                    'reservation_quotas' => [
                        [
                            'hour' => 0,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 1,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 2,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 3,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 4,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 5,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 6,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 7,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 8,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 9,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 10,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 11,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 12,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 13,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 14,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 15,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 16,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 17,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 18,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 19,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 20,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 21,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 22,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 23,
                            'status' => Status::Available,
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * 営業時間10-22時（跨がない）
     * 既に予約が入っているテスト
     */
    #[Test]
    public function test_10_to_22_hour_operation_already_reserved(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(22, 0, 0),
        ]);
        $studio = Studio::factory()->create([
            'start_at' => StartAt::Thirty,
        ]);
        $reservation = Reservation::factory()->create([
            'studio_id' => $studio->id,
            'start_at' => Carbon::create(2025, 5, 18, 18, 30, 0),
            'finish_at' => Carbon::create(2025, 5, 18, 20, 29, 59),
        ]);
        $this->loginAsOwner();

        $response = $this->getJson('/owner/reservations/get-quotas-by-date/2025-05-18');

        $response->assertOk();
        $response->assertExactJson([
            'date' => '2025-05-18',
            'studios' => [
                [
                    'id' => $studio->id,
                    'name' => $studio->name,
                    'start_at' => $studio->start_at->value,
                    'reservation_quotas' => [
                        [
                            'hour' => 0,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 1,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 2,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 3,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 4,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 5,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 6,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 7,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 8,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 9,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 10,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 11,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 12,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 13,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 14,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 15,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 16,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 17,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 18,
                            'status' => Status::Reserved,
                            'reservation_id' => $reservation->id,
                        ],
                        [
                            'hour' => 19,
                            'status' => Status::Reserved,
                            'reservation_id' => $reservation->id,
                        ],
                        [
                            'hour' => 20,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 21,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 22,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 23,
                            'status' => Status::NotAvailable,
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * 営業時間10-5時（跨ぐ）
     * 既に予約が入っているテスト
     */
    #[Test]
    public function test_10_to_5_hour_operation_already_reserved(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(5, 0, 0),
        ]);
        $studio = Studio::factory()->create([
            'start_at' => StartAt::Thirty,
        ]);
        $reservation = Reservation::factory()->create([
            'studio_id' => $studio->id,
            'start_at' => Carbon::create(2025, 5, 19, 0, 30, 0),
            'finish_at' => Carbon::create(2025, 5, 19, 4, 29, 59),
        ]);
        $this->loginAsOwner();

        $response = $this->getJson('/owner/reservations/get-quotas-by-date/2025-05-19');

        $response->assertOk();
        $response->assertExactJson([
            'date' => '2025-05-19',
            'studios' => [
                [
                    'id' => $studio->id,
                    'name' => $studio->name,
                    'start_at' => $studio->start_at->value,
                    'reservation_quotas' => [
                        [
                            'hour' => 0,
                            'status' => Status::Reserved,
                            'reservation_id' => $reservation->id,
                        ],
                        [
                            'hour' => 1,
                            'status' => Status::Reserved,
                            'reservation_id' => $reservation->id,
                        ],
                        [
                            'hour' => 2,
                            'status' => Status::Reserved,
                            'reservation_id' => $reservation->id,
                        ],
                        [
                            'hour' => 3,
                            'status' => Status::Reserved,
                            'reservation_id' => $reservation->id,
                        ],
                        [
                            'hour' => 4,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 5,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 6,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 7,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 8,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 9,
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 10,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 11,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 12,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 13,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 14,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 15,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 16,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 17,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 18,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 19,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 20,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 21,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 22,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 23,
                            'status' => Status::Available,
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * 営業時間0-0時（24時間）
     * 既に予約が入っているテスト
     * 予約が日を跨いでいる場合のテスト
     */
    #[Test]
    public function test_0_to_0_hour_operation_already_reserved(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(0, 0, 0),
            'close_time' => Carbon::createFromTime(0, 0, 0),
        ]);
        $studio = Studio::factory()->create([
            'start_at' => StartAt::Zero,
        ]);
        $reservation = Reservation::factory()->create([
            'studio_id' => $studio->id,
            'start_at' => Carbon::create(2025, 5, 18, 20, 0, 0),
            'finish_at' => Carbon::create(2025, 5, 19, 1, 59, 59),
        ]);
        $this->loginAsOwner();

        $response = $this->getJson('/owner/reservations/get-quotas-by-date/2025-05-19');

        $response->assertOk();
        $response->assertExactJson([
            'date' => '2025-05-19',
            'studios' => [
                [
                    'id' => $studio->id,
                    'name' => $studio->name,
                    'start_at' => $studio->start_at->value,
                    'reservation_quotas' => [
                        [
                            'hour' => 0,
                            'status' => Status::Reserved,
                            'reservation_id' => $reservation->id,
                        ],
                        [
                            'hour' => 1,
                            'status' => Status::Reserved,
                            'reservation_id' => $reservation->id,
                        ],
                        [
                            'hour' => 2,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 3,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 4,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 5,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 6,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 7,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 8,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 9,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 10,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 11,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 12,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 13,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 14,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 15,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 16,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 17,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 18,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 19,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 20,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 21,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 22,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 23,
                            'status' => Status::Available,
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * 営業時間10-10時（24時間）
     * 既に予約が入っているテスト
     * 予約が営業時間を跨いでいる場合のテスト
     */
    #[Test]
    public function test_10_to_10_hour_operation_already_reserved(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(10, 0, 0),
        ]);
        $studio = Studio::factory()->create([
            'start_at' => StartAt::Thirty,
        ]);
        $reservation = Reservation::factory()->create([
            'studio_id' => $studio->id,
            'start_at' => Carbon::create(2025, 5, 19, 7, 30, 0),
            'finish_at' => Carbon::create(2025, 5, 19, 11, 29, 59),
        ]);
        $this->loginAsOwner();

        $response = $this->getJson('/owner/reservations/get-quotas-by-date/2025-05-19');

        $response->assertOk();
        $response->assertExactJson([
            'date' => '2025-05-19',
            'studios' => [
                [
                    'id' => $studio->id,
                    'name' => $studio->name,
                    'start_at' => $studio->start_at->value,
                    'reservation_quotas' => [
                        [
                            'hour' => 0,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 1,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 2,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 3,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 4,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 5,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 6,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 7,
                            'status' => Status::Reserved,
                            'reservation_id' => $reservation->id,
                        ],
                        [
                            'hour' => 8,
                            'status' => Status::Reserved,
                            'reservation_id' => $reservation->id,
                        ],
                        [
                            'hour' => 9,
                            'status' => Status::Reserved,
                            'reservation_id' => $reservation->id,
                        ],
                        [
                            'hour' => 10,
                            'status' => Status::Reserved,
                            'reservation_id' => $reservation->id,
                        ],
                        [
                            'hour' => 11,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 12,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 13,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 14,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 15,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 16,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 17,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 18,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 19,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 20,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 21,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 22,
                            'status' => Status::Available,
                        ],
                        [
                            'hour' => 23,
                            'status' => Status::Available,
                        ],
                    ],
                ],
            ],
        ]);
    }
}
