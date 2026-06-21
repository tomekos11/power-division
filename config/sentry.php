<?php

return [

    'dsn' => env('SENTRY_LARAVEL_DSN', env('SENTRY_DSN')),

    'release' => env('SENTRY_RELEASE'),

    'environment' => env('SENTRY_ENVIRONMENT', env('APP_ENV', 'production')),

    'sample_rate' => env('SENTRY_SAMPLE_RATE') !== null
        ? (float) env('SENTRY_SAMPLE_RATE')
        : 1.0,

    'traces_sample_rate' => env('SENTRY_TRACES_SAMPLE_RATE') !== null
        ? (float) env('SENTRY_TRACES_SAMPLE_RATE')
        : null,

    'profiles_sample_rate' => env('SENTRY_PROFILES_SAMPLE_RATE') !== null
        ? (float) env('SENTRY_PROFILES_SAMPLE_RATE')
        : null,

    'send_default_pii' => filter_var(env('SENTRY_SEND_DEFAULT_PII', false), FILTER_VALIDATE_BOOL),

    'ignore_exceptions' => [],

    'breadcrumbs' => [
        'logs' => filter_var(env('SENTRY_BREADCRUMBS_LOGS', true), FILTER_VALIDATE_BOOL),
        'cache' => filter_var(env('SENTRY_BREADCRUMBS_CACHE', true), FILTER_VALIDATE_BOOL),
        'livewire' => filter_var(env('SENTRY_BREADCRUMBS_LIVEWIRE', true), FILTER_VALIDATE_BOOL),
        'sql_queries' => filter_var(env('SENTRY_BREADCRUMBS_SQL_QUERIES', true), FILTER_VALIDATE_BOOL),
        'sql_bindings' => filter_var(env('SENTRY_BREADCRUMBS_SQL_BINDINGS', false), FILTER_VALIDATE_BOOL),
        'queue_info' => filter_var(env('SENTRY_BREADCRUMBS_QUEUE_INFO', true), FILTER_VALIDATE_BOOL),
        'command_info' => filter_var(env('SENTRY_BREADCRUMBS_COMMAND_INFO', true), FILTER_VALIDATE_BOOL),
        'http_client_requests' => filter_var(env('SENTRY_BREADCRUMBS_HTTP_CLIENT_REQUESTS', true), FILTER_VALIDATE_BOOL),
        'notifications' => filter_var(env('SENTRY_BREADCRUMBS_NOTIFICATIONS', true), FILTER_VALIDATE_BOOL),
    ],

];
