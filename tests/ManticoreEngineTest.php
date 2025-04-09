<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Ritey\LaravelManticore\ManticoreEngine;
use Manticoresearch\Client;

class ManticoreEngineTest extends TestCase
{
    public function testUpdate()
    {
        $mockIndex = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['addDocuments'])
            ->getMock();

        $mockIndex->expects($this->once())->method('addDocuments');

        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client->method('index')->willReturn($mockIndex);

        $model = new class {
            public function getKey() { return 1; }
            public function searchableAs() { return 'test_index'; }
            public function toSearchableArray() { return ['field' => 'value']; }
        };

        $engine = new ManticoreEngine($client);
        $engine->update([$model]);

        $this->assertTrue(true);
    }

    public function testDelete()
    {
        $mockIndex = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['deleteDocument'])
            ->getMock();

        $mockIndex->expects($this->once())->method('deleteDocument');

        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client->method('index')->willReturn($mockIndex);

        $model = new class {
            public function getKey() { return 1; }
            public function searchableAs() { return 'test_index'; }
        };

        $engine = new ManticoreEngine($client);
        $engine->delete([$model]);

        $this->assertTrue(true);
    }

    public function testGetTotalCount()
    {
        $client = $this->createMock(Client::class);
        $engine = new ManticoreEngine($client);

        $this->assertEquals(42, $engine->getTotalCount(['hits' => ['total' => ['value' => 42]]]));
    }
}
