<?php

declare(strict_types=1);

namespace App\Services\Member\Auth;

use App\Auth\Member\EnsureMemberDoesntExist;
use App\Auth\Member\PassCodeDestroyer;
use App\Exceptions\Member\Auth\MemberAlreadyRegisteredException;
use App\Exceptions\Member\Auth\PassCodeVerifyFailedException;
use App\Mail\Member\Auth\RegisterCompletedMail;
use App\Models\Member;
use App\Services\Member\Auth\Email\VerifySignUpEmailVerifiedCodeService;
use Mail;

readonly class MemberRegisterService
{
    public function __construct(
        private VerifySignUpEmailVerifiedCodeService $verifySignUpEmailVerifiedCodeService,
        private EnsureMemberDoesntExist $ensureMemberDoesntExist,
        private PassCodeDestroyer $passCodeDestroyer,
    ) {}

    /**
     * @throws PassCodeVerifyFailedException
     * @throws MemberAlreadyRegisteredException
     */
    public function register(array $attributes): void
    {
        $email = $attributes['email'];
        $code = $attributes['code'];
        $this->verifySignUpEmailVerifiedCodeService->verify($email, $code);
        $this->passCodeDestroyer->handle($email);
        $this->ensureMemberDoesntExist->handle($email);

        $member = Member::create([
            'name' => $attributes['name'],
            'email' => $attributes['email'],
            'address' => $attributes['address'],
            'tel' => $attributes['tel'],
            'password' => \Hash::make($attributes['password']),
        ]);

        Mail::send(new RegisterCompletedMail($member));
    }
}
