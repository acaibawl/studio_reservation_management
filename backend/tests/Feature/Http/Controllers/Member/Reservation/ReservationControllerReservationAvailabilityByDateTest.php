<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Member\Reservation;

use App\Enums\Reservation\ReservationQuota\Status;
use App\Enums\Studio\StartAt;
use App\Models\BusinessTime;
use App\Models\Reservation;
use App\Models\Studio;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * 会員向けのスタジオ予約状況APIのテスト
 * オーナー向けのスタジオ枠状況APIのテストで、営業時間や休日、他の予約がある等のテストは実施している為、
 * 会員向けのテストでは他の予約が入っている枠が「予約済み」ではなく「予約不可」になることだけテストできればよい
 */
class ReservationControllerReservationAvailabilityByDateTest extends TestCase
{
    #[Test]
    public function test_get_success(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(22, 0, 0),
        ]);
        $studio = Studio::factory()->create([
            'start_at' => StartAt::Thirty,
        ]);
        Reservation::factory()->create([
            'studio_id' => $studio->id,
            'start_at' => '2025-05-18 18:30:00',
            'finish_at' => '2025-05-18 20:29:59',
        ]);

        $response = $this->getJson('/reservation_availability/date/2025-05-18');
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
                            'status' => Status::NotAvailable,
                        ],
                        [
                            'hour' => 19,
                            'status' => Status::NotAvailable,
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
}
