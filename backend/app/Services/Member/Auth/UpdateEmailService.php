<?php

declare(strict_types=1);

namespace App\Services\Member\Auth;

use App\Auth\Member\ChangeEmailPassCodeDestroyer;
use App\Auth\Member\EnsureMemberDoesntExist;
use App\Auth\Member\PassCodePool;
use App\Auth\Member\PassCodeType;
use App\Exceptions\Member\Auth\MemberAlreadyRegisteredException;
use App\Exceptions\Member\Auth\PassCodeVerifyFailedException;
use App\Models\Member;

readonly class UpdateEmailService
{
    public function __construct(
        private PassCodePool $passCodePool,
        private ChangeEmailPassCodeDestroyer $passCodeDestroyer,
        private EnsureMemberDoesntExist $ensureMemberDoesntExist,

    ) {}

    /**
     * @throws PassCodeVerifyFailedException
     * @throws MemberAlreadyRegisteredException
     */
    public function update(Member $member, string $email, string $code): void
    {
        $this->verifyCode($email, $code);
        $this->passCodeDestroyer->handle($email);
        $this->ensureMemberDoesntExist->handle($email);

        $member->update([
            'email' => $email,
        ]);
    }

    /**
     * @throws PassCodeVerifyFailedException
     */
    private function verifyCode(string $email, string $code): void
    {
        if (! $this->passCodePool->verify(PassCodeType::CHANGE_EMAIL, $email, $code)) {
            throw new PassCodeVerifyFailedException();
        }
    }
}
