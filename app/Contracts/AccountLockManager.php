<?php

namespace App\Contracts;

use App\Exceptions\AccountLockTimeoutException;
use App\Exceptions\AccountLockUnavailableException;

interface AccountLockManager
{
    /**
     * @template TReturn
     *
     * @param  callable(int $fenceToken): TReturn  $callback
     * @return TReturn
     *
     * @throws AccountLockUnavailableException
     * @throws AccountLockTimeoutException
     */
    public function withAccountLock(int $accountId, callable $callback, ?int $waitSeconds = null): mixed;
}
