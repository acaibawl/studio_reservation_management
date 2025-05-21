<?php

declare(strict_types=1);

namespace App\Auth\Member;

use Illuminate\Support\Facades\Redis;
use Random\RandomException;

class PassCodePool
{
    /**
     * 600秒=10分 で失効
     */
    private const int TTL_IN_SEC = 600;

    /**
     * @throws RandomException
     */
    public function issue(PassCodeType $type, string $email): string
    {
        $emailVerifiedCode = $this->generatePassCode();
        Redis::set(
            $this->generateRedisKey($type, $email),
            $emailVerifiedCode,
            'EX',
            self::TTL_IN_SEC
        );

        return $emailVerifiedCode;
    }

    public function verify(PassCodeType $type, string $email, string $code): bool
    {
        return Redis::get($this->generateRedisKey($type, $email)) === $code;
    }

    public function delete(PassCodeType $type, string $email): void
    {
        Redis::del([$this->generateRedisKey($type, $email)]);
    }

    /**
     * @throws RandomException
     */
    private function generatePassCode(): string
    {
        $randomNumber = (string) random_int(0, 999999);

        return mb_str_pad($randomNumber, 6, '0', STR_PAD_LEFT);
    }

    private function generateRedisKey(PassCodeType $type, string $email): string
    {
        return "{$type->value}_email_verified_code_{$email}";
    }
}
