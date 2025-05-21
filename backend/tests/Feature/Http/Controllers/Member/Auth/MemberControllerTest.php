<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Member\Auth;

use App\Mail\Member\Auth\MemberAlreadyRegisteredMail;
use App\Mail\Member\Auth\RegisterCompletedMail;
use App\Models\Member;
use App\Models\Owner;
use Illuminate\Support\Facades\Redis;
use Illuminate\Testing\Fluent\AssertableJson;
use Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MemberControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // テスト開始前にRedisをクリアする
        Redis::flushdb();
    }

    #[Test]
    #[dataProvider('dataProviderStoreValidParameter')]
    public function test_store_success(
        string $email,
        string $name,
        string $address,
        string $tel,
        string $password,
        string $password_confirmation,
    ): void {
        // 認証コードを発行
        Mail::fake();

        $response = $this->postJson('/member-auth/sign-up-email-verified-code/send', [
            'email' => $email,
        ]);

        $response->assertOk();
        // Redisから認証コードを取得
        $code = Redis::get("sign_up_email_verified_code_{$email}");
        $this->assertNotNull($code);

        // 会員登録のテスト
        $response = $this->postJson('/member-auth/member', [
            'email' => $email,
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'tel' => $tel,
            'password' => $password,
            'password_confirmation' => $password_confirmation,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('members', [
            'email' => $email,
            'name' => $name,
            'address' => $address,
            'tel' => $tel,
        ]);

        // Redisから認証コードが削除されていることの確認
        $code = Redis::get("sign_up_email_verified_code_{$email}");
        $this->assertNull($code);

        Mail::assertSent(RegisterCompletedMail::class, 1);
        Mail::assertSent(RegisterCompletedMail::class, function ($mail) use ($email) {
            return $mail->hasTo($email);
        });
    }

    public static function dataProviderStoreValidParameter(): array
    {
        return [
            '最大文字数' => [
                'email' => 'test@gmail.com',
                'name' => str_repeat('あ', 50),
                'address' => str_repeat('あ', 128),
                'tel' => '08012345678',
                'password' => str_repeat('a', 32),
                'password_confirmation' => str_repeat('a', 32),
            ],
            '最小文字数' => [
                'email' => 'test@gmail.com',
                'name' => 'a',
                'address' => 'a',
                'tel' => '0312345678',
                'password' => str_repeat('a', 8),
                'password_confirmation' => str_repeat('a', 8),
            ],
            'パスワードの受け入れ文字確認' => [
                'email' => 'test@gmail.com',
                'name' => 'a',
                'address' => 'a',
                'tel' => '0312345678',
                'password' => 'abcxyzABCXYZ012789-_',
                'password_confirmation' => 'abcxyzABCXYZ012789-_',
            ],

        ];
    }

    #[Test]
    #[DataProvider('dataProviderStoreInvalidParameter')]
    public function test_store_failed_by_validation_error(array $requestBody, array $expectedError): void
    {
        $response = $this->postJson('/member-auth/member', $requestBody);

        $response->assertUnprocessable();
        $response->assertInvalid($expectedError);
    }

    public static function dataProviderStoreInvalidParameter(): array
    {
        return [
            'メールアドレスは必須' => [
                'requestBody' => [
                    'email' => '',
                    'code' => '123456',
                    'name' => 'テスト太郎',
                    'address' => 'テスト住所',
                    'tel' => '0312345678',
                    'password' => 'password',
                    'password_confirmation' => 'password',
                ],
                'expectedError' => [
                    'email' => 'メールアドレスは必須項目です。',
                ],
            ],
            'メールアドレスのフォーマット誤り' => [
                'requestBody' => [
                    'email' => 'aaaa',
                    'code' => '123456',
                    'name' => 'テスト太郎',
                    'address' => 'テスト住所',
                    'tel' => '0312345678',
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
                    'code' => '123456',
                    'name' => 'テスト太郎',
                    'address' => 'テスト住所',
                    'tel' => '0312345678',
                    'password' => 'password',
                    'password_confirmation' => 'password',
                ],
                'expectedError' => [
                    'email' => 'メールアドレスは、有効なメールアドレス形式で指定してください。',
                ],
            ],
            '認証コードは必須' => [
                'requestBody' => [
                    'email' => 'test@gmail.com',
                    'code' => '',
                    'name' => 'テスト太郎',
                    'address' => 'テスト住所',
                    'tel' => '0312345678',
                    'password' => 'password',
                    'password_confirmation' => 'password',
                ],
                'expectedError' => [
                    'code' => '認証コードは必須項目です。',
                ],
            ],
            '認証コードは文字列' => [
                'requestBody' => [
                    'email' => 'test@gmail.com',
                    'code' => 123456,
                    'name' => 'テスト太郎',
                    'address' => 'テスト住所',
                    'tel' => '0312345678',
                    'password' => 'password',
                    'password_confirmation' => 'password',
                ],
                'expectedError' => [
                    'code' => '認証コードには、文字列を指定してください。',
                ],
            ],
            '認証コードの文字数不足' => [
                'requestBody' => [
                    'email' => 'test@gmail.com',
                    'code' => '12345',
                    'name' => 'テスト太郎',
                    'address' => 'テスト住所',
                    'tel' => '0312345678',
                    'password' => 'password',
                    'password_confirmation' => 'password',
                ],
                'expectedError' => [
                    'code' => '認証コードの文字数は、6文字にしてください。',
                ],
            ],
            '認証コードの文字数超過' => [
                'requestBody' => [
                    'email' => 'test@gmail.com',
                    'code' => '1234567',
                    'name' => 'テスト太郎',
                    'address' => 'テスト住所',
                    'tel' => '0312345678',
                    'password' => 'password',
                    'password_confirmation' => 'password',
                ],
                'expectedError' => [
                    'code' => '認証コードの文字数は、6文字にしてください。',
                ],
            ],
            '名前は必須' => [
                'requestBody' => [
                    'email' => 'test@gmail.com',
                    'code' => '123456',
                    'name' => '',
                    'address' => 'テスト住所',
                    'tel' => '0312345678',
                    'password' => 'password',
                    'password_confirmation' => 'password',
                ],
                'expectedError' => [
                    'name' => '名前は必須項目です。',
                ],
            ],
            '名前は文字列' => [
                'requestBody' => [
                    'email' => 'test@gmail.com',
                    'code' => '123456',
                    'name' => 123,
                    'address' => 'テスト住所',
                    'tel' => '0312345678',
                    'password' => 'password',
                    'password_confirmation' => 'password',
                ],
                'expectedError' => [
                    'name' => '名前には、文字列を指定してください。',
                ],
            ],
            '名前の文字数超過' => [
                'requestBody' => [
                    'email' => 'test@gmail.com',
                    'code' => '123456',
                    'name' => str_repeat('a', 51),
                    'address' => 'テスト住所',
                    'tel' => '0312345678',
                    'password' => 'password',
                    'password_confirmation' => 'password',
                ],
                'expectedError' => [
                    'name' => '名前の文字数は、50文字以下である必要があります。',
                ],
            ],
            '住所は必須' => [
                'requestBody' => [
                    'email' => 'test@gmail.com',
                    'code' => '123456',
                    'name' => 'テスト太郎',
                    'address' => '',
                    'tel' => '0312345678',
                    'password' => 'password',
                    'password_confirmation' => 'password',
                ],
                'expectedError' => [
                    'address' => '住所は必須項目です。',
                ],
            ],
            '住所は文字列' => [
                'requestBody' => [
                    'email' => 'test@gmail.com',
                    'code' => '123456',
                    'name' => 'テスト太郎',
                    'address' => 123,
                    'tel' => '0312345678',
                    'password' => 'password',
                    'password_confirmation' => 'password',
                ],
                'expectedError' => [
                    'address' => '住所には、文字列を指定してください。',
                ],
            ],
            '住所の文字数超過' => [
                'requestBody' => [
                    'email' => 'test@gmail.com',
                    'code' => '123456',
                    'name' => 'テスト太郎',
                    'address' => str_repeat('a', 129),
                    'tel' => '0312345678',
                    'password' => 'password',
                    'password_confirmation' => 'password',
                ],
                'expectedError' => [
                    'address' => '住所の文字数は、128文字以下である必要があります。',
                ],
            ],
            '電話番号は必須' => [
                'requestBody' => [
                    'email' => 'test@gmail.com',
                    'code' => '123456',
                    'name' => 'テスト太郎',
                    'address' => 'テスト住所',
                    'tel' => '',
                    'password' => 'password',
                    'password_confirmation' => 'password',
                ],
                'expectedError' => [
                    'tel' => '電話番号は必須項目です。',
                ],
            ],
            '電話番号は文字列' => [
                'requestBody' => [
                    'email' => 'test@gmail.com',
                    'code' => '123456',
                    'name' => 'テスト太郎',
                    'address' => 'テスト住所',
                    'tel' => 1234567890,
                    'password' => 'password',
                    'password_confirmation' => 'password',
                ],
                'expectedError' => [
                    'tel' => '電話番号には、文字列を指定してください。',
                ],
            ],
            '電話番号の文字数超過' => [
                'requestBody' => [
                    'email' => 'test@gmail.com',
                    'code' => '123456',
                    'name' => 'テスト太郎',
                    'address' => 'テスト住所',
                    'tel' => '080123456789',
                    'password' => 'password',
                    'password_confirmation' => 'password',
                ],
                'expectedError' => [
                    'tel' => '電話番号は、10文字から11文字にしてください。',
                ],
            ],
            '電話番号の文字数不足' => [
                'requestBody' => [
                    'email' => 'test@gmail.com',
                    'code' => '123456',
                    'name' => 'テスト太郎',
                    'address' => 'テスト住所',
                    'tel' => '031234567',
                    'password' => 'password',
                    'password_confirmation' => 'password',
                ],
                'expectedError' => [
                    'tel' => '電話番号は、10文字から11文字にしてください。',
                ],
            ],
            '電話番号は数字のみ' => [
                'requestBody' => [
                    'email' => 'test@gmail.com',
                    'code' => '123456',
                    'name' => 'テスト太郎',
                    'address' => 'テスト住所',
                    'tel' => 'aaaaaaaaaa',
                    'password' => 'password',
                    'password_confirmation' => 'password',
                ],
                'expectedError' => [
                    'tel' => '電話番号はハイフン抜きの数字のみ入力してください。',
                ],
            ],
            'パスワードは必須' => [
                'requestBody' => [
                    'email' => 'test@gmail.com',
                    'code' => '123456',
                    'name' => 'テスト太郎',
                    'address' => 'テスト住所',
                    'tel' => '0312345678',
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
                    'code' => '123456',
                    'name' => 'テスト太郎',
                    'address' => 'テスト住所',
                    'tel' => '0312345678',
                    'password' => 12345678,
                    'password_confirmation' => 'password',
                ],
                'expectedError' => [
                    'password' => 'パスワードには、文字列を指定してください。',
                ],
            ],
            'パスワードに使えない全角文字を入力' => [
                'requestBody' => [
                    'email' => 'test@gmail.com',
                    'code' => '123456',
                    'name' => 'テスト太郎',
                    'address' => 'テスト住所',
                    'tel' => '0312345678',
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
                    'code' => '123456',
                    'name' => 'テスト太郎',
                    'address' => 'テスト住所',
                    'tel' => '0312345678',
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
                    'code' => '123456',
                    'name' => 'テスト太郎',
                    'address' => 'テスト住所',
                    'tel' => '0312345678',
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
                    'code' => '123456',
                    'name' => 'テスト太郎',
                    'address' => 'テスト住所',
                    'tel' => '0312345678',
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
                    'code' => '123456',
                    'name' => 'テスト太郎',
                    'address' => 'テスト住所',
                    'tel' => '0312345678',
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
                    'code' => '123456',
                    'name' => 'テスト太郎',
                    'address' => 'テスト住所',
                    'tel' => '0312345678',
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
                    'code' => '123456',
                    'name' => 'テスト太郎',
                    'address' => 'テスト住所',
                    'tel' => '0312345678',
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
                    'code' => '123456',
                    'name' => 'テスト太郎',
                    'address' => 'テスト住所',
                    'tel' => '0312345678',
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
                    'code' => '123456',
                    'name' => 'テスト太郎',
                    'address' => 'テスト住所',
                    'tel' => '0312345678',
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
     * 指定したメールアドレスの会員が既に存在して登録失敗
     */
    public function test_store_failed_by_member_already_registered(): void
    {
        $email = 'test@gmail.com';
        // 認証コードを発行

        $response = $this->postJson('/member-auth/sign-up-email-verified-code/send', [
            'email' => $email,
        ]);

        $response->assertOk();
        // Redisから認証コードを取得
        $code = Redis::get("sign_up_email_verified_code_{$email}");
        $this->assertNotNull($code);

        // 通常は認証コード発行のタイミングで会員登録済みのメールアドレス宛に発行したら弾かれるが、
        // 何らかの理由により認証コード発行できてしまった場合を想定
        Member::factory()->create([
            'email' => $email,
        ]);

        $name = 'テスト太郎';
        $address = 'テスト住所';
        $tel = '0312345678';
        $password = 'password';
        $logSpy = \Log::spy();
        Mail::fake();

        // 会員登録のテスト
        $response = $this->postJson('/member-auth/member', [
            'email' => $email,
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'tel' => $tel,
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        // 登録失敗していても登録成功と同じレスポンス
        $response->assertCreated();
        $this->assertDatabaseMissing('members', [
            'email' => $email,
            'name' => $name,
            'address' => $address,
            'tel' => $tel,
        ]);

        // Redisから認証コードが削除されていることの確認
        $code = Redis::get("sign_up_email_verified_code_{$email}");
        $this->assertNull($code);

        // 既に登録されている旨のメールが1回送信されていること
        Mail::assertSent(MemberAlreadyRegisteredMail::class, 1);
        // 既に登録されている旨のメールが指定のアドレスに送信されていること
        Mail::assertSent(MemberAlreadyRegisteredMail::class, function ($mail) use ($email) {
            return $mail->hasTo($email);
        });
        // 会員登録完了メールが送信されていないこと
        Mail::assertNotSent(RegisterCompletedMail::class);
        // ログにinfoレベルで出力されていること
        $logSpy->shouldHaveReceived('info', ["email:{$email} is already registered."]);
    }

    /**
     * 認証コードが不一致で失敗
     */
    public function test_store_failed_by_verified_code_mismatch(): void
    {
        // 認証コードを発行
        Mail::fake();
        $email = 'test@gmail.com';

        $response = $this->postJson('/member-auth/sign-up-email-verified-code/send', [
            'email' => $email,
        ]);

        $response->assertOk();
        // Redisから認証コードを取得
        $code = Redis::get("sign_up_email_verified_code_{$email}");
        $this->assertNotNull($code);

        // 認証コードは数字なので、英字は必ず異なる仕様
        $unmatchedCode = 'abcdef';
        $name = 'テスト太郎';
        $address = 'テスト住所';
        $tel = '0312345678';
        $password = 'password';

        // 会員登録のテスト
        $response = $this->postJson('/member-auth/member', [
            'email' => $email,
            'code' => $unmatchedCode,
            'name' => $name,
            'address' => $address,
            'tel' => $tel,
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $response->assertBadRequest();
        $this->assertDatabaseMissing('members', [
            'email' => $email,
            'name' => $name,
            'address' => $address,
            'tel' => $tel,
        ]);

        // Redisから認証コードが削除されていないことの確認
        $code = Redis::get("sign_up_email_verified_code_{$email}");
        $this->assertNotNull($code);

        Mail::assertNotSent(RegisterCompletedMail::class);
    }

    #[Test]
    public function test_login_success(): void
    {
        $password = 'password';
        $email = 'test@gmail.com';
        Member::factory()->create([
            'email' => $email,
            'password' => \Hash::make($password),
        ]);
        $requestBody = [
            'email' => $email,
            'password' => $password,
        ];

        $response = $this->postJson('/member-auth/login', $requestBody);
        $response->assertStatus(200);
        $response->assertJson(fn (AssertableJson $json) => $json->hasAll(
            [
                'member_access_token',
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
        $response = $this->postJson('/member-auth/login', $requestBody);
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
                'errorMessage' => 'メールアドレスは必須項目です。',
            ],
            'emailフォーマット誤り' => [
                'requestBody' => [
                    'email' => 'acai',
                    'password' => 'password',
                ],
                'errorMessage' => 'メールアドレスは、有効なメールアドレス形式で指定してください。',
            ],
            'email文字数超過' => [
                'requestBody' => [
                    'email' => str_repeat('a', 244) . '@example.com',
                    'password' => 'password',
                ],
                'errorMessage' => 'メールアドレスの文字数は、255文字以下である必要があります。',
            ],
            'password空文字' => [
                'requestBody' => [
                    'email' => 'acai@example.com',
                    'password' => '',
                ],
                'errorMessage' => 'パスワードは必須項目です。',
            ],
            'password文字数不足' => [
                'requestBody' => [
                    'email' => 'acai@example.com',
                    'password' => str_repeat('a', 7),
                ],
                'errorMessage' => 'パスワードは、8文字から32文字にしてください。',
            ],
            'password文字数超過' => [
                'requestBody' => [
                    'email' => 'acai@example.com',
                    'password' => str_repeat('a', 33),
                ],
                'errorMessage' => 'パスワードは、8文字から32文字にしてください。',
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
        $response = $this->postJson('/member-auth/login', $requestBody);
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

    #[Test]
    public function test_show_me_success(): void
    {
        $email = 'test@gmail.com';
        $name = 'テスト太郎';
        $address = 'テスト住所';
        $tel = '0312345678';
        $member = Member::factory()->create([
            'email' => $email,
            'name' => $name,
            'address' => $address,
            'tel' => $tel,
        ]);
        $this->loginAsMember($member);
        $response = $this->getJson('/member-auth/me');
        $response->assertOk();
        $response->assertExactJson([
            'email' => $email,
            'name' => $name,
            'address' => $address,
            'tel' => $tel,
        ]);
    }

    /**
     * 未ログインではshow meはアクセス不可
     */
    #[Test]
    public function test_show_me_failed_by_not_logged_in(): void
    {
        $response = $this->getJson('/member-auth/me');
        $response->assertUnauthorized();
    }
}
