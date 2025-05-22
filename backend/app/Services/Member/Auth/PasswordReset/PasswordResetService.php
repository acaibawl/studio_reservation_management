<?php

declare(strict_types=1);

namespace App\Services\Member\Auth\PasswordReset;

use App\Auth\Member\PasswordResetTokenPool;
use App\Exceptions\Member\Auth\PasswordResetTokenVerifyFailedException;
use App\Models\Member;

readonly class PasswordResetService
{
    public function __construct(
        private PasswordResetTokenPool $passwordResetTokenPool,
    ) {}

    /**
     * @throws PasswordResetTokenVerifyFailedException
     */
    public function reset(string $token, string $email, string $password): void
    {
        if (! $this->passwordResetTokenPool->verify($token, $email)) {
            throw new PasswordResetTokenVerifyFailedException();
        }
        // tokenが検証できたら削除
        $this->passwordResetTokenPool->delete($email);
        $member = Member::where('email', $email)->first();
        // memberが存在しない場合については考慮不要。パスワードリセットtokenの発行もできないので、tokenのverifyができれば必ず存在する。
        $member->update(['password' => \Hash::make($password)]);
    }
}
