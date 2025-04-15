<?php

namespace Tests;

use Manticoresearch\Client;
use Manticoresearch\Tables;
use Orchestra\Testbench\TestCase;
use Ritey\LaravelManticore\ManticoreServiceProvider;

/**
 * @internal
 *
 * @coversNothing
 */
class CreateIndexCommandTest extends TestCase
{
    public function testCreateIndexCommandExecutes()
    {
        // Mock the Manticore client to avoid real HTTP request
        $this->app->bind(Client::class, function () {
            $mockTables = $this->createMock(Tables::class);
            $mockTables->method('create')->willReturn(true);

            $mockClient = $this->createMock(Client::class);
            $mockClient->method('tables')->willReturn($mockTables);

            return $mockClient;
        });

        $this->artisan('manticore:create-index', [
            'model' => 'App\Models\FakeModel',
            '--fields' => 'title:text',
        ])->assertExitCode(0);
    }

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
}
