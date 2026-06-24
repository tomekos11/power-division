<?php

namespace Tests\Feature;

use App\Contracts\AccountLockManager;
use App\Exceptions\AccountLockTimeoutException;
use App\Exceptions\AccountLockUnavailableException;
use App\Services\RedisAccountLockManager;
use Illuminate\Redis\Connections\PhpRedisConnection;
use InvalidArgumentException;
use Symfony\Component\Process\Process;
use Tests\Concerns\CleansAccountLockRedisKeys;
use Tests\TestCase;

class AccountLockManagerTest extends TestCase
{
    use CleansAccountLockRedisKeys;

    private AccountLockManager $lockManager;

    private PhpRedisConnection $redis;

    /** @var list<int> */
    private array $testAccountIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        if (! $this->redisIsAvailable()) {
            $this->markTestSkipped('Redis is not available.');
        }

        $this->configureTestLockPrefixes();

        $this->testAccountIds = range(1, 13);

        $this->redis = $this->app->make('redis')->connection();
        $this->cleanupAccountLockRedisKeys();

        $this->lockManager = $this->app->make(AccountLockManager::class);
    }

    protected function tearDown(): void
    {
        $this->cleanupAccountLockRedisKeys();

        parent::tearDown();
    }

    public function test_runs_callback_and_returns_its_result(): void
    {
        $result = $this->lockManager->withAccountLock(
            $this->accountId(1),
            fn (int $fenceToken): string => 'ok',
        );

        $this->assertSame('ok', $result);
    }

    public function test_lock_can_be_reacquired_after_previous_scope_ends(): void
    {
        $accountId = $this->accountId(2);

        $this->lockManager->withAccountLock($accountId, fn (int $fenceToken): null => null);

        $result = $this->lockManager->withAccountLock(
            $accountId,
            fn (int $fenceToken): string => 'second',
        );

        $this->assertSame('second', $result);
    }

    public function test_different_accounts_can_be_locked_at_the_same_time(): void
    {
        $this->lockManager->withAccountLock($this->accountId(3), function (): void {
            $result = $this->lockManager->withAccountLock(
                $this->accountId(4),
                fn (int $fenceToken): string => 'other-account',
            );

            $this->assertSame('other-account', $result);
        });
    }

    public function test_concurrent_lock_for_same_account_fails_when_wait_timeout_is_zero(): void
    {
        $this->expectException(AccountLockUnavailableException::class);

        $accountId = $this->accountId(5);

        $this->lockManager->withAccountLock($accountId, function () use ($accountId): void {
            $this->lockManager->withAccountLock(
                $accountId,
                fn (int $fenceToken): null => null,
                waitSeconds: 0,
            );
        });
    }

    public function test_rejects_negative_wait_seconds(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->lockManager->withAccountLock(
            $this->accountId(6),
            fn (int $fenceToken): null => null,
            waitSeconds: -1,
        );
    }

    public function test_fence_token_is_monotonic_per_account(): void
    {
        $accountId = $this->accountId(7);

        $first = $this->lockManager->withAccountLock($accountId, fn (int $fenceToken): int => $fenceToken);
        $second = $this->lockManager->withAccountLock($accountId, fn (int $fenceToken): int => $fenceToken);

        $this->assertGreaterThan(0, $first);
        $this->assertGreaterThan($first, $second);
    }

    public function test_lock_ttl_outlives_the_callback_deadline(): void
    {
        $this->configureLockManager([
            'account.lock.max_operation_seconds' => 30,
            'account.lock.ttl_buffer_seconds' => 5,
        ]);

        $accountId = $this->accountId(8);
        $key = $this->lockKey($accountId);

        $this->lockManager->withAccountLock($accountId, function () use ($key): void {
            $ttl = (int) $this->redis->ttl($key);

            $this->assertGreaterThan(30, $ttl);
            $this->assertLessThanOrEqual(35, $ttl);
        });
    }

    public function test_callback_exceeding_deadline_throws_timeout(): void
    {
        if (! extension_loaded('pcntl')) {
            $this->markTestSkipped('pcntl is required to enforce the callback deadline.');
        }

        $this->configureLockManager([
            'account.lock.max_operation_seconds' => 1,
            'account.lock.ttl_buffer_seconds' => 5,
        ]);

        $this->expectException(AccountLockTimeoutException::class);

        $this->lockManager->withAccountLock($this->accountId(9), function (): void {
            sleep(3);
        });
    }

    public function test_lock_is_released_after_deadline_timeout(): void
    {
        if (! extension_loaded('pcntl')) {
            $this->markTestSkipped('pcntl is required to enforce the callback deadline.');
        }

        $this->configureLockManager([
            'account.lock.max_operation_seconds' => 1,
            'account.lock.ttl_buffer_seconds' => 5,
        ]);

        $accountId = $this->accountId(10);
        $key = $this->lockKey($accountId);

        try {
            $this->lockManager->withAccountLock($accountId, fn () => sleep(3));
        } catch (AccountLockTimeoutException) {
        }

        $this->assertFalse((bool) $this->redis->exists($key), 'Lock must be released after a timeout.');
    }

    public function test_mutual_exclusion_across_real_processes(): void
    {
        $script = base_path('scripts/lock-contender.php');

        if (! is_file($script)) {
            $this->markTestSkipped('Lock contender script is missing.');
        }

        $accountId = $this->accountId(11);
        $counterKey = $this->fencePrefix.':counter:'.$accountId;
        $this->redis->set($counterKey, 0);

        $contenders = 5;
        $processes = [];

        for ($i = 0; $i < $contenders; $i++) {
            $process = new Process([
                'php',
                $script,
                (string) $accountId,
                $counterKey,
                $this->lockPrefix,
                $this->fencePrefix,
            ], base_path());
            $process->setTimeout(60);
            $process->start();
            $processes[] = $process;
        }

        foreach ($processes as $process) {
            $process->wait();
            $this->assertSame(
                0,
                $process->getExitCode(),
                'Contender failed: '.$process->getErrorOutput(),
            );
        }

        $this->assertSame(
            $contenders,
            (int) $this->redis->get($counterKey),
            'Lost update detected: the lock did not serialize concurrent writers.',
        );

        $this->redis->del($counterKey);
    }

    private function configureLockManager(array $config): void
    {
        config($config);

        $this->app->forgetInstance(AccountLockManager::class);
        $this->app->forgetInstance(RedisAccountLockManager::class);

        $this->lockManager = $this->app->make(AccountLockManager::class);
    }

    private function accountId(int $offset): int
    {
        return $this->testAccountIds[$offset - 1];
    }

    private function lockKey(int $accountId): string
    {
        return $this->lockPrefix.':'.$accountId;
    }

    private function redisIsAvailable(): bool
    {
        try {
            return (bool) $this->app->make('redis')->connection()->ping();
        } catch (\Throwable) {
            return false;
        }
    }
}
