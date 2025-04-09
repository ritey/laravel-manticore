<?php

namespace Tests;

use Orchestra\Testbench\TestCase;
use Ritey\LaravelManticore\ManticoreServiceProvider;
use Laravel\Scout\EngineManager;
use Ritey\LaravelManticore\ManticoreEngine;

class ServiceProviderTest extends TestCase
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

    public function testBootRegistersEngine()
    {
        $this->app->make('config')->set('scout.driver', 'manticore');
        $this->app->make(ManticoreServiceProvider::class)->boot();
        $engine = resolve(EngineManager::class)->engine('manticore');
        $this->assertInstanceOf(ManticoreEngine::class, $engine);
    }
}
