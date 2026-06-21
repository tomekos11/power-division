<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'amount' => '10.00',
            'balance_after' => '110.00',
            'created_at' => now(),
        ];
    }

    public function credit(string $amount, string $balanceAfter): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $amount,
            'balance_after' => $balanceAfter,
        ]);
    }

    public function debit(string $amount, string $balanceAfter): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $amount,
            'balance_after' => $balanceAfter,
        ]);
    }

    public function forAccount(Account $account): static
    {
        return $this->state(fn (array $attributes) => [
            'account_id' => $account->id,
        ]);
    }
}
