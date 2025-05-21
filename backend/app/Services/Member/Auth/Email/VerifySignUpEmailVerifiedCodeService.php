<?php

declare(strict_types=1);

namespace App\Services\Member\Auth\Email;

use App\Auth\Member\PassCodePool;
use App\Auth\Member\PassCodeType;
use App\Exceptions\Member\Auth\PassCodeVerifyFailedException;

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
