<?php

namespace App\Exceptions;

use RuntimeException;

final class AccountLockUnavailableException extends RuntimeException
{
    public function __construct(public readonly int $accountId)
    {
        parent::__construct("Could not acquire account lock for account [{$accountId}].");
    }
}
