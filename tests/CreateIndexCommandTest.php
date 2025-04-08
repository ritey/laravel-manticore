<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\TestCase;

class CreateIndexCommandTest extends TestCase
{
    public function testCreateIndexCommandExecutes()
    {
        $exitCode = Artisan::call('manticore:create-index', [
            'model' => 'App\\Models\\FakeModel'
        ]);
        $this->assertIsInt($exitCode);
    }
}
