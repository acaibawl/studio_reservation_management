<?php

declare(strict_types=1);

namespace App\Auth\Member;

use Illuminate\Support\Facades\Redis;
use Str;

class PasswordResetTokenPool
{
    /**
     * 600秒=10分 で失効
     */
    private const int TTL_IN_SEC = 600;

    private const int TOKEN_LENGTH = 64;

    public function issue(string $email): string
    {
        $emailVerifiedToken = $this->generateToken();
        Redis::set(
            $this->generateRedisKey($email),
            $emailVerifiedToken,
            'EX',
            self::TTL_IN_SEC
        );

        return $emailVerifiedToken;
    }

    public function verify(string $email, string $token): bool
    {
        return Redis::get($this->generateRedisKey($email)) === $token;
    }

    public function delete(string $email): void
    {
        Redis::del([$this->generateRedisKey($email)]);
    }

    private function generateToken(): string
    {
        return Str::random(self::TOKEN_LENGTH);
    }

    private function generateRedisKey(string $email): string
    {
        return "password_reset_email_verified_token_{$email}";
    }
}
