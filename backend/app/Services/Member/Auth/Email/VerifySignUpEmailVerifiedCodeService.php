<?php

declare(strict_types=1);

namespace App\Services\Member\Auth\Email;

use App\Auth\Member\PassCodePool;
use App\Auth\Member\PassCodeType;
use App\Exceptions\Member\Auth\MemberAlreadyRegisteredException;
use App\Exceptions\Member\Auth\PassCodeVerifyFailedException;
use App\Mail\Member\Auth\MemberAlreadyRegisteredMail;
use App\Mail\Member\Auth\SignUpEmailVerifiedCodeMail;
use App\Models\Member;
use Mail;
use Random\RandomException;

readonly class VerifySignUpEmailVerifiedCodeService
{
    public function __construct(
        private PassCodePool $passCodePool,
    ) {}

    /**
     * @throws PassCodeVerifyFailedException
     */
    public function verify(string $email, string $code): void
    {
        if (! $this->passCodePool->verify(PassCodeType::SIGN_UP, $email, $code)) {
            throw new PassCodeVerifyFailedException();
        }
    }
}
