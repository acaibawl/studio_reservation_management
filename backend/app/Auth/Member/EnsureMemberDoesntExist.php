<?php

declare(strict_types=1);

namespace App\Auth\Member;

use App\Exceptions\Member\Auth\MemberAlreadyRegisteredException;
use App\Mail\Member\Auth\MemberAlreadyRegisteredMail;
use App\Models\Member;
use Mail;

class EnsureMemberDoesntExist
{
    /**
     * @throws MemberAlreadyRegisteredException
     */
    public function handle(string $email): void
    {
        $member = Member::where('email', $email)->first();
        if ($member) {
            Mail::send(new MemberAlreadyRegisteredMail($email));
            throw new MemberAlreadyRegisteredException("email:{$email} is already registered.");
        }
    }
}
