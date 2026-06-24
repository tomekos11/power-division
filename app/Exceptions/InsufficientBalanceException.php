<?php

namespace App\Exceptions;

final class InsufficientBalanceException extends BusinessException
{
    public function __construct(public readonly int $accountId)
    {
        parent::__construct("Insufficient balance on account [{$accountId}].");
    }
}
