<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Member;

use App\Mail\Member\Auth\MemberAlreadyRegisteredMail;
use App\Mail\Member\Auth\SignUpEmailVerifiedCodeMail;
use App\Models\Member;
use Illuminate\Support\Facades\Redis;
use Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MemberAuthControllerSendSignUpEmailVerifiedCodeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // テスト開始前にRedisをクリアする
        Redis::flushdb();
    }

    #[Test]
    public function test_send_success(): void
    {
        Mail::fake();
        $email = 'test@gmail.com';

        $response = $this->postJson('/member-auth/send-sign-up-email-verified-code', [
            'email' => $email,
        ]);

        $response->assertOk();
        // 指定のメールが指定のアドレスに送信されていること
        Mail::assertSent(SignUpEmailVerifiedCodeMail::class, function ($mail) use ($email) {
            return $mail->hasTo($email);
        });
        // 指定のメールが1回送信されていること
        Mail::assertSent(SignUpEmailVerifiedCodeMail::class, 1);

        // Redisに認証コードが保存されていること
        $code = Redis::get("sign_up_email_verified_code_{$email}");
        $this->assertNotNull($code);
    }

    #[Test]
    #[DataProvider('dataProviderSendInvalidParameter')]
    public function test_send_failed_by_validation_error(array $requestBody, array $expectedError): void
    {
        $response = $this->postJson('/member-auth/send-sign-up-email-verified-code', $requestBody);

        $response->assertUnprocessable();
        $response->assertInvalid($expectedError);
    }

    public static function dataProviderSendInvalidParameter(): array
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

    /**
     * メールアドレスが既に存在する会員のもので失敗
     */
    #[Test]
    public function test_send_failed_by_member_already_registered(): void
    {
        Mail::fake();
        $logSpy = \Log::spy();
        $email = 'test@gmail.com';
        Member::factory()->create([
            'email' => $email,
        ]);

        $response = $this->postJson('/member-auth/send-sign-up-email-verified-code', [
            'email' => $email,
        ]);

        $response->assertOk();
        // 既に登録されている旨のメールが指定のアドレスに送信されていること
        Mail::assertSent(MemberAlreadyRegisteredMail::class, function ($mail) use ($email) {
            return $mail->hasTo($email);
        });
        // 既に登録されている旨のメールが1回送信されていること
        Mail::assertSent(MemberAlreadyRegisteredMail::class, 1);
        // 認証コードメールが送信されていないこと
        Mail::assertNotSent(SignUpEmailVerifiedCodeMail::class);
        // Redisに認証コードが保存されていないこと
        $code = Redis::get("sign_up_email_verified_code_{$email}");
        $this->assertNull($code);
        // ログにinfoレベルで出力されていること
        $logSpy->shouldHaveReceived('info', ["email:{$email} is already registered."]);
    }
}
