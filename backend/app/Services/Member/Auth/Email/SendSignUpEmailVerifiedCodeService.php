<?php

declare(strict_types=1);

namespace App\Services\Member\Auth\Email;

use App\Auth\Member\PassCodePool;
use App\Auth\Member\PassCodeType;
use App\Exceptions\Member\Auth\MemberAlreadyRegisteredException;
use App\Mail\Member\Auth\MemberAlreadyRegisteredMail;
use App\Mail\Member\Auth\SignUpEmailVerifiedCodeMail;
use App\Models\Member;
use Mail;
use Random\RandomException;

readonly class SendSignUpEmailVerifiedCodeService
{
    public function __construct(
        private PassCodePool $passCodePool,
    ) {}

    /**
     * @throws RandomException
     * @throws MemberAlreadyRegisteredException
     */
    public function send(string $email): void
    {
        $member = Member::where('email', $email)->first();
        if ($member) {
            Mail::send(new MemberAlreadyRegisteredMail($email));
            throw new MemberAlreadyRegisteredException("email:{$email} is already registered.");
        }
        $emailVerifiedCode = $this->passCodePool->issue(PassCodeType::SIGN_UP, $email);
        Mail::send(new SignUpEmailVerifiedCodeMail($email, $emailVerifiedCode));
    }
}
