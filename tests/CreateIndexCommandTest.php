<?php

namespace Tests;

use Orchestra\Testbench\TestCase;
use Ritey\LaravelManticore\ManticoreServiceProvider;

class CreateIndexCommandTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [ManticoreServiceProvider::class];
    }

    public function testCreateIndexCommandExecutes()
    {
        $this->artisan('manticore:create-index', [
            'model' => 'App\\Models\\FakeModel'
        ])->assertExitCode(0);
    }
}
