<?php

namespace App\Exceptions;

final class AccountNotFoundException extends BusinessException
{
    public function __construct(public readonly int $userId)
    {
        parent::__construct("No active account found for user [{$userId}].");
    }
}
