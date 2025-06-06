<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Member\Reservation;

use App\Enums\Studio\StartAt;
use App\Mail\Member\Reservation\ReservationApplicationHaveBeenReceivedMail;
use App\Models\BusinessTime;
use App\Models\Studio;
use Illuminate\Support\Carbon;
use Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * オーナー側の予約申請のテストで予約失敗のケースはカバーしているので、このクラスではテストしない
 * @see \Tests\Feature\Http\Controllers\Owner\Reservation\ReservationControllerStoreTest
 */
class ReservationControllerStoreTest extends TestCase
{
    #[Test]
    public function test_store_success(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');
        Mail::fake();

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(22, 0, 0),
        ]);
        $studio = Studio::factory()->create([
            'start_at' => StartAt::Thirty,
        ]);
        $requestBody = [
            'studio_id' => $studio->id,
            'start_at' => '2025-05-18 16:30:00',
            'usage_hour' => 6,
        ];
        $member = $this->loginAsMember();

        $response = $this->postJson("/studios/{$studio->id}/reservations/", $requestBody);

        $response->assertCreated();
        $this->assertDatabaseHas('reservations', [
            'member_id' => $member->id,
            'start_at' => '2025-05-18 16:30:00',
            'finish_at' => '2025-05-18 22:29:59',
            'memo' => null,
        ]);
        // 指定のメールが指定のアドレスに送信されていること
        Mail::assertSent(ReservationApplicationHaveBeenReceivedMail::class, function ($mail) {
            return $mail->hasTo(config('app.owner_email'));
        });
        // 指定のメールが1回送信されていること
        Mail::assertSent(ReservationApplicationHaveBeenReceivedMail::class, 1);
    }
}
