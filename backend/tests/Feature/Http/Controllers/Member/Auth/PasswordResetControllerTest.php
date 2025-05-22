<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Member\Auth;

use App\Mail\Member\Auth\PasswordResetMail;
use App\Models\Member;
use Illuminate\Support\Facades\Redis;
use Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PasswordResetControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // テスト開始前にRedisをクリアする
        Redis::flushdb();
    }

    #[Test]
    public function test_send_code_success(): void
    {
        $email = 'test@gmail.com';
        Member::factory()->create([
            'email' => $email,
        ]);
        $requestBody = [
            'email' => $email,
        ];
        Mail::fake();

        $response = $this->postJson('/member-auth/password-reset/send-email', $requestBody);

        $response->assertOk();
        // 指定のメールが指定のアドレスに送信されていること
        Mail::assertSent(PasswordResetMail::class, function ($mail) use ($email) {
            return $mail->hasTo($email);
        });
        // 指定のメールが1回送信されていること
        Mail::assertSent(PasswordResetMail::class, 1);

        // Redisに認証コードが保存されていること
        $code = Redis::get("password_reset_email_verified_token_{$email}");
        $this->assertNotNull($code);
    }

    /**
     * 指定のメールアドレスの会員が存在しない場合は、何もしないことのテスト
     */
    #[Test]
    public function test_send_code_nothing_to_do(): void
    {
        $email = 'test@gmail.com';
        $requestBody = [
            'email' => $email,
        ];
        Mail::fake();

        $response = $this->postJson('/member-auth/password-reset/send-email', $requestBody);

        $response->assertOk();
        // 指定のメールが送信されていないこと
        Mail::assertNotSent(PasswordResetMail::class);

        // Redisに認証コードが保存されていないこと
        $code = Redis::get("password_reset_email_verified_token_{$email}");
        $this->assertNull($code);
    }

    #[Test]
    #[DataProvider('dataProviderSendCodeInvalidParameter')]
    public function test_send_code_failed_by_validation_error(array $requestBody, array $expectedError): void
    {
        $response = $this->postJson('/member-auth/sign-up-email-verified-code/send', $requestBody);

        $response->assertUnprocessable();
        $response->assertInvalid($expectedError);
    }

    public static function dataProviderSendCodeInvalidParameter(): array
    {
        return [
            'メールアドレスは必須' => [
                'requestBody' => [
                    'email' => '',
                ],
                'expectedError' => [
                    'email' => 'メールアドレスは必須項目です。',
                ],
            ],
            'メールアドレスのフォーマット誤り' => [
                'requestBody' => [
                    'email' => 'aaaaa',
                ],
                'expectedError' => [
                    'email' => 'メールアドレスは、有効なメールアドレス形式で指定してください。',
                ],
            ],
            'メールアドレスが無効なドメイン' => [
                'requestBody' => [
                    'email' => 'aaaaa@feaifjaiefijffefee.com',
                ],
                'expectedError' => [
                    'email' => 'メールアドレスは、有効なメールアドレス形式で指定してください。',
                ],
            ],
        ];
    }
}
