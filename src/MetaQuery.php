<?php

declare(strict_types=1);

namespace Devly\WP\Query;

use Closure;

use function call_user_func;
use function strtoupper;

class MetaQuery extends Builder
{
    /**
     * @param string|Closure(self): void          $key     Meta key name to test or a Closure
     * @param string|array<array-key, mixed>|null $value   Custom field value to test. It can be an array only
     *                                                     when compare is 'IN', 'NOT IN', 'BETWEEN', or
     *                                                     'NOT BETWEEN'. You don’t have to specify a value when
     *                                                     using the 'EXISTS' or 'NOT EXISTS' comparisons.
     * @param string                              $compare Operator to test. Possible values are ‘=’, ‘!=’, ‘>’,
     *                                                     ‘>=’, ‘<‘, ‘<=’, ‘LIKE’, ‘NOT LIKE’, ‘IN’, ‘NOT IN’,
     *                                                     ‘BETWEEN’, ‘NOT BETWEEN’, ‘EXISTS’ and ‘NOT EXISTS’.
     *                                                     Default value is ‘=’.
     * @param string                              $type    Custom field type. Possible values are ‘NUMERIC’,
     *                                                     ‘BINARY’, ‘CHAR’, ‘DATE’, ‘DATETIME’, ‘DECIMAL’,
     *                                                     ‘SIGNED’, ‘TIME’, ‘UNSIGNED’.
     *                                                     Default value is ‘CHAR’.
     */
    public function where($key, $value = null, string $compare = '=', string $type = 'CHAR'): self
    {
        if (isset($this->query) && ! isset($this->query['relation'])) {
            $this->query['relation'] = 'AND';
        }

        if (! $key instanceof Closure) {
            $q['key'] = $key;
            if ($value !== null) {
                $q['value'] = $value;
            }

            $q['compare'] = Helpers::parseCompareOperator($compare);
            $q['type']    = strtoupper($type);

            $this->query[] = $q;

            return $this;
        }

        $callback  = $key;
        $metaQuery = new self();
        call_user_func($callback, $metaQuery);

        $this->query[] = $metaQuery->getQueryArgs();

        return $this;
    }

    /**
     * @param string|Closure(self): void          $key     Meta key name to test or a Closure
     * @param string|array<array-key, mixed>|null $value   Custom field value to test. It can be an array only
     *                                                     when compare is 'IN', 'NOT IN', 'BETWEEN', or
     *                                                     'NOT BETWEEN'. You don’t have to specify a value when
     *                                                     using the 'EXISTS' or 'NOT EXISTS' comparisons.
     * @param string                              $compare Operator to test. Possible values are ‘=’, ‘!=’, ‘>’,
     *                                                     ‘>=’, ‘<‘, ‘<=’, ‘LIKE’, ‘NOT LIKE’, ‘IN’, ‘NOT IN’,
     *                                                     ‘BETWEEN’, ‘NOT BETWEEN’, ‘EXISTS’ and ‘NOT EXISTS’.
     *                                                     Default value is ‘=’.
     * @param string                              $type    Custom field type. Possible values are ‘NUMERIC’,
     *                                                     ‘BINARY’, ‘CHAR’, ‘DATE’, ‘DATETIME’, ‘DECIMAL’,
     *                                                     ‘SIGNED’, ‘TIME’, ‘UNSIGNED’.
     *                                                     Default value is ‘CHAR’.
     */
    public function andWhere($key, $value = null, string $compare = '=', string $type = 'CHAR'): self
    {
        $this->where($key, $value, $compare, $type);
        if (! isset($this->query['relation']) || $this->query['relation'] !== 'AND') {
            $this->query['relation'] = 'AND';
        }

        return $this;
    }

    /**
     * @param string|Closure(self): void          $key     Meta key name to test or a Closure
     * @param string|array<array-key, mixed>|null $value   Custom field value to test. It can be an array only
     *                                                     when compare is 'IN', 'NOT IN', 'BETWEEN', or
     *                                                     'NOT BETWEEN'. You don’t have to specify a value when
     *                                                     using the 'EXISTS' or 'NOT EXISTS' comparisons.
     * @param string                              $compare Operator to test. Possible values are ‘=’, ‘!=’, ‘>’,
     *                                                     ‘>=’, ‘<‘, ‘<=’, ‘LIKE’, ‘NOT LIKE’, ‘IN’, ‘NOT IN’,
     *                                                     ‘BETWEEN’, ‘NOT BETWEEN’, ‘EXISTS’ and ‘NOT EXISTS’.
     *                                                     Default value is ‘=’.
     * @param string                              $type    Custom field type. Possible values are ‘NUMERIC’,
     *                                                     ‘BINARY’, ‘CHAR’, ‘DATE’, ‘DATETIME’, ‘DECIMAL’,
     *                                                     ‘SIGNED’, ‘TIME’, ‘UNSIGNED’.
     *                                                     Default value is ‘CHAR’.
     */
    public function orWhere($key, $value = null, string $compare = '=', string $type = 'CHAR'): self
    {
        $this->where($key, $value, $compare, $type);
        if (! isset($this->query['relation']) || $this->query['relation'] !== 'OR') {
            $this->query['relation'] = 'OR';
        }

        return $this;
    }
}
