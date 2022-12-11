<?php

declare(strict_types=1);

namespace Devly\WP\Query\Tests\Unit;

use Devly\WP\Query\Builder;
use Devly\WP\Query\Concerns\HasDateQuery;
use PHPUnit\Framework\TestCase;

class DateQueryTest extends TestCase
{
    protected Builder $dateQuery;

    protected function setUp(): void
    {
        $this->dateQuery = new class extends Builder {
            use HasDateQuery;
        };
    }

    public function testWhereDateWithKeyValue(): void
    {
        $this->dateQuery->whereDate('hour', 9, '>=');
        $this->dateQuery->whereDate('hour', 17, '<=');
        $this->dateQuery->whereDate('dayofweek', [2, 6], 'between');

        $this->assertEquals([
            'date_query' => [
                [
                    'hour'      => 9,
                    'compare'   => '>=',
                ],
                [
                    'hour'      => 17,
                    'compare'   => '<=',
                ],
                [
                    'dayofweek' => [ 2, 6 ],
                    'compare'   => 'BETWEEN',
                ],
            ],
        ], $this->dateQuery->getQueryArgs());
    }

    public function testWhereDateWithArray(): void
    {
        $this->dateQuery->whereDate([
            'year'  => 2022,
            'month' => 11,
            'day'   => 3,
        ]);

        $this->assertEquals([
            'date_query' => [
                [
                    'year'  => 2022,
                    'month' => 11,
                    'day'   => 3,
                ],
            ],
        ], $this->dateQuery->getQueryArgs());
    }

    public function testWhereBetween(): void
    {
        $this->dateQuery->whereDateBetween(
            'January 1st, 2013',
            [
                'year'  => 2013,
                'month' => 2,
                'day'   => 28,
            ],
            true
        );

        $this->assertEquals([
            'date_query' => [
                [
                    'after'     => 'January 1st, 2013',
                    'before'    => [
                        'year'  => 2013,
                        'month' => 2,
                        'day'   => 28,
                    ],
                    'inclusive' => true,
                ],
            ],
        ], $this->dateQuery->getQueryArgs());
    }

    public function testWhereDateBeforeAndAfter(): void
    {
        $this->dateQuery->whereDateBefore('1 year ago', false, 'post_date_gmt');
        $this->dateQuery->whereDateAfter('1 month ago', false, 'post_modified_gmt');

        $this->assertEquals([
            'date_query' => [
                [
                    'column' => 'post_date_gmt',
                    'before' => '1 year ago',
                ],
                [
                    'column' => 'post_modified_gmt',
                    'after'  => '1 month ago',
                ],
            ],
        ], $this->dateQuery->getQueryArgs());
    }
}
