<?php

/**
 * Laravel Manticore Scout
 * (c) Ritey, MIT License.
 */

namespace Tests;

use Manticoresearch\Client;
use Manticoresearch\Table;
use PHPUnit\Framework\TestCase;
use Ritey\LaravelManticore\ManticoreEngine;

/**
 * @internal
 *
 * @coversNothing
 */
class ManticoreEngineTest extends TestCase
{
    public function testUpdate()
    {
        $mockIndex = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addDocuments'])
            ->getMock()
        ;

        $mockIndex->expects($this->once())->method('addDocuments');

        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['table'])
            ->getMock()
        ;

        $client->method('table')->willReturn($mockIndex);

        $model = new class {
            public function getKey()
            {
                return 1;
            }

            public function searchableAs()
            {
                return 'test_index';
            }

            public function toSearchableArray()
            {
                return ['field' => 'value'];
            }
        };

        $engine = new ManticoreEngine($client);
        $engine->update([$model]);

        $this->assertTrue(true);
    }

    public function testDelete()
    {
        $mockIndex = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['deleteDocument'])
            ->getMock()
        ;

        $mockIndex->expects($this->once())->method('deleteDocument');

        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['table'])
            ->getMock()
        ;

        $client->method('table')->willReturn($mockIndex);

        $model = new class {
            public function getKey()
            {
                return 1;
            }

            public function searchableAs()
            {
                return 'test_index';
            }
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
