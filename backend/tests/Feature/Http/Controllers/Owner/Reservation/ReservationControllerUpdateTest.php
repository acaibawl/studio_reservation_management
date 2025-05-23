<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Owner\Reservation;

use App\Enums\Studio\StartAt;
use App\Exceptions\Reservation\AvailableHourExceededException;
use App\Models\BusinessTime;
use App\Models\Reservation;
use App\Models\Studio;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Exceptions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReservationControllerUpdateTest extends TestCase
{
    /**
     * 更新成功3->4時間
     */
    #[Test]
    public function test_update_success_3_to_4_hour(): void
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
            'start_at' => '2025-05-18 18:30:00',
            'finish_at' => '2025-05-18 21:29:59',
        ]);
        $this->loginAsOwner();

        $response = $this->patchJson("/owner/studios/{$studio->id}/reservations/{$reservation->id}", [
            'usage_hour' => 4,
            'memo' => 'test',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'start_at' => '2025-05-18 18:30:00',
            'finish_at' => '2025-05-18 22:29:59',
        ]);
    }

    /**
     * 更新成功3->1時間
     */
    #[Test]
    public function test_update_success_3_to_1_hour(): void
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
            'start_at' => '2025-05-18 18:30:00',
            'finish_at' => '2025-05-18 21:29:59',
        ]);
        $this->loginAsOwner();

        $response = $this->patchJson("/owner/studios/{$studio->id}/reservations/{$reservation->id}", [
            'usage_hour' => 1,
            'memo' => 'test',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'start_at' => '2025-05-18 18:30:00',
            'finish_at' => '2025-05-18 19:29:59',
        ]);
    }

    /**
     * 更新成功3->4時間が、既に予約入っていて失敗
     */
    #[Test]
    public function test_update_failed_3_to_4_hour_by_other_reservation(): void
    {
        Carbon::setTestNow('2025-05-17 18:00:00');
        Exceptions::fake();

        BusinessTime::factory()->create([
            'open_time' => Carbon::createFromTime(10, 0, 0),
            'close_time' => Carbon::createFromTime(22, 0, 0),
        ]);
        $studio = Studio::factory()->create([
            'start_at' => StartAt::Thirty,
        ]);
        $reservation = Reservation::factory()->create([
            'studio_id' => $studio->id,
            'start_at' => '2025-05-18 18:30:00',
            'finish_at' => '2025-05-18 21:29:59',
        ]);
        // 後続の予約を作っておく
        Reservation::factory()->create([
            'studio_id' => $studio->id,
            'start_at' => '2025-05-18 21:30:00',
            'finish_at' => '2025-05-18 22:29:59',
        ]);
        $this->loginAsOwner();

        $response = $this->patchJson("/owner/studios/{$studio->id}/reservations/{$reservation->id}", [
            'usage_hour' => 4,
            'memo' => 'test',
        ]);

        Exceptions::assertReported(AvailableHourExceededException::class);
        $response->assertBadRequest();
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'start_at' => '2025-05-18 18:30:00',
            'finish_at' => '2025-05-18 21:29:59',
        ]);
    }

    /**
     * バリデーションエラーによる更新失敗のテスト
     */
    #[Test]
    #[DataProvider('dataProviderUpdateInvalidParameter')]
    public function test_update_failed_by_validation_error(array $requestBody, array $expectedError): void
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
            'start_at' => '2025-05-18 18:30:00',
            'finish_at' => '2025-05-18 21:29:59',
        ]);
        $this->loginAsOwner();

        $response = $this->patchJson("/owner/studios/{$studio->id}/reservations/{$reservation->id}", $requestBody);

        $response->assertUnprocessable();
        $response->assertInvalid($expectedError);
    }

    public static function dataProviderUpdateInvalidParameter(): array
    {
        return [
            '利用時間が下限未満' => [
                'requestBody' => [
                    'usage_hour' => 0,
                    'memo' => 'メモ本文',
                ],
                'expectedError' => [
                    'usage_hour' => '利用時間には、1から、6までの数字を指定してください。',
                ],
            ],
            '利用時間が上限超過' => [
                'requestBody' => [
                    'usage_hour' => 7,
                    'memo' => 'メモ本文',
                ],
                'expectedError' => [
                    'usage_hour' => '利用時間には、1から、6までの数字を指定してください。',
                ],
            ],
            '利用時間は数値' => [
                'requestBody' => [
                    'usage_hour' => 'あ',
                    'memo' => 'メモ本文',
                ],
                'expectedError' => [
                    'usage_hour' => '利用時間には、整数を指定してください。',
                ],
            ],
            'メモの文字数が上限超過' => [
                'requestBody' => [
                    'usage_hour' => 6,
                    'memo' => str_repeat('a', 513),
                ],
                'expectedError' => [
                    'memo' => 'メモの文字数は、512文字以下である必要があります。',
                ],
            ],
        ];
    }
}
