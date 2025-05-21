<?php

declare(strict_types=1);

namespace App\Exceptions\Member\Auth;

use Exception;

/**
 * メールアドレス認証コードを既に登録されているメールアドレス宛に送信しようとした場合の例外
 */
class MemberAlreadyRegisteredException extends Exception
{
    //
}
