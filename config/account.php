<?php

return [

    'lock' => [
        'prefix' => env('ACCOUNT_LOCK_PREFIX', 'account-lock'),
        'fence_prefix' => env('ACCOUNT_LOCK_FENCE_PREFIX', 'account-lock-fence'),
        'max_operation_seconds' => (int) env('ACCOUNT_LOCK_MAX_OPERATION', 30),
        'ttl_buffer_seconds' => (int) env('ACCOUNT_LOCK_TTL_BUFFER', 5),
        'wait_seconds' => (int) env('ACCOUNT_LOCK_WAIT', 10),
        'retry_interval_ms' => (int) env('ACCOUNT_LOCK_RETRY_MS', 100),
    ],

    'payment_simulation_seconds' => (int) env('ACCOUNT_PAYMENT_SIMULATION_SECONDS', 5),

];
