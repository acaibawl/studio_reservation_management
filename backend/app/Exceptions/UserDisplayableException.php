<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class UserDisplayableException extends Exception
{
    /** レスポンスのメッセージ */
    public const string MESSAGE = '例外の本文です。';

    /** HTTPステータスコード */
    public const int HTTP_STATUS_CODE = Response::HTTP_INTERNAL_SERVER_ERROR;
}
