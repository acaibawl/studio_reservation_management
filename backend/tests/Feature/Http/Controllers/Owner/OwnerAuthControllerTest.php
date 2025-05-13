<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Owner;

use App\Models\Owner;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * オーナー認証周りのテスト
 */
class OwnerAuthControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * ログイン成功のテスト
     */
    #[Test]
    public function test_login_success(): void
    {
        $owner = Owner::factory()->create([
            'password' => \Hash::make('password'),
        ]);

        $requestBody = [
            'email' => $owner->email,
            'password' => 'password',
        ];
        $response = $this->postJson('/owner-auth/login', $requestBody);

        $response->assertStatus(200);
        $response->assertJson(fn (AssertableJson $json) => $json->hasAll(
            [
                'owner_access_token',
                'token_type',
                'expires_in',
            ])
            ->where('token_type', 'bearer')
        );
    }

    /**
     * ログインリクエストのパラメータバリデーションエラーのテスト
     */
    #[Test]
    #[DataProvider('dataProviderLoginInvalidParameter')]
    public function test_login_failed_by_validation_error(array $requestBody, string $errorMessage): void
    {
        $response = $this->postJson('/owner-auth/login', $requestBody);
        $response->assertUnprocessable();
        $response->assertJson(fn (AssertableJson $json) => $json->where('message', $errorMessage)
            ->etc()
        );
    }

    /**
     * @return array[]
     */
    public static function dataProviderLoginInvalidParameter(): array
    {
        return [
            'email空文字' => [
                'requestBody' => [
                    'email' => '',
                    'password' => 'password',
                ],
                'errorMessage' => 'Emailは必須項目です。',
            ],
            'emailフォーマット誤り' => [
                'requestBody' => [
                    'email' => 'acai',
                    'password' => 'password',
                ],
                'errorMessage' => 'Emailは、有効なメールアドレス形式で指定してください。',
            ],
            'email文字数超過' => [
                'requestBody' => [
                    'email' => str_repeat('a', 244) . '@example.com',
                    'password' => 'password',
                ],
                'errorMessage' => 'Emailの文字数は、255文字以下である必要があります。',
            ],
            'password空文字' => [
                'requestBody' => [
                    'email' => 'acai@example.com',
                    'password' => '',
                ],
                'errorMessage' => 'Passwordは必須項目です。',
            ],
            'password文字数不足' => [
                'requestBody' => [
                    'email' => 'acai@example.com',
                    'password' => str_repeat('a', 7),
                ],
                'errorMessage' => 'Passwordは、8文字から32文字にしてください。',
            ],
            'password文字数超過' => [
                'requestBody' => [
                    'email' => 'acai@example.com',
                    'password' => str_repeat('a', 33),
                ],
                'errorMessage' => 'Passwordは、8文字から32文字にしてください。',
            ],
        ];
    }

    /**
     * 認証情報誤りによるログイン失敗のテスト
     */
    #[Test]
    #[DataProvider('dataProviderLoginMismatch')]
    public function test_login_failed_by_mismatch_of_authentication_information(array $requestBody): void
    {
        Owner::factory()->create([
            'email' => 'acai@example.com',
            'password' => \Hash::make('password'),
        ]);
        $response = $this->postJson('/owner-auth/login', $requestBody);
        $response->assertUnauthorized();
    }

    /**
     * @return array[]
     */
    public static function dataProviderLoginMismatch(): array
    {
        return [
            'email不一致' => [
                'requestBody' => [
                    'email' => 'mismatch@example.com',
                    'password' => 'password',
                ],
            ],
            'password不一致' => [
                'requestBody' => [
                    'email' => 'acai@example.com',
                    'password' => 'mismatch',
                ],
            ],
        ];
    }
}
