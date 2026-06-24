<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * @property Application $app
 */
abstract class TestCase extends BaseTestCase
{
    private const string TEST_DATABASE = 'power_division_test';

    public function createApplication(): Application
    {
        $app = parent::createApplication();

        $app['config']->set('database.default', 'pgsql');
        $app['config']->set('database.connections.pgsql.database', self::TEST_DATABASE);

        return $app;
    }
}
