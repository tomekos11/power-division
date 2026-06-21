<?php

use Knuckles\Scribe\Extracting\Strategies\BodyParameters\GetFromBodyParamAttribute;
use Knuckles\Scribe\Extracting\Strategies\BodyParameters\GetFromBodyParamTag;
use Knuckles\Scribe\Extracting\Strategies\Headers\GetFromHeaderAttribute;
use Knuckles\Scribe\Extracting\Strategies\Headers\GetFromHeaderTag;
use Knuckles\Scribe\Extracting\Strategies\Metadata\GetFromDocBlocks;
use Knuckles\Scribe\Extracting\Strategies\QueryParameters\GetFromFormRequest;
use Knuckles\Scribe\Extracting\Strategies\QueryParameters\GetFromInlineValidator;
use Knuckles\Scribe\Extracting\Strategies\QueryParameters\GetFromQueryParamAttribute;
use Knuckles\Scribe\Extracting\Strategies\QueryParameters\GetFromQueryParamTag;
use Knuckles\Scribe\Extracting\Strategies\ResponseFields\GetFromResponseFieldAttribute;
use Knuckles\Scribe\Extracting\Strategies\ResponseFields\GetFromResponseFieldTag;
use Knuckles\Scribe\Extracting\Strategies\Responses\ResponseCalls;
use Knuckles\Scribe\Extracting\Strategies\Responses\UseApiResourceTags;
use Knuckles\Scribe\Extracting\Strategies\Responses\UseResponseAttributes;
use Knuckles\Scribe\Extracting\Strategies\Responses\UseResponseFileTag;
use Knuckles\Scribe\Extracting\Strategies\Responses\UseResponseTag;
use Knuckles\Scribe\Extracting\Strategies\UrlParameters\GetFromUrlParamAttribute;
use Knuckles\Scribe\Extracting\Strategies\UrlParameters\GetFromUrlParamTag;
use Knuckles\Scribe\Matching\RouteMatcher;

return [

    'theme' => 'default',

    'title' => env('APP_NAME', 'Power Division').' API Documentation',

    'description' => 'API documentation for account balance operations.',

    'base_url' => env('APP_URL', 'http://localhost:8080'),

    'routes' => [
        [
            'match' => [
                'prefixes' => ['api/*'],
                'domains' => ['*'],
            ],
            'include' => [],
            'exclude' => [
                'api/health',
            ],
        ],
    ],

    'type' => 'static',

    'static' => [
        'output_path' => 'public/docs',
    ],

    'laravel' => [
        'add_routes' => true,
        'docs_url' => '/docs',
        'assets_directory' => null,
        'middleware' => [],
    ],

    'try_it_out' => [
        'enabled' => true,
        'base_url' => null,
        'use_csrf' => false,
        'csrf_url' => '/sanctum/csrf-cookie',
    ],

    'auth' => [
        'enabled' => false,
        'default' => false,
        'in' => 'bearer',
        'name' => 'Authorization',
        'use_value' => env('SCRIBE_AUTH_KEY'),
        'placeholder' => '{YOUR_AUTH_KEY}',
        'extra_info' => 'You can retrieve your token by visiting your dashboard and clicking <b>Generate API token</b>.',
    ],

    'intro_text' => <<<'INTRO'
This documentation describes the Power Division account API.

Use the endpoints below to credit or debit user account balances.
INTRO,

    'example_languages' => [
        'bash',
        'javascript',
    ],

    'postman' => [
        'enabled' => true,
        'overrides' => [],
    ],

    'openapi' => [
        'enabled' => true,
        'overrides' => [],
        'generators' => [],
    ],

    'groups' => [
        'default' => 'Endpoints',
        'order' => [],
    ],

    'logo' => false,

    'last_updated' => 'Last updated: {date:F j, Y}',

    'examples' => [
        'faker_seed' => 1234,
        'models_source' => ['factoryCreate', 'factoryMake', 'databaseFirst'],
    ],

    'strategies' => [
        'metadata' => [
            GetFromDocBlocks::class,
        ],
        'urlParameters' => [
            GetFromUrlParamAttribute::class,
            GetFromUrlParamTag::class,
        ],
        'queryParameters' => [
            GetFromFormRequest::class,
            GetFromInlineValidator::class,
            GetFromQueryParamAttribute::class,
            GetFromQueryParamTag::class,
        ],
        'headers' => [
            GetFromHeaderAttribute::class,
            GetFromHeaderTag::class,
        ],
        'bodyParameters' => [
            Knuckles\Scribe\Extracting\Strategies\BodyParameters\GetFromFormRequest::class,
            Knuckles\Scribe\Extracting\Strategies\BodyParameters\GetFromInlineValidator::class,
            GetFromBodyParamAttribute::class,
            GetFromBodyParamTag::class,
        ],
        'responses' => [
            UseResponseAttributes::class,
            UseApiResourceTags::class,
            UseResponseTag::class,
            UseResponseFileTag::class,
            ResponseCalls::class,
        ],
        'responseFields' => [
            GetFromResponseFieldAttribute::class,
            GetFromResponseFieldTag::class,
        ],
    ],

    'database_connections_to_transact' => [config('database.default')],

    'fractal' => [
        'serializer' => null,
    ],

    'routeMatcher' => RouteMatcher::class,

];
