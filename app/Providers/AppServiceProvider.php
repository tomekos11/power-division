<?php

namespace App\Providers;

use App\Contracts\AccountLockManager;
use App\Contracts\Repositories\AccountRepository;
use App\Repositories\DatabaseAccountRepository;
use App\Services\AccountTransactionService;
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

        $this->app->singleton(AccountRepository::class, DatabaseAccountRepository::class);

        $this->app->when(AccountTransactionService::class)
            ->needs('$paymentSimulationSeconds')
            ->give(fn () => (int) config('account.payment_simulation_seconds', 5));
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
