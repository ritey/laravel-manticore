<?php

namespace Tests;

use Laravel\Scout\EngineManager;
use Orchestra\Testbench\TestCase;
use Ritey\LaravelManticore\ManticoreEngine;
use Ritey\LaravelManticore\ManticoreServiceProvider;

/**
 * @internal
 *
 * @coversNothing
 */
class ServiceProviderTest extends TestCase
{
    public function testBootRegistersEngine()
    {
        $engine = resolve(EngineManager::class)->engine('manticore');
        $this->assertInstanceOf(ManticoreEngine::class, $engine);
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
