<?php

namespace App\Services;

use App\Contracts\AccountLockManager;
use App\Exceptions\AccountLockTimeoutException;
use App\Exceptions\AccountLockUnavailableException;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Redis\Connections\PhpRedisConnection;
use InvalidArgumentException;
use Throwable;

final class RedisAccountLockManager implements AccountLockManager
{
    private const ACQUIRE_SCRIPT = <<<'LUA'
        if redis.call('EXISTS', KEYS[1]) == 1 then
            return -1
        end

        local token = redis.call('INCR', KEYS[2])
        redis.call('SET', KEYS[1], token, 'EX', ARGV[1])

        return token
        LUA;

    private const RELEASE_SCRIPT = <<<'LUA'
        if redis.call('GET', KEYS[1]) == ARGV[1] then
            return redis.call('DEL', KEYS[1])
        end

        return 0
        LUA;

    private readonly string $prefix;

    private readonly string $fencePrefix;

    private readonly int $maxOperationSeconds;

    private readonly int $lockTtlSeconds;

    private readonly int $waitSeconds;

    private readonly int $retryIntervalMs;

    public function __construct(
        private readonly PhpRedisConnection $redis,
        private readonly ExceptionHandler $exceptions,
    ) {
        $this->prefix = (string) config('account.lock.prefix', 'account-lock');
        $this->fencePrefix = (string) config('account.lock.fence_prefix', 'account-lock-fence');
        $this->maxOperationSeconds = max(1, (int) config('account.lock.max_operation_seconds', 30));
        $ttlBuffer = max(1, (int) config('account.lock.ttl_buffer_seconds', 5));
        $this->lockTtlSeconds = $this->maxOperationSeconds + $ttlBuffer;
        $this->waitSeconds = (int) config('account.lock.wait_seconds', 10);
        $this->retryIntervalMs = max(1, (int) config('account.lock.retry_interval_ms', 100));
    }

    public function withAccountLock(int $accountId, callable $callback, ?int $waitSeconds = null): mixed
    {
        $waitSeconds ??= $this->waitSeconds;

        if ($waitSeconds < 0) {
            throw new InvalidArgumentException('Account lock waitSeconds must be zero or greater.');
        }

        $lockKey = $this->lockKey($accountId);
        $fenceToken = $this->acquire($lockKey, $this->fenceKey($accountId), $waitSeconds);

        if ($fenceToken === null) {
            throw new AccountLockUnavailableException($accountId);
        }

        try {
            return $this->runWithDeadline($accountId, fn () => $callback($fenceToken));
        } finally {
            $this->release($lockKey, (string) $fenceToken);
        }
    }

    private function acquire(string $lockKey, string $fenceKey, int $waitSeconds): ?int
    {
        $deadline = microtime(true) + $waitSeconds;

        do {
            $token = (int) $this->redis->eval(
                self::ACQUIRE_SCRIPT,
                2,
                $lockKey,
                $fenceKey,
                (string) $this->lockTtlSeconds,
            );

            if ($token > 0) {
                return $token;
            }

            if (microtime(true) >= $deadline) {
                return null;
            }

            usleep($this->retryIntervalMs * 1000);
        } while (true);
    }

    private function runWithDeadline(int $accountId, callable $callback): mixed
    {
        if (! extension_loaded('pcntl')) {
            return $callback();
        }

        pcntl_async_signals(true);
        $previousHandler = pcntl_signal_get_handler(SIGALRM);

        pcntl_signal(SIGALRM, function () use ($accountId): void {
            throw new AccountLockTimeoutException($accountId, $this->maxOperationSeconds);
        });

        pcntl_alarm($this->maxOperationSeconds);

        try {
            return $callback();
        } finally {
            pcntl_alarm(0);
            pcntl_signal(SIGALRM, $previousHandler);
        }
    }

    private function release(string $lockKey, string $token): void
    {
        try {
            $this->redis->eval(self::RELEASE_SCRIPT, 1, $lockKey, $token);
        } catch (Throwable $exception) {
            $this->exceptions->report($exception);
        }
    }

    private function lockKey(int $accountId): string
    {
        return "{$this->prefix}:{$accountId}";
    }

    private function fenceKey(int $accountId): string
    {
        return "{$this->fencePrefix}:{$accountId}";
    }
}
