<?php

use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\TestCase;

class SyncIndexCommandTest extends TestCase
{
    public function testSyncIndexCommandExecutes()
    {
        $exitCode = Artisan::call('manticore:sync-index', [
            'model' => 'App\\Models\\FakeModel'
        ]);
        $this->assertIsInt($exitCode);
    }
}
