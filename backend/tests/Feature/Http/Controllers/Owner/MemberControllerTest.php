<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Owner;

use App\Enums\Studio\StartAt;
use App\Models\Member;
use App\Models\Reservation;
use App\Models\Studio;
use Arr;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MemberControllerTest extends TestCase
{
    /**
     * パラメータなしのindexの成功テスト
     */
    #[Test]
    public function test_index_success_without_parameter(): void
    {
        $members = Member::factory()->count(100)->create();
        // 先頭3件のメンバーは未来の予約を持っている
        Reservation::factory()->state([
            'start_at' => now()->addHours(1),
            'finish_at' => now()->addHours(2),
        ])->createMany([
            ['member_id' => $members[0]->id],
            ['member_id' => $members[1]->id],
            ['member_id' => $members[2]->id],
        ]);
        // 4件目のメンバーは過去の予約を持っている
        Reservation::factory()->create([
            'member_id' => $members[3]->id,
            'start_at' => now()->subHours(5),
            'finish_at' => now()->subHours(4),
        ]);
        $this->loginAsOwner();

        $response = $this->getJson('/owner/members');

        $response->assertOk();
        $expect = [
            'members' => $members->sortBy('id')->take(20)->map(fn (Member $member) => [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'has_reservation' => false,
            ])->toArray(),
            'page_size' => 5,
            'current_page' => 1,
        ];
        $expect['members'][0]['has_reservation'] = true;
        $expect['members'][1]['has_reservation'] = true;
        $expect['members'][2]['has_reservation'] = true;
        $response->assertExactJson($expect);
    }

    /**
     * page指定のindexの成功テスト
     */
    #[Test]
    public function test_index_success_with_page(): void
    {
        $members = Member::factory()->count(100)->create();
        $this->loginAsOwner();

        $response = $this->getJson('/owner/members?page=3');

        $response->assertOk();
        $expect = [
            'members' => $members->sortBy('id')->skip(40)->take(20)->map(fn (Member $member) => [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'has_reservation' => false,
            ])->values()->toArray(),
            'page_size' => 5,
            'current_page' => 3,
        ];
        $response->assertExactJson($expect);
    }

    /**
     * 名前絞り込みの成功テスト
     */
    #[Test]
    public function test_index_success_name_filter(): void
    {
        $takeda = Member::factory()->create(['name' => '武田信玄']);
        $oda = Member::factory()->create(['name' => '織田信長']);
        Member::factory()->create(['name' => '徳川家康']);
        Member::factory()->create(['name' => '北条政子']);
        $this->loginAsOwner();

        $response = $this->getJson('/owner/members?name=田');

        $response->assertOk();
        $response->assertExactJson([
            'members' => [
                [
                    'id' => $takeda->id,
                    'name' => $takeda->name,
                    'email' => $takeda->email,
                    'has_reservation' => false,
                ],
                [
                    'id' => $oda->id,
                    'name' => $oda->name,
                    'email' => $oda->email,
                    'has_reservation' => false,
                ],
            ],
            'page_size' => 1,
            'current_page' => 1,
        ]);
    }

    /**
     * 名前絞り込みで0件の成功テスト
     */
    #[Test]
    public function test_index_success_name_filter_0_result(): void
    {
        $takeda = Member::factory()->create(['name' => '武田信玄']);
        $oda = Member::factory()->create(['name' => '織田信長']);
        Member::factory()->create(['name' => '徳川家康']);
        Member::factory()->create(['name' => '北条政子']);
        $this->loginAsOwner();

        $response = $this->getJson('/owner/members?name=あああ');

        $response->assertOk();
        $response->assertExactJson([
            'members' => [],
            'page_size' => 0,
            'current_page' => 1,
        ]);
    }

    /**
     * バリデーションエラーでindexの失敗テスト
     */
    #[Test]
    #[DataProvider('dataProviderIndexInvalidParameter')]
    public function test_index_failed_by_validation_error(array $requestBody, array $expectedError): void
    {
        $this->loginAsOwner();

        $response = $this->getJson('/owner/members?' . Arr::query($requestBody));

        $response->assertUnprocessable();
        $response->assertInvalid($expectedError);
    }

    public static function dataProviderIndexInvalidParameter(): array
    {
        return [
            'name文字数超過' => [
                'requestBody' => [
                    'name' => str_repeat('a', 51),
                    'page' => 1,
                ],
                'expectedError' => [
                    'name' => '名前の文字数は、50文字以下である必要があります。',
                ],
            ],
            'pageは数字のみ許可' => [
                'requestBody' => [
                    'name' => str_repeat('あ', 50),
                    'page' => 'あ',
                ],
                'expectedError' => [
                    'page' => 'ページには、整数を指定してください。',
                ],
            ],
            'pageは1以上を許可' => [
                'requestBody' => [
                    'name' => str_repeat('あ', 50),
                    'page' => 0,
                ],
                'expectedError' => [
                    'page' => 'ページには、1以上の数値を指定してください。',
                ],
            ],
        ];
    }

    /**
     * 会員の詳細のテスト
     */
    public function test_show_success(): void
    {
        Carbon::setTestNow('2025-05-16 09:00:00');
        $member = Member::factory()->create();
        $bStudio = Studio::factory()->create([
            'name' => 'Bスタ',
            'start_at' => StartAt::Zero,
        ]);
        // 過去の予約（取得対象外）
        Reservation::factory()->create([
            'member_id' => $member->id,
            'studio_id' => $bStudio->id,
            'start_at' => Carbon::create(2025, 5, 10, 10, 0, 0),
            'finish_at' => Carbon::create(2025, 5, 10, 11, 59, 59),
        ]);
        // 未来の予約（取得対象）
        $reservationBst2 = Reservation::factory()->create([
            'member_id' => $member->id,
            'studio_id' => $bStudio->id,
            'start_at' => Carbon::create(2025, 5, 19, 15, 0, 0),
            'finish_at' => Carbon::create(2025, 5, 19, 17, 59, 59),
        ]);

        // Aスタは未来の予約だけ登録
        $aStudio = Studio::factory()->create([
            'name' => 'Aスタ',
            'start_at' => StartAt::Thirty,

        ]);
        $reservationAst1 = Reservation::factory()->create([
            'member_id' => $member->id,
            'studio_id' => $aStudio->id,
            'start_at' => Carbon::create(2025, 5, 17, 12, 30, 0),
            'finish_at' => Carbon::create(2025, 5, 17, 15, 29, 59),
        ]);
        $reservationAst2 = Reservation::factory()->create([
            'member_id' => $member->id,
            'studio_id' => $aStudio->id,
            'start_at' => Carbon::create(2025, 5, 16, 10, 30, 0),
            'finish_at' => Carbon::create(2025, 5, 16, 11, 29, 59),
        ]);
        $this->loginAsOwner();

        $response = $this->getJson("/owner/members/{$member->id}");

        $response->assertOk();
        $response->assertExactJson([
            'member' => [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'address' => $member->address,
                'tel' => $member->tel,
                'reservations' => [
                    [
                        'id' => $reservationAst2->id,
                        'member_id' => $member->id,
                        'studio_id' => $aStudio->id,
                        'studio_name' => $aStudio->name,
                        'start_at' => $reservationAst2->start_at->format('Y-m-d H:i:s'),
                        'finish_at' => $reservationAst2->finish_at->format('Y-m-d H:i:s'),
                        'memo' => $reservationAst2->memo,
                    ],
                    [
                        'id' => $reservationAst1->id,
                        'member_id' => $member->id,
                        'studio_id' => $aStudio->id,
                        'studio_name' => $aStudio->name,
                        'start_at' => $reservationAst1->start_at->format('Y-m-d H:i:s'),
                        'finish_at' => $reservationAst1->finish_at->format('Y-m-d H:i:s'),
                        'memo' => $reservationAst1->memo,
                    ],
                    [
                        'id' => $reservationBst2->id,
                        'member_id' => $member->id,
                        'studio_id' => $bStudio->id,
                        'studio_name' => $bStudio->name,
                        'start_at' => $reservationBst2->start_at->format('Y-m-d H:i:s'),
                        'finish_at' => $reservationBst2->finish_at->format('Y-m-d H:i:s'),
                        'memo' => $reservationBst2->memo,
                    ],
                ],
            ],
        ]);
    }
}
