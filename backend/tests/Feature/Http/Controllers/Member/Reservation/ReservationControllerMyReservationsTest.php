<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Member\Reservation;

use App\Enums\Studio\StartAt;
use App\Models\BusinessTime;
use App\Models\Reservation;
use App\Models\Studio;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReservationControllerMyReservationsTest extends TestCase
{
    #[Test]
    public function test_my_reservations_success(): void
    {
        Carbon::setTestNow('2025-05-17 18:30:00');

        $member = $this->loginAsMember();
        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(22, 0, 0),
        ]);
        $studio1 = Studio::factory()->create([
            'start_at' => StartAt::Thirty,
        ]);
        $studio2 = Studio::factory()->create([
            'start_at' => StartAt::Thirty,
        ]);
        $reservation5 = Reservation::factory()->create([
            'studio_id' => $studio2->id,
            'member_id' => $member->id,
            'start_at' => '2025-05-20 18:30:00',
            'finish_at' => '2025-05-20 21:29:59',
        ]);
        $reservation4 = Reservation::factory()->create([
            'studio_id' => $studio2->id,
            'member_id' => $member->id,
            'start_at' => '2025-05-19 15:30:00',
            'finish_at' => '2025-05-19 17:29:59',
        ]);
        $reservation3 = Reservation::factory()->create([
            'studio_id' => $studio1->id,
            'member_id' => $member->id,
            'start_at' => '2025-05-19 15:30:00',
            'finish_at' => '2025-05-19 17:29:59',
        ]);
        $reservation2 = Reservation::factory()->create([
            'studio_id' => $studio1->id,
            'member_id' => $member->id,
            'start_at' => '2025-05-18 18:30:00',
            'finish_at' => '2025-05-18 21:29:59',
        ]);
        // 終了時間が現在時刻とイコールの場合は表示される（通常30分のような開始時刻値が終了時間に設定されることはないが、テスト用なので許容）
        $reservation1 = Reservation::factory()->create([
            'studio_id' => $studio2->id,
            'member_id' => $member->id,
            'start_at' => '2025-05-17 17:30:00',
            'finish_at' => '2025-05-17 18:30:00',
        ]);

        // 終了時間が過去の予約は取得されない
        Reservation::factory()->create([
            'studio_id' => $studio1->id,
            'member_id' => $member->id,
            'start_at' => '2025-05-17 15:30:00',
            'finish_at' => '2025-05-17 18:29:59',
            'memo' => '取得されない予約',
        ]);
        // 他人の予約は表示されない
        Reservation::factory()->create([
            'studio_id' => $studio1->id,
            'start_at' => '2025-05-20 18:30:00',
            'finish_at' => '2025-05-20 21:29:59',
        ]);

        $response = $this->getJson('/me/reservations');

        $response->assertOk();
        $response->assertExactJson([
            [
                'id' => $reservation1->id,
                'studio_id' => $studio2->id,
                'studio_name' => $studio2->name,
                'start_at' => $reservation1->start_at->format('Y-m-d H:i:s'),
                'finish_at' => $reservation1->finish_at->format('Y-m-d H:i:s'),
            ],
            [
                'id' => $reservation2->id,
                'studio_id' => $studio1->id,
                'studio_name' => $studio1->name,
                'start_at' => $reservation2->start_at->format('Y-m-d H:i:s'),
                'finish_at' => $reservation2->finish_at->format('Y-m-d H:i:s'),
            ],
            [
                'id' => $reservation3->id,
                'studio_id' => $studio1->id,
                'studio_name' => $studio1->name,
                'start_at' => $reservation3->start_at->format('Y-m-d H:i:s'),
                'finish_at' => $reservation3->finish_at->format('Y-m-d H:i:s'),
            ],
            [
                'id' => $reservation4->id,
                'studio_id' => $studio2->id,
                'studio_name' => $studio2->name,
                'start_at' => $reservation4->start_at->format('Y-m-d H:i:s'),
                'finish_at' => $reservation4->finish_at->format('Y-m-d H:i:s'),
            ],
            [
                'id' => $reservation5->id,
                'studio_id' => $studio2->id,
                'studio_name' => $studio2->name,
                'start_at' => $reservation5->start_at->format('Y-m-d H:i:s'),
                'finish_at' => $reservation5->finish_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
