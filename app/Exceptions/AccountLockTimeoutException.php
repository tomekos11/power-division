<?php

namespace App\Exceptions;

use RuntimeException;

final class AccountLockTimeoutException extends RuntimeException
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
