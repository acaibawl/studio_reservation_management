<?php

declare(strict_types=1);

namespace App\Services\Member\Auth\Email;

use App\Auth\Member\EnsureMemberDoesntExist;
use App\Auth\Member\PassCodePool;
use App\Auth\Member\PassCodeType;
use App\Exceptions\Member\Auth\MemberAlreadyRegisteredException;
use App\Mail\Member\Auth\SignUpEmailVerifiedCodeMail;
use Mail;
use Random\RandomException;

readonly class SendSignUpEmailVerifiedCodeService
{
    public function __construct(
        private PassCodePool $passCodePool,
        private EnsureMemberDoesntExist $ensureMemberDoesntExist,
    ) {}

    /**
     * @throws RandomException
     * @throws MemberAlreadyRegisteredException
     */
    public function send(string $email): void
    {
        $this->ensureMemberDoesntExist->handle($email);
        $emailVerifiedCode = $this->passCodePool->issue(PassCodeType::SIGN_UP, $email);
        Mail::send(new SignUpEmailVerifiedCodeMail($email, $emailVerifiedCode));
    }
}
