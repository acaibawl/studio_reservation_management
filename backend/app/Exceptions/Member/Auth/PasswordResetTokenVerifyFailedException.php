<?php

declare(strict_types=1);

namespace App\Exceptions\Member\Auth;

use App\Exceptions\UserDisplayableException;
use Symfony\Component\HttpFoundation\Response;

/**
 * パスコードの検証失敗
 */
class PasswordResetTokenVerifyFailedException extends UserDisplayableException
{
    public const string MESSAGE = 'パスワードリセットに失敗しました。リセットメールの有効期限が切れている可能性があります。';

    public const int HTTP_STATUS_CODE = Response::HTTP_BAD_REQUEST;
}
