<?php

namespace App\Contracts\Repositories;

use App\Data\AppliedTransaction;
use App\Models\Account;

interface AccountRepository
{
    public function findActiveByUserId(int $userId): ?Account;

    public function applyTransaction(Account $account, string $amount, int $fenceToken): AppliedTransaction;
}
