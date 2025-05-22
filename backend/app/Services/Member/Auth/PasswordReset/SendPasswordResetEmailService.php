<?php

declare(strict_types=1);

namespace App\Services\Member\Auth\PasswordReset;

use App\Auth\Member\PasswordResetTokenPool;
use App\Mail\Member\Auth\PasswordResetMail;
use App\Models\Member;
use Mail;

class SendPasswordResetEmailService
{
    public function __construct(
        private readonly PasswordResetTokenPool $passwordResetTokenPool,
    ) {}

    public function send(string $email): void
    {
        $member = Member::where('email', $email)->first();
        if (! $member) {
            // 指定のメールアドレスの会員がいない場合は何もしない
            return;
        }

        $passwordResetToken = $this->passwordResetTokenPool->issue($email);
        Mail::send(new PasswordResetMail($email, $passwordResetToken));
    }
}
