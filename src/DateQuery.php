<?php

declare(strict_types=1);

namespace Devly\WP\Query;

use Closure;

use function call_user_func;
use function is_array;

class DateQuery extends Builder
{
    /**
     * @param string|Closure(self): void|array<string, string|int> $key       Meta key name to test or a Closure
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
    public function where(
        $key,
        $value = null,
        ?string $compare = null,
        bool $inclusive = false,
        ?string $column = null
    ): self {
        if ($key instanceof Closure) {
            $dateQuery = new self();

            call_user_func($key, $dateQuery);
            $this->query[] = $dateQuery->getQueryArgs();

            return $this;
        }

        if (is_array($key)) {
            $this->query[] = $key;

            return $this;
        }

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

        $this->query[] = $dateQuery;

        return $this;
    }

    /**
     * @param string|Closure(self): void|array<string, string|int> $key       Meta key name to test or a Closure
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
    public function orWhere(
        $key,
        $value = null,
        ?string $compare = null,
        bool $inclusive = false,
        ?string $column = null
    ): self {
        $this->query['relation'] = 'OR';

        return $this->where($key, $value, $compare, $inclusive, $column);
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
    public function whereBefore($date, bool $inclusive = false, ?string $column = null): self
    {
        return $this->where('before', $date, null, $inclusive, $column);
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
    public function orWhereBefore($date, bool $inclusive = false, ?string $column = null): self
    {
        return $this->orWhere('before', $date, null, $inclusive, $column);
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
    public function whereAfter($date, bool $inclusive = false, ?string $column = null): self
    {
        return $this->where('after', $date, null, $inclusive, $column);
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
    public function orWhereAfter($date, bool $inclusive = false, ?string $column = null): self
    {
        return $this->where('after', $date, null, $inclusive, $column);
    }
}
