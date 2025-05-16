<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Owner;

use App\Models\Member;
use App\Models\Reservation;
use Arr;
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
        $response->assertExactJson(['members' => []]);
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
}
