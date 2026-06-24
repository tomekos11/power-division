<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CleansAccountLockRedisKeys;
use Tests\TestCase;

class AccountTransactionTest extends TestCase
{
    use CleansAccountLockRedisKeys;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! $this->redisIsAvailable()) {
            $this->markTestSkipped('Redis is not available.');
        }

        $this->configureTestLockPrefixes();
        config(['account.payment_simulation_seconds' => 0]);
        $this->cleanupAccountLockRedisKeys();
    }

    protected function tearDown(): void
    {
        $this->cleanupAccountLockRedisKeys();

        parent::tearDown();
    }

    public function test_credit_increases_balance(): void
    {
        $user = User::factory()->create();
        Account::factory()->for($user)->withBalance('100.00')->create();

        $response = $this->postJson("/api/users/{$user->id}/transactions", [
            'amount' => '50.00',
        ]);

        $response->assertOk()
            ->assertJson([
                'user_id' => $user->id,
                'balance' => '150.00',
            ])
            ->assertJsonStructure(['last_transaction_at']);

        $this->assertDatabaseHas('transactions', [
            'amount' => '50.00',
            'balance_after' => '150.00',
        ]);
    }

    public function test_debit_decreases_balance(): void
    {
        $user = User::factory()->create();
        Account::factory()->for($user)->withBalance('100.00')->create();

        $response = $this->postJson("/api/users/{$user->id}/transactions", [
            'amount' => '-25.50',
        ]);

        $response->assertOk()
            ->assertJson([
                'user_id' => $user->id,
                'balance' => '74.50',
            ]);
    }

    public function test_debit_below_zero_returns_unprocessable(): void
    {
        $user = User::factory()->create();
        Account::factory()->for($user)->withBalance('10.00')->create();

        $response = $this->postJson("/api/users/{$user->id}/transactions", [
            'amount' => '-10.01',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('message', fn (string $message) => str_contains($message, 'Insufficient balance'));
    }

    public function test_unknown_user_returns_not_found(): void
    {
        $response = $this->postJson('/api/users/99999/transactions', [
            'amount' => '10.00',
        ]);

        $response->assertNotFound();
    }

    public function test_zero_amount_is_rejected_by_validation(): void
    {
        $user = User::factory()->create();
        Account::factory()->for($user)->create();

        $response = $this->postJson("/api/users/{$user->id}/transactions", [
            'amount' => '0',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['amount']);
    }

    private function redisIsAvailable(): bool
    {
        try {
            $this->app->make('redis')->connection()->ping();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
