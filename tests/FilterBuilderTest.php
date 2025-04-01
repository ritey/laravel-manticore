<?php

use PHPUnit\Framework\TestCase;
use Ritey\LaravelManticore\FilterBuilder;

/**
 * @internal
 *
 * @coversNothing
 */
class FilterBuilderTest extends TestCase
{
    public function testWhereEquals()
    {
        $builder = (new FilterBuilder())->where('field', 'value');
        $filters = $builder->get();

        $this->assertEquals([['equals' => ['field' => 'value']]], $filters);
    }

    public function testWhereIn()
    {
        $builder = (new FilterBuilder())->whereIn('field', ['a', 'b']);
        $filters = $builder->get();

        $this->assertEquals([['in' => ['field' => ['a', 'b']]]], $filters);
    }

    public function testWhereNot()
    {
        $builder = (new FilterBuilder())->whereNot('status', 'inactive');
        $filters = $builder->get();

        $this->assertEquals([['not' => ['equals' => ['status' => 'inactive']]]], $filters);
    }

    public function testWhereRange()
    {
        $builder = (new FilterBuilder())->whereRange('score', ['gte' => 0.5]);
        $filters = $builder->get();

        $this->assertEquals([['range' => ['score' => ['gte' => 0.5]]]], $filters);
    }
}
