<?php

namespace App\Repositories;

use App\Contracts\Repositories\AccountRepository;
use App\Data\AppliedTransaction;
use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\StaleFenceException;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Carbon;

final class DatabaseAccountRepository implements AccountRepository
{
    public function findActiveByUserId(int $userId): ?Account
    {
        return Account::query()
            ->where('user_id', $userId)
            ->first();
    }

    public function applyTransaction(Account $account, string $amount, int $fenceToken): AppliedTransaction
    {
        return Account::query()->getConnection()->transaction(function () use ($account, $amount, $fenceToken): AppliedTransaction {
            $now = Carbon::now();

            $updated = Account::query()
                ->whereKey($account->id)
                ->where('last_fence', '<', $fenceToken)
                ->whereRaw('balance + ? >= 0', [$amount])
                ->increment(
                    'balance',
                    (float) $amount,
                    [
                        'last_fence' => $fenceToken,
                        'updated_at' => $now,
                    ],
                );

            if ($updated === 0) {
                $this->rejectUpdate($account->id, $amount, $fenceToken);
            }

            $balanceAfter = (string) $account->refresh()->balance;

            Transaction::query()->create([
                'account_id' => $account->id,
                'amount' => $amount,
                'balance_after' => $balanceAfter,
                'created_at' => $now,
            ]);

            return new AppliedTransaction($balanceAfter, $now);
        });
    }

    private function rejectUpdate(int $accountId, string $amount, int $fenceToken): never
    {
        $account = Account::query()
            ->whereKey($accountId)
            ->first(['balance', 'last_fence']);

        if ($account === null) {
            throw new StaleFenceException($accountId);
        }

        if ($account->last_fence >= $fenceToken) {
            throw new StaleFenceException($accountId);
        }

        if (self::sumWouldBeNegative((string) $account->balance, $amount)) {
            throw new InsufficientBalanceException($accountId);
        }

        throw new StaleFenceException($accountId);
    }

    private static function sumWouldBeNegative(string $balance, string $amount): bool
    {
        return self::toCents($balance) + self::toCents($amount) < 0;
    }

    private static function toCents(string $value): int
    {
        if (str_starts_with($value, '-')) {
            return -self::toCents(substr($value, 1));
        }

        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '0');
        $fraction = str_pad(substr($fraction, 0, 2), 2, '0');

        return ((int) $whole * 100) + (int) $fraction;
    }
}
