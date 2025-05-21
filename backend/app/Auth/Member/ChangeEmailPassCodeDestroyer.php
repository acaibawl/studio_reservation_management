<?php

declare(strict_types=1);

namespace App\Auth\Member;

readonly class ChangeEmailPassCodeDestroyer
{
    public function __construct(
        private PassCodePool $passCodePool,
    ) {}

    public function handle(string $email): void
    {
        $this->passCodePool->delete(PassCodeType::CHANGE_EMAIL, $email);
    }
}
