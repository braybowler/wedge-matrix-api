<?php

namespace App\Exceptions\Contracts;

interface ApiException
{
    public function getStatusCode(): int;

    public function getUserMessage(): string;
}