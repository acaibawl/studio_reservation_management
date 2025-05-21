<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Member\Auth;

use App\Mail\Member\Auth\MemberAlreadyRegisteredMail;
use App\Models\Member;
use Illuminate\Support\Facades\Redis;
use Log;
use Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EmailUpdateControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // テスト開始前にRedisをクリアする
        Redis::flushdb();
    }

    #[Test]
    public function test_update_success(): void
    {
        $member = $this->loginAsMember();
        // 認証コード送信処理を先に実施
        Mail::fake();
        $email = 'test@gmail.com';

        $response = $this->postJson('/member-auth/change-email-verified-code/send', [
            'email' => $email,
        ]);

        $response->assertOk();
        // Redisから認証コード取得
        $code = Redis::get("change_email_email_verified_code_{$email}");
        $this->assertNotNull($code);

        // 認証コードの認証テスト
        $response = $this->patchJson('/member-auth/email', [
            'email' => $email,
            'code' => $code,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('members', [
            'email' => $email,
            'name' => $member->name,
            'address' => $member->address,
            'tel' => $member->tel,
        ]);
        // Redisから認証コードが削除されていることの確認
        $code = Redis::get("change_email_email_verified_code_{$email}");
        $this->assertNull($code);
    }

    #[Test]
    #[DataProvider('dataProviderUpdateInvalidParameter')]
    public function test_update_failed_by_validation_error(array $requestBody, array $expectedError): void
    {
        $this->loginAsMember();
        $response = $this->postJson('/member-auth/sign-up-email-verified-code/verify', $requestBody);

        $response->assertUnprocessable();
        $response->assertInvalid($expectedError);
    }

    public static function dataProviderUpdateInvalidParameter(): array
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
    public function test_update_failed_by_email_unmatch(): void
    {
        $this->loginAsMember();
        // 認証コード送信処理を先に実施
        Mail::fake();
        $email = 'test@gmail.com';

        $response = $this->postJson('/member-auth/change-email-verified-code/send', [
            'email' => $email,
        ]);

        $response->assertOk();
        // Redisから認証コード取得
        $code = Redis::get("change_email_email_verified_code_{$email}");
        $this->assertNotNull($code);

        // 認証コード送信時と異なるメールアドレスで認証コードの認証テスト
        $response = $this->patchJson('/member-auth/email', [
            'email' => 'xxxxx@gmail.com',
            'code' => $code,
        ]);

        $response->assertBadRequest();
    }

    #[Test]
    public function test_update_failed_by_code_unmatch(): void
    {
        $this->loginAsMember();
        // 認証コード送信処理を先に実施
        Mail::fake();
        $email = 'test@gmail.com';

        $response = $this->postJson('/member-auth/change-email-verified-code/send', [
            'email' => $email,
        ]);

        $response->assertOk();
        // Redisから認証コード取得
        $code = Redis::get("change_email_email_verified_code_{$email}");
        $this->assertNotNull($code);
        // 認証コードは数字で6文字なので、英字文字列は仕様上必ず不一致となる
        $unmatchCode = 'abcdef';

        // 誤った認証コードで検証をリクエスト
        $response = $this->patchJson('/member-auth/email', [
            'email' => $email,
            'code' => $unmatchCode,
        ]);

        $response->assertBadRequest();
    }

    #[Test]
    public function test_update_failed_by_member_already_registered(): void
    {
        $member = $this->loginAsMember();
        // 認証コード送信処理を先に実施
        Mail::fake();
        $email = 'test@gmail.com';

        $response = $this->postJson('/member-auth/change-email-verified-code/send', [
            'email' => $email,
        ]);

        $response->assertOk();
        // Redisから認証コード取得
        $code = Redis::get("change_email_email_verified_code_{$email}");
        $this->assertNotNull($code);

        // 通常あり得ないが、認証コード発行後にメールアドレスの会員が作られた（会員登録とメールアドレス変更を同時にコード発行すれば可能）
        Member::factory()->create([
            'email' => $email,
        ]);
        $logSpy = Log::spy();

        // 認証コードの認証テスト
        $response = $this->patchJson('/member-auth/email', [
            'email' => $email,
            'code' => $code,
        ]);

        // レスポンスは成功のレスポンスが返る
        $response->assertOk();
        $this->assertDatabaseMissing('members', [
            'email' => $email,
            'name' => $member->name,
            'address' => $member->address,
            'tel' => $member->tel,
        ]);
        // Redisから認証コードが削除されていることの確認
        $code = Redis::get("change_email_email_verified_code_{$email}");
        $this->assertNull($code);
        // ログ確認
        $logSpy->shouldHaveReceived('info', ["email:{$email} is already registered."]);
        // 既に登録されている旨のメールが1回送信されていること
        Mail::assertSent(MemberAlreadyRegisteredMail::class, 1);
    }
}
