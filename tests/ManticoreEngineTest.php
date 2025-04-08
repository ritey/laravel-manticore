<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Laravel\Scout\Builder;
use PHPUnit\Framework\TestCase;
use Ritey\LaravelManticore\ManticoreEngine;
use Manticoresearch\Client;

class DummyModel extends Model {
    public function getKey() { return 1; }
    public function searchableAs() { return 'dummy_index'; }
    public function toSearchableArray() { return ['field' => 'value']; }
}

class ManticoreEngineTest extends TestCase
{
    protected $client;
    protected $engine;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->engine = new ManticoreEngine($this->client);
    }

    public function testUpdate()
    {
        $index = $this->createMock(\stdClass::class);
        $index->expects($this->once())->method('addDocuments');
        $this->client->method('index')->willReturn($index);
        $this->engine->update([new DummyModel()]);
        $this->assertTrue(true); // just ensure no exception thrown
    }

    public function testDelete()
    {
        $index = $this->createMock(\stdClass::class);
        $index->expects($this->once())->method('deleteDocument');
        $this->client->method('index')->willReturn($index);
        $this->engine->delete([new DummyModel()]);
        $this->assertTrue(true);
    }

    public function testMapIds()
    {
        $results = ['hits' => ['hits' => [['_id' => 1], ['_id' => 2]]]];
        $this->assertEquals([1, 2], $this->engine->mapIds($results)->toArray());
    }

    public function testGetTotalCount()
    {
        $results = ['hits' => ['total' => ['value' => 42]]];
        $this->assertEquals(42, $this->engine->getTotalCount($results));
    }
}
