<?php

declare(strict_types=1);

namespace App\Auth\Member;

readonly class SignUpPassCodeDestroyer
{
    public function __construct(
        private PassCodePool $passCodePool,
    ) {}

    public function handle(string $email): void
    {
        $this->passCodePool->delete(PassCodeType::SIGN_UP, $email);
    }
}
