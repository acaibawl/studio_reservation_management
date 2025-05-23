<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Owner\Reservation;

use App\Enums\Studio\StartAt;
use App\Models\BusinessTime;
use App\Models\Member;
use App\Models\RegularHoliday;
use App\Models\Reservation;
use App\Models\Studio;
use App\Models\TemporaryClosingDay;
use Carbon\WeekDay;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReservationControllerShowTest extends TestCase
{
    /**
     * ギリギリまで予め予約していた
     * 営業時間でブロック
     */
    #[Test]
    public function test_show_blocked_by_business_time(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(22, 0, 0),
        ]);
        $studio = Studio::factory()->create([
            'start_at' => StartAt::Zero,
        ]);
        $member = Member::factory()->create();
        $reservation = Reservation::factory()->create([
            'member_id' => $member->id,
            'studio_id' => $studio->id,
            'start_at' => '2025-05-18 18:00:00',
            'finish_at' => '2025-05-18 21:59:59',
        ]);
        $this->loginAsOwner();

        $response = $this->getJson("/owner/studios/{$studio->id}/reservations/{$reservation->id}");

        $response->assertOk();
        $response->assertExactJson([
            'reservation' => [
                'id' => $reservation->id,
                'studio_id' => $studio->id,
                'studio_name' => $studio->name,
                'start_at' => $reservation->start_at->format('Y-m-d H:i:s'),
                'finish_at' => $reservation->finish_at->format('Y-m-d H:i:s'),
                'max_usage_hour' => 4,
                'member_id' => $member->id,
                'member_name' => $member->name,
                'memo' => $reservation->memo,
            ],
        ]);
    }

    /**
     * ギリギリまで予め予約していた
     * 定休日でブロック
     */
    #[Test]
    public function test_show_blocked_by_regular_holiday(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(10, 0, 0),
        ]);
        $studio = Studio::factory()->create([
            'start_at' => StartAt::Thirty,
        ]);
        RegularHoliday::factory()->create([
            'code' => WeekDay::Sunday,
        ]);
        $member = Member::factory()->create();
        $reservation = Reservation::factory()->create([
            'member_id' => $member->id,
            'studio_id' => $studio->id,
            'start_at' => '2025-05-18 8:30:00',
            'finish_at' => '2025-05-18 10:29:59',
        ]);
        $this->loginAsOwner();

        $response = $this->getJson("/owner/studios/{$studio->id}/reservations/{$reservation->id}");

        $response->assertOk();
        $response->assertExactJson([
            'reservation' => [
                'id' => $reservation->id,
                'studio_id' => $studio->id,
                'studio_name' => $studio->name,
                'start_at' => $reservation->start_at->format('Y-m-d H:i:s'),
                'finish_at' => $reservation->finish_at->format('Y-m-d H:i:s'),
                'max_usage_hour' => 2,
                'member_id' => $member->id,
                'member_name' => $member->name,
                'memo' => $reservation->memo,
            ],
        ]);
    }

    /**
     * ギリギリまで予め予約していた
     * 臨時休業日でブロック
     */
    #[Test]
    public function test_show_blocked_by_temporary_closing_day(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(10, 0, 0),
        ]);
        $studio = Studio::factory()->create([
            'start_at' => StartAt::Thirty,
        ]);
        TemporaryClosingDay::factory()->create([
            'date' => '2025-05-18',
        ]);
        $member = Member::factory()->create();
        $reservation = Reservation::factory()->create([
            'member_id' => $member->id,
            'studio_id' => $studio->id,
            'start_at' => '2025-05-18 8:30:00',
            'finish_at' => '2025-05-18 10:29:59',
        ]);
        $this->loginAsOwner();

        $response = $this->getJson("/owner/studios/{$studio->id}/reservations/{$reservation->id}");

        $response->assertOk();
        $response->assertExactJson([
            'reservation' => [
                'id' => $reservation->id,
                'studio_id' => $studio->id,
                'studio_name' => $studio->name,
                'start_at' => $reservation->start_at->format('Y-m-d H:i:s'),
                'finish_at' => $reservation->finish_at->format('Y-m-d H:i:s'),
                'max_usage_hour' => 2,
                'member_id' => $member->id,
                'member_name' => $member->name,
                'memo' => $reservation->memo,
            ],
        ]);
    }

    /**
     * ギリギリまで予め予約していた
     * 2ヶ月先でブロック
     */
    #[Test]
    public function test_show_blocked_by_over_max_reservation_period(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(10, 0, 0),
        ]);
        $studio = Studio::factory()->create([
            'start_at' => StartAt::Thirty,
        ]);
        $member = Member::factory()->create();
        $reservation = Reservation::factory()->create([
            'member_id' => $member->id,
            'studio_id' => $studio->id,
            'start_at' => '2025-07-17 8:30:00',
            'finish_at' => '2025-07-17 10:29:59',
        ]);
        $this->loginAsOwner();

        $response = $this->getJson("/owner/studios/{$studio->id}/reservations/{$reservation->id}");

        $response->assertOk();
        $response->assertExactJson([
            'reservation' => [
                'id' => $reservation->id,
                'studio_id' => $studio->id,
                'studio_name' => $studio->name,
                'start_at' => $reservation->start_at->format('Y-m-d H:i:s'),
                'finish_at' => $reservation->finish_at->format('Y-m-d H:i:s'),
                'max_usage_hour' => 2,
                'member_id' => $member->id,
                'member_name' => $member->name,
                'memo' => $reservation->memo,
            ],
        ]);
    }

    /**
     * ギリギリまで予め予約していた
     * 他の予約でブロック
     */
    #[Test]
    public function test_show_blocked_by_other_reservation(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(10, 0, 0),
        ]);
        $studio = Studio::factory()->create([
            'start_at' => StartAt::Thirty,
        ]);
        $member = Member::factory()->create();
        $reservation = Reservation::factory()->create([
            'member_id' => $member->id,
            'studio_id' => $studio->id,
            'start_at' => '2025-05-18 8:30:00',
            'finish_at' => '2025-05-18 10:29:59',
        ]);
        Reservation::factory()->create([
            'studio_id' => $studio->id,
            'start_at' => '2025-05-18 10:30:00',
            'finish_at' => '2025-05-18 12:29:59',
        ]);
        $this->loginAsOwner();

        $response = $this->getJson("/owner/studios/{$studio->id}/reservations/{$reservation->id}");

        $response->assertOk();
        $response->assertExactJson([
            'reservation' => [
                'id' => $reservation->id,
                'studio_id' => $studio->id,
                'studio_name' => $studio->name,
                'start_at' => $reservation->start_at->format('Y-m-d H:i:s'),
                'finish_at' => $reservation->finish_at->format('Y-m-d H:i:s'),
                'max_usage_hour' => 2,
                'member_id' => $member->id,
                'member_name' => $member->name,
                'memo' => $reservation->memo,
            ],
        ]);
    }

    /**
     * ギリギリまで予め予約していた
     * 他の予約が入っていても、別のスタジオなら影響ないことのテスト
     */
    #[Test]
    public function test_show_unaffected_by_other_studios(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(10, 0, 0),
        ]);
        $studio = Studio::factory()->create([
            'start_at' => StartAt::Thirty,
        ]);
        $member = Member::factory()->create();
        $reservation = Reservation::factory()->create([
            'member_id' => $member->id,
            'studio_id' => $studio->id,
            'start_at' => '2025-05-18 8:30:00',
            'finish_at' => '2025-05-18 10:29:59',
        ]);
        // 別のスタジオの予約を作る
        Reservation::factory()->create([
            'start_at' => '2025-05-18 10:30:00',
            'finish_at' => '2025-05-18 12:29:59',
        ]);
        $this->loginAsOwner();

        $response = $this->getJson("/owner/studios/{$studio->id}/reservations/{$reservation->id}");

        $response->assertOk();
        $response->assertExactJson([
            'reservation' => [
                'id' => $reservation->id,
                'studio_id' => $studio->id,
                'studio_name' => $studio->name,
                'start_at' => $reservation->start_at->format('Y-m-d H:i:s'),
                'finish_at' => $reservation->finish_at->format('Y-m-d H:i:s'),
                'max_usage_hour' => 6,
                'member_id' => $member->id,
                'member_name' => $member->name,
                'memo' => $reservation->memo,
            ],
        ]);
    }

    /**
     * 24時間営業の場合は日付を跨いで延長できる
     */
    #[Test]
    public function test_show_can_be_extended_across_dates(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(10, 0, 0),
        ]);
        $studio = Studio::factory()->create([
            'start_at' => StartAt::Thirty,
        ]);
        $member = Member::factory()->create();
        $reservation = Reservation::factory()->create([
            'member_id' => $member->id,
            'studio_id' => $studio->id,
            'start_at' => '2025-05-18 9:30:00',
            'finish_at' => '2025-05-18 10:29:59',
        ]);
        $this->loginAsOwner();

        $response = $this->getJson("/owner/studios/{$studio->id}/reservations/{$reservation->id}");

        $response->assertOk();
        $response->assertExactJson([
            'reservation' => [
                'id' => $reservation->id,
                'studio_id' => $studio->id,
                'studio_name' => $studio->name,
                'start_at' => $reservation->start_at->format('Y-m-d H:i:s'),
                'finish_at' => $reservation->finish_at->format('Y-m-d H:i:s'),
                'max_usage_hour' => 6,
                'member_id' => $member->id,
                'member_name' => $member->name,
                'memo' => $reservation->memo,
            ],
        ]);
    }
}
