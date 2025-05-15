<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\TemporaryClosingDay;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TemporaryClosingDayControllerTest extends TestCase
{
    /**
     * indexの成功テスト
     */
    #[Test]
    public function test_index_success(): void
    {
        /** @var Collection<int, TemporaryClosingDay> $days */
        $days = TemporaryClosingDay::factory()->createMany([
            ['date' => '2025-05-14'],
            ['date' => '2025-06-02'],
            ['date' => '2025-05-29'],
        ]);
        $days = $days->sortBy('date')->values();
        $this->loginOwner();

        $response = $this->getJson('/owner/temporary-closing-days');
        $response->assertOk();
        $response->assertExactJson([
            'temporary_closing_days' => [
                [
                    'id' => $days[0]->id,
                    'date' => $days[0]->date->format('Y-m-d'),
                ],
                [
                    'id' => $days[1]->id,
                    'date' => $days[1]->date->format('Y-m-d'),
                ],
                [
                    'id' => $days[2]->id,
                    'date' => $days[2]->date->format('Y-m-d'),
                ],
            ],
        ]);
    }

    /**
     * storeの成功テスト
     */
    #[Test]
    public function test_store_success(): void
    {
        $this->loginOwner();
        $response = $this->postJson('/owner/temporary-closing-days', [
            'date' => '2025-05-14',
        ]);
        $response->assertCreated();
        $this->assertDatabaseHas('temporary_closing_days', [
            'date' => '2025-05-14',
        ]);
    }

    #[Test]
    #[DataProvider('dataProviderStoreInvalidParameter')]
    public function test_store_failed_by_validation_error(array $requestBody, array $expectedError): void
    {
        TemporaryClosingDay::factory()->create([
            'date' => '2025-05-14',
        ]);
        $this->loginOwner();
        $response = $this->postJson('/owner/temporary-closing-days', $requestBody);
        $response->assertUnprocessable();
        $response->assertInvalid($expectedError);
    }

    public static function dataProviderStoreInvalidParameter(): array
    {
        return [
            'dateは必須' => [
                'requestBody' => [],
                'expectedError' => [
                    'date' => '日付は必須項目です。',
                ],
            ],
            'dateは有効な日付' => [
                'requestBody' => [
                    'date' => '2025-02-30',
                ],
                'expectedError' => [
                    'date' => "日付の形式が'Y-m-d'と一致しません。",
                ],
            ],
            'dateは日付' => [
                'requestBody' => [
                    'date' => 'あいうえお',
                ],
                'expectedError' => [
                    'date' => "日付の形式が'Y-m-d'と一致しません。",
                ],
            ],
            'dateはユニーク' => [
                'requestBody' => [
                    'date' => '2025-05-14',
                ],
                'expectedError' => [
                    'date' => '指定の日付は既に使用されています。',
                ],
            ],
        ];
    }

    public function test_destroy_success(): void
    {
        $day = TemporaryClosingDay::factory()->create();
        $this->loginOwner();

        $response = $this->deleteJson("/owner/temporary-closing-days/{$day->date->format('Y-m-d')}");

        $response->assertOk();
        $this->assertDatabaseMissing('temporary_closing_days', [
            'date' => $day->date->format('Y-m-d'),
        ]);
    }
}
