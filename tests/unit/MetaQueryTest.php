<?php

declare(strict_types=1);

namespace Devly\WP\Query\Tests;

use Devly\WP\Query\Builder;
use Devly\WP\Query\Concerns\HasMetaQuery;
use Devly\WP\Query\MetaQuery;
use PHPUnit\Framework\TestCase;

class MetaQueryTest extends TestCase
{
    protected Builder $builder;

    protected function setUp(): void
    {
        $this->builder = new class extends Builder {
            use HasMetaQuery;
        };
    }

    public function testWithSimpleQuery(): void
    {
        $this->builder->whereMetaQuery('color', 'blue', '!like');
        $this->builder->orWhereMetaQuery('price', [20, 100], 'between', 'numeric');

        $this->assertEquals([
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key'     => 'color',
                    'value'   => 'blue',
                    'compare' => 'NOT LIKE',
                    'type' => 'CHAR',
                ],
                [
                    'key'     => 'price',
                    'value'   => [20, 100],
                    'type'    => 'NUMERIC',
                    'compare' => 'BETWEEN',
                ],
            ],
        ], $this->builder->getQueryArgs());
    }

    public function testWithNestedQuery(): void
    {
        $this->builder->whereMetaQuery('color', 'orange');
        $this->builder->orWhereMetaQuery(static function (MetaQuery $query): void {
            $query->where('color', 'red');
            $query->andWhere('size', 'small');
        });

        $this->assertEquals([
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key'     => 'color',
                    'value'   => 'orange',
                    'compare' => '=',
                    'type' => 'CHAR',
                ],
                [
                    'relation' => 'AND',
                    [
                        'key'     => 'color',
                        'value'   => 'red',
                        'compare' => '=',
                        'type'    => 'CHAR',
                    ],
                    [
                        'key'     => 'size',
                        'value'   => 'small',
                        'compare' => '=',
                        'type'    => 'CHAR',
                    ],
                ],
            ],
        ], $this->builder->getQueryArgs());
    }
}
