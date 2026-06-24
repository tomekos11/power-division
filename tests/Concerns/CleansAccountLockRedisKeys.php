<?php

namespace Tests\Concerns;

use Illuminate\Redis\Connections\PhpRedisConnection;

trait CleansAccountLockRedisKeys
{
    protected string $lockPrefix = '';

    protected string $fencePrefix = '';

    protected function configureTestLockPrefixes(): void
    {
        $this->lockPrefix = 'account-lock-test-'.getmypid();
        $this->fencePrefix = 'account-lock-fence-test-'.getmypid();

        config([
            'account.lock.prefix' => $this->lockPrefix,
            'account.lock.fence_prefix' => $this->fencePrefix,
        ]);
    }

    protected function cleanupAccountLockRedisKeys(): void
    {
        if ($this->lockPrefix === '' && $this->fencePrefix === '') {
            return;
        }

        try {
            $redis = $this->app->make('redis')->connection();
        } catch (\Throwable) {
            return;
        }

        $this->deleteRedisKeysByPattern($redis, $this->lockPrefix.'*');
        $this->deleteRedisKeysByPattern($redis, $this->fencePrefix.'*');
    }

    protected function deleteRedisKeysByPattern(PhpRedisConnection $redis, string $pattern): void
    {
        $client = $redis->client();
        $prefix = (string) config('database.redis.options.prefix');
        $keys = $client->keys($pattern);

        if (! is_array($keys) || $keys === []) {
            return;
        }

        foreach ($keys as $key) {
            $logicalKey = str_starts_with($key, $prefix)
                ? substr($key, strlen($prefix))
                : $key;

            $redis->del($logicalKey);
        }
    }
}
