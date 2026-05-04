<?php

namespace App\Exceptions;

use App\Exceptions\Contracts\ApiException;
use RuntimeException;

class CouldNotUpdateWedgeMatrixException extends RuntimeException implements ApiException
{
    public function getStatusCode(): int
    {
        return 400;
    }

    public function getUserMessage(): string
    {
        return 'Could not update wedge matrix';
    }
}