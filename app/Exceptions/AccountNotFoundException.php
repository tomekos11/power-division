<?php

namespace App\Exceptions;

use RuntimeException;

final class AccountNotFoundException extends RuntimeException
{
    public function __construct(public readonly int $userId)
    {
        parent::__construct("No active account found for user [{$userId}].");
    }
}
