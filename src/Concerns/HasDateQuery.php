<?php

declare(strict_types=1);

namespace Devly\WP\Query\Concerns;

use Closure;
use Devly\WP\Query\DateQuery;
use Devly\WP\Query\Helpers;

use function call_user_func;
use function is_array;
use function strtoupper;

trait HasDateQuery
{
    /**
     * Set the date query parameters.
     *
     * Note that this method will overwrite the existing date query parameters.
     *
     * @param array<array<string, mixed>> $query
     *
     * @return static
     */
    protected function dateQuery(array $query): self
    {
        $this->set('date_query', $query);

        return $this;
    }

    /**
     * @param string|array<string, mixed>|Closure(DateQuery): void $key
     * @param mixed                                                $value     Date to test.
     * @param string|null                                          $compare   Operator to test. Possible values are
     *                                                                        '=', '!=', '>', '>=', '<', '<=', 'IN',
     *                                                                        'NOT IN', 'BETWEEN', 'NOT BETWEEN'.
     * @param bool                                                 $inclusive Whether exact value should be
     *                                                                        matched or not
     * @param string|null                                          $column    Table column to query against.
     *                                                                        Default set to match the ‘post_date’
     *                                                                        column.
     *
     * @return static
     */
    public function whereDate(
        $key,
        $value = null,
        ?string $compare = null,
        bool $inclusive = false,
        ?string $column = null
    ): self {
        if ($key instanceof Closure) {
            $dateQuery = new DateQuery();

            call_user_func($key, $dateQuery);
            $dateQuery = $dateQuery->getQueryArgs();
        } elseif (is_array($key)) {
            $dateQuery = $key;
        } else {
            $dateQuery = [$key => $value];
            if (! empty($compare)) {
                $dateQuery['compare'] = Helpers::parseCompareOperator($compare);
            }

            if (! empty($inclusive)) {
                $dateQuery['inclusive'] = $inclusive;
            }

            if (! empty($column)) {
                $dateQuery['column'] = $column;
            }
        }

        $query = $this->getKey('date_query', []);

        $query[] = $dateQuery;

        return $this->dateQuery($query);
    }

    /**
     * @param string|array<string, mixed>|Closure(DateQuery): void $key
     * @param mixed                                                $value     Date to test.
     * @param string|null                                          $compare   Operator to test. Possible values are
     *                                                                        '=', '!=', '>', '>=', '<', '<=', 'IN',
     *                                                                        'NOT IN', 'BETWEEN', 'NOT BETWEEN'.
     * @param bool                                                 $inclusive Whether exact value should be
     *                                                                        matched or not
     * @param string|null                                          $column    Table column to query against.
     *                                                                        Default set to match the ‘post_date’
     *                                                                        column.
     *
     * @return static
     */
    public function orWhereDate(
        $key,
        $value = null,
        ?string $compare = null,
        bool $inclusive = false,
        ?string $column = null
    ): self {
        $this->dateQueryRelation('or');

        return $this->whereDate($key, $value, $compare, $inclusive, $column);
    }

    /** @return static */
    public function dateQueryRelation(string $relation): self
    {
        $query = $this->getKey('date_query', []);

        $query['relation'] = strtoupper($relation);

        return $this->set('date_query', $query);
    }

    /**
     * @param string|array{yaer?: int, month?: int, day?: int} $date      Date to retrieve posts before. Accepts
     *                                                                    strtotime()-compatible string, or array
     *                                                                    of ‘year’, ‘month’, ‘day’.
     * @param bool                                             $inclusive Whether exact value should be
     *                                                                    matched or not
     * @param string|null                                      $column    Table column to query against.
     *                                                                    Default set to match the ‘post_date’
     *                                                                    column.
     *
     * @return static
     */
    public function whereDateBefore($date, bool $inclusive = false, ?string $column = null): self
    {
        return $this->whereDate('before', $date, null, $inclusive, $column);
    }

    /**
     * @param string|array{yaer?: int, month?: int, day?: int} $date      Date to retrieve posts before. Accepts
     *                                                                    strtotime()-compatible string, or array
     *                                                                    of ‘year’, ‘month’, ‘day’.
     * @param bool                                             $inclusive Whether exact value should be
     *                                                                    matched or not
     * @param string|null                                      $column    Table column to query against.
     *                                                                    Default set to match the ‘post_date’
     *                                                                    column.
     *
     * @return static
     */
    public function orWhereDateBefore($date, bool $inclusive = false, ?string $column = null): self
    {
        $this->dateQueryRelation('or');

        return $this->whereDateBefore($date, $inclusive, $column);
    }

    /**
     * @param string|array{yaer?: int, month?: int, day?: int} $date      Date to retrieve posts after. Accepts
     *                                                                    strtotime()-compatible string, or array
     *                                                                    of ‘year’, ‘month’, ‘day’.
     * @param bool                                             $inclusive Whether exact value should be
     *                                                                    matched or not
     * @param string|null                                      $column    Table column to query against.
     *                                                                    Default set to match the ‘post_date’
     *                                                                    column.
     *
     * @return static
     */
    public function whereDateAfter($date, bool $inclusive = false, ?string $column = null): self
    {
        return $this->whereDate('after', $date, null, $inclusive, $column);
    }

    /**
     * @param string|array{yaer?: int, month?: int, day?: int} $date      Date to retrieve posts after. Accepts
     *                                                                    strtotime()-compatible string, or array
     *                                                                    of ‘year’, ‘month’, ‘day’.
     * @param bool                                             $inclusive Whether exact value should be
     *                                                                    matched or not
     * @param string|null                                      $column    Table column to query against.
     *                                                                    Default set to match the ‘post_date’
     *                                                                    column.
     *
     * @return static
     */
    public function orWhereDateAfter($date, bool $inclusive = false, ?string $column = null): self
    {
        $this->dateQueryRelation('or');

        return $this->whereDateAfter($date, $inclusive, $column);
    }

    /**
     * @param string|array{yaer: int, month: int, day: int} $after     Date to retrieve posts after. Accepts
     *                                                                 strtotime()-compatible string, or array
     *                                                                 of ‘year’, ‘month’, ‘day’.
     * @param string|array{yaer: int, month: int, day: int} $before    Date to retrieve posts before. Accepts
     *                                                                 strtotime()-compatible string, or array
     *                                                                 of ‘year’, ‘month’, ‘day’.
     * @param bool                                          $inclusive Whether exact value should be
     *                                                                 matched or not
     * @param string|null                                   $column    Table column to query against.
     *                                                                 Default set to match the ‘post_date’
     *                                                                 column.
     *
     * @return static
     */
    public function whereDateBetween($after, $before, bool $inclusive = false, ?string $column = null): self
    {
        $query = [
            'before' => $before,
            'after' => $after,
        ];

        if ($inclusive) {
            $query['inclusive'] = $inclusive;
        }

        if (! empty($column)) {
            $query['column'] = $column;
        }

        return $this->whereDate($query);
    }

    /**
     * @param string|array{yaer: int, month: int, day: int} $after     Date to retrieve posts after. Accepts
     *                                                                 strtotime()-compatible string, or array
     *                                                                 of ‘year’, ‘month’, ‘day’.
     * @param string|array{yaer: int, month: int, day: int} $before    Date to retrieve posts before. Accepts
     *                                                                 strtotime()-compatible string, or array
     *                                                                 of ‘year’, ‘month’, ‘day’.
     * @param bool                                          $inclusive Whether exact value should be
     *                                                                 matched or not
     * @param string|null                                   $column    Table column to query against.
     *                                                                 Default set to match the ‘post_date’
     *                                                                 column.
     *
     * @return static
     */
    public function orWhereDateBetween($after, $before, bool $inclusive = false, ?string $column = null): self
    {
        $this->dateQueryRelation('or');

        return $this->whereDateBetween($after, $before, $inclusive, $column);
    }
}
