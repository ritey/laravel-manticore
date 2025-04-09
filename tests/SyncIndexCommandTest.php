<?php

namespace Tests;

use Orchestra\Testbench\TestCase;
use Ritey\LaravelManticore\ManticoreServiceProvider;

class SyncIndexCommandTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [ManticoreServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('scout.driver', 'manticore');
        $app['config']->set('laravel_manticore.host', '127.0.0.1');
        $app['config']->set('laravel_manticore.port', 9308);
    }

    public function testSyncIndexCommandExecutes()
    {
        $this->artisan('manticore:sync-index', [
            'model' => 'App\\Models\\FakeModel'
        ])->assertExitCode(0);
    }
}
