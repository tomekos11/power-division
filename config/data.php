<?php

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Normalizers\ArrayableNormalizer;
use Spatie\LaravelData\Normalizers\ArrayNormalizer;
use Spatie\LaravelData\Normalizers\FormRequestNormalizer;
use Spatie\LaravelData\Normalizers\JsonNormalizer;
use Spatie\LaravelData\Normalizers\ModelNormalizer;
use Spatie\LaravelData\Normalizers\ObjectNormalizer;
use Spatie\LaravelData\RuleInferrers\AttributesRuleInferrer;
use Spatie\LaravelData\RuleInferrers\BuiltInTypesRuleInferrer;
use Spatie\LaravelData\RuleInferrers\NullableRuleInferrer;
use Spatie\LaravelData\RuleInferrers\RequiredRuleInferrer;
use Spatie\LaravelData\RuleInferrers\SometimesRuleInferrer;

return [

    /*
    |--------------------------------------------------------------------------
    | Date format
    |--------------------------------------------------------------------------
    |
    | Global date format used when transforming Data objects to arrays/JSON.
    |
    */

    'date_format' => DATE_ATOM,

    /*
    |--------------------------------------------------------------------------
    | Date timezone
    |--------------------------------------------------------------------------
    |
    | Global timezone used when transforming Data objects to arrays/JSON.
    |
    */

    'date_timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    */

    'features' => [
        'cast_and_transform_iterables' => true,
        'ignore_invalid_partials' => false,
        'max_transformation_depth' => null,
        'throw_when_max_transformation_depth_reached' => true,
        'wrap_execution_state_in_data_objects' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rule inferrers
    |--------------------------------------------------------------------------
    |
    | Rule inferrers are used to infer validation rules from typed properties.
    |
    */

    'rule_inferrers' => [
        SometimesRuleInferrer::class,
        NullableRuleInferrer::class,
        RequiredRuleInferrer::class,
        BuiltInTypesRuleInferrer::class,
        AttributesRuleInferrer::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Normalizers
    |--------------------------------------------------------------------------
    */

    'normalizers' => [
        ModelNormalizer::class,
        FormRequestNormalizer::class,
        ArrayableNormalizer::class,
        ObjectNormalizer::class,
        ArrayNormalizer::class,
        JsonNormalizer::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Data objects
    |--------------------------------------------------------------------------
    */

    'data' => [
        Data::class => [
            'include_partials' => false,
        ],
    ],

];
