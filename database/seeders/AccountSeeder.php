<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AccountSeeder extends Seeder
{
    /**
     * Seed users with accounts and sample transaction history for local API testing.
     */
    public function run(): void
    {
        $this->seedUser(
            name: 'Alice Example',
            email: 'alice@example.com',
            transactions: [
                ['amount' => '100.00', 'balance_after' => '100.00', 'at' => '-2 days'],
            ],
        );

        $this->seedUser(
            name: 'Bob Example',
            email: 'bob@example.com',
            transactions: [
                ['amount' => '600.00', 'balance_after' => '600.00', 'at' => '-5 days'],
                ['amount' => '-100.00', 'balance_after' => '500.00', 'at' => '-1 day'],
            ],
        );

        $this->seedUser(
            name: 'Charlie Example',
            email: 'charlie@example.com',
            transactions: [
                ['amount' => '50.00', 'balance_after' => '50.00', 'at' => '-3 days'],
                ['amount' => '-50.00', 'balance_after' => '0.00', 'at' => '-1 day'],
            ],
        );
    }

    /**
     * @param  list<array{amount: string, balance_after: string, at: string}>  $transactions
     */
    private function seedUser(string $name, string $email, array $transactions): void
    {
        $user = User::factory()->create([
            'name' => $name,
            'email' => $email,
        ]);

        $account = Account::factory()->for($user)->empty()->create();

        foreach ($transactions as $transaction) {
            Transaction::factory()
                ->forAccount($account)
                ->create([
                    'amount' => $transaction['amount'],
                    'balance_after' => $transaction['balance_after'],
                    'created_at' => Carbon::parse($transaction['at']),
                ]);
        }

        $last = end($transactions);

        $account->update([
            'balance' => $last['balance_after'],
        ]);
    }
}
