<?php

namespace App\Services;

use App\Contracts\AccountLockManager;
use App\Contracts\Repositories\AccountRepository;
use App\Data\AccountStateData;
use App\Exceptions\AccountNotFoundException;
use App\Models\User;

final class AccountTransactionService
{
    public function __construct(
        private readonly AccountRepository $accounts,
        private readonly AccountLockManager $lockManager,
        private readonly int $paymentSimulationSeconds,
    ) {}

    public function process(User $user, string $amount): AccountStateData
    {
        $account = $this->accounts->findActiveForUser($user);

        if ($account === null) {
            throw new AccountNotFoundException($user);
        }

        return $this->lockManager->withAccountLock(
            $account->id,
            function (int $fenceToken) use ($account, $user, $amount): AccountStateData {
                sleep($this->paymentSimulationSeconds);

                $applied = $this->accounts->applyTransaction($account, $amount, $fenceToken);

                return new AccountStateData(
                    userId: $user->id,
                    balance: $applied->balance,
                    lastTransactionAt: $applied->createdAt,
                );
            },
        );
    }
}
