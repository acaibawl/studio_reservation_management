<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Member\Reservation;

use App\Enums\Studio\StartAt;
use App\Models\BusinessTime;
use App\Models\Studio;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * 処理内容はオーナー向けの最大予約時間取得処理と同じで、そちらで詳細なテストを実施しているので、このクラスではテストしない
 * @see \Tests\Feature\Http\Controllers\Owner\Reservation\ReservationControllerGetReservationQuotaTest
 */
class ReservationControllerGetReservationQuotaTest extends TestCase
{
    #[Test]
    public function test_success(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(22, 0, 0),
        ]);
        $studio = Studio::factory()->create([
            'start_at' => StartAt::Thirty,
        ]);

        $this->loginAsMember();

        $response = $this->getJson("/studios/{$studio->id}/reservation-quota/2025-05-18/18");

        $response->assertOk();
        $response->assertExactJson([
            'studio_id' => $studio->id,
            'studio_name' => $studio->name,
            'studio_start_at' => $studio->start_at->value,
            'date' => '2025-05-18',
            'hour' => 18,
            'max_available_hour' => 4,
        ]);
    }
}
