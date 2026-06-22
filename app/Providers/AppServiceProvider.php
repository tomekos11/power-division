<?php

namespace App\Providers;

use App\Contracts\AccountLockManager;
use App\Services\RedisAccountLockManager;
use Illuminate\Redis\Connections\PhpRedisConnection;
use Illuminate\Support\ServiceProvider;
use Spatie\Health\Checks\Checks\CacheCheck;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\RedisCheck;
use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;
use Spatie\Health\Health;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->when(RedisAccountLockManager::class)
            ->needs(PhpRedisConnection::class)
            ->give(fn ($app) => $app->make('redis')->connection());

        $this->app->singleton(AccountLockManager::class, RedisAccountLockManager::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! class_exists(Health::class)) {
            return;
        }

        $this->app->make(Health::class)->checks([
            DatabaseCheck::new(),
            CacheCheck::new(),
            RedisCheck::new(),
            UsedDiskSpaceCheck::new()
                ->warnWhenUsedSpaceIsAbovePercentage(70)
                ->failWhenUsedSpaceIsAbovePercentage(90),
        ]);
    }
}
