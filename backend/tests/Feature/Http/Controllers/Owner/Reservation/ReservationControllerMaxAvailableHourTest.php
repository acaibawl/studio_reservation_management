<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Owner\Reservation;

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

class ReservationControllerMaxAvailableHourTest extends TestCase
{
    /**
     * 6時間取れずに頭打ち
     * 営業時間
     */
    #[Test]
    public function test_success_block_by_business_time(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(22, 0, 0),
        ]);
        $studio = Studio::factory()->create([
            'start_at' => StartAt::Thirty,
        ]);

        $this->loginAsOwner();

        $response = $this->getJson("/owner/reservations/studios/{$studio->id}/2025-05-18/18/max-available-hour");

        $response->assertOk();
        $response->assertExactJson([
            'studio_id' => $studio->id,
            'studio_name' => $studio->name,
            'date' => '2025-05-18',
            'hour' => 18,
            'max_available_hour' => 4,
        ]);
    }

    /**
     * 6時間取れずに頭打ち
     * 定休日
     */
    #[Test]
    public function test_success_block_by_regular_holiday(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(5, 0, 0),
            'close_time' => Carbon::createFromTime(5, 0, 0),
        ]);
        $studio = Studio::factory()->create([
            'start_at' => StartAt::Thirty,
        ]);
        RegularHoliday::factory()->create([
            'code' => WeekDay::Sunday,
        ]);

        $this->loginAsOwner();

        $response = $this->getJson("/owner/reservations/studios/{$studio->id}/2025-05-18/3/max-available-hour");

        $response->assertOk();
        $response->assertExactJson([
            'studio_id' => $studio->id,
            'studio_name' => $studio->name,
            'date' => '2025-05-18',
            'hour' => 3,
            'max_available_hour' => 2,
        ]);
    }

    /**
     * 6時間取れずに頭打ち
     * 臨時休業日
     */
    #[Test]
    public function test_success_block_by_temporary_closing_day(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(5, 0, 0),
            'close_time' => Carbon::createFromTime(5, 0, 0),
        ]);
        $studio = Studio::factory()->create([
            'start_at' => StartAt::Thirty,
        ]);
        TemporaryClosingDay::factory()->create([
            'date' => '2025-05-18',
        ]);

        $this->loginAsOwner();

        $response = $this->getJson("/owner/reservations/studios/{$studio->id}/2025-05-18/3/max-available-hour");

        $response->assertOk();
        $response->assertExactJson([
            'studio_id' => $studio->id,
            'studio_name' => $studio->name,
            'date' => '2025-05-18',
            'hour' => 3,
            'max_available_hour' => 2,
        ]);
    }

    /**
     * 6時間取れずに頭打ち
     * 2ヶ月先
     */
    #[Test]
    public function test_success_block_by_over_max_duration(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(5, 0, 0),
            'close_time' => Carbon::createFromTime(5, 0, 0),
        ]);
        $studio = Studio::factory()->create([
            'start_at' => StartAt::Thirty,
        ]);

        $this->loginAsOwner();

        $response = $this->getJson("/owner/reservations/studios/{$studio->id}/2025-07-17/3/max-available-hour");

        $response->assertOk();
        $response->assertExactJson([
            'studio_id' => $studio->id,
            'studio_name' => $studio->name,
            'date' => '2025-07-17',
            'hour' => 3,
            'max_available_hour' => 2,
        ]);
    }

    /**
     * 6時間取れずに頭打ち
     * 他の予約がある
     */
    #[Test]
    public function test_success_block_by_other_reservation(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(5, 0, 0),
            'close_time' => Carbon::createFromTime(5, 0, 0),
        ]);
        $studio = Studio::factory()->create([
            'start_at' => StartAt::Thirty,
        ]);
        Reservation::factory()->create([
            'studio_id' => $studio->id,
            'start_at' => '2025-05-18 5:30:00',
            'finish_at' => '2025-05-18 7:29:59',
        ]);

        $this->loginAsOwner();

        $response = $this->getJson("/owner/reservations/studios/{$studio->id}/2025-05-18/3/max-available-hour");

        $response->assertOk();
        $response->assertExactJson([
            'studio_id' => $studio->id,
            'studio_name' => $studio->name,
            'date' => '2025-05-18',
            'hour' => 3,
            'max_available_hour' => 2,
        ]);
    }

    /**
     * 最大の6時間取れる
     * 日付を跨ぐ
     */
    #[Test]
    public function test_success_get_max_hour_cross_date(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(5, 0, 0),
        ]);
        $studio = Studio::factory()->create([
            'start_at' => StartAt::Thirty,
        ]);
        $this->loginAsOwner();

        $response = $this->getJson("/owner/reservations/studios/{$studio->id}/2025-05-18/22/max-available-hour");

        $response->assertOk();
        $response->assertExactJson([
            'studio_id' => $studio->id,
            'studio_name' => $studio->name,
            'date' => '2025-05-18',
            'hour' => 22,
            'max_available_hour' => 6,
        ]);
    }

    /**
     * 最大の6時間取れる
     * 24時間営業の営業時間を跨ぐ
     */
    #[Test]
    public function test_success_get_max_hour_cross_business_time(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(10, 0, 0),
        ]);
        $studio = Studio::factory()->create([
            'start_at' => StartAt::Thirty,
        ]);
        $this->loginAsOwner();

        $response = $this->getJson("/owner/reservations/studios/{$studio->id}/2025-05-18/8/max-available-hour");

        $response->assertOk();
        $response->assertExactJson([
            'studio_id' => $studio->id,
            'studio_name' => $studio->name,
            'date' => '2025-05-18',
            'hour' => 8,
            'max_available_hour' => 6,
        ]);
    }

    /**
     * 0時間（予約不可）
     * 現在時刻が既に利用開始時間に達している枠を指定
     */
    #[Test]
    public function test_success_get_0_hour(): void
    {
        Carbon::setTestNow('2025-05-17 18:30:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(10, 0, 0),
        ]);
        $studio = Studio::factory()->create([
            'start_at' => StartAt::Thirty,
        ]);
        $this->loginAsOwner();

        $response = $this->getJson("/owner/reservations/studios/{$studio->id}/2025-05-17/18/max-available-hour");

        $response->assertOk();
        $response->assertExactJson([
            'studio_id' => $studio->id,
            'studio_name' => $studio->name,
            'date' => '2025-05-17',
            'hour' => 18,
            'max_available_hour' => 0,
        ]);
    }
}
