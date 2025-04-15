<?php

/**
 * Laravel Manticore Scout
 * (c) Ritey, MIT License.
 */

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
class ListIndexesCommandTest extends TestCase
{
    public function testListIndexesCommandExecutes()
    {
        // Mock the Manticore Client and Tables
        $this->app->bind(Client::class, function () {
            $mockTables = $this->createMock(Tables::class);
            $mockTables->method('status')->willReturn([
                ['index' => 'posts_index'],
                ['index' => 'users_index'],
            ]);

            $mockClient = $this->createMock(Client::class);
            $mockClient->method('tables')->willReturn($mockTables);

            return $mockClient;
        });

        $this->artisan('manticore:list-indexes')
            ->expectsOutput('- posts_index')
            ->expectsOutput('- users_index')
            ->assertExitCode(0)
        ;
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
