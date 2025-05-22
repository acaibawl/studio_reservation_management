<?php

declare(strict_types=1);

namespace App\Auth\Member;

enum PassCodeType: string
{
    case SIGN_UP = 'sign_up';
    case CHANGE_EMAIL = 'change_email';
}
