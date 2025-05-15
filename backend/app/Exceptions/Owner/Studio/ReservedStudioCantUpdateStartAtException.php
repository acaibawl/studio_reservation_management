<?php

declare(strict_types=1);

namespace App\Exceptions\Owner\Studio;

use App\Exceptions\UserDisplayableException;
use Symfony\Component\HttpFoundation\Response;

class ReservedStudioCantUpdateStartAtException extends UserDisplayableException
{
    public const string MESSAGE = 'まだ終了していない予約が入っているスタジオの開始時間は変更できません。';

    public const int HTTP_STATUS_CODE = Response::HTTP_BAD_REQUEST;
}
