<?php

namespace App\Contracts\Repositories;

use App\Data\AppliedTransaction;
use App\Models\Account;
use App\Models\User;

interface AccountRepository
{
    public function findActiveForUser(User $user): ?Account;

    public function applyTransaction(Account $account, string $amount, int $fenceToken): AppliedTransaction;
}
