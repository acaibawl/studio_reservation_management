<?php

declare(strict_types=1);

namespace App\Exceptions\Owner\Studio;

use App\Exceptions\UserDisplayableException;
use Symfony\Component\HttpFoundation\Response;

class ReservedStudioCantDeleteException extends UserDisplayableException
{
    public const string MESSAGE = 'まだ終了していない予約が入っているスタジオは削除できません。';

    public const int HTTP_STATUS_CODE = Response::HTTP_BAD_REQUEST;
}
