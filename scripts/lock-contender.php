<?php

require __DIR__.'/../vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$accountId = (int) ($argv[1] ?? 0);
$counterKey = (string) ($argv[2] ?? '');
$lockPrefix = (string) ($argv[3] ?? '');
$fencePrefix = (string) ($argv[4] ?? '');

if ($accountId <= 0 || $counterKey === '') {
    fwrite(STDERR, "Invalid contender arguments.\n");
    exit(1);
}

if ($lockPrefix !== '') {
    config(['account.lock.prefix' => $lockPrefix]);
}

if ($fencePrefix !== '') {
    config(['account.lock.fence_prefix' => $fencePrefix]);
}

$manager = $app->make(\App\Contracts\AccountLockManager::class);
$redis = $app->make('redis')->connection();

$manager->withAccountLock($accountId, function () use ($redis, $counterKey): void {
    $value = (int) $redis->get($counterKey);
    usleep(50_000);
    $redis->set($counterKey, $value + 1);
});

exit(0);
