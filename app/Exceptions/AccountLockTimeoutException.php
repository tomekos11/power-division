<?php

namespace App\Exceptions;

final class AccountLockTimeoutException extends BusinessException
{
    public function __construct(
        public readonly int $accountId,
        public readonly int $seconds,
    ) {
        parent::__construct(
            "Locked operation for account [{$accountId}] exceeded the {$seconds}s deadline.",
        );
    }
}
