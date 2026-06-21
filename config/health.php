<?php

use Spatie\Health\ResultStores\InMemoryHealthResultStore;

return [

    /*
    |--------------------------------------------------------------------------
    | Health check endpoint
    |--------------------------------------------------------------------------
    */

    'route' => [
        'enabled' => env('HEALTH_ROUTE_ENABLED', true),
        'path' => env('HEALTH_ROUTE_PATH', 'api/health'),
        'middleware' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Health check results
    |--------------------------------------------------------------------------
    */

    'result_stores' => [
        InMemoryHealthResultStore::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    'notifications' => [
        'enabled' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Oh Dear
    |--------------------------------------------------------------------------
    */

    'oh_dear_endpoint' => [
        'enabled' => false,
        'always_send_fresh_results' => true,
        'secret' => env('OH_DEAR_HEALTH_CHECK_SECRET'),
        'url' => '/oh-dear-health-check-results',
    ],

    'json_results_failure_status' => 503,

    /*
    |--------------------------------------------------------------------------
    | Theme
    |--------------------------------------------------------------------------
    */

    'theme' => 'light',

];
