<?php

declare(strict_types=1);

namespace App\Exceptions\Reservation;

use App\Exceptions\UserDisplayableException;
use Symfony\Component\HttpFoundation\Response;

class AvailableHourExceededException extends UserDisplayableException
{
    public const string MESSAGE = '予約可能な利用時間を超えています';

    public const int HTTP_STATUS_CODE = Response::HTTP_BAD_REQUEST;
}
