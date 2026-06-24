<?php

namespace App\Exceptions;

final class AccountLockUnavailableException extends BusinessException
{
    public function __construct(public readonly int $accountId)
    {
        parent::__construct("Could not acquire account lock for account [{$accountId}].");
    }
}
