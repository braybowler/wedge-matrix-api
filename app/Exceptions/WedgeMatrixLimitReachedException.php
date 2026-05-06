<?php

namespace App\Exceptions;

use App\Exceptions\Contracts\ApiException;
use RuntimeException;

class WedgeMatrixLimitReachedException extends RuntimeException implements ApiException
{
    public function getStatusCode(): int
    {
        return 422;
    }

    public function getUserMessage(): string
    {
        return 'Wedge matrix limit reached';
    }
}
