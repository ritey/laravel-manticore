<?php

use Illuminate\Support\Facades\Log;
use Orchestra\Testbench\TestCase;
use Ritey\LaravelManticore\ManticoreServiceProvider;

class ServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [ManticoreServiceProvider::class];
    }

    public function testClientIsBound()
    {
        $this->assertTrue($this->app->bound(Manticoresearch\Client::class));
    }

    public function testBootRegistersEngine()
    {
        $engine = resolve(\Laravel\Scout\EngineManager::class)->engine('manticore');
        $this->assertInstanceOf(\Ritey\LaravelManticore\ManticoreEngine::class, $engine);
    }
}
