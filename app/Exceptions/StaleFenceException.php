<?php

namespace App\Exceptions;

final class StaleFenceException extends BusinessException
{
    public function __construct(public readonly int $accountId)
    {
        parent::__construct("Transaction rejected for account [{$accountId}] due to a stale lock generation.");
    }
}
