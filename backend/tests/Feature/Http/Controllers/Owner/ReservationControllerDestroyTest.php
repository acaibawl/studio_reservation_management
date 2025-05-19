<?php

namespace Tests\Feature\Http\Controllers\Owner;

use App\Enums\Studio\StartAt;
use App\Models\BusinessTime;
use App\Models\Member;
use App\Models\Reservation;
use App\Models\Studio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReservationControllerDestroyTest extends TestCase
{
    /**
     * 予約削除の成功テスト
     */
    #[Test]
    public function test_destroy_success(): void
    {
        $reservation = Reservation::factory()->create();
        $this->loginAsOwner();

        $response = $this->deleteJson("/owner/reservations/{$reservation->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('reservations', [
            'id' => $reservation->id,
        ]);
    }
}
