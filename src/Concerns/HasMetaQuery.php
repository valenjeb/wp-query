<?php

declare(strict_types=1);

namespace Devly\WP\Query\Concerns;

use Closure;
use Devly\WP\Query\Helpers;
use Devly\WP\Query\MetaQuery;

use function call_user_func;
use function strtoupper;

trait HasMetaQuery
{
    /**
     * Custom field parameters.
     *
     * @param string|Closure(MetaQuery): void $key     Meta key name to test or a Closure
     * @param string|mixed[]|null             $value   Custom field value to test. It can be an array only
     *                                                 when compare is 'IN', 'NOT IN', 'BETWEEN', or
     *                                                 'NOT BETWEEN'. You don’t have to specify a value when
     *                                                 using the 'EXISTS' or 'NOT EXISTS' comparisons.
     * @param string                          $compare Operator to test. Possible values are ‘=’, ‘!=’, ‘>’,
     *                                                 ‘>=’, ‘<‘, ‘<=’, ‘LIKE’, ‘NOT LIKE’, ‘IN’, ‘NOT IN’,
     *                                                 ‘BETWEEN’, ‘NOT BETWEEN’, ‘EXISTS’ and ‘NOT EXISTS’.
     *                                                 Default value is ‘=’.
     * @param string                          $type    Custom field type. Possible values are ‘NUMERIC’,
     *                                                 ‘BINARY’, ‘CHAR’, ‘DATE’, ‘DATETIME’, ‘DECIMAL’,
     *                                                 ‘SIGNED’, ‘TIME’, ‘UNSIGNED’.
     *                                                 Default value is ‘CHAR’.
     */
    public function whereMetaQuery($key, $value = null, string $compare = '=', string $type = 'CHAR'): self
    {
        if (isset($this->query['meta_query']) && ! isset($this->query['meta_query']['relation'])) {
            $this->query['meta_query']['relation'] = 'AND';
        }

        if (! $key instanceof Closure) {
            $q['key'] = $key;
            if ($value !== null) {
                $q['value'] = $value;
            }

            $q['compare'] = Helpers::parseCompareOperator($compare);
            $q['type']    = strtoupper($type);

            $this->query['meta_query'][] = $q;

            return $this;
        }

        $callback  = $key;
        $metaQuery = new MetaQuery();
        call_user_func($callback, $metaQuery);

        $this->query['meta_query'][] = $metaQuery->getQueryArgs();

        return $this;
    }

    /**
     * @param string|Closure(MetaQuery): void $key     Meta key name to test or a Closure
     * @param string|mixed[]|null             $value   Custom field value to test. It can be an array only
     *                                                 when compare is 'IN', 'NOT IN', 'BETWEEN', or
     *                                                 'NOT BETWEEN'. You don’t have to specify a value when
     *                                                 using the 'EXISTS' or 'NOT EXISTS' comparisons.
     * @param string                          $compare Operator to test. Possible values are ‘=’, ‘!=’, ‘>’,
     *                                                 ‘>=’, ‘<‘, ‘<=’, ‘LIKE’, ‘NOT LIKE’, ‘IN’, ‘NOT IN’,
     *                                                 ‘BETWEEN’, ‘NOT BETWEEN’, ‘EXISTS’ and ‘NOT EXISTS’.
     *                                                 Default value is ‘=’.
     * @param string                          $type    Custom field type. Possible values are ‘NUMERIC’,
     *                                                 ‘BINARY’, ‘CHAR’, ‘DATE’, ‘DATETIME’, ‘DECIMAL’,
     *                                                 ‘SIGNED’, ‘TIME’, ‘UNSIGNED’.
     *                                                 Default value is ‘CHAR’.
     */
    public function andWhereMetaQuery($key, $value = null, string $compare = '=', string $type = 'CHAR'): self
    {
        $this->whereMetaQuery($key, $value, $compare, $type);
        $this->query['meta_query']['relation'] = 'AND';

        return $this;
    }

    /**
     * @param string|Closure(MetaQuery): void $key     Meta key name to test or a Closure
     * @param string|mixed[]|null             $value   Custom field value to test. It can be an array only
     *                                                 when compare is 'IN', 'NOT IN', 'BETWEEN', or
     *                                                 'NOT BETWEEN'. You don’t have to specify a value when
     *                                                 using the 'EXISTS' or 'NOT EXISTS' comparisons.
     * @param string                          $compare Operator to test. Possible values are ‘=’, ‘!=’, ‘>’,
     *                                                 ‘>=’, ‘<‘, ‘<=’, ‘LIKE’, ‘NOT LIKE’, ‘IN’, ‘NOT IN’,
     *                                                 ‘BETWEEN’, ‘NOT BETWEEN’, ‘EXISTS’ and ‘NOT EXISTS’.
     *                                                 Default value is ‘=’.
     * @param string                          $type    Custom field type. Possible values are ‘NUMERIC’,
     *                                                 ‘BINARY’, ‘CHAR’, ‘DATE’, ‘DATETIME’, ‘DECIMAL’,
     *                                                 ‘SIGNED’, ‘TIME’, ‘UNSIGNED’.
     *                                                 Default value is ‘CHAR’.
     */
    public function orWhereMetaQuery($key, $value = null, string $compare = '=', string $type = 'CHAR'): self
    {
        $this->whereMetaQuery($key, $value, $compare, $type);
        $this->query['meta_query']['relation'] = 'OR';

        return $this;
    }
}
