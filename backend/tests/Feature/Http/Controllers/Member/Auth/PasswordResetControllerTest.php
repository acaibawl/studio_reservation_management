<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Member\Auth;

use App\Exceptions\Member\Auth\PasswordResetTokenVerifyFailedException;
use App\Mail\Member\Auth\PasswordResetMail;
use App\Models\Member;
use Illuminate\Support\Facades\Exceptions;
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
        $token = Redis::get("password_reset_email_verified_token_{$email}");
        $this->assertNotNull($token);
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
        $token = Redis::get("password_reset_email_verified_token_{$email}");
        $this->assertNull($token);
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

    #[Test]
    #[DataProvider('dataProviderResetValidParameter')]
    public function test_reset_success(
        string $email,
        string $password,
        string $passwordConfirmation,
    ): void {
        // 先にリセットコードの発行をする
        Member::factory()->create([
            'email' => $email,
        ]);
        $requestBody = [
            'email' => $email,
        ];
        Mail::fake();

        $response = $this->postJson('/member-auth/password-reset/send-email', $requestBody);

        $response->assertOk();

        // 発行したtokenを取得
        $emailVerifiedToken = Redis::get("password_reset_email_verified_token_{$email}");
        $this->assertNotNull($emailVerifiedToken);

        $requestBody = [
            'email' => $email,
            'email_verified_token' => $emailVerifiedToken,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ];

        // リセットAPIをコール
        $response = $this->postJson('/member-auth/password-reset/reset', $requestBody);

        $response->assertOk();

        // 再設定したパスワードでログインできることをテスト
        $requestBody = [
            'email' => $email,
            'password' => $password,
        ];

        $response = $this->postJson('/member-auth/login', $requestBody);
        $response->assertOk();
    }

    public static function dataProviderResetValidParameter(): array
    {
        return [
            '最小文字数' => [
                'email' => 'test@gmail.com',
                'password' => str_repeat('a', 8),
                'passwordConfirmation' => str_repeat('a', 8),
            ],
            '最大文字数' => [
                'email' => 'test@gmail.com',
                'password' => str_repeat('a', 32),
                'passwordConfirmation' => str_repeat('a', 32),
            ],
            'パスワードの受け入れ可能文字列確認' => [
                'email' => 'test@gmail.com',
                'password' => 'abcxyzABCXYZ0123456789-_',
                'passwordConfirmation' => 'abcxyzABCXYZ0123456789-_',
            ],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderResetInvalidParameter')]
    public function test_reset_failed_by_validation_error(array $requestBody, array $expectedError): void
    {
        $response = $this->postJson('/member-auth/password-reset/reset', $requestBody);

        $response->assertUnprocessable();
        $response->assertInvalid($expectedError);
    }

    public static function dataProviderResetInvalidParameter(): array
    {
        return [
            'メールアドレスは必須' => [
                'requestBody' => [
                    'email' => '',
                    'email_verified_token' => 'token',
                    'password' => 'password',
                    'password_confirmation' => 'password',
                ],
                'expectedError' => [
                    'email' => 'メールアドレスは必須項目です。',
                ],
            ],
            'メールアドレスのフォーマット誤り' => [
                'requestBody' => [
                    'email' => 'aaaaa',
                    'email_verified_token' => 'token',
                    'password' => 'password',
                    'password_confirmation' => 'password',
                ],
                'expectedError' => [
                    'email' => 'メールアドレスは、有効なメールアドレス形式で指定してください。',
                ],
            ],
            'メールアドレスが無効なドメイン' => [
                'requestBody' => [
                    'email' => 'aaaaa@feaifjaiefijffefee.com',
                    'email_verified_token' => 'token',
                    'password' => 'password',
                    'password_confirmation' => 'password',
                ],
                'expectedError' => [
                    'email' => 'メールアドレスは、有効なメールアドレス形式で指定してください。',
                ],
            ],
            'メールアドレス検証トークンは必須' => [
                'requestBody' => [
                    'email' => 'test@gmail.com',
                    'email_verified_token' => '',
                    'password' => 'password',
                    'password_confirmation' => 'password',
                ],
                'expectedError' => [
                    'email_verified_token' => 'メールアドレス検証トークンは必須項目です。',
                ],
            ],
            'メールアドレス検証トークンは文字列' => [
                'requestBody' => [
                    'email' => 'test@gmail.com',
                    'email_verified_token' => 123456789,
                    'password' => 'password',
                    'password_confirmation' => 'password',
                ],
                'expectedError' => [
                    'email_verified_token' => 'メールアドレス検証トークンには、文字列を指定してください。',
                ],
            ],
            'メールアドレス検証トークンの文字数超過' => [
                'requestBody' => [
                    'email' => 'test@gmail.com',
                    'email_verified_token' => str_repeat('a', 256),
                    'password' => 'password',
                    'password_confirmation' => 'password',
                ],
                'expectedError' => [
                    'email_verified_token' => 'メールアドレス検証トークンの文字数は、255文字以下である必要があります。',
                ],
            ],
            'パスワードは必須' => [
                'requestBody' => [
                    'email' => 'test@gmail.com',
                    'email_verified_token' => 'token',
                    'password' => '',
                    'password_confirmation' => 'password',
                ],
                'expectedError' => [
                    'password' => 'パスワードは必須項目です。',
                ],
            ],
            'パスワードは文字列' => [
                'requestBody' => [
                    'email' => 'test@gmail.com',
                    'email_verified_token' => 'token',
                    'password' => 123456789,
                    'password_confirmation' => 'password',
                ],
                'expectedError' => [
                    'password' => 'パスワードには、文字列を指定してください。',
                ],
            ],
            'パスワードに使えない全角文字を入力' => [
                'requestBody' => [
                    'email' => 'test@gmail.com',
                    'email_verified_token' => 'token',
                    'password' => 'あいうえおかきくけこ',
                    'password_confirmation' => 'password',
                ],
                'expectedError' => [
                    'password' => 'パスワードには半角英数字及び-と_のみ入力できます。',
                ],
            ],
            'パスワードの文字数不足' => [
                'requestBody' => [
                    'email' => 'test@gmail.com',
                    'email_verified_token' => 'token',
                    'password' => str_repeat('a', 7),
                    'password_confirmation' => 'password',
                ],
                'expectedError' => [
                    'password' => 'パスワードは、8文字から32文字にしてください。',
                ],
            ],
            'パスワードの文字数超過' => [
                'requestBody' => [
                    'email' => 'test@gmail.com',
                    'email_verified_token' => 'token',
                    'password' => str_repeat('a', 33),
                    'password_confirmation' => 'password',
                ],
                'expectedError' => [
                    'password' => 'パスワードは、8文字から32文字にしてください。',
                ],
            ],
            'パスワードが確認入力と不一致' => [
                'requestBody' => [
                    'email' => 'test@gmail.com',
                    'email_verified_token' => 'token',
                    'password' => str_repeat('a', 8),
                    'password_confirmation' => str_repeat('b', 8),
                ],
                'expectedError' => [
                    'password' => 'パスワードとパスワード確認が一致しません。',
                ],
            ],
            'パスワード確認は必須' => [
                'requestBody' => [
                    'email' => 'test@gmail.com',
                    'email_verified_token' => 'token',
                    'password' => 'password',
                    'password_confirmation' => '',
                ],
                'expectedError' => [
                    'password_confirmation' => 'パスワード確認は必須項目です。',
                ],
            ],
            'パスワード確認は文字列' => [
                'requestBody' => [
                    'email' => 'test@gmail.com',
                    'email_verified_token' => 'token',
                    'password' => 'password',
                    'password_confirmation' => 12345678,
                ],
                'expectedError' => [
                    'password_confirmation' => 'パスワード確認には、文字列を指定してください。',
                ],
            ],
            'パスワード確認に使えない全角文字を入力' => [
                'requestBody' => [
                    'email' => 'test@gmail.com',
                    'email_verified_token' => 'token',
                    'password' => 'password',
                    'password_confirmation' => 'あいうえおかきくけこ',
                ],
                'expectedError' => [
                    'password_confirmation' => 'パスワード確認には半角英数字及び-と_のみ入力できます。',
                ],
            ],
            'パスワード確認の文字数不足' => [
                'requestBody' => [
                    'email' => 'test@gmail.com',
                    'email_verified_token' => 'token',
                    'password' => 'password',
                    'password_confirmation' => str_repeat('a', 7),
                ],
                'expectedError' => [
                    'password_confirmation' => 'パスワード確認は、8文字から32文字にしてください。',
                ],
            ],
            'パスワード確認の文字数超過' => [
                'requestBody' => [
                    'email' => 'test@gmail.com',
                    'email_verified_token' => 'token',
                    'password' => 'password',
                    'password_confirmation' => str_repeat('a', 33),
                ],
                'expectedError' => [
                    'password_confirmation' => 'パスワード確認は、8文字から32文字にしてください。',
                ],
            ],
        ];
    }

    /**
     * メールアドレス検証トークンの不一致により失敗
     */
    #[Test]
    public function test_reset_failed_by_token_unmatched(): void
    {
        $email = 'test@gmail.com';
        $password = 'passwordUpdated';
        $passwordConfirmation = 'passwordUpdated';
        // 先にリセットコードの発行をする
        Member::factory()->create([
            'email' => $email,
        ]);
        $requestBody = [
            'email' => $email,
        ];
        Mail::fake();

        $response = $this->postJson('/member-auth/password-reset/send-email', $requestBody);

        $response->assertOk();

        // 発行したtokenを取得
        $emailVerifiedToken = Redis::get("password_reset_email_verified_token_{$email}");
        $this->assertNotNull($emailVerifiedToken);
        $unmatchedEmailVerifiedToken = str_repeat('a', 255);

        $requestBody = [
            'email' => $email,
            'email_verified_token' => $unmatchedEmailVerifiedToken,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ];
        Exceptions::fake();

        // リセットAPIをコール
        $response = $this->postJson('/member-auth/password-reset/reset', $requestBody);

        $response->assertBadRequest();
        Exceptions::assertReported(PasswordResetTokenVerifyFailedException::class);
        $response->assertExactJson([
            'message' => 'パスワードリセットに失敗しました。リセットメールの有効期限が切れている可能性があります。',
        ]);

        // 再設定したパスワードでログインできないことをテスト
        $requestBody = [
            'email' => $email,
            'password' => $password,
        ];

        $response = $this->postJson('/member-auth/login', $requestBody);
        $response->assertUnauthorized();
    }

    /**
     * メールアドレスの不一致により失敗
     */
    #[Test]
    public function test_reset_failed_by_email_unmatched(): void
    {
        $email = 'test@gmail.com';
        $password = 'passwordUpdated';
        $passwordConfirmation = 'passwordUpdated';
        // 先にリセットコードの発行をする
        Member::factory()->create([
            'email' => $email,
        ]);
        $requestBody = [
            'email' => $email,
        ];
        Mail::fake();

        $response = $this->postJson('/member-auth/password-reset/send-email', $requestBody);

        $response->assertOk();

        // 発行したtokenを取得
        $emailVerifiedToken = Redis::get("password_reset_email_verified_token_{$email}");
        $this->assertNotNull($emailVerifiedToken);
        $unmatchedEmailVerifiedToken = str_repeat('a', 255);

        $requestBody = [
            'email' => "unmatch_{$email}",
            'email_verified_token' => $unmatchedEmailVerifiedToken,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ];
        Exceptions::fake();

        // リセットAPIをコール
        $response = $this->postJson('/member-auth/password-reset/reset', $requestBody);

        $response->assertBadRequest();
        Exceptions::assertReported(PasswordResetTokenVerifyFailedException::class);
        $response->assertExactJson([
            'message' => 'パスワードリセットに失敗しました。リセットメールの有効期限が切れている可能性があります。',
        ]);

        // 再設定したパスワードでログインできないことをテスト
        $requestBody = [
            'email' => $email,
            'password' => $password,
        ];

        $response = $this->postJson('/member-auth/login', $requestBody);
        $response->assertUnauthorized();
    }
}
