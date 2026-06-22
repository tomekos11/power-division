<?php

namespace App\Services;

use App\Contracts\AccountLockManager;
use App\Contracts\Repositories\AccountRepository;
use App\Data\AccountStateData;
use App\Exceptions\AccountNotFoundException;

final class AccountTransactionService
{
    public function __construct(
        private readonly AccountRepository $accounts,
        private readonly AccountLockManager $lockManager,
        private readonly int $paymentSimulationSeconds,
    ) {}

    public function process(int $userId, string $amount): AccountStateData
    {
        $account = $this->accounts->findActiveByUserId($userId);

        if ($account === null) {
            throw new AccountNotFoundException($userId);
        }

        return $this->lockManager->withAccountLock(
            $account->id,
            function (int $fenceToken) use ($account, $userId, $amount): AccountStateData {
                sleep($this->paymentSimulationSeconds);

                $applied = $this->accounts->applyTransaction($account, $amount, $fenceToken);

                return new AccountStateData(
                    user_id: $userId,
                    balance: $applied->balance,
                    lastTransactionAt: $applied->createdAt,
                );
            },
        );
    }
}
