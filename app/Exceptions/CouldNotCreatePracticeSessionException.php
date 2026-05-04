<?php

namespace App\Exceptions;

use App\Exceptions\Contracts\ApiException;
use RuntimeException;

class CouldNotCreatePracticeSessionException extends RuntimeException implements ApiException
{
    public function getStatusCode(): int
    {
        return 400;
    }

    public function getUserMessage(): string
    {
        return 'Could not create practice session';
    }
}
