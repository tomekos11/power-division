<?php

namespace App\Exceptions;

use RuntimeException;

final class InsufficientBalanceException extends RuntimeException
{
    public function __construct(public readonly int $accountId)
    {
        parent::__construct("Insufficient balance on account [{$accountId}].");
    }
}
