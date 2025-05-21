<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Member\Auth;

use Illuminate\Support\Facades\Redis;
use Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SignUpEmailVerifiedCodeControllerVerifyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // テスト開始前にRedisをクリアする
        Redis::flushdb();
    }

    #[Test]
    public function test_verify_success(): void
    {
        // 認証コード送信処理を先に実施
        Mail::fake();
        $email = 'test@gmail.com';

        $response = $this->postJson('/member-auth/sign-up-email-verified-code/send', [
            'email' => $email,
        ]);

        $response->assertOk();
        // Redisから認証コード取得
        $code = Redis::get("sign_up_email_verified_code_{$email}");
        $this->assertNotNull($code);

        // 認証コードの認証テスト
        $response = $this->postJson('/member-auth/sign-up-email-verified-code/verify', [
            'email' => $email,
            'code' => $code,
        ]);

        $response->assertOk();
    }

    #[Test]
    #[DataProvider('dataProviderVerifyInvalidParameter')]
    public function test_verify_failed_by_validation_error(array $requestBody, array $expectedError): void
    {
        $response = $this->postJson('/member-auth/sign-up-email-verified-code/verify', $requestBody);

        $response->assertUnprocessable();
        $response->assertInvalid($expectedError);
    }

    public static function dataProviderVerifyInvalidParameter(): array
    {
        return [
            'メールアドレスは必須' => [
                'requestBody' => [
                    'email' => '',
                    'code' => '012345',
                ],
                'expectedError' => [
                    'email' => 'メールアドレスは必須項目です。',
                ],
            ],
            'メールアドレスのフォーマット誤り' => [
                'requestBody' => [
                    'email' => 'aaaaa',
                    'code' => '012345',
                ],
                'expectedError' => [
                    'email' => 'メールアドレスは、有効なメールアドレス形式で指定してください。',
                ],
            ],
            'メールアドレスが無効なドメイン' => [
                'requestBody' => [
                    'email' => 'aaaaa@feaifjaiefijffefee.com',
                    'code' => '012345',
                ],
                'expectedError' => [
                    'email' => 'メールアドレスは、有効なメールアドレス形式で指定してください。',
                ],
            ],
            '認証コードは必須' => [
                'requestBody' => [
                    'email' => 'test@gmail.com',
                    'code' => '',
                ],
                'expectedError' => [
                    'code' => '認証コードは必須項目です。',
                ],
            ],
            '認証コードは文字列' => [
                'requestBody' => [
                    'email' => 'test@gmail.com',
                    'code' => 123456,
                ],
                'expectedError' => [
                    'code' => '認証コードには、文字列を指定してください。',
                ],
            ],
            '認証コードの文字数不足' => [
                'requestBody' => [
                    'email' => 'test@gmail.com',
                    'code' => '12345',
                ],
                'expectedError' => [
                    'code' => '認証コードの文字数は、6文字にしてください。',
                ],
            ],
            '認証コードの文字数超過' => [
                'requestBody' => [
                    'email' => 'test@gmail.com',
                    'code' => '1234567',
                ],
                'expectedError' => [
                    'code' => '認証コードの文字数は、6文字にしてください。',
                ],
            ],
        ];
    }

    #[Test]
    public function test_verify_failed_by_email_unmatch(): void
    {
        // 認証コード送信処理を先に実施
        Mail::fake();
        $email = 'test@gmail.com';

        $response = $this->postJson('/member-auth/sign-up-email-verified-code/send', [
            'email' => $email,
        ]);

        $response->assertOk();
        // Redisから認証コード取得
        $code = Redis::get("sign_up_email_verified_code_{$email}");
        $this->assertNotNull($code);

        // 認証コード送信時と異なるメールアドレスで認証コードの認証テスト
        $response = $this->postJson('/member-auth/sign-up-email-verified-code/verify', [
            'email' => 'xxxxx@gmail.com',
            'code' => $code,
        ]);

        $response->assertBadRequest();
    }

    #[Test]
    public function test_verify_failed_by_code_unmatch(): void
    {
        // 認証コード送信処理を先に実施
        Mail::fake();
        $email = 'test@gmail.com';

        $response = $this->postJson('/member-auth/sign-up-email-verified-code/send', [
            'email' => $email,
        ]);

        $response->assertOk();
        // Redisから認証コード取得
        $code = Redis::get("sign_up_email_verified_code_{$email}");
        $this->assertNotNull($code);
        // 認証コードは数字で6文字なので、英字文字列は仕様上必ず不一致となる
        $unmatchCode = 'abcdef';

        // 誤った認証コードで検証をリクエスト
        $response = $this->postJson('/member-auth/sign-up-email-verified-code/verify', [
            'email' => $email,
            'code' => $unmatchCode,
        ]);

        $response->assertBadRequest();
    }
}
