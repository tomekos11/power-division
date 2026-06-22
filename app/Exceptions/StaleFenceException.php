<?php

namespace App\Exceptions;

use RuntimeException;

final class StaleFenceException extends RuntimeException
{
    public function __construct(public readonly int $accountId)
    {
        parent::__construct("Transaction rejected for account [{$accountId}] due to a stale lock generation.");
    }
}
