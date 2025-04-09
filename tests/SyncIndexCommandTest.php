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

    public function testSyncIndexCommandExecutes()
    {
        $this->artisan('manticore:sync-index', [
            'model' => 'App\\Models\\FakeModel'
        ])->assertExitCode(0);
    }
}
